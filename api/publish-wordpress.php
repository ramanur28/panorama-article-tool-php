<?php
/**
 * WordPress Publish & Schedule API Endpoint
 * Publishes or schedules article to WordPress REST API and updates MySQL tracking
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/ai_service.php';
require_once __DIR__ . '/../includes/wordpress_service.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    Auth::requireAuth();
    $pdo = Database::getConnection();

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $articleId = $input['articleId'] ?? null;
    $queueId = $input['queueId'] ?? null;
    $title = trim($input['title'] ?? '');
    $keyphrase = trim($input['keyphrase'] ?? '');
    $action = $input['action'] ?? 'publish';
    $rawDate = !empty($input['date']) ? $input['date'] : date('Y-m-d H:i:s');
    $targetDate = substr($rawDate, 0, 10);
    $content = $input['content'] ?? '';
    $images = $input['images'] ?? [];
    $wpCredentials = $input['wpCredentials'] ?? null;

    // Find article record in database if articleId, title, or keyphrase passed
    $existingArticle = null;
    if ($articleId) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $existingArticle = $stmt->fetch();
    }
    // High-precision matching: Same title AND keyphrase
    if (!$existingArticle && !empty($title) && !empty($keyphrase)) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE LOWER(TRIM(title)) = LOWER(TRIM(?)) AND LOWER(TRIM(keyphrase)) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([$title, $keyphrase]);
        $existingArticle = $stmt->fetch();
    }
    if (!$existingArticle && !empty($title)) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE LOWER(TRIM(title)) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([$title]);
        $existingArticle = $stmt->fetch();
    }
    if (!$existingArticle && !empty($keyphrase)) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE LOWER(TRIM(keyphrase)) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([$keyphrase]);
        $existingArticle = $stmt->fetch();
    }

    // Resolve target wp_post_id and target link for update
    $targetWpPostId = $input['wpPostId'] ?? ($existingArticle['wp_post_id'] ?? null);
    $targetLink = $input['link'] ?? ($existingArticle['link'] ?? null);

    // Resolve content and images if not passed directly
    if (empty($content) && $existingArticle && !empty($existingArticle['article_content'])) {
        $content = $existingArticle['article_content'];
    }
    if (empty($images) && $existingArticle && !empty($existingArticle['images_json'])) {
        $images = json_decode($existingArticle['images_json'], true) ?: [];
    }

    // Post or Update WordPress
    $wpResult = WordPressService::postToWordPress([
        'url' => $wpCredentials['url'] ?? null,
        'username' => $wpCredentials['username'] ?? null,
        'password' => $wpCredentials['password'] ?? null,
        'wpPostId' => $targetWpPostId,
        'link' => $targetLink,
        'title' => $title ?: ($existingArticle['title'] ?? 'New Article'),
        'content' => $content,
        'action' => $action,
        'date' => $rawDate,
        'images' => $images
    ]);

    $wpLink = $wpResult['wpPost']['link'] ?? ($targetLink ?: '');
    $wpPostId = $wpResult['wpPost']['id'] ?? $targetWpPostId;
    $newStatus = ($action === 'publish' || $action === 'update') ? 'telah_dibuat' : 'dijadwalkan';
    $schedDate = ($action === 'schedule') ? $rawDate : null;
    $pubDate = ($action === 'schedule') ? null : ($existingArticle['published_date'] ?? $rawDate);

    // Update existing article in MySQL - replace entire content with new generated content
    if ($existingArticle) {
        $up = $pdo->prepare("UPDATE articles SET 
            status = ?, 
            link = COALESCE(NULLIF(?, ''), link), 
            scheduled_date = ?, 
            published_date = ?, 
            wp_post_id = COALESCE(?, wp_post_id),
            article_content = ?,
            images_json = COALESCE(?, images_json),
            updated_at = NOW()
            WHERE id = ?");
        $up->execute([$newStatus, $wpLink, $schedDate, $pubDate, $wpPostId, $content, !empty($images) ? json_encode($images) : null, $existingArticle['id']]);
        $artId = $existingArticle['id'];
    } else {
        // Insert new article record
        $ins = $pdo->prepare("INSERT INTO articles (page_role, keyphrase, title, link, status, scheduled_date, published_date, article_content, images_json, wp_post_id) 
                              VALUES ('Cluster', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $keyphrase ?: $title,
            $title ?: $keyphrase,
            $wpLink,
            $newStatus,
            $schedDate,
            $pubDate,
            $content,
            !empty($images) ? json_encode($images) : null,
            $wpPostId
        ]);
        $artId = (int)$pdo->lastInsertId();
    }

    // Dynamically update generation queue status to match article status (telah_dibuat or dijadwalkan)
    $qMsg = ($action === 'update') ? 'Artikel telah diperbarui di WordPress & database' : (($action === 'publish') ? 'Published to WordPress' : 'Scheduled in WordPress');
    $qUp = $pdo->prepare("UPDATE generation_queue SET 
        status = ?, 
        article_content = ?, 
        progress = 100, 
        progress_message = ?, 
        updated_at = NOW() 
        WHERE id = ? 
           OR id = ? 
           OR id = ?
           OR JSON_UNQUOTE(JSON_EXTRACT(input_params, '$.managerId')) = ?
           OR JSON_UNQUOTE(JSON_EXTRACT(input_params, '$.managerId')) = ?
           OR (LOWER(TRIM(title)) = LOWER(?) AND LOWER(TRIM(keyphrase)) = LOWER(?))");
    $qUp->execute([$newStatus, $content, $qMsg, (string)$queueId, (string)$articleId, (string)$artId, (string)$articleId, (string)$artId, trim($title), trim($keyphrase)]);

    $isSuccess = !empty($wpResult['success']);
    if (!$isSuccess) {
        http_response_code(400);
    }

    echo json_encode([
        'success' => $isSuccess,
        'isUpdated' => ($action === 'update' || !empty($wpResult['isUpdated'])),
        'wpSynced' => $isSuccess,
        'wpLink' => $wpLink,
        'wpPostId' => $wpPostId,
        'message' => ($action === 'update') 
            ? "Seluruh isi artikel yang telah dipublish berhasil diganti dengan konten baru!" 
            : (($action === 'publish') ? "Artikel berhasil dipublish ke WordPress!" : "Artikel berhasil dijadwalkan di WordPress!"),
        'error' => $wpResult['error'] ?? null,
        'wpError' => $wpResult['error'] ?? null,
        'item' => [
            'id' => $artId,
            'title' => $title,
            'status' => $newStatus,
            'scheduledDate' => $schedDate,
            'publishedDate' => $pubDate,
            'link' => $wpLink,
            'wpPostId' => $wpPostId
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

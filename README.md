# Panorama Lens Trip — Article Tool (PHP & MySQL Edition)

[![PHP Version](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-5.7+%20|%20MariaDB%2010.3+-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Web Server](https://img.shields.io/badge/Apache-mod__rewrite-D22128?logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/License-Proprietary-gray.svg)](#license)
[![Zero Build Steps](https://img.shields.io/badge/Node.js-Not%20Required-success.svg)](https://nodejs.org/)

An automated, SEO-optimized AI article generator and content management platform built with **pure PHP, vanilla JavaScript, and MySQL**. 

Specially engineered to operate 100% reliably on standard **Shared Hosting environments (cPanel, DirectAdmin, CyberPanel)** and **local development setups** without requiring Node.js, PM2, Docker, or build compilation steps.

---

## 🌟 Key Features

- 🤖 **Multi-Provider AI Generation**: Powered by **Google Gemini API** (with OpenAI GPT fallback compatibility) to produce comprehensive, SEO-optimized 2,000+ word long-form articles.
- ⚡ **Real-Time Streaming Output**: Live streaming of generated text directly into the web editor using **Server-Sent Events (SSE)**.
- 📝 **Interactive Markdown Editor**: Side-by-side editing and preview with section regeneration, real-time word counting, and fast Markdown rendering.
- 🔄 **WordPress REST API Integration**:
  - 1-click publishing as drafts or published posts.
  - Scheduled publication support.
  - Automatic synchronization of categories, tags, and publishing statuses.
- 📋 **Batch Processing Queue**: Queue multiple article topics and keywords for sequential, unattended generation.
- 🖼️ **Image & Media Management**:
  - Integrated local image uploader.
  - AI-assisted image metadata and alt text generation.
- 🔗 **Internal Link Injector**: Smart tool to insert relevant internal anchors and call-to-actions into generated articles.
- 👥 **Role-Based Access Control**:
  - Secure session-based authentication with bcrypt password hashing.
  - Dedicated access roles for **Administrator** and **Standard User**.
- 🌓 **Modern Responsive Interface**: Clean design system with support for Dark and Light modes.

---

## 🏗️ Technology Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 7.4 – 8.3 (Modular Object-Oriented Architecture, PDO, cURL) |
| **Frontend** | Vanilla HTML5, Modern CSS Variables, Vanilla ES6 JavaScript, [Marked.js](https://marked.js.org/) |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ (InnoDB, UTF8mb4) |
| **Server** | Apache (`mod_rewrite` via `.htaccess`) or PHP Built-in Server (`router.php`) |
| **External APIs** | Google Gemini REST API, OpenAI API, WordPress REST API |

---

## 📁 Directory Structure

```text
Panorama-article-php/
├── api/                       # REST API endpoints
│   ├── articles.php           # CRUD operations for articles
│   ├── auth.php               # Login, session validation, password management
│   ├── generate.php           # AI article generation & streaming engine (SSE)
│   ├── generate-image-meta.php# AI image alt & caption generator
│   ├── insert-link.php        # Internal link injector helper
│   ├── publish-wordpress.php  # WordPress publishing & scheduling handler
│   ├── queue.php              # Batch processing queue manager
│   ├── settings.php           # Global settings & API credentials
│   ├── sync-wp.php            # WordPress category & post synchronization
│   ├── update-section.php     # Section regeneration endpoint
│   └── upload.php             # Image & media upload processor
├── assets/                    # Static assets
│   ├── css/style.css          # Core application styling & themes
│   └── js/                    # Client-side scripts
│       ├── app.js             # Main SPA logic & UI handlers
│       └── marked.min.js      # Markdown parsing engine
├── includes/                  # Core backend helper classes & services
│   ├── ai_service.php         # Gemini / OpenAI API client
│   ├── auth_middleware.php    # Session authentication & authorization guard
│   ├── db.php                 # PDO database singleton connection
│   ├── markdown_helper.php    # Markdown formatting utilities
│   ├── sse_helper.php         # Server-Sent Events output buffer handler
│   └── wordpress_service.php  # WordPress REST API client
├── uploads/                   # User-uploaded images & media
├── .htaccess                  # Apache URL rewrite rules & security policies
├── .gitignore                 # Git ignore rules for configs, dumps, and media
├── config.example.php         # Configuration template
├── config.php                 # Local runtime configuration (Ignored by Git)
├── database.example.sql       # Initial database schema & seed data
├── index.php                  # Application entry point & web interface
├── migrate.php                # Database migration utility
├── README.md                  # Main GitHub documentation
├── README_SHARED_HOSTING.md   # Deployment guide for cPanel / Shared Hosting (Indonesian)
└── router.php                 # Router for PHP local development server
```

---

## 📋 System Requirements

- **PHP Version**: `7.4`, `8.0`, `8.1`, `8.2`, or `8.3`
- **Required PHP Extensions**:
  - `pdo_mysql` (Database connectivity)
  - `curl` (AI services and WordPress REST communication)
  - `json` & `mbstring` (Data encoding and Unicode support)
  - `fileinfo` & `gd` (Media and image processing)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache with `mod_rewrite` enabled, or standard PHP CLI for local testing.

---

## 🚀 Getting Started (Local Development)

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Panorama-article-php
```

### 2. Configure Environment

Copy the example configuration to create your local `config.php`:

```bash
cp config.example.php config.php
```

Open `config.php` and set your local MySQL database connection details:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'panorama_articles');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
define('DB_PORT', '3306');
```

### 3. Initialize the Database

Create the database in MySQL, then import `database.example.sql`:

```bash
# Create database
mysql -u your_mysql_user -p -e "CREATE DATABASE panorama_articles CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema and seed data
mysql -u your_mysql_user -p panorama_articles < database.example.sql
```

> [!NOTE]
> `database.example.sql` includes the default database tables, initial settings schema, and sample administrative accounts.

### 4. Ensure Write Permissions for Uploads

```bash
chmod -R 755 uploads
```

### 5. Launch the Built-in Development Server

Run the development server using the bundled `router.php`:

```bash
php -S 0.0.0.0:8088 router.php
```

Navigate to `http://localhost:8088` in your web browser.

---

## 🔐 Default Credentials

| Role | Username | Password |
|---|---|---|
| **Administrator** | `admin` | `admin123` |
| **Standard User** | `user` | `user123` |

> [!WARNING]
> Change the default credentials immediately after your first login via the user profile modal in the top right corner.

---

## ⚙️ Configuration & API Setup

Once logged in as an **Administrator**, open the **Settings** modal (gear icon in the header) to configure your integration keys:

1. **Google Gemini API Key**:
   - Obtain an API key from [Google AI Studio](https://aistudio.google.com/apikey).
   - Enter your key and choose your preferred model (e.g. `gemini-1.5-flash`, `gemini-1.5-pro`).
2. **OpenAI API Key** *(Optional)*:
   - Provide if utilizing GPT-based models.
3. **WordPress REST API**:
   - **WordPress Site URL**: Your WordPress website URL (e.g. `https://panoramalenstrip.com`).
   - **WordPress Username**: The WordPress admin/editor username.
   - **WordPress Application Password**: Generate an Application Password under **WordPress Admin > Users > Profile > Application Passwords** (format: `xxxx xxxx xxxx xxxx`).
   - Click **Test WordPress Connection** to confirm connectivity.

---

## 🌐 Shared Hosting / cPanel Deployment

Deploying on cPanel, DirectAdmin, or CyberPanel takes just a few steps:

1. Create a MySQL database and user in your hosting control panel.
2. Import `database.example.sql` via **phpMyAdmin**.
3. Upload all files into your `public_html` (or subdomain) directory.
4. Rename `config.example.php` to `config.php` and fill in your hosting database credentials.
5. Verify that the `uploads/` directory has `755` write permission.
6. Ensure `.htaccess` is present in the root folder so API routing works properly.

> [!TIP]
> For a detailed, step-by-step cPanel guide written in Indonesian, see [README_SHARED_HOSTING.md](README_SHARED_HOSTING.md).

---

## 🛠️ API Reference Overview

All API endpoints return JSON payloads and require active session cookies (except `/api/auth/login`).

| Endpoint | Method | Description |
|---|---|---|
| `/api/auth/login` | `POST` | Authenticate user and initiate session |
| `/api/auth/logout` | `POST` | Invalidate current session |
| `/api/auth/me` | `GET` | Get authenticated user info & role |
| `/api/auth/change-password` | `POST` | Update password for current user |
| `/api/articles` | `GET` / `POST` | List articles or save new article |
| `/api/articles/{id}` | `GET` / `PUT` / `DELETE`| Retrieve, update, or remove an article |
| `/api/articles/import-csv` | `POST` | Bulk import topics from CSV |
| `/api/articles/export-csv` | `GET` | Export articles list to CSV format |
| `/api/generate` | `POST` | Trigger AI generation stream (SSE) |
| `/api/update-section` | `POST` | Regenerate specific section of an article |
| `/api/insert-link` | `POST` | Inject internal contextual links |
| `/api/generate-image-meta` | `POST` | Generate SEO image metadata |
| `/api/queue` | `GET` / `POST` | View or push jobs to generation queue |
| `/api/queue/{id}` | `DELETE` | Cancel or remove a queued job |
| `/api/upload` | `POST` | Upload images to local storage |
| `/api/publish-wordpress` | `POST` | Publish or schedule article to WordPress |
| `/api/sync-wp` | `GET` | Fetch categories and tags from WordPress |
| `/api/settings` | `GET` / `POST` | Retrieve or update application settings *(Admin only)* |

---

## 🛡️ Security & Performance Tips

- **Execution Timeout**: For generating 2,000+ word articles, configure `max_execution_time` in your `php.ini` to at least `180` seconds.
- **Upload Limits**: Recommended `upload_max_filesize = 16M` and `post_max_size = 32M` for media uploads.
- **Proxy Buffering**: If using Cloudflare or Nginx reverse proxies, ensure proxy buffering is disabled for Server-Sent Events to enable real-time text streaming.
- **Environment Isolation**: `config.php`, `.env`, and raw database dumps are excluded in `.gitignore` by default to prevent leaking credentials.

---

## 📄 License

This software is proprietary and confidential. Unauthorized copying, distribution, or modification of this project, via any medium, is strictly prohibited.
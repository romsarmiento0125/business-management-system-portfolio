# 1 Blend Feeds — Full Project Documentation

> **Copy-pastable format** — All sections below are self-contained and can be copied into any word processor, wiki, or documentation system.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Requirements](#2-system-requirements)
3. [Technology Stack](#3-technology-stack)
4. [Architecture Overview](#4-architecture-overview)
5. [Directory Structure](#5-directory-structure)
6. [Installation & Setup](#6-installation--setup)
7. [Environment Configuration](#7-environment-configuration)
8. [Database Configuration](#8-database-configuration)
9. [Running the Application](#9-running-the-application)
10. [Running with Docker](#10-running-with-docker)
11. [Application Modules](#11-application-modules)
12. [Routes & API Reference](#12-routes--api-reference)
13. [Controllers Reference](#13-controllers-reference)
14. [Models Reference](#14-models-reference)
15. [Views & Frontend](#15-views--frontend)
16. [Authentication & Authorization](#16-authentication--authorization)
17. [Security Features](#17-security-features)
18. [Audit Logging](#18-audit-logging)
19. [Testing](#19-testing)
20. [Deployment](#20-deployment)
21. [CI/CD Pipeline](#21-cicd-pipeline)
22. [Troubleshooting](#22-troubleshooting)
23. [Glossary](#23-glossary)
24. [License](#24-license)

---

## 1. Project Overview

| Field              | Details                                           |
|--------------------|---------------------------------------------------|
| **Project Name**   | 1 Blend Feeds Business Management System          |
| **Repository**     | number1                                           |
| **Framework**      | CodeIgniter 4                                     |
| **Language**       | PHP 8.2                                           |
| **License**        | MIT                                               |
| **Current Version**| 1.0                                               |

### 1.1 Purpose

1 Blend Feeds is a full-stack web-based business management application tailored for a feed/food distribution company. It centralizes the management of:

- **Sales Invoices** — Create, draft, print, and manage customer invoices.
- **Delivery Receipts** — Track and document product deliveries.
- **Products** — Maintain a product catalog with cost tracking.
- **Clients** — Manage customer and client records.
- **Users & Roles** — Control who has access and what they can do.
- **Accounting & Reports** — Generate financial summaries, volume analytics, and export data.
- **SI/DR Payment Dashboard** — Monitor paid and unpaid invoices and delivery receipts.
- **Dynamic Filters** — Save and reuse custom search filters for clients and products.

### 1.2 Key Highlights

- Secure session-based authentication with role-based access control.
- Full audit logging of user actions.
- Printable invoice and delivery receipt generation.
- Export functionality for accounting totals.
- Containerized deployment via Docker.
- CI/CD pipeline using GitHub Actions.

---

## 2. System Requirements

### 2.1 Server / Hosting

| Component       | Minimum Requirement                  |
|-----------------|--------------------------------------|
| PHP             | 8.1 or higher (8.2 recommended)      |
| Web Server      | Nginx or Apache                      |
| Database        | MySQL 5.7+ or MariaDB 10.3+          |
| Composer        | 2.x                                  |
| Disk Space      | At least 500 MB (including assets)   |

### 2.2 Required PHP Extensions

| Extension  | Purpose                                       |
|------------|-----------------------------------------------|
| `intl`     | Internationalization support                  |
| `mbstring` | Multi-byte string handling                    |
| `json`     | JSON encoding/decoding (enabled by default)   |
| `mysqlnd`  | MySQL native driver                           |
| `libcurl`  | HTTP client requests                          |
| `gd`       | Image processing                              |
| `pdo_mysql`| PDO MySQL driver                              |
| `mysqli`   | MySQLi driver                                 |
| `xml`      | XML processing                                |
| `bcmath`   | Arbitrary precision math                      |
| `zip`      | ZIP compression                               |

### 2.3 Client / Browser

| Browser         | Supported Version |
|-----------------|-------------------|
| Google Chrome   | Latest            |
| Mozilla Firefox | Latest            |
| Microsoft Edge  | Latest            |
| Safari          | Latest            |

> **Note:** The application uses Bootstrap 4, jQuery 3.7.1, and AdminLTE 3. JavaScript must be enabled in the browser.

---

## 3. Technology Stack

### 3.1 Backend

| Technology         | Version | Role                              |
|--------------------|---------|-----------------------------------|
| PHP                | 8.2     | Server-side language              |
| CodeIgniter 4      | ^4.0    | MVC web framework                 |
| MySQL / MariaDB    | 5.7+    | Relational database               |
| Nginx              | Latest  | Web server / reverse proxy        |
| PHP-FPM            | 8.2     | PHP FastCGI process manager       |
| Supervisor         | Latest  | Process manager inside Docker     |

### 3.2 Frontend

| Technology        | Version | Role                                  |
|-------------------|---------|---------------------------------------|
| Bootstrap         | 4       | Responsive CSS framework              |
| AdminLTE          | 3       | Admin dashboard template              |
| jQuery            | 3.7.1   | DOM manipulation and AJAX             |
| DataTables        | Latest  | Interactive data table plugin         |
| Font Awesome      | Latest  | Icon library                          |
| Select2           | Latest  | Enhanced dropdown selects             |
| DateRangePicker   | Latest  | Date range input widget               |

### 3.3 DevOps & Infrastructure

| Technology        | Role                             |
|-------------------|----------------------------------|
| Docker            | Application containerization     |
| Docker Compose    | Multi-container orchestration    |
| GitHub Actions    | CI/CD pipeline automation        |
| Composer          | PHP dependency management        |
| PHPUnit           | Unit and integration testing      |

---

## 4. Architecture Overview

### 4.1 MVC Pattern

The application follows the **Model-View-Controller (MVC)** architecture as enforced by CodeIgniter 4.

```
HTTP Request
     │
     ▼
  Router (app/Config/Routes.php)
     │
     ▼
  Filters (AuthCheck → AuditLog)
     │
     ▼
  Controller (app/Controllers/)
     │         │
     ▼         ▼
  Model     View
(Database  (HTML
  Layer)   Template)
     │
     ▼
  Database (MySQL)
```

### 4.2 Request Lifecycle

1. Browser sends an HTTP request to the server.
2. Nginx forwards PHP requests to PHP-FPM.
3. `public/index.php` bootstraps CodeIgniter 4.
4. The Router matches the URI to a controller and method.
5. Before the controller runs, Filters are executed:
   - **AuthCheck**: Validates the user session. Redirects to `/login` if unauthenticated.
   - **AuditLogFilter**: Records the user action in the audit log.
6. The Controller handles business logic, calls Models as needed.
7. Models interact with the MySQL database.
8. The Controller passes data to Views.
9. Views render HTML and return the response.

### 4.3 Folder Responsibilities

| Folder               | Responsibility                                   |
|----------------------|--------------------------------------------------|
| `app/Controllers/`   | Request handling, business logic orchestration   |
| `app/Models/`        | Database queries and data layer                  |
| `app/Views/`         | HTML templates rendered to the browser           |
| `app/Config/`        | Application configuration (routes, DB, auth)     |
| `app/Filters/`       | Middleware (authentication, audit logging)       |
| `app/Database/`      | Migrations and seeders                           |
| `public/`            | Web-accessible directory (entry point + assets)  |
| `writable/`          | Logs, cache, sessions, user uploads              |
| `tests/`             | PHPUnit test suites                              |
| `docker/`            | Nginx and Supervisor configs for Docker          |

---

## 5. Directory Structure

```
number1/
├── app/                            # Application source code
│   ├── Config/                     # Configuration files
│   │   ├── Routes.php              # URL routing definitions
│   │   ├── Database.php            # Database connection settings
│   │   ├── Filters.php             # Middleware filter registration
│   │   ├── Validation.php          # Form validation rules
│   │   ├── Session.php             # Session configuration
│   │   ├── Email.php               # Email configuration
│   │   ├── Encryption.php          # Encryption key settings
│   │   ├── Cache.php               # Cache driver settings
│   │   ├── View.php                # View configuration
│   │   ├── Migrations.php          # Migration settings
│   │   └── Boot/                   # Environment-specific overrides
│   │       ├── development.php
│   │       ├── production.php
│   │       └── testing.php
│   ├── Controllers/                # MVC Controllers (13 files)
│   │   ├── BaseController.php      # Abstract base controller
│   │   ├── Home.php                # Landing page + receipt views
│   │   ├── Login.php               # Authentication controller
│   │   ├── Logout.php              # Session logout
│   │   ├── DashboardController.php # Main dashboard KPIs
│   │   ├── SalesInvoice.php        # Sales invoice CRUD
│   │   ├── DeliveryReceiptController.php # Delivery receipt CRUD
│   │   ├── AccountingController.php # Accounting reports
│   │   ├── SiDrDashboardController.php  # SI/DR payment tracking
│   │   ├── Products.php            # Product management
│   │   ├── Clients.php             # Client management
│   │   ├── UserController.php      # User & role management
│   │   └── DynamicFilterController.php  # Custom filter management
│   ├── Models/                     # Database models (10 files)
│   │   ├── CoreModel.php           # Shared base queries
│   │   ├── SalesInoviceModel.php   # Sales invoice queries
│   │   ├── DeliveryReceiptModel.php # Delivery receipt queries
│   │   ├── AccountingModel.php     # Financial/accounting queries
│   │   ├── DashboardModel.php      # Dashboard statistics
│   │   ├── ProductModel.php        # Product queries
│   │   ├── SiDrDashboardModel.php  # SI/DR payment queries
│   │   ├── DynamicFilterModel.php  # Dynamic filter queries
│   │   └── AuditLogModel.php       # Audit log queries
│   ├── Views/                      # HTML view templates (25+ files)
│   │   ├── layout.php              # Master layout template
│   │   ├── home/                   # Home/welcome views
│   │   ├── login/                  # Login form views
│   │   ├── dashboard/              # Dashboard views
│   │   ├── sales_invoice/          # Sales invoice views
│   │   ├── delivery_receipts/      # Delivery receipt views
│   │   ├── accounting/             # Accounting report views
│   │   ├── products/               # Product management views
│   │   ├── clients/                # Client management views
│   │   ├── user_management/        # User management views
│   │   ├── dynamic_filter/         # Dynamic filter views
│   │   ├── si_and_dr_dashboard/    # SI/DR dashboard views
│   │   ├── receipts/               # Printable receipt templates
│   │   ├── profile/                # User profile views
│   │   ├── partials/               # Reusable partial templates
│   │   └── errors/                 # Error page templates
│   ├── Filters/                    # Custom middleware
│   │   ├── AuthCheck.php           # Session authentication check
│   │   └── AuditLogFilter.php      # Action audit logging
│   ├── Database/
│   │   ├── Migrations/             # Schema migration files
│   │   └── Seeds/                  # Database seeders
│   ├── Language/
│   │   └── en/
│   │       └── Validation.php      # English validation messages
│   ├── Helpers/                    # Custom helper functions
│   ├── Libraries/                  # Custom libraries
│   └── ThirdParty/                 # Third-party integrations
├── public/                         # Web root (point web server here)
│   ├── index.php                   # Application front controller
│   ├── info.php                    # PHP info page (disable in production)
│   ├── favicon.ico                 # Browser tab icon
│   ├── robots.txt                  # SEO crawler rules
│   ├── .htaccess                   # Apache URL rewriting rules
│   └── assets/                     # Static files (CSS, JS, images)
│       ├── admin_lte/              # AdminLTE 3 dashboard template
│       ├── bootstrap/              # Bootstrap 4 framework
│       ├── fontawesome/            # Font Awesome icons
│       ├── datatables/             # DataTables plugin
│       ├── jquery-3.7.1.min.js    # jQuery library
│       ├── logo.png               # Company logo
│       ├── banner.jpg             # Marketing banner
│       ├── my_css/                # Module-specific stylesheets
│       ├── my_js/                 # Module-specific JavaScript
│       └── global_css.css         # Global stylesheet
├── tests/                          # PHPUnit test suites
│   ├── README.md
│   ├── unit/
│   │   └── HealthTest.php
│   ├── database/
│   │   └── ExampleDatabaseTest.php
│   ├── session/
│   │   └── ExampleSessionTest.php
│   └── _support/                   # Test utilities and fixtures
├── docker/                         # Docker service configurations
│   ├── nginx/
│   │   └── default.conf            # Nginx virtual host config
│   └── supervisord.conf            # Supervisor process manager config
├── writable/                       # Runtime-writable directory
│   ├── logs/                       # Application error logs
│   ├── cache/                      # Framework cache files
│   ├── session/                    # PHP session files
│   └── uploads/                    # User file uploads
├── builds/                         # Build scripts/artifacts
├── scripts/                        # Utility scripts
├── certs/                          # SSL/TLS certificate files
├── env/                            # Additional environment files
├── .github/
│   └── workflows/
│       └── docker-ci.yml           # GitHub Actions CI pipeline
├── Dockerfile                      # Docker image definition
├── .dockerignore                   # Docker build exclusions
├── composer.json                   # PHP dependency manifest
├── composer.lock                   # Locked dependency versions
├── phpunit.xml.dist                # PHPUnit configuration
├── preload.php                     # PHP OPcache preloader
├── env                             # Environment variable template
├── .gitignore                      # Git exclusion rules
├── README.md                       # Framework-level readme
├── DOCUMENTATION.md                # This document
└── LICENSE                         # MIT License
```

---

## 6. Installation & Setup

### 6.1 Prerequisites

Before installing, make sure the following are available:

- PHP 8.1+ with required extensions (see Section 2.2)
- Composer 2.x
- MySQL 5.7+ or MariaDB 10.3+
- Nginx or Apache web server

### 6.2 Clone the Repository

```bash
git clone https://github.com/romsarmiento0125/number1.git
cd number1
```

### 6.3 Install PHP Dependencies

```bash
composer install
```

For production, use:

```bash
composer install --no-dev --optimize-autoloader
```

### 6.4 Configure the Environment

Copy the environment template:

```bash
cp env .env
```

Open `.env` and configure at minimum:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/'

database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 6.5 Set Directory Permissions

The `writable/` directory must be writable by the web server:

```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

### 6.6 Configure Your Web Server

**Nginx** — Point the document root to the `public/` directory:

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

**Apache** — Point `DocumentRoot` to the `public/` folder. An `.htaccess` file is already included in `public/`.

> ⚠️ **Security Warning:** Never point your web server root to the project root directory. Always use the `public/` subfolder.

### 6.7 Create and Seed the Database

Create the database manually in MySQL:

```sql
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
```

Then run any available migrations:

```bash
php spark migrate
```

And seeders if available:

```bash
php spark db:seed DatabaseSeeder
```

---

## 7. Environment Configuration

The `.env` file controls all environment-specific settings. Below is a full reference:

```ini
# ─────────────────────────────────────────────
# ENVIRONMENT
# ─────────────────────────────────────────────
# Values: development | testing | production
CI_ENVIRONMENT = development

# ─────────────────────────────────────────────
# APPLICATION
# ─────────────────────────────────────────────
app.baseURL = 'http://localhost/'
app.forceGlobalSecureRequests = false
app.CSPEnabled = false

# ─────────────────────────────────────────────
# DATABASE (Default / Production)
# ─────────────────────────────────────────────
database.default.hostname = localhost
database.default.database = your_db_name
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.collation = utf8mb4_0900_ai_ci

# ─────────────────────────────────────────────
# DATABASE (Testing)
# ─────────────────────────────────────────────
database.tests.hostname = localhost
database.tests.database = your_test_db
database.tests.username = your_test_user
database.tests.password = your_test_password
database.tests.DBDriver = MySQLi
database.tests.DBPrefix = tests_

# ─────────────────────────────────────────────
# ENCRYPTION
# ─────────────────────────────────────────────
# Generate with: php spark key:generate
encryption.key = hex2bin:YOUR_HEX_KEY_HERE

# ─────────────────────────────────────────────
# SESSION
# ─────────────────────────────────────────────
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.savePath = null

# ─────────────────────────────────────────────
# LOGGER
# ─────────────────────────────────────────────
# 0=off, 1=emergency, 2=alert, 3=critical, 4=error, 5=warning, 6=notice, 7=info, 8=debug
logger.threshold = 4
```

---

## 8. Database Configuration

The application uses **MySQL/MariaDB** via CodeIgniter 4's `MySQLi` driver.

### 8.1 Connection Settings

| Setting       | Value              | Notes                                  |
|---------------|--------------------|----------------------------------------|
| `hostname`    | localhost          | Change for remote DB servers           |
| `port`        | 3306               | Default MySQL port                     |
| `DBDriver`    | MySQLi             | Use `MySQLi` (not PDO) driver          |
| `charset`     | utf8mb4            | Full Unicode including emoji support   |
| `collation`   | utf8mb4_0900_ai_ci | Case-insensitive comparison            |

### 8.2 Core Database Entities

Based on application code analysis, the following primary tables are used:

| Table Name          | Purpose                                      |
|---------------------|----------------------------------------------|
| `users`             | System user accounts and credentials         |
| `clients`           | Customer and client records                  |
| `products`          | Product catalog with pricing                 |
| `sales_invoices`    | Sales invoice header records                 |
| `delivery_receipts` | Delivery receipt header records              |
| `audit_logs`        | User action audit trail                      |
| `client_filters`    | Saved custom filters for clients             |
| `product_filters`   | Saved custom filters for products            |

> **Note:** Run `php spark migrate` to create the database schema from migration files.

---

## 9. Running the Application

### 9.1 Development (Built-in PHP Server)

```bash
php spark serve
```

Access the application at: `http://localhost:8080`

### 9.2 Production (Nginx + PHP-FPM)

Ensure Nginx and PHP-FPM are running and configured as described in Section 6.6.

```bash
sudo systemctl start nginx
sudo systemctl start php8.2-fpm
```

### 9.3 Useful Spark Commands

```bash
php spark                    # List all available commands
php spark serve              # Start development server
php spark migrate            # Run pending migrations
php spark migrate:rollback   # Rollback last migration
php spark db:seed ClassName  # Run a specific seeder
php spark cache:clear        # Clear application cache
php spark logs               # View recent log entries
php spark key:generate       # Generate encryption key
```

---

## 10. Running with Docker

### 10.1 Prerequisites

- Docker Engine 20+
- Docker Compose v2+

### 10.2 Build and Run

```bash
docker build -t number1-app .
docker run -d -p 80:80 --name number1-app number1-app
```

Access the application at: `http://localhost`

### 10.3 Docker Image Details

| Property            | Value                           |
|---------------------|---------------------------------|
| Base Image          | `php:8.2-fpm`                   |
| Web Server          | Nginx                           |
| Process Manager     | Supervisor (manages FPM+Nginx)  |
| Exposed Port        | 80 (HTTP)                       |
| Working Directory   | `/var/www/html`                 |
| PHP User            | `www-data` (UID 1000)           |

### 10.4 Installed PHP Extensions (Docker)

`gd`, `pdo_mysql`, `mysqli`, `mbstring`, `xml`, `bcmath`, `intl`, `zip`

### 10.5 Environment Variables in Docker

Pass environment variables via `-e` flags or an env file:

```bash
docker run -d -p 80:80 \
  -e CI_ENVIRONMENT=production \
  -e DB_HOST=your_db_host \
  -e DB_NAME=your_db_name \
  -e DB_USER=your_db_user \
  -e DB_PASS=your_db_password \
  --name number1-app number1-app
```

---

## 11. Application Modules

### 11.1 Authentication

**Purpose:** Secure login and session management.

**Features:**
- Username/password login form.
- Session-based authentication (`session.login = 1`).
- Automatic redirect to `/login` when session expires.
- Logout destroys the session completely.

**Access:** `/login`

---

### 11.2 Dashboard

**Purpose:** High-level business KPI overview.

**Features:**
- Real-time sales and delivery statistics.
- Key performance indicators for the business.
- Export accounting totals.

**Access:** `/dashboard` (requires authentication)

---

### 11.3 Sales Invoice Management

**Purpose:** Create and manage customer sales invoices.

**Features:**
- Create new sales invoices with client and product data.
- Save as draft and update later.
- Print formatted invoices.
- Print and manage SI receipts.
- Cancel receipts with user password verification.
- Track invoice status.

**Access:** `/sales_invoice` (requires authentication)

---

### 11.4 Delivery Receipt Management

**Purpose:** Track product deliveries with formal documentation.

**Features:**
- Create delivery receipts linked to products and clients.
- Save as draft and update later.
- Print delivery receipt documents.
- Print and manage DR receipts.
- Cancel receipts with authentication.
- Track delivery status.

**Access:** `/delivery_receipt` (requires authentication)

---

### 11.5 SI/DR Payment Dashboard

**Purpose:** Track payment status for sales invoices and delivery receipts.

**Features:**
- View all SI and DR records with payment status.
- Update payment status (paid/unpaid).
- Filter by paid or unpaid.
- Combined SI and DR view.

**Access:** `/sidrdashboard` (requires authentication)

---

### 11.6 Accounting & Reports

**Purpose:** Financial reporting and analytics.

**Features:**
- Sales invoice accounting data with filtering.
- Delivery receipt accounting data with filtering.
- SI volume analytics.
- DR volume analytics.
- Combined SI/DR volume analysis.
- Dynamic client and product filter application.

**Access:** `/accounting` (requires authentication)

---

### 11.7 Product Management

**Purpose:** Maintain the product catalog.

**Features:**
- View all products in a searchable table.
- Add and edit product details.
- Save product costs.
- Activate or deactivate products.
- Apply custom product filters.

**Access:** `/products` (requires authentication)

---

### 11.8 Client Management

**Purpose:** Maintain customer/client records.

**Features:**
- View all clients in a searchable table.
- Add and edit client details.
- Activate or deactivate clients.
- Apply custom client filters.

**Access:** `/clients` (requires authentication)

---

### 11.9 User Management

**Purpose:** Manage system users and their roles.

**Features:**
- View all users with their roles and status.
- Create new user accounts with role assignment.
- Edit existing users.
- Archive (deactivate) users.
- Reactivate archived users.

**Access:** `/user` (requires authentication)

---

### 11.10 Dynamic Filter Management

**Purpose:** Create and reuse custom filters for reporting and data views.

**Features:**
- **Client Filters:** Create, view, edit, delete saved client groupings.
- **Product Filters:** Create, view, edit, delete saved product groupings.
- Filters are reusable across sales invoices, delivery receipts, and accounting.

**Access:**
- `/dynamic_filter_client`
- `/dynamic_filter_product`

---

### 11.11 User Profile

**Purpose:** View and update the logged-in user's profile.

**Features:**
- View current profile information.
- Update profile details.

**Access:** `/profile` (requires authentication)

---

## 12. Routes & API Reference

All protected routes require an active session (`authcheck` filter).

### 12.1 Public Routes

| Method | URI                   | Controller & Method        | Description           |
|--------|-----------------------|----------------------------|-----------------------|
| GET    | `/login`              | `Login::index`             | Show login form       |
| POST   | `/login/authenticate` | `Login::authenticate`      | Process login         |
| POST   | `/login/logout`       | `Login::logout`            | Logout user           |

### 12.2 Home / Receipt Views

| Method | URI                                         | Controller & Method        | Description                 |
|--------|---------------------------------------------|----------------------------|-----------------------------|
| GET    | `/`                                         | `Home::index`              | Application home page       |
| GET    | `/sales_invoice_view/{id}/{status}`         | `Home::si_receipt`         | View SI receipt             |
| GET    | `/delivery_receipt_view/{id}/{status}`      | `Home::dr_receipt`         | View DR receipt             |

### 12.3 Dashboard

| Method | URI                                   | Controller & Method                        | Description              |
|--------|---------------------------------------|--------------------------------------------|--------------------------|
| GET    | `/dashboard`                          | `DashboardController::index`               | Show dashboard           |
| POST   | `/dashboard/get_dashboard_data`       | `DashboardController::get_dashboard_data`  | Fetch KPI data           |
| POST   | `/dashboard/export_accounting_total`  | `DashboardController::export_accounting_total` | Export data          |

### 12.4 Sales Invoices

| Method | URI                                         | Controller & Method                    | Description                  |
|--------|---------------------------------------------|----------------------------------------|------------------------------|
| GET    | `/sales_invoice`                            | `SalesInvoice::index`                  | SI list page                 |
| POST   | `/sales_invoice/get_products_clients_si`    | `SalesInvoice::get_products_clients_si`| Fetch dropdown data          |
| POST   | `/sales_invoice/save_draft`                 | `SalesInvoice::save_draft`             | Create draft invoice         |
| POST   | `/sales_invoice/print_invoice`              | `SalesInvoice::print_invoice`          | Print invoice                |
| POST   | `/sales_invoice/get_sales_invoice_by_id`    | `SalesInvoice::get_sales_invoice_by_id`| Fetch invoice by ID          |
| POST   | `/sales_invoice/update_draft`               | `SalesInvoice::update_draft`           | Update draft invoice         |
| POST   | `/sales_invoice/get_si_receipt_by_id`       | `SalesInvoice::get_si_receipt_by_id`   | Fetch SI receipt by ID       |
| POST   | `/sales_invoice/print_si_receipt`           | `SalesInvoice::print_si_receipt`       | Print SI receipt             |
| POST   | `/sales_invoice/draft_si_receipt`           | `SalesInvoice::draft_si_receipt`       | Save SI receipt as draft     |
| POST   | `/sales_invoice/cancel_si_receipt`          | `SalesInvoice::cancel_si_receipt`      | Cancel SI receipt            |
| POST   | `/sales_invoice/authenticate_user`          | `SalesInvoice::authenticate_user`      | Verify user password         |

### 12.5 Delivery Receipts

| Method | URI                                          | Controller & Method                          | Description             |
|--------|----------------------------------------------|----------------------------------------------|-------------------------|
| GET    | `/delivery_receipt`                          | `DeliveryReceiptController::index`           | DR list page            |
| POST   | `/delivery_receipt/get_products_clients_dr`  | `DeliveryReceiptController::get_products_clients_dr` | Fetch dropdown data |
| POST   | `/delivery_receipt/save_draft`               | `DeliveryReceiptController::save_draft`      | Create draft DR         |
| POST   | `/delivery_receipt/print_delivery`           | `DeliveryReceiptController::print_delivery`  | Print delivery receipt  |
| POST   | `/delivery_receipt/get_delivery_receipt_by_id` | `DeliveryReceiptController::get_delivery_receipt_by_id` | Fetch DR by ID |
| POST   | `/delivery_receipt/update_draft`             | `DeliveryReceiptController::update_draft`    | Update draft DR         |
| POST   | `/delivery_receipt/get_dr_receipt_by_id`     | `DeliveryReceiptController::get_dr_receipt_by_id` | Fetch DR receipt   |
| POST   | `/delivery_receipt/print_dr_receipt`         | `DeliveryReceiptController::print_dr_receipt`| Print DR receipt        |
| POST   | `/delivery_receipt/draft_dr_receipt`         | `DeliveryReceiptController::draft_dr_receipt`| Save DR receipt draft   |
| POST   | `/delivery_receipt/cancel_dr_receipt`        | `DeliveryReceiptController::cancel_dr_receipt` | Cancel DR receipt    |
| POST   | `/delivery_receipt/authenticate_user`        | `DeliveryReceiptController::authenticate_user` | Verify user password |

### 12.6 Products

| Method | URI                                | Controller & Method            | Description              |
|--------|------------------------------------|--------------------------------|--------------------------|
| GET    | `/products`                        | `Products::index`              | Products list page       |
| POST   | `/products/save_product`           | `Products::save_product`       | Create or update product |
| POST   | `/products/get_table_products`     | `Products::get_table_products` | Fetch product table data |
| POST   | `/products/edit_product`           | `Products::edit_product`       | Get product for editing  |
| POST   | `/products/active_inactive`        | `Products::active_inactive`    | Toggle product status    |
| POST   | `/products/get_custom_filters`     | `Products::get_custom_filters` | Get saved filters        |
| POST   | `/products/save_product_cost`      | `Products::save_product_cost`  | Save product cost        |

### 12.7 Clients

| Method | URI                              | Controller & Method           | Description              |
|--------|----------------------------------|-------------------------------|--------------------------|
| GET    | `/clients`                       | `Clients::index`              | Clients list page        |
| POST   | `/clients/save_client`           | `Clients::save_client`        | Create or update client  |
| POST   | `/clients/get_table_clients`     | `Clients::get_table_clients`  | Fetch client table data  |
| POST   | `/clients/edit_client`           | `Clients::edit_client`        | Get client for editing   |
| POST   | `/clients/active_inactive`       | `Clients::active_inactive`    | Toggle client status     |
| POST   | `/clients/get_custom_filters`    | `Clients::get_custom_filters` | Get saved filters        |

### 12.8 Users

| Method | URI                           | Controller & Method           | Description              |
|--------|-------------------------------|-------------------------------|--------------------------|
| GET    | `/user`                       | `UserController::index`       | User management page     |
| POST   | `/user/get_user_role`         | `UserController::get_user_role`| Get roles list          |
| POST   | `/user/get_table_user`        | `UserController::get_table_user`| Fetch users table      |
| POST   | `/user/save_user`             | `UserController::save_user`   | Create user              |
| POST   | `/user/edit_user`             | `UserController::edit_user`   | Update user              |
| POST   | `/user/archive_user`          | `UserController::archive_user`| Archive user             |
| POST   | `/user/activate_user`         | `UserController::activate_user`| Reactivate user         |

### 12.9 Accounting

| Method | URI                                               | Controller & Method                              | Description               |
|--------|---------------------------------------------------|--------------------------------------------------|---------------------------|
| GET    | `/accounting`                                     | `AccountingController::index`                    | Accounting page           |
| POST   | `/accounting/get_filters`                         | `AccountingController::get_filters`              | Get saved filters         |
| POST   | `/accounting/get_si_data_items_accounting`        | `AccountingController::get_si_data_items_accounting` | SI accounting data    |
| POST   | `/accounting/get_dr_data_items_accounting`        | `AccountingController::get_dr_data_items_accounting` | DR accounting data    |
| POST   | `/accounting/dynamic_change_client_show`          | `AccountingController::dynamic_change_client_show`| Change client filter     |
| POST   | `/accounting/dynamic_change_product_show`         | `AccountingController::dynamic_change_product_show`| Change product filter   |
| POST   | `/accounting/get_si_volume`                       | `AccountingController::get_si_volume`            | SI volume analytics       |
| POST   | `/accounting/get_dr_volume`                       | `AccountingController::get_dr_volume`            | DR volume analytics       |
| POST   | `/accounting/get_si_dr_volume`                    | `AccountingController::get_si_dr_volume`         | Combined SI/DR analytics  |

### 12.10 SI/DR Dashboard

| Method | URI                                        | Controller & Method                            | Description                |
|--------|--------------------------------------------|------------------------------------------------|----------------------------|
| GET    | `/sidrdashboard`                           | `SiDrDashboardController::index`               | SI/DR dashboard            |
| POST   | `/sidrdashboard/get_si_dr`                 | `SiDrDashboardController::get_si_dr`           | Get SI/DR records          |
| POST   | `/sidrdashboard/update_si_dr_payment`      | `SiDrDashboardController::update_si_dr_payment`| Update payment status      |
| POST   | `/sidrdashboard/si_get_paid_unpaid`        | `SiDrDashboardController::si_get_paid_unpaid`  | Filter SI by payment status|
| POST   | `/sidrdashboard/dr_get_paid_unpaid`        | `SiDrDashboardController::dr_get_paid_unpaid`  | Filter DR by payment status|

### 12.11 Dynamic Filters

| Method | URI                                              | Controller & Method                              | Description               |
|--------|--------------------------------------------------|--------------------------------------------------|---------------------------|
| GET    | `/dynamic_filter_client`                         | `DynamicFilterController::dynamic_filter_client` | Client filter management  |
| GET    | `/dynamic_filter_client/get_clients`             | `DynamicFilterController::get_clients`           | Get clients list          |
| POST   | `/dynamic_filter_client/save_filter`             | `DynamicFilterController::save_filter`           | Create client filter      |
| POST   | `/dynamic_filter_client/get_client_filters`      | `DynamicFilterController::get_client_filters`    | List client filters       |
| POST   | `/dynamic_filter_client/view_client_filter`      | `DynamicFilterController::view_client_filter`    | View filter details       |
| POST   | `/dynamic_filter_client/edit_client_filter`      | `DynamicFilterController::edit_client_filter`    | Update client filter      |
| POST   | `/dynamic_filter_client/delete_client_filter`    | `DynamicFilterController::delete_client_filter`  | Delete client filter      |
| GET    | `/dynamic_filter_product`                        | `DynamicFilterController::dynamic_filter_product`| Product filter management |
| GET    | `/dynamic_filter_product/get_products`           | `DynamicFilterController::get_products`          | Get products list         |
| POST   | `/dynamic_filter_product/save_product_filter`    | `DynamicFilterController::save_product_filter`   | Create product filter     |
| POST   | `/dynamic_filter_product/get_product_filters`    | `DynamicFilterController::get_product_filters`   | List product filters      |
| POST   | `/dynamic_filter_product/view_product_filter`    | `DynamicFilterController::view_product_filter`   | View filter details       |
| POST   | `/dynamic_filter_product/edit_product_filter`    | `DynamicFilterController::edit_product_filter`   | Update product filter     |
| POST   | `/dynamic_filter_product/delete_product_filter`  | `DynamicFilterController::delete_product_filter` | Delete product filter     |

### 12.12 Profile

| Method | URI               | Controller & Method            | Description                |
|--------|-------------------|--------------------------------|----------------------------|
| GET    | `/profile`        | `UserController::profile`      | View profile page          |
| POST   | `/profile/update` | `UserController::update_profile`| Update profile information |

---

## 13. Controllers Reference

### 13.1 BaseController

**File:** `app/Controllers/BaseController.php`  
**Extends:** `CodeIgniter\Controller`

All other controllers extend this class. It initializes:
- Request and response objects
- Session handler
- Validation library
- Logger

---

### 13.2 Home

**File:** `app/Controllers/Home.php`

| Method        | Description                                   |
|---------------|-----------------------------------------------|
| `index()`     | Renders the application home/landing page     |
| `si_receipt()`| Displays a Sales Invoice receipt view         |
| `dr_receipt()`| Displays a Delivery Receipt view              |

---

### 13.3 Login

**File:** `app/Controllers/Login.php`

| Method           | Description                                             |
|------------------|---------------------------------------------------------|
| `index()`        | Renders the login form                                  |
| `authenticate()` | Validates credentials and creates the user session      |
| `logout()`       | Destroys the session and redirects to `/login`          |

---

### 13.4 DashboardController

**File:** `app/Controllers/DashboardController.php`

| Method                    | Description                                 |
|---------------------------|---------------------------------------------|
| `index()`                 | Renders the main dashboard page             |
| `get_dashboard_data()`    | Returns KPI data via AJAX                   |
| `export_accounting_total()`| Exports accounting totals (download)       |

---

### 13.5 SalesInvoice

**File:** `app/Controllers/SalesInvoice.php`

| Method                      | Description                                       |
|-----------------------------|---------------------------------------------------|
| `hasAccess()`               | Role-based access gate                            |
| `index()`                   | Renders the SI management page                    |
| `get_products_clients_si()` | Returns products and clients for SI form          |
| `save_draft()`              | Creates a new draft invoice                       |
| `update_draft()`            | Updates an existing draft                         |
| `print_invoice()`           | Generates a printable invoice                     |
| `get_sales_invoice_by_id()` | Returns invoice data by ID                        |
| `get_si_receipt_by_id()`    | Returns SI receipt data by ID                     |
| `print_si_receipt()`        | Finalizes and prints a SI receipt                 |
| `draft_si_receipt()`        | Saves a SI receipt as draft                       |
| `cancel_si_receipt()`       | Cancels an SI receipt                             |
| `authenticate_user()`       | Verifies user password before sensitive action    |

---

### 13.6 DeliveryReceiptController

**File:** `app/Controllers/DeliveryReceiptController.php`

| Method                        | Description                                     |
|-------------------------------|-------------------------------------------------|
| `index()`                     | Renders the DR management page                  |
| `get_products_clients_dr()`   | Returns products and clients for DR form        |
| `save_draft()`                | Creates a new draft DR                          |
| `update_draft()`              | Updates an existing draft DR                    |
| `print_delivery()`            | Generates a printable delivery receipt          |
| `get_delivery_receipt_by_id()`| Returns DR data by ID                           |
| `get_dr_receipt_by_id()`      | Returns DR receipt data by ID                   |
| `print_dr_receipt()`          | Finalizes and prints a DR receipt               |
| `draft_dr_receipt()`          | Saves a DR receipt as draft                     |
| `cancel_dr_receipt()`         | Cancels a DR receipt                            |
| `authenticate_user()`         | Verifies user password before sensitive action  |

---

### 13.7 AccountingController

**File:** `app/Controllers/AccountingController.php`

| Method                          | Description                                     |
|---------------------------------|-------------------------------------------------|
| `index()`                       | Renders the accounting page                     |
| `get_filters()`                 | Returns available dynamic filters               |
| `get_si_data_items_accounting()`| Returns SI accounting line items                |
| `get_dr_data_items_accounting()`| Returns DR accounting line items                |
| `dynamic_change_client_show()`  | Applies a client filter to the accounting view  |
| `dynamic_change_product_show()` | Applies a product filter to the accounting view |
| `get_si_volume()`               | Returns SI volume analytics                     |
| `get_dr_volume()`               | Returns DR volume analytics                     |
| `get_si_dr_volume()`            | Returns combined SI/DR volume                   |

---

### 13.8 SiDrDashboardController

**File:** `app/Controllers/SiDrDashboardController.php`

| Method                   | Description                                       |
|--------------------------|---------------------------------------------------|
| `index()`                | Renders the SI/DR payment dashboard               |
| `get_si_dr()`            | Returns SI and DR records                         |
| `update_si_dr_payment()` | Updates payment status of a record                |
| `si_get_paid_unpaid()`   | Filters SIs by paid/unpaid status                 |
| `dr_get_paid_unpaid()`   | Filters DRs by paid/unpaid status                 |

---

### 13.9 Products

**File:** `app/Controllers/Products.php`

| Method                | Description                                     |
|-----------------------|-------------------------------------------------|
| `index()`             | Renders the products management page            |
| `save_product()`      | Creates or updates a product record             |
| `get_table_products()`| Returns product data for DataTables             |
| `edit_product()`      | Returns product data for editing                |
| `active_inactive()`   | Toggles the active/inactive status              |
| `get_custom_filters()`| Returns saved product filters                   |
| `save_product_cost()` | Saves cost information for a product            |

---

### 13.10 Clients

**File:** `app/Controllers/Clients.php`

| Method                | Description                                     |
|-----------------------|-------------------------------------------------|
| `index()`             | Renders the clients management page             |
| `save_client()`       | Creates or updates a client record              |
| `get_table_clients()` | Returns client data for DataTables              |
| `edit_client()`       | Returns client data for editing                 |
| `active_inactive()`   | Toggles the active/inactive status              |
| `get_custom_filters()`| Returns saved client filters                    |

---

### 13.11 UserController

**File:** `app/Controllers/UserController.php`

| Method             | Description                                     |
|--------------------|-------------------------------------------------|
| `index()`          | Renders the user management page                |
| `get_user_role()`  | Returns the list of available roles             |
| `get_table_user()` | Returns user data for DataTables                |
| `save_user()`      | Creates a new user account                      |
| `edit_user()`      | Updates an existing user                        |
| `archive_user()`   | Archives (deactivates) a user                   |
| `activate_user()`  | Reactivates an archived user                    |
| `profile()`        | Renders the profile page                        |
| `update_profile()` | Saves updated profile information               |

---

### 13.12 DynamicFilterController

**File:** `app/Controllers/DynamicFilterController.php`

| Method                   | Description                                    |
|--------------------------|------------------------------------------------|
| `dynamic_filter_client()`| Renders client filter management page          |
| `get_clients()`          | Returns clients for filter creation            |
| `save_filter()`          | Creates a new client filter                    |
| `get_client_filters()`   | Returns saved client filters                   |
| `view_client_filter()`   | Returns filter details for viewing             |
| `edit_client_filter()`   | Updates a client filter                        |
| `delete_client_filter()` | Deletes a client filter                        |
| `dynamic_filter_product()`| Renders product filter management page        |
| `get_products()`         | Returns products for filter creation           |
| `save_product_filter()`  | Creates a new product filter                   |
| `get_product_filters()`  | Returns saved product filters                  |
| `view_product_filter()`  | Returns filter details for viewing             |
| `edit_product_filter()`  | Updates a product filter                       |
| `delete_product_filter()`| Deletes a product filter                       |

---

## 14. Models Reference

### 14.1 CoreModel

**File:** `app/Models/CoreModel.php`

Base model providing shared database queries used across multiple controllers.

| Key Method              | Description                             |
|-------------------------|-----------------------------------------|
| `user_login($username)` | Retrieve user record for authentication |
| `get_clients()`         | Fetch all active clients                |
| `get_products()`        | Fetch all active products               |
| `get_users()`           | Fetch all user accounts                 |
| `get_si_receipt_data()` | Sales invoice receipt data              |
| `get_dr_receipt_data()` | Delivery receipt data                   |

---

### 14.2 SalesInvoiceModel

**File:** `app/Models/SalesInoviceModel.php` *(note: filename contains a typo in the codebase)*

Handles all database operations for the Sales Invoice module.

---

### 14.3 DeliveryReceiptModel

**File:** `app/Models/DeliveryReceiptModel.php`

Handles all database operations for the Delivery Receipt module.

---

### 14.4 AccountingModel

**File:** `app/Models/AccountingModel.php`

The largest model in the application. Provides comprehensive financial queries for the Accounting module.

---

### 14.5 DashboardModel

**File:** `app/Models/DashboardModel.php`

Provides statistics and KPI data for the Dashboard module.

---

### 14.6 ProductModel

**File:** `app/Models/ProductModel.php`

Handles all database operations for the Product management module.

---

### 14.7 SiDrDashboardModel

**File:** `app/Models/SiDrDashboardModel.php`

Provides payment tracking data for the SI/DR Dashboard module.

---

### 14.8 DynamicFilterModel

**File:** `app/Models/DynamicFilterModel.php`

Handles storage and retrieval of custom saved filters.

---

### 14.9 AuditLogModel

**File:** `app/Models/AuditLogModel.php`

Records user actions to the `audit_logs` database table.

---

## 15. Views & Frontend

### 15.1 Master Layout

**File:** `app/Views/layout.php`

The master layout wraps all pages and includes:
- HTML `<head>` with Bootstrap 4, Font Awesome, AdminLTE styles
- AdminLTE sidebar navigation
- Top navigation bar
- Main content area (yielded by child views)
- Footer
- jQuery, AdminLTE, DataTables scripts
- Module-specific scripts and styles

### 15.2 Frontend Libraries

| Library          | Version  | Loaded Via         |
|------------------|----------|--------------------|
| jQuery           | 3.7.1    | `/assets/`         |
| Bootstrap        | 4        | `/assets/bootstrap/` |
| AdminLTE         | 3        | `/assets/admin_lte/` |
| Font Awesome     | Latest   | `/assets/fontawesome/` |
| DataTables       | Latest   | `/assets/datatables/` |
| Select2          | Latest   | AdminLTE plugins   |
| DateRangePicker  | Latest   | AdminLTE plugins   |

### 15.3 Custom Assets

**Custom CSS** (`/public/assets/my_css/`):

| File                          | Module        |
|-------------------------------|---------------|
| `accounting/*.css`            | Accounting    |
| `dashboard/*.css`             | Dashboard     |
| `sales_invoice/*.css`         | Sales Invoice |
| `delivery_receipts/*.css`     | Delivery      |
| `products/*.css`              | Products      |
| `clients/*.css`               | Clients       |
| `user_management/*.css`       | Users         |
| `dynamic_filter/*.css`        | Filters       |

**Custom JavaScript** (`/public/assets/my_js/`):

| File                                    | Module          |
|-----------------------------------------|-----------------|
| `global.js`                             | All modules     |
| `accounting/accounting.js`              | Accounting      |
| `accounting/accounting_si.js`           | Accounting (SI) |
| `accounting/accounting_dr.js`           | Accounting (DR) |
| `dashboard/dashboard.js`                | Dashboard       |
| `sales_invoice/sales_invoice.js`        | Sales Invoice   |
| `sales_invoice/sales_invoice_data_population.js` | Sales Invoice |
| `sales_invoice/sales_invoice_summary.js`| Sales Invoice   |
| `delivery_receipts/*.js`                | Delivery        |
| `products/products.js`                  | Products        |
| `clients/clients.js`                    | Clients         |
| `user_management/user_management.js`    | Users           |
| `dynamic_filter/dynamic_filter_client.js` | Client Filters |
| `dynamic_filter/dynamic_filter_product.js`| Product Filters|

---

## 16. Authentication & Authorization

### 16.1 Login Process

1. User submits username and password at `/login`.
2. `Login::authenticate()` queries the database via `CoreModel::user_login()`.
3. Password is verified.
4. On success: session variable `login = 1` and user data are stored.
5. On failure: user is redirected back to login with an error message.

### 16.2 Session Validation (AuthCheck Filter)

**File:** `app/Filters/AuthCheck.php`

Every request to a protected route passes through this filter:

```php
if ($session->get('login') != 1) {
    return redirect()->to('/login');
}
```

### 16.3 Role-Based Access Control

The application defines **6 user roles** (Role IDs 1–6). Controllers implement `hasAccess()` methods to enforce role restrictions. Specific actions (such as canceling receipts) require password re-authentication via `authenticate_user()`.

### 16.4 Logout

Calling `POST /login/logout` via `Login::logout()`:
1. Destroys the active session.
2. Redirects to `/login`.

---

## 17. Security Features

| Feature                  | Implementation                                         |
|--------------------------|--------------------------------------------------------|
| Authentication           | Session-based (`authcheck` filter on all protected routes) |
| CSRF Protection          | CodeIgniter 4 built-in CSRF token validation           |
| Password Re-auth         | Sensitive actions require password re-verification     |
| Secure Headers           | `secureheaders` filter available                       |
| HTTPS Enforcement        | `forcehttps` filter available                          |
| Hidden Files Protection  | Nginx denies access to `.ht*` files                    |
| Public Folder Isolation  | Web root points to `public/` — app code is not exposed |
| Honeypot Protection      | `honeypot` filter available against bots               |
| Audit Logging            | All user actions are logged (see Section 18)           |
| Input Validation         | CodeIgniter 4 validation library with custom rules     |

> ⚠️ **Production Checklist:**
> - Set `CI_ENVIRONMENT = production` in `.env`
> - Remove or protect `/public/info.php`
> - Ensure `writable/` is not web-accessible
> - Use HTTPS and configure `app.forceGlobalSecureRequests = true`
> - Set a strong `encryption.key`

---

## 18. Audit Logging

**File:** `app/Filters/AuditLogFilter.php`  
**Model:** `app/Models/AuditLogModel.php`

All user actions on protected routes are logged to the `audit_logs` table. Each log entry captures:

- User ID and username
- HTTP method and URI accessed
- Timestamp
- IP address (if available)

The `auditlog` filter is configured in `app/Config/Filters.php`.

---

## 19. Testing

### 19.1 Test Setup

Install test dependencies:

```bash
composer install
```

Copy and configure the test environment:

```bash
cp env .env
# Set database.tests.* values in .env
```

### 19.2 Running Tests

```bash
# Run all tests
composer test

# Run a specific test directory
vendor/bin/phpunit tests/unit

# Run with code coverage (requires Xdebug or pcov)
vendor/bin/phpunit --coverage-html build/coverage
```

### 19.3 Test Structure

```
tests/
├── unit/
│   └── HealthTest.php          # Basic health/sanity tests
├── database/
│   └── ExampleDatabaseTest.php # Database integration test template
├── session/
│   └── ExampleSessionTest.php  # Session test template
└── _support/
    ├── Database/Migrations/    # Test-only migrations
    ├── Models/ExampleModel.php # Test model
    └── Libraries/ConfigReader.php # Test utilities
```

### 19.4 PHPUnit Configuration

**File:** `phpunit.xml.dist`

Key settings:

| Setting             | Value                                   |
|---------------------|-----------------------------------------|
| Bootstrap           | CodeIgniter 4 test bootstrap            |
| Cache directory     | `build/.phpunit.cache`                  |
| Code coverage input | `./app` directory                       |
| Coverage excludes   | `app/Views`, `app/Config/Routes.php`    |
| Coverage output     | HTML, Clover XML, text, JUnit XML       |

---

## 20. Deployment

### 20.1 Production Deployment Checklist

- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Set `app.baseURL` to the production domain
- [ ] Configure production database credentials
- [ ] Generate a new encryption key: `php spark key:generate`
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Set `writable/` directory permissions: `chmod -R 775 writable/`
- [ ] Point web server document root to `public/`
- [ ] Enable HTTPS and set `app.forceGlobalSecureRequests = true`
- [ ] Remove or protect `public/info.php`
- [ ] Configure log rotation for `writable/logs/`
- [ ] Set up database backups

### 20.2 Docker Deployment

Build and run the Docker image:

```bash
# Build the image
docker build -t number1-app:latest .

# Run the container
docker run -d \
  -p 80:80 \
  -e CI_ENVIRONMENT=production \
  --name number1-app \
  number1-app:latest
```

### 20.3 Nginx Virtual Host (Production)

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate     /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}
```

---

## 21. CI/CD Pipeline

**File:** `.github/workflows/docker-ci.yml`

The GitHub Actions pipeline automatically:

1. Triggers on push or pull request to the main branch.
2. Builds the Docker image from the `Dockerfile`.
3. Runs automated checks.

### Pipeline Steps

```yaml
name: Docker CI
on: [push, pull_request]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - Checkout source code
      - Build Docker image
      - Run tests (if configured)
```

---

## 22. Troubleshooting

### 22.1 Common Issues

**Problem:** White screen / 500 error on first load.  
**Solution:**
- Check that `.env` exists and is configured correctly.
- Verify `writable/` directory has write permissions.
- Enable debug mode: set `CI_ENVIRONMENT = development` in `.env`.
- Check `writable/logs/` for error messages.

---

**Problem:** Database connection error.  
**Solution:**
- Verify database credentials in `.env`.
- Ensure MySQL/MariaDB server is running.
- Confirm the database exists and the user has access.
- Check that `database.default.DBDriver = MySQLi` is set.

---

**Problem:** Session not persisting (keeps logging out).  
**Solution:**
- Ensure `writable/session/` exists and is writable.
- Check that `app.baseURL` in `.env` matches the URL you are accessing.
- Verify cookies are enabled in the browser.

---

**Problem:** Assets (CSS/JS) not loading.  
**Solution:**
- Confirm the web server document root is pointing to `public/`, not the project root.
- Check `app.baseURL` is correctly set.
- Clear browser cache.

---

**Problem:** CSRF token mismatch error on form submissions.  
**Solution:**
- Ensure forms include the CSRF hidden field (CodeIgniter 4 handles this automatically).
- Check that CSRF is not accidentally disabled in `app/Config/Filters.php`.

---

**Problem:** Docker container exits immediately.  
**Solution:**
- Check container logs: `docker logs number1-app`
- Verify PHP-FPM and Nginx are starting correctly via Supervisor.
- Ensure the `writable/` directory has correct permissions inside the container.

---

### 22.2 Log Files

| Log Location                | Content                          |
|-----------------------------|----------------------------------|
| `writable/logs/`            | CodeIgniter application logs     |
| `/var/log/nginx/`           | Nginx access and error logs      |
| `/var/log/php8.2-fpm.log`   | PHP-FPM process logs             |
| `audit_logs` DB table       | User action audit trail          |

---

## 23. Glossary

| Term        | Definition                                                                              |
|-------------|-----------------------------------------------------------------------------------------|
| **SI**      | Sales Invoice — A billing document issued to a customer for goods sold                  |
| **DR**      | Delivery Receipt — A document confirming physical delivery of goods to a customer        |
| **CI4**     | CodeIgniter 4 — The PHP MVC framework this application is built on                      |
| **MVC**     | Model-View-Controller — Architectural pattern separating data, logic, and presentation   |
| **KPI**     | Key Performance Indicator — A measurable metric used to evaluate business performance    |
| **CRUD**    | Create, Read, Update, Delete — The four basic database operations                        |
| **CSRF**    | Cross-Site Request Forgery — An attack vector; CI4 includes built-in protection          |
| **AuthCheck** | The custom authentication middleware/filter that protects all secured routes           |
| **Dynamic Filter** | A user-defined, saved grouping of clients or products for reuse in reports       |
| **Draft**   | An invoice or receipt that has been saved but not yet finalized/printed                  |
| **FPM**     | FastCGI Process Manager — The PHP process manager used with Nginx                       |
| **AdminLTE**| An open-source admin dashboard template based on Bootstrap 4                            |
| **Composer**| PHP dependency manager used to install and manage project libraries                     |
| **PSR-4**   | PHP autoloading standard — used for namespace-to-directory mapping                      |

---

## 24. License

This project is licensed under the **MIT License**.

```
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

*Documentation generated for the 1 Blend Feeds Business Management System.*  
*Last updated: 2026-03-17*

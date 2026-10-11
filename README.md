# 📦 Core Inventory Management System

A lightweight, modern web-based **Core Inventory Management System** built with **HTML5, CSS3, JavaScript, PHP 8.x**, and **MySQL**. Designed for dual-environment support: local development with **XAMPP** and production deployment on **Vercel** (serverless PHP runtime) with cloud database hosting on **Railway (MySQL)**.

---

## 📑 Table of Contents
1. [Project Overview](#-project-overview)
2. [Technology Stack](#-technology-stack)
3. [Folder Structure](#-folder-structure)
4. [Page-by-Page Functionality & Architecture](#-page-by-page-functionality--architecture)
   - [Root Entry](#root-entry)
   - [Authentication Pages](#authentication-pages)
   - [Core Application Pages](#core-application-pages)
   - [Backend Endpoints & Action Handlers](#backend-endpoints--action-handlers)
   - [Shared Components & Configuration](#shared-components--configuration)
5. [Database Architecture](#-database-architecture)
6. [Cloud Deployment Strategy](#-cloud-deployment-strategy)
   - [1. Railway (MySQL Database Hosting)](#1-railway-mysql-database-hosting)
   - [2. Vercel (Frontend & PHP Serverless Deployment)](#2-vercel-frontend--php-serverless-deployment)
7. [Local Development Setup (XAMPP)](#-local-development-setup-xampp)
8. [Environment Variables Reference](#-environment-variables-reference)
9. [Development Rules & Next Steps](#-development-rules--next-steps)

---

## 🌟 Project Overview

The **Core Inventory Management System** is designed for small to medium businesses to streamline catalog management, track stock intake and dispatch, monitor minimum threshold alerts, maintain supplier directories, and log an immutable audit trail of inventory adjustments.

### Key Objectives:
- **Real-Time Stock Auditing**: Maintain full visibility over stock balances through atomic stock-in and stock-out ledger transactions.
- **Stock Depletion Safeguards**: Automated alerts for low-stock and out-of-stock items based on customizable reorder thresholds.
- **Cloud-Ready Architecture**: Decoupled infrastructure using Railway for high-availability managed MySQL and Vercel for serverless PHP hosting.
- **Pure Web Stack**: Built using standard HTML, CSS, JavaScript, and PHP without bulky framework dependencies.

---

## 🛠 Technology Stack

| Layer | Technology | Purpose |
| :--- | :--- | :--- |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript (ES6+) | User interface, responsive layout, dynamic client-side validation, AJAX requests |
| **Backend** | PHP 8.x (PDO) | Server-side business logic, session management, secure parameterized database queries |
| **Database** | MySQL 8.x (Railway Cloud) | Relational persistence, foreign key constraints, transactional integrity |
| **Cloud Hosting** | Vercel | Serverless web hosting via community PHP serverless runtime (`vercel-php`) |
| **Database Cloud** | Railway | Cloud-hosted managed MySQL instance with SSL support |
| **Local Environment** | XAMPP | Local Apache web server and MariaDB/MySQL testing environment |

---

## 📁 Folder Structure

```
Core_inventory/
│
├── .env.example                     # Sample configuration for Railway & DB credentials
├── .gitignore                       # Git ignore list (secrets, vendor, OS files)
├── README.md                        # Master project documentation and blueprint
├── vercel.json                      # Vercel deployment routing & PHP serverless runtime config
├── index.html                       # Root gateway router / entry redirector
│
├── config/                          # Application Configuration
│   ├── config.php                   # Global constants, site settings, session config
│   └── db.php                       # PDO database connection handler (Railway & Local)
│
├── database/                        # Database Assets
│   └── schema.sql                   # SQL schema definition, tables, indexes & seed data
│
├── includes/                        # Reusable Layout & Middleware Components
│   ├── auth_check.php               # Authentication check middleware (route guard)
│   ├── header.php                   # Reusable HTML <head>, navigation bar, user profile dropdown
│   ├── sidebar.php                  # Collapsible main navigation sidebar
│   └── footer.php                   # Reusable footer, modal skeletons, global JavaScript imports
│
├── pages/                           # Application Views & Pages
│   ├── login.php                    # User login page
│   ├── register.php                 # User registration page
│   ├── dashboard.php                # Master analytics dashboard & KPI metrics
│   ├── products.php                 # Product inventory table with filters & search
│   ├── product-add.php              # Create new product form
│   ├── product-edit.php             # Update existing product details
│   ├── categories.php               # Product categories CRUD management
│   ├── stock-in.php                 # Stock intake (receiving shipments / restock)
│   ├── stock-out.php                # Stock dispatch (sales, damage, write-offs)
│   ├── stock-history.php            # Transaction audit log / stock movement history
│   ├── suppliers.php                # Supplier directory CRUD management
│   └── reports.php                  # Stock valuation & low-inventory reporting
│
├── api/                             # Backend Request Handlers & API Endpoints
│   ├── auth.php                     # Authentication actions (login, register, logout)
│   ├── products.php                 # Product CRUD backend operations
│   ├── categories.php               # Category CRUD backend operations
│   ├── stock.php                    # Transaction processor (atomic stock adjustments)
│   └── suppliers.php                # Supplier CRUD backend operations
│
└── assets/                          # Static Frontend Assets
    ├── css/
    │   ├── style.css                # Global styles, variables, typography, utilities
    │   ├── auth.css                 # Dedicated styles for login & register screens
    │   └── dashboard.css            # Styles for dashboard grid, tables, cards, badges
    ├── js/
    │   ├── main.js                  # Global scripts: sidebar toggler, modal handler, alerts
    │   ├── auth.js                  # Frontend validation for login/registration forms
    │   ├── products.js              # Live search, sorting, filtering, delete confirmations
    │   ├── stock.js                 # Dynamic stock calculations, validation, SKU lookup
    │   └── dashboard.js             # KPI metric computations and status indicators
    └── img/                         # Application logos, product placeholder icons
```

---

## 📄 Page-by-Page Functionality & Architecture

### Root Entry

#### 1. `index.html`
- **Role**: Front Gateway / Root entry redirector.
- **Functionality**:
  - Automatically redirects incoming traffic to the application's authentication flow or dashboard.
  - Ensures seamless compatibility across both local Apache/XAMPP web servers and Vercel edge deployment without requiring complex rewrite rules.

---

### Authentication Pages

#### 2. `pages/login.php`
- **Role**: User authentication view.
- **Functionality**:
  - Displays a clean, responsive login card with fields for `Username/Email` and `Password`.
  - Client-side validation for empty fields and valid email/username formats.
  - Submits credentials asynchronously via AJAX or standard POST to `api/auth.php?action=login`.
  - Renders inline error alerts on invalid credentials.
  - Contains navigation link to `register.php`.

#### 3. `pages/register.php`
- **Role**: User sign-up view.
- **Functionality**:
  - Registration form with fields: `Full Name`, `Username`, `Email`, `Password`, `Confirm Password`, and initial `Role` (`Admin` or `Staff`).
  - Client-side password strength meter and confirmation matching check.
  - Submits to `api/auth.php?action=register`.
  - Automatically redirects to login upon successful user creation.

---

### Core Application Pages

#### 4. `pages/dashboard.php`
- **Role**: Executive summary and inventory overview.
- **Functionality**:
  - **KPI Cards**:
    - **Total Products**: Count of unique active items.
    - **Total Stock Value**: Sum of `(quantity * unit_price)` across all products.
    - **Low Stock Warnings**: Count of items where `quantity <= min_stock_threshold`.
    - **Out of Stock**: Count of items where `quantity = 0`.
  - **Quick Action Bar**: Instant buttons for *New Stock In*, *New Stock Out*, and *Add Product*.
  - **Stock Status Summary**: Visual indicators or progress meters for inventory health.
  - **Recent Activity Ledger**: Table displaying the latest 5–10 stock movements (timestamp, product, transaction type, quantity, user).

#### 5. `pages/products.php`
- **Role**: Master inventory catalog table.
- **Functionality**:
  - Tabular list of all products with columns: `SKU`, `Product Name`, `Category`, `Supplier`, `Unit Price`, `Current Stock`, `Status Badge` (In Stock / Low Stock / Out of Stock), and `Actions`.
  - **Live Search & Filter**: Instant filtering by product name, SKU, category, or stock status without full page reload.
  - **Actions per item**:
    - *View*: Opens modal with complete item metadata.
    - *Edit*: Directs to `product-edit.php?id={product_id}`.
    - *Delete*: Confirmation dialog to safely remove or deactivate the product.
  - Button to navigate to `product-add.php`.

#### 6. `pages/product-add.php`
- **Role**: New product creation interface.
- **Functionality**:
  - Form fields:
    - `Product Name` (Required)
    - `SKU / Barcode` (Manual input or auto-generated unique identifier)
    - `Category` (Dynamic dropdown loaded from `categories` table)
    - `Supplier` (Dynamic dropdown loaded from `suppliers` table)
    - `Cost Price` & `Selling / Unit Price`
    - `Initial Quantity` (Default: 0)
    - `Minimum Stock Level / Reorder Point` (Threshold alert trigger)
    - `Description / Notes`
  - Validates SKU uniqueness prior to submission.
  - Submits to `api/products.php?action=create`.

#### 7. `pages/product-edit.php`
- **Role**: Product modification interface.
- **Functionality**:
  - Reads `id` parameter from URL query string.
  - Pre-populates all existing product details for modification.
  - Prevents arbitrary direct stock manipulation here (directing users to use official Stock In/Out workflows to preserve audit integrity).
  - Submits updates to `api/products.php?action=update`.

#### 8. `pages/categories.php`
- **Role**: Product taxonomy management.
- **Functionality**:
  - Displays all product categories with their respective product counts.
  - Inline or modal form to add a new category (Name, Description).
  - Edit category name/description.
  - Delete category (includes verification checks to prevent deletion of categories with linked products).

#### 9. `pages/stock-in.php`
- **Role**: Inventory intake / shipment receiving.
- **Functionality**:
  - Used when new stock arrives from suppliers or returns.
  - Fields:
    - `Select Product`: Searchable dropdown displaying current available stock.
    - `Quantity Added`: Positive integer validation.
    - `Supplier`: Source vendor.
    - `Reference / Invoice Number`: Tracking invoice or purchase order number.
    - `Notes`: Description of delivery condition or shipment details.
  - Submits to `api/stock.php?action=stock_in`.
  - Atomically increments product stock count and writes an entry to `stock_transactions`.

#### 10. `pages/stock-out.php`
- **Role**: Inventory dispatch / sales / wastage processing.
- **Functionality**:
  - Used when items are sold, consumed, damaged, or transferred.
  - Fields:
    - `Select Product`: Displays real-time stock balance.
    - `Quantity Deducted`: Validated to ensure deducted amount does not exceed available stock.
    - `Reason / Type`: Dropdown with options: `Sale`, `Damage / Spoilage`, `Internal Consumption`, `Return to Vendor`.
    - `Reference / Order Number`: Optional invoice or order reference.
    - `Notes`: Reason description.
  - Submits to `api/stock.php?action=stock_out`.
  - Atomically decrements product stock count and creates movement audit log.

#### 11. `pages/stock-history.php`
- **Role**: Immutable movement audit log.
- **Functionality**:
  - Comprehensive historical table showing all inventory modifications.
  - Columns: `Date/Time`, `Reference No.`, `Product Name`, `SKU`, `Movement Type` (IN in green badge, OUT in red badge), `Quantity`, `Stock Before`, `Stock After`, `Logged By (User)`, `Reason/Notes`.
  - Date-range filter and transaction type filter (All, In Only, Out Only).
  - Search by SKU or Product Name.

#### 12. `pages/suppliers.php`
- **Role**: Vendor & supplier directory.
- **Functionality**:
  - Table of suppliers: `Company Name`, `Contact Person`, `Email`, `Phone`, `Address`, `Linked Products`.
  - Modal/Form to add and edit supplier records.
  - Delete supplier with protective checks against deleting suppliers with active stock history.

#### 13. `pages/reports.php`
- **Role**: Analytical reporting and exports.
- **Functionality**:
  - **Low Stock Report**: Immediate tabular view of items below reorder thresholds for purchasing teams.
  - **Valuation Report**: Financial breakdown of total inventory value by category and product.
  - **Exporting Options**: Client-side triggers for CSV export and browser Print formatting.

---

### Backend Endpoints & Action Handlers

All API handlers in `/api/` accept POST or GET requests, validate session permissions, sanitize inputs, execute parameterized PDO statements, and return standardized JSON responses:
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

- **`api/auth.php`**: Handles session login, registration with `password_hash()`, and session destruction (`logout`).
- **`api/products.php`**: Handles product creation, retrieval, updates, and deletion.
- **`api/categories.php`**: Handles category CRUD operations.
- **`api/stock.php`**: Uses database transactions (`beginTransaction`, `commit`, `rollBack`) to guarantee atomic stock increments/decrements alongside audit log creation.
- **`api/suppliers.php`**: Handles supplier CRUD operations.

---

### Shared Components & Configuration

- **`config/db.php`**: Establishes a secure PDO connection. Reads environment variables (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL`) with fallback defaults for local XAMPP (`localhost`, `root`, blank password, `core_inventory`).
- **`config/config.php`**: Manages site name, base URL resolution, and timezone defaults (`date_default_timezone_set`).
- **`includes/auth_check.php`**: Session guard included at the start of all protected pages. Redirects unauthenticated visitors to `login.php`.
- **`includes/header.php`**: Global HTML `<head>`, CSS links, top navigation bar, and profile dropdown.
- **`includes/sidebar.php`**: Semantic sidebar containing navigation links with active state highlighting.
- **`includes/footer.php`**: Closes container elements, renders footer text, and imports core JavaScript bundles.

---

## 🗄 Database Architecture

The relational schema is configured in `database/schema.sql` with five core entities:

```
+---------------+        +----------------------+        +-------------------+
|  categories   |        |       products       |        |     suppliers     |
+---------------+        +----------------------+        +-------------------+
| id (PK)       |<---+   | id (PK)              |   +--->| id (PK)           |
| name          |    +---| category_id (FK)     |   |    | name              |
| description   |        | supplier_id (FK)     |---+    | contact_person    |
| created_at    |        | sku (UNIQUE)         |        | email             |
+---------------+        | name                 |        | phone             |
                         | description          |        | address           |
                         | cost_price           |        | created_at        |
                         | unit_price           |        +-------------------+
                         | quantity             |
                         | min_threshold        |
                         | created_at           |
                         | updated_at           |
                         +----------------------+
                                    |
                                    | 1
                                    |
                                    | N
                         +----------------------+        +-------------------+
                         |  stock_transactions  |        |       users       |
                         +----------------------+        +-------------------+
                         | id (PK)              |        | id (PK)           |
                         | product_id (FK)      |        | full_name         |
                         | user_id (FK)         |------->| username (UNIQUE) |
                         | type (IN / OUT)      |        | email (UNIQUE)    |
                         | quantity             |        | password_hash     |
                         | balance_after        |        | role              |
                         | reference_no         |        | created_at        |
                         | notes                |        +-------------------+
                         | created_at           |
                         +----------------------+
```

---

## ☁️ Cloud Deployment Strategy

### 1. Railway (MySQL Database Hosting)
1. Sign in to [Railway](https://railway.app/).
2. Create a **New Project** -> Select **Provision MySQL**.
3. Once provisioned, navigate to the **Variables** or **Connect** tab.
4. Copy the connection credentials:
   - `MYSQLHOST` (Host)
   - `MYSQLPORT` (Port, usually 3306 or dynamic)
   - `MYSQLUSER` (User)
   - `MYSQLPASSWORD` (Password)
   - `MYSQLDATABASE` (Database name)
5. Open Railway's **Query** interface or connect via any database GUI (TablePlus, DBeaver, phpMyAdmin) using the connection URL and run the script from `database/schema.sql` to initialize all tables.

### 2. Vercel (Frontend & PHP Serverless Deployment)
Vercel natively supports static sites and Node.js/Python/Go serverless runtimes. To run standard PHP applications on Vercel, we utilize the community-standard runtime `vercel-php`.

#### `vercel.json` Configuration:
```json
{
  "functions": {
    "api/**/*.php": {
      "runtime": "vercel-php@0.7.3"
    },
    "pages/**/*.php": {
      "runtime": "vercel-php@0.7.3"
    }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/(.*)", "dest": "/$1" }
  ]
}
```

#### Steps to Deploy on Vercel:
1. Push this repository to GitHub.
2. In [Vercel](https://vercel.com/), click **Add New Project** and import the GitHub repository.
3. In the project **Settings -> Environment Variables**, add the Railway database credentials:
   - `DB_HOST` = `<Railway host>`
   - `DB_PORT` = `<Railway port>`
   - `DB_NAME` = `<Railway database name>`
   - `DB_USER` = `<Railway user>`
   - `DB_PASSWORD` = `<Railway password>`
4. Click **Deploy**. Vercel will build the serverless functions and host the application.

---

## 💻 Local Development Setup (XAMPP)

1. **Clone / Place Project**:
   Ensure this folder resides in your XAMPP web root:
   ```
   C:\xampp\htdocs\Core_inventory
   ```
2. **Start Services**:
   Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Database Setup**:
   - Navigate to `http://localhost/phpmyadmin/`.
   - Create a new database named `core_inventory`.
   - Import `database/schema.sql`.
4. **Environment Configuration**:
   - Copy `.env.example` to `.env` if using environment tools, or rely on `config/db.php` defaults (which automatically connect to `localhost:3306`, user: `root`, blank password, DB: `core_inventory`).
5. **Access Application**:
   Open your browser and navigate to:
   ```
   http://localhost/Core_inventory/
   ```

---

## 🔑 Environment Variables Reference

| Variable Name | Description | Default (Local) | Railway Example |
| :--- | :--- | :--- | :--- |
| `DB_HOST` | Database host server | `127.0.0.1` | `viaduct.proxy.rlwy.net` |
| `DB_PORT` | Database port | `3306` | `48215` |
| `DB_NAME` | Database schema name | `core_inventory` | `railway` |
| `DB_USER` | Database username | `root` | `root` |
| `DB_PASSWORD` | Database user password | *(empty)* | `secret_password` |
| `APP_ENV` | Environment mode | `development` | `production` |

---

## 🚦 Development Rules & Next Steps

> **Notice**: As per architectural planning guidelines, the folder structure and structural blueprints are established first. Application implementation code must not be authored until the structural layout and documentation are approved.

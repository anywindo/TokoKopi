# ☕ TokoKopi - Coffee Shop Management System

**TokoKopi** is a comprehensive web-based management system designed to streamline operations for a coffee shop chain. It provides distinct dashboards for Administrators, Branch Managers, and Corporate Executives to manage users, track daily revenue, and monitor stock usage across multiple branches.

## 🚀 Project Overview

The system is built to handle the daily operational data flow of a coffee shop business. It allows:

- **Managers** to report daily sales and ingredient usage.
- **Corporate** to visualize trends and analyze performance across all branches.
- **Admins** to manage the system's users and branch infrastructure.

## ✨ Key Features by Role

### 🛡️ Admin (`/views/admin.xhtml`)

The Administrator has full control over the system's configuration.

- **User Management**:
  - Create, edit, and delete user accounts.
  - Assign roles (Manager, Corporate).
  - Link Managers to specific branches.
- **Branch Management**:
  - Add and manage coffee shop branches (Name, Address).

### 👔 Branch Manager (`/views/manager.xhtml`)

Managers are responsible for the day-to-day reporting of their specific assigned branch.

- **Daily Revenue Reporting**: Submit daily gross revenue (_omzet_) reports.
- **Stock Usage Reporting**: Track daily consumption of key ingredients:
  - Arabica, Robusta, Liberica, Decaf (in Kg)
  - Milk (in Liters)
- **History & Filtering**: View past reports with filters (Today, Yesterday, Last 7 Days, This Month, Custom Range).

### 💼 Corporate (`/views/corporate.xhtml`)

Corporate users have a high-level view of the entire business performance.

- **Executive Dashboard**:
  - Visual charts for **7-Day Revenue Trends**.
  - **Average Daily Stock Usage** analysis.
  - **Revenue by Branch** comparison.
- **Global Reporting**: Access revenue and stock reports for **all branches**.
- **Advanced Filtering**: Filter data by specific branches and date ranges to generate targeted insights.
- **Branch Management**: Ability to manage branch details.

## 🛠️ Technology Stack

- **Backend**: PHP (Native)
- **Database**: MySQL / MariaDB
- **Frontend**:
  - XHTML 1.0 Strict (Structure)
  - **TailwindCSS** (Styling via CDN)
  - **Chart.js** (Data Visualization)
  - Vanilla JavaScript (Logic & Fetch API)
- **Server**: Apache (via XAMPP)

## 📂 Database Schema

The system uses a relational database (`pwd_coffeeshop`) with the following key tables:

- `users`: Stores credentials, roles, and branch associations.
- `branch`: Stores branch locations and details.
- `omzet`: Records daily revenue reports linked to branches and reporters.
- `pemakaian`: Records daily ingredient usage linked to branches.

## ⚙️ Installation & Setup

1.  **Environment**: Ensure you have XAMPP (or similar AMP stack) installed.
2.  **Database**:
    - Open phpMyAdmin.
    - Create a database named `pwd_coffeeshop`.
    - Import the `pwd_coffeeshop.sql` file located in the root directory.
3.  **Configuration**:
    - Verify database credentials in `api/koneksi.php`.
4.  **Run**:
    - Place the project folder in `htdocs`.
    - Access via browser: `http://localhost/TokoKopi/`

## 🔒 Security

- **Password Hashing**: Uses PHP's `password_hash()` (Bcrypt) for secure password storage.
- **Session Management**: Role-based access control (RBAC) ensures users can only access authorized views.

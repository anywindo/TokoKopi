# brew. Project Blueprint

## 1. Project Overview
**brew.** is a web-based coffee shop management system. It facilitates user registration, authentication, and role-based access control for different user types (Manager, Corporate).

## 2. Technology Stack
- **Backend**: Native PHP (Version 8.2.4 compatible)
- **Frontend**: XHTML 1.0 Strict, CSS, Vanilla JavaScript
- **Database**: MySQL/MariaDB
- **Server Environment**: XAMPP (Apache + MySQL)

## 3. Directory Structure
```
/brew.
├── index.xhtml          # Landing page (Root)
├── pwd_coffeeshop.sql   # Database schema dump
├── backend/             # PHP scripts for logic & DB interaction
│   ├── koneksi.php      # Database connection
│   ├── login.php        # Login processing
│   ├── register.php     # Registration processing
│   └── get_branches.php # API to fetch branches
└── frontend/            # User Interface
    ├── css/             # Stylesheets
    ├── js/              # Client-side scripts
    ├── login.xhtml      # Login page
    ├── register.xhtml   # Registration page
    ├── manager.xhtml    # Dashboard for Managers
    └── corporate.xhtml  # Dashboard for Corporate users
```

## 4. Database Schema (`pwd_coffeeshop`)
### Tables
1.  **`branch`**
    *   `id_branch` (PK, int, auto_increment)
    *   `nama` (varchar)
    *   `alamat` (text)
2.  **`users`**
    *   `id_user` (PK, int, auto_increment)
    *   `username` (varchar, unique)
    *   `password` (varchar, hashed)
    *   `role` (enum: 'manager', 'corporate')
    *   `telp` (varchar)
    *   `id_branch` (FK -> branch.id_branch)
3.  **`omzet`** (Turnover Reports)
    *   `id_laporan` (PK)
    *   `id_pelapor` (FK -> users.id_user)
    *   `id_branch` (FK -> branch.id_branch)
    *   `tanggal` (date)
    *   `omzet` (float)
4.  **`pemakaian`** (Usage Reports)
    *   `id_laporan` (PK)
    *   `id_pelapor` (FK -> users.id_user)
    *   `id_branch` (FK -> branch.id_branch)
    *   `tanggal` (date)
    *   `arabica`, `robusta`, `liberica`, `decaf`, `susu` (float)

## 5. Key Workflows

### A. Database Connection (`backend/koneksi.php`)
-   Host: `localhost`
-   User: `root`
-   Password: `` (empty)
-   Database: `pwd_coffeeshop`

### B. Registration Flow
1.  **UI**: `frontend/register.xhtml`
2.  **Dynamic Data**: `frontend/js/register.js` fetches branches from `backend/get_branches.php` to populate the branch dropdown.
3.  **Processing**: `backend/register.php`
    *   Validates inputs (password match, branch selection).
    *   Checks if username exists.
    *   Hashes password using `PASSWORD_ARGON2ID`.
    *   Inserts new user into `users` table.
    *   Redirects to `frontend/registerSukses.xhtml` on success.

### C. Login Flow
1.  **UI**: `frontend/login.xhtml`
2.  **Processing**: `backend/login.php`
    *   Validates credentials.
    *   Verifies password hash.
    *   **Role-Based Redirect**:
        *   `manager` -> `frontend/manager.xhtml`
        *   `corporate` -> `frontend/corporate.xhtml`

## 6. Setup Instructions
1.  Place the `brew.` folder in the web server's root (e.g., `htdocs`).
2.  Import `pwd_coffeeshop.sql` into your MySQL database.
3.  **Crucial Step**: Manually insert at least one record into the `branch` table, otherwise registration will fail as it requires a valid branch selection.

## 7. Limitations & Constraints (Current State)
### A. Missing Functionality
1.  **Empty Dashboards**: The `manager.xhtml` and `corporate.xhtml` pages are currently **empty placeholders**. They contain no logic, data visualization, or forms.
2.  **No Branch Management**: There is **no UI to create, edit, or delete branches**. All branch management must be done directly via SQL in the database.
3.  **No Data Entry**: There are no forms implemented to submit turnover (`omzet`) or usage (`pemakaian`) reports, even though the database supports it.
4.  **No Reporting**: There is no functionality to view or analyze the data stored in the `omzet` and `pemakaian` tables.

### B. Technical Limitations
1.  **Session Management**: The application **does not use PHP Sessions**. Login redirects users but does not persist their state. This means:
    *   Users are not truly "logged in".
    *   Pages like `manager.xhtml` are not protected and can be accessed directly via URL without authentication.
2.  **Security**:
    *   **No CSRF Protection**: Forms are vulnerable to Cross-Site Request Forgery.
    *   **Basic Error Handling**: The backend uses `die()` which stops script execution abruptly, offering a poor user experience.
3.  **Input Validation**: While basic validation exists (e.g., checking for empty fields), there is no advanced sanitization or validation for data types and formats.

## 8. Assignment Compliance Matrix (Tugas Besar)
This section maps the current project state against the specific requirements of the "Developing Web Application" assignment.

### A. Core Requirements (Ketentuan Project)
| Requirement | Status | Notes |
| :--- | :--- | :--- |
| **1. Native PHP Backend** | ✅ **Met** | Uses pure PHP 8.2 without frameworks. |
| **2. Frontend HTML/CSS/JS** | ✅ **Met** | Uses XHTML, CSS, and Vanilla JS. |
| **3. Registration Feature** | ✅ **Met** | Fully implemented in `register.xhtml` & `register.php`. |
| **4. Password Encryption** | ✅ **Met** | Uses `password_hash()` with `PASSWORD_ARGON2ID`. |
| **5. Login Feature** | ✅ **Met** | Fully implemented in `login.xhtml` & `login.php`. |
| **6. Profile View/Update** | ❌ **Not Met** | No page exists to view or edit user profile. |
| **7. CRUD Operations (Min 2)** | ⚠️ **Partially Met** | **Create** (User) is done. **Read/Update/Delete** for business data (e.g., Reports, Branches) are missing. |

### B. Bonus Features
| Feature | Status | Notes |
| :--- | :--- | :--- |
| **1. Admin Dashboard** | ⚠️ **Partially Met** | `manager.xhtml` exists but is empty. No data display yet. |
| **2. Geolocation** | ❌ **Not Met** | No geolocation features implemented. |
| **3. Live Username Check** | ❌ **Not Met** | Backend checks on submit, but no frontend AJAX check before submit. |
| **4. Profile Photo** | ❌ **Not Met** | No file upload capability for profile photos. |

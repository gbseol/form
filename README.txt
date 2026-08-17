================================================================================
  COMPUTER LAB MANAGEMENT SYSTEM
  Production-ready PHP/MySQL web application for InfinityFree hosting
================================================================================

A complete, self-contained Computer Lab Management System built with plain
PHP 8+, MySQL, HTML5, CSS3, JavaScript, Bootstrap 5 and AJAX.

No Node.js, Composer, Python, Laravel or any paid services are required.
Just upload the files, import the database and log in.

--------------------------------------------------------------------------------
TABLE OF CONTENTS
--------------------------------------------------------------------------------
  1. Requirements
  2. File / folder structure
  3. Installation on InfinityFree (step by step)
  4. Default login
  5. User roles & permissions
  6. Features
  7. Reports & exports
  8. Backup & restore
  9. Security notes
  10. Troubleshooting
  11. License
  12. Local development (Linux / XAMPP)

--------------------------------------------------------------------------------
1. REQUIREMENTS
--------------------------------------------------------------------------------
  - PHP 8.0 or newer (InfinityFree runs PHP 8.2+)
  - MySQL 5.7 / 8.0 (provided by InfinityFree)
  - A web browser (Chrome, Firefox, Edge, Safari)
  - No terminal / SSH / Composer / Node.js needed at all

  REQUIRED PHP EXTENSIONS
    - pdo_mysql (database)
    - gd         (image handling / chart images)
    - mbstring   (string handling)
    - xml        (XML parsing)
    - fileinfo   (upload MIME validation)
    - zip        (Excel .xlsx export - WITHOUT it the Excel export fails
                  with "Class ZipArchive not found")

  On Debian/Ubuntu install them all with:
    sudo apt-get install -y php-cli php-mysql php-gd php-mbstring php-xml php-zip

--------------------------------------------------------------------------------
2. FILE / FOLDER STRUCTURE
--------------------------------------------------------------------------------

  computer-management-system/
  ├── index.php                 Entry point (redirects to login/dashboard)
  ├── login.php                 Login page
  ├── logout.php                Logout handler
  ├── dashboard.php             Dashboard with charts
  ├── dashboard_ajax.php        AJAX endpoint feeding the dashboard charts
  ├── php.ini                   Optional PHP settings for InfinityFree
  ├── .htaccess                 Root Apache security rules
  ├── README.txt                This file
  │
  ├── config/
  │   ├── config.php            <-- EDIT: database credentials
  │   └── .htaccess             Denied to browsers
  │
  ├── includes/
  │   ├── auth.php              Sessions, roles, permissions
  │   ├── csrf.php              CSRF protection helpers
  │   ├── db.php                PDO connection (prepared statements)
  │   ├── functions.php         Helpers (escaping, logs, settings, uploads)
  │   ├── computer_form.php     Computer form validation & photo saving
  │   ├── header.php            Layout header (sidebar/topbar)
  │   ├── footer.php            Layout footer
  │   ├── print_header.php      Print layout header
  │   ├── print_footer.php      Print layout footer
  │   └── .htaccess             Denied to browsers
  │
  ├── database/
  │   ├── database.sql          <-- IMPORT this file
  │   └── .htaccess             Denied to browsers
  │
  ├── computers/                Computer inventory (list/add/edit/view/...)
  ├── users/                    User management
  ├── labs/                     Lab management
  ├── reports/                  Reports + CSV/Excel/print exports
  ├── settings/                 Settings, profile, password, backup/restore
  │
  ├── uploads/
  │   ├── computers/            Uploaded computer photos
  │   └── logos/                Uploaded site logo
  │
  └── assets/
      ├── css/style.css         Application styles
      ├── css/print.css         Printable report styles
      ├── js/main.js            Application JavaScript (AJAX, charts, toggles)
      └── images/               favicon + default logo

--------------------------------------------------------------------------------
3. INSTALLATION ON INFINITYFREE (STEP BY STEP)
--------------------------------------------------------------------------------

STEP 1 - Create a MySQL database
  1. Log in to your InfinityFree control panel.
  2. Click "MySQL Databases" in the menu.
  3. Create a database. A username/password is generated for you.
  4. Write down the four values exactly as shown:
        Database Host      (e.g. sql123.infinityfree.com)
        Database Name      (e.g. if0_12345678_computers)
        Database Username  (e.g. if0_12345678)
        Database Password
     IMPORTANT: on InfinityFree the host is NEVER "localhost".

STEP 2 - Import the database
  1. Click the "phpMyAdmin" button next to your database.
  2. Select your database in the left panel (it must be selected!).
  3. Click the "Import" tab.
  4. Choose the file database/database.sql from this package.
  5. Click "Go". You should see a green "Import has been successfully finished".
  6. The tables users, labs, computers, computer_photos, activity_logs and
     settings are now created with a default Super Admin account.

STEP 3 - Upload the files
  1. In the control panel go to "File Manager" (or use FTP).
  2. Open the /htdocs folder of your domain (e.g. yourdomain.infinityfreeapp.com).
  3. Upload EVERY file and folder of this project into /htdocs.
     You can upload the whole "computer-management-system" folder so the site
     runs at yourdomain.infinityfreeapp.com/computer-management-system/, or
     upload the CONTENTS directly into /htdocs to run at the domain root.
  4. Make sure the uploads/computers and uploads/logos folders exist and that
     PHP can write to them (InfinityFree permissions are normally fine as is).
  5. Upload php.ini together with the files (optional but recommended).

STEP 4 - Update the configuration
  1. Open the File Manager and edit /htdocs/config/config.php
     (right-click the file -> Edit).
  2. Replace the four DB_* values with the ones from STEP 1.
  3. Save the file.

STEP 5 - Log in
  1. Visit your site (e.g. yourdomain.infinityfreeapp.com or
     yourdomain.infinityfreeapp.com/computer-management-system/).
  2. You will be redirected to the login page.
  3. Log in with the default Super Admin account (see section 4).
  4. IMPORTANT: change the default password immediately
     (Settings -> Change Password).

That is it. The application is fully functional.

--------------------------------------------------------------------------------
4. DEFAULT LOGIN
--------------------------------------------------------------------------------

  Username : admin
  Password : Admin@123

  Role     : Super Admin (full control)

  CHANGE THIS PASSWORD AS SOON AS YOU LOG IN.

--------------------------------------------------------------------------------
5. USER ROLES & PERMISSIONS
--------------------------------------------------------------------------------

  SUPER ADMIN
    - Everything: manage users, reset passwords, manage computers, labs,
      reports, exports, activity logs, settings, backup & restore.

  ADMIN
    - Add / edit / delete computers
    - Manage labs
    - View reports and export them (CSV / Excel / PDF)
    - Create and edit STAFF accounts (cannot manage Super Admin or Admins,
      cannot delete users)

  STAFF
    - Add computers
    - Edit computer details
    - Update computer status
    - Cannot delete computers, cannot delete users, no report access

--------------------------------------------------------------------------------
6. FEATURES
--------------------------------------------------------------------------------

  DASHBOARD
    - Cards: total, working, not working, has some issues, labs, users
    - Charts (Chart.js, loaded over AJAX):
        Computers per Lab | Computers by Status | Monthly updates | Recent activity

  COMPUTERS
    - Full inventory with every requested field:
      Computer ID, Asset Number, Name, Lab, Department, Desk Number,
      CPU, Motherboard, RAM, RAM Slots, Storage Type, Storage Capacity,
      Graphics Card, Power Supply, Monitor (brand/size/serial/condition),
      Keyboard (brand/condition), Mouse (brand/condition), UPS, UPS battery,
      Printer, Scanner, Speaker, Webcam, LAN/WiFi/Bluetooth status,
      IP Address, MAC Address, Windows, Office, Antivirus, Purchase Date,
      Warranty Expiry, Vendor, Invoice Number, Last/Next Service Date,
      Status, Remarks and multiple photos.
    - Statuses: Working, Not Working, Has Some Issues
    - Quick AJAX status change directly from the list
    - Add / Edit / View / Delete / Duplicate / Print / Export
    - Search by Computer ID, Asset Number, Name, CPU, IP, MAC, Lab, Department
    - Filters by Lab, Status, RAM, CPU, Storage, Windows Version, Purchase Year
    - Pagination

  LABS
    - Multiple labs (Lab 1, Lab 2, Lab 3, Office, Library, ...)
    - Admin can create, edit and delete labs

  ACTIVITY LOG
    - Every action is recorded: user, date, time, IP, action, old value,
      new value. Filters by date range and user. Viewable by Super Admin.

  REPORTS
    - Inventory report, Not Working PCs, Working PCs, Lab report, User activity
    - Export to CSV and Excel (.xls, opens in Microsoft Excel)
    - Printable PDF-style pages via the browser print dialog
      (Print / Save as PDF)

  SETTINGS
    - Company/School name
    - Logo upload
    - Theme colour (drives the whole interface accent)
    - My profile
    - Change password
    - Backup database (downloads a .sql dump)
    - Restore database (upload a .sql dump)

--------------------------------------------------------------------------------
7. REPORTS & EXPORTS
--------------------------------------------------------------------------------

  Every report page has buttons for:
    - CSV   : proper CSV file with UTF-8 BOM (opens correctly in Excel)
    - Excel : .xls file generated without any external library
    - Print : opens a print-friendly page; use your browser's
              "Print / Save as PDF" to produce a PDF.

  Report URLs:
    reports/index.php            Report hub
    reports/inventory.php        All computers
    reports/faulty.php           Not Working PCs
    reports/working.php          Working PCs
    reports/lab.php?lab_id=1     One lab
    reports/activity.php         Activity log (Super Admin)

--------------------------------------------------------------------------------
8. BACKUP & RESTORE
--------------------------------------------------------------------------------

  - Settings -> Download Database Backup
      Produces a complete .sql dump using pure PHP (no shell access required,
      which is exactly what InfinityFree needs).
  - Settings -> Restore Database
      Upload a previously downloaded .sql file. A confirmation checkbox is
      required. The restore is executed inside a transaction.

  Also keep copies of the uploads/ folder (computer photos and logo) if you
  need a complete backup.

--------------------------------------------------------------------------------
9. SECURITY NOTES
--------------------------------------------------------------------------------

  The application follows secure coding practices:
    - All database queries use PDO prepared statements (SQL injection safe)
    - Passwords hashed with password_hash() / bcrypt
    - CSRF tokens on every data-changing form and AJAX request
    - Sessions hardened (httponly cookie, same-site, regenerate on login)
    - All output escaped with htmlspecialchars() (XSS safe)
    - Login brute-force lockout (5 attempts -> 15 minutes)
    - Role-based access control on every page and action
    - Uploaded files validated by MIME type and random file names
    - /uploads folders cannot execute PHP scripts
    - /config, /includes and /database are denied to browsers

  Recommended extra steps on InfinityFree:
    - Change the default admin password right away.
    - Keep config.php credentials secret.
    - Do not store the database.sql dump under /htdocs in production
      (the .htaccess already blocks it, but remove it anyway if you want).

--------------------------------------------------------------------------------
10. TROUBLESHOOTING
--------------------------------------------------------------------------------

  "Class ZipArchive not found" (Excel export fails)
    -> The PHP zip extension is not installed. On Debian/Ubuntu run:
         sudo apt-get install -y php-zip
       then restart the web server / PHP service. See section 12 for the full
       list of required extensions.

  "Unknown column 'permissions' in 'SET'" (profile photo upload or
   user edit fails)
    -> The database was restored from an OLD dump
       (database/computer_management_backup_20260809_174643.sql) whose users
       table lacks the `permissions` column that the current code uses.
       Fix it once with:
         mysql computer_management -e "ALTER TABLE users ADD COLUMN permissions TEXT NULL AFTER status;"
       Fresh installs from database/database.sql already include the column.

  "Database connection failed"
    -> config/config.php contains wrong DB_HOST / DB_NAME / DB_USER / DB_PASS.
       Double check the values in the InfinityFree control panel. The host is
       usually NOT localhost.

  "Table ... doesn't exist"
    -> The database.sql was not imported, or was imported into the wrong
       database. Import it again with your database selected in phpMyAdmin.

  "CSRF token validation failed"
    -> Your session expired. Go back, reload the page and try again.

  Charts do not appear on the dashboard
    -> Chart.js is loaded from a CDN. Make sure your browser has internet
       access. The dashboard cards still work without the charts.

  Photos fail to upload
    -> Check the php.ini upload size limits and the space quota of your
       InfinityFree account. Keep photos under 5 MB each.

  Logged out while editing a large form
    -> Hosting session timeout. Work in shorter sessions and re-login.
       You can increase session.gc_maxlifetime in php.ini if you wish.

--------------------------------------------------------------------------------
11. LICENSE
--------------------------------------------------------------------------------
  Free to use for any organisation or school. Modify and distribute freely.
  Provided as-is without warranty.

-------------------------------------------------------------------------------
12. LOCAL DEVELOPMENT (RUNNING ON YOUR OWN MACHINE)
-------------------------------------------------------------------------------
  Everything you need to run this system locally for previewing and testing.
  Verified on Debian/Ubuntu Linux with MariaDB and the PHP built-in server;
  the same steps apply to XAMPP/WAMP on Windows or macOS.

  STEP 1 - Install PHP (with all required extensions) and MariaDB/MySQL
    Debian / Ubuntu:
      sudo apt-get update
      sudo apt-get install -y php-cli php-mysql php-gd php-mbstring php-xml php-zip mariadb-server
    XAMPP: PHP, MariaDB and every required extension are already bundled.
    Do NOT skip php-zip - the Excel (.xlsx) export requires it.

  STEP 2 - Start the database server
    Linux:
      sudo service mariadb start
    Windows / macOS (XAMPP):
      Start "Apache" and "MySQL" from the XAMPP control panel.

  STEP 3 - Create the database, the user and import the schema
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS computer_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER IF NOT EXISTS 'cms_user'@'localhost' IDENTIFIED BY 'cms_pass';
    CREATE USER IF NOT EXISTS 'cms_user'@'127.0.0.1' IDENTIFIED BY 'cms_pass';
    GRANT ALL PRIVILEGES ON computer_management.* TO 'cms_user'@'localhost';
    GRANT ALL PRIVILEGES ON computer_management.* TO 'cms_user'@'127.0.0.1';
    FLUSH PRIVILEGES;"
    mysql computer_management < database/database.sql

    These credentials already match config/config.php (DB_HOST=127.0.0.1,
    DB_NAME=computer_management, DB_USER=cms_user, DB_PASS=cms_pass).
    Edit config/config.php if you use different ones.

  STEP 4 - Start the PHP development server
    cd /path/to/this/project
    php -S 0.0.0.0:8000

  STEP 5 - Open the app
    http://localhost:8000  and log in with  admin / Admin@123
    Change the default password immediately after your first login
    (Settings -> Change Password).

  IMPORTANT - WHEN RESTORING THE OLD BACKUP FILE
    database/computer_management_backup_20260809_174643.sql comes from an
    older database whose `users` table has NO `permissions` column. The current
    code uses that column, so after restoring that dump you MUST add it:

      mysql computer_management -e "ALTER TABLE users ADD COLUMN permissions TEXT NULL AFTER status;"

    If you skip this, editing a user or uploading a profile photo fails with
    "Unknown column 'permissions' in 'SET'". Fresh installs from
    database/database.sql already include the column and need no fix.

  TIP - Fresh demo data
    database/computer_management_data.sql contains ready-made computers, labs
    and users so the dashboard is not empty. Import it after the schema if you
    want demo content. Other files in the database/ folder are full backups
    created from the Settings -> Backup page.

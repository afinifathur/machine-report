# Official Demonstration Database (v1)

This directory contains the official demonstration dataset for the Machine Report Management (MRM) System.

## Database File
* **Filename**: `machine_report_demo_v1.sql`
* **Purpose**: Restores the complete set of master data, demo machines, schedules, maintenance templates, user roles, and execution histories for testing, evaluation, and development.

---

## ⚠️ Important Safety Guidelines

> [!WARNING]
> * **DO NOT OVERWRITE PRODUCTION**: This SQL dump is intended **only** for demonstrations, User Acceptance Testing (UAT), and development environments. Importing this file will overwrite existing tables.
> * **PRODUCTION BACKUPS**: Active production databases must always be backed up independently. Never use this file as a replacement or backup source for production.

---

## Restore Instructions

You can restore this demonstration dataset using one of the following methods:

### Method 1: Using Command Line (CLI)

1. Open your terminal or command prompt.
2. Run the following command (replace `username` with your database user, e.g. `root`):

```bash
mysql -u username -p machine_report < database/demo/machine_report_demo_v1.sql
```

3. Enter your database password when prompted.

### Method 2: Using phpMyAdmin

1. Open **phpMyAdmin** in your browser.
2. Select the `machine_report` database from the left sidebar (create it first if it does not exist).
3. Click on the **Import** tab at the top menu.
4. Click **Choose File** and select `database/demo/machine_report_demo_v1.sql`.
5. Scroll to the bottom and click **Import** (or **Go**).

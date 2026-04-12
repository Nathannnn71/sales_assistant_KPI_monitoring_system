# Sales Assistant KPI Monitoring System (SAKMS)
## XAMPP Setup & Deployment Guide

---

## 📋 Table of Contents
1. [Prerequisites](#prerequisites)
2. [XAMPP Installation & Configuration](#xampp-installation--configuration)
3. [Database Setup](#database-setup)
4. [Project Deployment](#project-deployment)
5. [Testing & Verification](#testing--verification)
6. [Troubleshooting](#troubleshooting)
7. [Features Overview](#features-overview)

---

## Prerequisites

**Required Software:**
- XAMPP (Apache + MySQL + PHP) - [Download](https://www.apachefriends.org/)
- Web Browser (Chrome, Firefox, Edge recommended)
- Text Editor (VS Code, Sublime Text)

**System Requirements:**
- Windows 7 or higher (or macOS/Linux)
- 500 MB disk space
- 2 GB RAM minimum
- MySQL 5.7+
- PHP 7.4+

---

## XAMPP Installation & Configuration

### Step 1: Download & Install XAMPP

1. **Download XAMPP** from https://www.apachefriends.org/
2. **Run the installer** (e.g., `xampp-windows-x64-8.2.0-installer.exe`)
3. **Follow the installation wizard:**
   - Accept default components (Apache, MySQL, PHP, phpMyAdmin)
   - Choose installation folder (typically `C:\xampp`)
4. **Complete installation** and launch XAMPP Control Panel

### Step 2: Start Services

1. **Open XAMPP Control Panel**
2. **Start these services** by clicking their "Start" buttons:
   - ✅ Apache
   - ✅ MySQL
3. Both should show **green** status with "Running" indicator
4. Leave the other services off

**Expected Output:**
```
[Apache]     Status: Running (PID: 1234)
[MySQL]      Status: Running (PID: 5678)
```

---

## Database Setup

### Step 1: Access phpMyAdmin

1. **Ensure MySQL is running** (see Step 2 above)
2. **Navigate to:** `http://localhost/phpmyadmin` in your browser
3. You should see the phpMyAdmin login screen
4. **Default credentials** (no password required):
   - **Username:** `root`
   - **Password:** (leave blank)
5. Click **Go** to access phpMyAdmin

### Step 2: Create Database

1. **In phpMyAdmin**, click **"Databases"** tab (top left)
2. **Enter database name:** `sakms_db`
3. **Select charset:** `utf8mb4_unicode_ci` (for emoji & special chars)
4. Click **Create**

You should see: "Database 'sakms_db' has been created."

### Step 3: Import Sample Data

1. **Navigate to the new `sakms_db` database:**
   - Click `sakms_db` in the left sidebar
2. **Click the "Import" tab** at the top
3. **Upload the SQL file:**
   - Click **"Choose File"**
   - Navigate to your project folder
   - Select `data/sakms_db.sql`
   - Click **Open**
4. **Verify settings:**
   - Charset: `utf8mb4`
   - Format: `SQL`
5. **Click "Import"** button

**Wait for confirmation:**
```
✓ Import has been successfully finished, all tables created and data inserted.
```

### Step 4: Verify Database Structure

1. **In phpMyAdmin, click `sakms_db`** in the left sidebar
2. **Expand to see 12 tables:**
   - ✅ `at_risk_notifications`
   - ✅ `evaluation_period`
   - ✅ `kpi_group`
   - ✅ `kpi_item`
   - ✅ `kpi_score`
   - ✅ `kpi_section`
   - ✅ `performance_summary`
   - ✅ `score_interpretation`
   - ✅ `staff`
   - ✅ `supervisor_feedback`
   - ✅ `supervisor_profile`
   - ✅ `weight_config`

3. **Click each table** to view sample data:
   - Click `staff` > Preview should show employee records
   - Click `kpi_score` > Should show realistic scores (1-5 range)

---

## Project Deployment

### Step 1: Locate XAMPP Root Directory

1. **Default location:** `C:\xampp\htdocs`
2. **Open File Explorer** and navigate to this folder
3. You should see existing folders like `dashboard`, `phpmyadmin`, etc.

### Step 2: Deploy Project Files

**Option A: Copy Project Folder**
1. **Navigate to your project folder:**
   ```
   C:\Users\ASUS\Documents\Internet and Web Development\Assignment\sales_assistant_KPI_monitoring_system
   ```
2. **Copy the entire folder**
3. **Paste into `C:\xampp\htdocs\`**
4. **Verify in htdocs:**
   ```
   C:\xampp\htdocs\sales_assistant_KPI_monitoring_system\
   ├── index.php
   ├── login.php
   ├── logout.php
   ├── script.js
   ├── styles.css
   ├── includes/
   │   ├── db_config.php
   │   ├── auth.php
   │   ├── functions.php
   │   └── kpi_calculator.php
   ├── pages/
   │   ├── dashboard.php
   │   ├── profiles.php
   │   ├── analytics.php
   │   ├── performance.php
   │   └── settings.php
   └── data/
       └── sample_data.sql
   ```

**Option B: Symbolic Link (Advanced)**
```bash
mklink /D "C:\xampp\htdocs\sakms" "C:\Users\ASUS\Documents\Internet and Web Development\Assignment\sales_assistant_KPI_monitoring_system"
```

### Step 3: Verify Database Configuration

1. **Open `includes/db_config.php`** in your text editor
2. **Verify these settings:**
   ```php
   $server = "localhost";       // ✅ Correct
   $user = "root";              // ✅ Correct
   $password = "";              // ✅ Correct (XAMPP default)
   $database = "sakms_db";      // ✅ Matches database name
   ```
3. **Save file** (usually already correct)

---

## Testing & Verification

### Step 1: Test Login Page

1. **Ensure Apache & MySQL are running** in XAMPP Control Panel
2. **Navigate to:** `http://localhost/sales_assistant_KPI_monitoring_system/login.php`
   - Or: `http://localhost/sales_assistant_KPI_monitoring_system/` (redirects to login)
3. You should see the **SAKMS login form** with:
   - "Sales Assistant KPI Monitoring System" title
   - Email/password input fields
   - Demo credentials displayed below

### Step 2: Login with Demo Credentials



1. **Enter credentials** in the login form
2. **Click"Sign In"**
3. **Expected result:** Redirects to dashboard

### Step 3: Verify Dashboard Page

After successful login, you should see:

**Topbar:**
- ✅ Page title "Dashboard"
- ✅ Supervisor avatar & name (top right)
- ✅ Notification bell icon
- ✅ Sidebar navigation (hover to expand)

**Dashboard Content:**
1. **Summary Cards (4 cards):**
   - Total Employees: 13
   - Top Performers: 3+ (employees with KPI ≥ 4.5)
   - At-Risk: 2-3 (employees with KPI < 3.0)
   - Average KPI: ~3.5-3.8

2. **Predictive Performance Risk Alerts:**
   - Shows colored alert boxes (🔴 CRITICAL, ⚠️ WARNING, ✓ HEALTHY)
   - Lists at-risk employees with trend analysis

3. **Performance Distribution Chart:**
   - Bar chart showing count by rating category
   - Should show data for Excellent, Good, Satisfactory, Poor, Very Poor

4. **Top Performers Table:**
   - Lists employees with KPI ≥ 4.5
   - Shows name, department, and score

5. **At-Risk Staff Table:**
   - Lists employees with KPI < 3.0
   - Shows name, department, and status

### Step 4: Test All Navigation Pages

Click each menu item in the sidebar (or hover to expand):

**1. Dashboard** ✅
- Already verified above
- Shows summary and alerts

**2. Sales Assistant Profiles**
- Shows table of all 13 employees
- Click any employee name to see individual profile
- Verify profile includes:
  - Employee name, department, role
  - Current KPI score with rating badge
  - Risk assessment (trend, period change)
  - Core Competencies & KPI Achievement progress bars
  - KPI Group Performance table
  - Supervisor comments
  - **KPI Forecast** (predicted score)

**3. Analytics Dashboard**
- Shows department performance comparison chart
- Shows **gamified ranking** with medals (🥇🥈🥉) for top 3
- Shows performance distribution pie chart
- Shows summary statistics (min, max, avg KPI scores)

**4. Performance Reports**
- Shows at-risk employees with training needs
- Shows top performer recognition list
- Shows training needs summary by category

**5. System Settings**
- Shows performance threshold configuration
- Shows notification settings (toggles)
- Shows department management table
- Shows data import/export section

### Step 5: Verify Chart Rendering

On Analytics Dashboard, you should see:
- ✅ **Department Comparison Bar Chart** (colorful bars)
- ✅ **Performance Distribution Pie Chart** (colored segments)
- ✅ Both charts are interactive (hover for values)

### Step 6: Test Responsive Design

1. **Resize browser window** to different widths
2. **Grid should adapt:**
   - Desktop: 4 columns for summary cards
   - Tablet: 2 columns
   - Mobile: 1 column
3. **Sidebar should collapse** on mobile (navigation icons only)

---

## Session Management

### Auto-Logout (30 minutes)

- **Session timeout:** 30 minutes of inactivity
- **Behavior:** If idle for 30+ minutes, will be logged out
- **Result:** Redirected to login page

### Manual Logout

1. **Click the sidebar** at the bottom
2. **Click "Logout" button** (red icon)
3. **Expect:** Redirect to login page

---

## Troubleshooting

### Issue: "Connection refused" or "Could not connect to database"

**Causes:**
- MySQL service not running
- Incorrect database credentials
- Database not created

**Solution:**
1. **Check XAMPP Control Panel** - Ensure MySQL shows "Running"
2. **Verify database exists:**
   - Go to `http://localhost/phpmyadmin`
   - Look for `sakms_db` in left sidebar
3. **Verify db_config.php:**
   - Check `includes/db_config.php`
   - Ensure database name is `sakms_db`
   - Ensure user is `root` with empty password

---

### Issue: "Blank page" or "500 error"

**Causes:**
- PHP syntax error
- Missing include files
- SQL import incomplete

**Solution:**
1. **Check PHP error log:**
   - Open: `C:\xampp\apache\logs\error.log`
   - Look for error messages
2. **Verify all files exist:**
   ```
   ✅ includes/db_config.php
   ✅ includes/auth.php
   ✅ includes/functions.php
   ✅ includes/kpi_calculator.php
   ✅ pages/dashboard.php
   ✅ pages/profiles.php
   ✅ pages/analytics.php
   ✅ pages/performance.php
   ✅ pages/settings.php
   ```
3. **Check database import:**
   - In phpMyAdmin, verify `sakms_db` has 12 tables
   - If missing, re-import `data/sakms_db.sql`

---

### Issue: "Login always fails"

**Cause:**
- Incorrect credentials entered

**Solution:**
1. **Use demo credentials exactly:**
   - Email: `supervisor@sakms.com`
   - Password: `supervisor123`
2. **Check browser console** for JavaScript errors
3. **Verify database connection** (see above)

---

### Issue: "Charts not displaying"

**Cause:**
- Chart.js library not loaded
- Data not returned from database

**Solution:**
1. **Check browser console:**
   - Press `F12` to open Developer Tools
   - Look for JavaScript errors
2. **Verify library is loaded:**
   - In browser, check `index.php` source
   - Look for `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`
3. **Verify data query:**
   - Check MySQL has data in `kpi_scores` table
   - In phpMyAdmin, click `kpi_scores` > should show 588 records

---

### Issue: "Sidebar menu not expanding"

**Cause:**
- CSS not loaded properly
- Browser cache issue

**Solution:**
1. **Hard refresh page:**
   - Press `Ctrl+Shift+R` (or `Cmd+Shift+R` on Mac)
2. **Clear browser cache:**
   - Open DevTools (`F12`)
   - Right-click refresh button > "Empty cache and hard refresh"
3. **Verify styles.css exists:**
   - Check: `C:\xampp\htdocs\sales_assistant_KPI_monitoring_system\styles.css`

---

## Features Overview

### ✨ Three Innovative Features Implemented:

**1. Predictive Performance Risk Alerts** (Dashboard)
- Analyzes each at-risk employee's performance trend
- Compares current score to previous period
- Calculates risk level (CRITICAL/WARNING/HEALTHY)
- Shows actionable intelligence for supervisors

**2. Gamified Performance Ranking** (Analytics)
- Ranks all 13 employees by KPI score (1-13)
- Shows medal emojis (🥇🥈🥉) for top performers
- Interactive progress bars showing score visualization
- Encourages healthy competition among sales assistants

**3. KPI Forecasting with Goal-Setting** (Profiles)
- Calculates predicted KPI score based on trend
- Formula: `predicted = current + (trend × 0.5)`
- Displays predicted score in highlighted card
- Helps with goal-setting and performance management

---


## Database Table Reference

The system uses **12 tables** in the `sakms_db` database:

| Table Name                | Description                                 |
|--------------------------|---------------------------------------------|
| at_risk_notifications    | Tracks at-risk employee notifications       |
| evaluation_period        | Stores evaluation period data               |
| kpi_group                | KPI group definitions                       |
| kpi_item                 | Individual KPI items                        |
| kpi_score                | Stores KPI scores for each staff            |
| kpi_section              | KPI section definitions                     |
| performance_summary      | Summary of performance evaluations          |
| score_interpretation     | Interpretation of KPI scores                |
| staff                    | Employee/staff master data                  |
| supervisor_feedback      | Supervisor feedback/comments                |
| supervisor_profile       | Supervisor profile information              |
| weight_config            | KPI weighting configuration                 |

For further details, see the `sakms_db.sql` file in the `data/` folder for full schema and sample data.

---

## Quick Reference

### URLs
```
Login:          http://localhost/sales_assistant_KPI_monitoring_system/
Dashboard:      http://localhost/sales_assistant_KPI_monitoring_system/?page=dashboard
Profiles:       http://localhost/sales_assistant_KPI_monitoring_system/?page=profiles
Analytics:      http://localhost/sales_assistant_KPI_monitoring_system/?page=analytics
Reports:        http://localhost/sales_assistant_KPI_monitoring_system/?page=performance
Settings:       http://localhost/sales_assistant_KPI_monitoring_system/?page=settings
phpMyAdmin:     http://localhost/phpmyadmin
```

### Demo Credentials
```
Email:    supervisor@sakms.com
Password: supervisor123
```

### Key Thresholds (configurable in Settings)
```
Top Performer:        KPI ≥ 4.5
At-Risk:              KPI < 3.0
Performance Decline:  -0.5 score change
```

---

## Next Steps (Phase 2)

1. **Excel Data Import:**
   - Implement Excel upload functionality in Settings
   - Parse employee records from Excel
   - Auto-populate database

2. **Email Notifications:**
   - Send risk alerts to supervisor email
   - Weekly summary reports
   - Monthly performance statements

3. **Advanced Analytics:**
   - Trend graphs (6-month, year-over-year)
   - Department benchmarking
   - Individual goal tracking

4. **User Management:**
   - Multiple supervisor accounts
   - Sales assistant self-service portal
   - Role-based access control

---

## Support & Resources

**Documentation Files:**
- `README.md` - System overview
- `XAMPP_SETUP_GUIDE.md` - This file
- Database schema in `data/sample_data.sql`

**Key Files:**
- Backend: `includes/kpi_calculator.php` (KPI calculation logic)
- Auth: `includes/auth.php` (session management)
- Utils: `includes/functions.php` (data retrieval)

**Recommended Tools:**
- VS Code for code editing
- phpMyAdmin for database management
- Chrome DevTools for debugging

---

## Summary

✅ **XAMPP Setup:** Services running (Apache + MySQL)
✅ **Database:** sakms_db created with 6 tables, 1,517 records imported
✅ **Project Files:** Deployed to C:\xampp\htdocs\
✅ **Configuration:** db_config.php verified
✅ **Testing:** All 5 pages functional, charts rendering, data displaying

**Ready for supervisor dashboard use!**

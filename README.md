# Cybersecurity Risk Management Dashboard (CRMD)

**A centralized dashboard for monitoring and managing cybersecurity risks**

- **Student**: Vaileth Aloyce Mkaakaa (23051012318)
- **Program**: Ordinary Diploma in Cybersecurity and Digital Forensics (NTA Level 06)
- **Institution**: Arusha Technical College
- **Supervisor**: Mr. Peter Simalike
- **Year**: 2026

---

## 📋 Project Overview

The Cybersecurity Risk Management Dashboard (CRMD) is a web-based application designed to provide organizations with a centralized platform for monitoring, analyzing, and managing cybersecurity risks. It replaces manual, fragmented approaches with real-time visualization and comprehensive incident tracking.

### Key Features

✅ **User Authentication & Role-Based Access Control (RBAC)**
- Secure login with session management
- Admin (full CRUD) and Viewer (read-only) roles
- 30-minute session timeout
- CSRF token protection

✅ **Risk Management**
- Record and track cybersecurity risks
- Auto-calculated risk levels (High/Medium/Low)
- Filter by status (Open/Mitigated/Closed)
- Risk scoring based on Likelihood × Impact

✅ **Incident Tracking**
- Log security incidents with severity levels
- Track resolution status
- Filter by date range
- Quick incident management

✅ **Data Visualization**
- Risk distribution pie chart
- Incident trends (last 6 months)
- Dashboard widgets with KPIs
- Real-time data updates via AJAX

✅ **Reporting**
- Comprehensive security reports
- Printable/PDF export (browser print)
- Date range filtering
- Risk and incident analytics

✅ **Security Features**
- Prepared statements (PDO) for SQL injection prevention
- Output escaping (htmlspecialchars) for XSS prevention
- Password hashing (password_hash/bcrypt)
- CSRF token validation on all forms
- Session regeneration after login

✅ **Responsive UI**
- Bootstrap 5 framework
- Mobile-friendly design
- Collapsible navigation
- Professional styling

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6), Bootstrap 5 |
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ |
| **Charts** | Chart.js 3.9.1 |
| **Icons** | Font Awesome 6.4.0 |
| **Development** | XAMPP/WAMP Stack |
| **Deployment** | Docker, Render.com |

---

## 📁 Project Structure

```
crmd/
├── assets/
│   ├── css/
│   │   └── style.css           # Custom styling
│   └── js/
│       └── dashboard.js        # JavaScript functionality
├── config/
│   └── database.php            # Database configuration
├── includes/
│   ├── header.php              # HTML header template
│   ├── footer.php              # HTML footer template
│   ├── auth.php                # Authentication functions
│   └── functions.php           # Utility functions
├── api/
│   └── chart-data.php          # JSON API for charts
├── sql/
│   └── database.sql            # Database schema & sample data
├── index.php                   # Dashboard homepage
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── risks.php                   # Risk management
├── incidents.php               # Incident tracking
├── reports.php                 # Security reports
├── Dockerfile                  # Docker configuration
├── .gitignore                  # Git ignore rules
└── README.md                   # This file
```

---

## 🚀 Local Setup Instructions

### Prerequisites

- **XAMPP** or **WAMP** (Apache + PHP 7.4+ + MySQL)
- **phpMyAdmin** (comes with XAMPP/WAMP)
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Clone or Download the Project

```bash
# Clone from GitHub
git clone https://github.com/yourusername/crmd.git
cd crmd

# OR copy files to htdocs
# XAMPP: C:\xampp\htdocs\crmd
# WAMP: C:\wamp64\www\crmd
```

### Step 2: Import Database

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click **"New"** to create a new database
3. Name it: **`crmd_db`**
4. Click **"Create"**
5. Select the `crmd_db` database
6. Click the **"Import"** tab
7. Choose the file: `sql/database.sql`
8. Click **"Import"**

✅ Database is now set up with sample data!

### Step 3: Configure Database Connection

Edit `config/database.php` if your MySQL credentials differ:

```php
$db_host = 'localhost';      // Usually localhost
$db_name = 'crmd_db';        // Database name
$db_user = 'root';           // MySQL username
$db_pass = '';               // MySQL password (empty for XAMPP/WAMP default)
```

### Step 4: Access the Application

Open your browser and navigate to:

```
http://localhost/crmd
```

You will be redirected to the login page.

### Step 5: Login

Use the default credentials:

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Viewer | `viewer` | `viewer123` |

> **Important**: Change these credentials in production!

---

## 📊 Default Sample Data

The database includes:

- **2 Users**: admin (admin123) and viewer (viewer123)
- **8 Risks**: ranging from High to Low levels
- **6 Incidents**: with various severity levels

These are provided for testing and demonstration purposes.

---

## 🔐 Security Features Implemented

### SQL Injection Prevention
- ✅ All queries use prepared statements (PDO)
- ✅ No string concatenation in SQL

### Cross-Site Scripting (XSS) Prevention
- ✅ Output escaped with `htmlspecialchars()`
- ✅ HTML entities converted

### Cross-Site Request Forgery (CSRF) Prevention
- ✅ CSRF tokens generated for all forms
- ✅ Tokens validated on form submission

### Authentication & Session
- ✅ Passwords hashed with `password_hash()` (bcrypt)
- ✅ Sessions regenerated after login
- ✅ 30-minute auto-logout on inactivity
- ✅ Secure session handling

### Authorization (RBAC)
- ✅ Admin: Full CRUD operations
- ✅ Viewer: Read-only access
- ✅ Protected routes require login

---

## 📝 Usage Guide

### Dashboard
- **Home page** showing key metrics and charts
- Quick access to Risks, Incidents, and Reports
- Real-time data visualization

### Risk Management
1. **View**: Click "Risks" to see all recorded risks
2. **Add** (Admin only): Click "Add New Risk"
   - Fill in Name, Description
   - Set Likelihood (1-5) and Impact (1-5)
   - Risk Level auto-calculates
3. **Edit** (Admin only): Click the edit icon
4. **Delete** (Admin only): Click delete and confirm

**Risk Level Calculation**:
- High Risk: Likelihood × Impact ≥ 15
- Medium Risk: Likelihood × Impact 6-14
- Low Risk: Likelihood × Impact ≤ 5

### Incident Tracking
1. **Report**: Click "Report Incident"
2. **Fill Details**:
   - Title, Description, Date
   - Severity (High/Medium/Low)
   - Mark as Resolved (optional)
3. **Manage**: Edit or delete incidents (Admin only)
4. **Track**: View resolution status

### Reports
1. **Generate**: Click "Reports" menu
2. **Filter** (Optional):
   - Select start and end dates
   - Click "Apply Filter"
3. **View**: Summary statistics, risk analysis, incident analysis
4. **Print**: Click "Print" button or Ctrl+P

---

## 🌐 Deployment to Render.com

### Step 1: Create GitHub Repository

```bash
cd crmd
git init
git add .
git commit -m "Initial commit: CRMD application"
git branch -M main
git remote add origin https://github.com/yourusername/crmd.git
git push -u origin main
```

### Step 2: Set Up Remote Database

Choose one of these free options:

**Option A: Aiven (Recommended)**
1. Go to https://aiven.io
2. Sign up (free tier available)
3. Create a PostgreSQL database (or MySQL if preferred)
4. Get connection details:
   - Host, Port, Database, Username, Password

**Option B: FreeMySQL**
1. Go to https://www.freemysqlhosting.net
2. Register and create a database
3. Note the credentials

**Option C: Clever Cloud**
1. https://www.clever-cloud.com
2. Create MySQL addon
3. Get connection string

### Step 3: Connect Render to GitHub

1. Go to https://render.com
2. Click "New +"
3. Select "Web Service"
4. Connect your GitHub repository
5. Select the CRMD repository

### Step 4: Configure Render Deployment

**Settings**:

```
Name: crmd
Environment: Docker
Branch: main
```

### Step 5: Set Environment Variables

In Render dashboard, add:

```
DB_HOST=<your-database-host>
DB_NAME=<your-database-name>
DB_USER=<your-database-user>
DB_PASS=<your-database-password>
```

### Step 6: Update config/database.php

```php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'crmd_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
```

### Step 7: Deploy

- Click "Create Web Service"
- Render will build and deploy automatically
- Check logs for any errors
- Your site will be live at: `https://crmd-xxxxx.onrender.com`

### Step 8: Import Database on Remote

1. Use phpMyAdmin on remote host (if available)
2. Or import via command line:
   ```bash
   mysql -h <host> -u <user> -p <db> < sql/database.sql
   ```

---

## 📋 API Documentation

### Chart Data Endpoint

**URL**: `api/chart-data.php`

**Method**: `GET`

**Authentication**: Required (session login)

**Response**:
```json
{
  "success": true,
  "riskByLevel": {
    "high": 3,
    "medium": 2,
    "low": 1
  },
  "incidentsByMonth": {
    "2026-01": 5,
    "2026-02": 3,
    "2026-03": 7
  },
  "timestamp": "2026-05-09 10:30:45"
}
```

---

## 🐛 Troubleshooting

### "Database Connection Failed"
- Check `config/database.php` credentials
- Verify MySQL is running
- Ensure `crmd_db` database exists

### "Login page loops back to login"
- Clear browser cookies
- Check browser console for errors
- Verify database has users table

### Charts not loading
- Check browser console (F12) for errors
- Verify `api/chart-data.php` is accessible
- Ensure user is authenticated

### "Permission Denied" errors
- Check that you're logged in as admin
- Verify user role in database
- Clear session and login again

---

## 📱 Responsive Design

The dashboard is fully responsive:

| Device | Status |
|--------|--------|
| **Desktop** (≥1024px) | ✅ Optimized |
| **Tablet** (768-1023px) | ✅ Responsive |
| **Mobile** (≤767px) | ✅ Mobile-first |

---

## 🔑 Default Credentials (Change in Production!)

| User | Username | Password | Role |
|------|----------|----------|------|
| Admin | `admin` | `admin123` | Full CRUD |
| Viewer | `viewer` | `viewer123` | Read-only |
| Manager | `manager` | `admin123` | Full CRUD |

---

## 📄 File Size & Performance

- **Total Code**: ~2,500 lines
- **CSS**: ~500 lines
- **JavaScript**: ~300 lines
- **Optimized**: Yes (CDN for libraries)
- **Load Time**: < 2 seconds (typical)

---

## 🔄 Maintenance

### Regular Tasks

1. **Backup Database**:
   ```bash
   mysqldump -u root -p crmd_db > backup.sql
   ```

2. **Update Dependencies** (if using Composer):
   ```bash
   composer update
   ```

3. **Monitor Logs**:
   - Check `php_errors.log`
   - Review application logs

4. **Change Default Passwords**:
   - Login as admin
   - Update credentials in database

---

## 📚 References

- NIST Cybersecurity Framework
- OWASP Web Security Guidelines
- Bootstrap 5 Documentation: https://getbootstrap.com
- Chart.js Documentation: https://www.chartjs.org
- PHP PDO: https://www.php.net/manual/en/book.pdo.php

---

## 📞 Support & Contact

- **Institution**: Arusha Technical College
- **Website**: https://atc.ac.tz
- **Email**: rector@atc.ac.tz
- **Supervisor**: Mr. Peter Simalike

---

## 📄 License

This project is part of the Arusha Technical College curriculum. Use for educational purposes only.

---

## ✅ Checklist Before Deployment

- [ ] Database imported successfully
- [ ] All sample data appears in dashboard
- [ ] Login works with demo credentials
- [ ] Can add/edit/delete risks (as admin)
- [ ] Charts load and display data
- [ ] Reports generate correctly
- [ ] Mobile design is responsive
- [ ] No console errors in browser
- [ ] Session timeout works (30 minutes)
- [ ] CSRF tokens validate correctly
- [ ] Passwords are hashed in database

---

**Version**: 1.0  
**Last Updated**: May 2026  
**Status**: Production Ready ✅

---

## 🎓 Academic Credits

This dashboard was developed as a final year project in the Ordinary Diploma in Cybersecurity and Digital Forensics program at Arusha Technical College, in fulfillment of academic requirements.

**Project Development Methodology**: System Development Life Cycle (SDLC)  
**Design Pattern**: Three-Tier Architecture  
**Testing Approach**: Functional, Usability, and Security Testing

---

For questions or issues, please contact your supervisor or the ICT Department at Arusha Technical College.

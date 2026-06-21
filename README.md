# Cybersecurity Risk Management Dashboard (CRMD)

**A centralized SOC-style platform for monitoring and managing cybersecurity risks, incidents, and user activities**

- **Student**: Vaileth Aloyce Mkaakaa (23051012318)
- **Program**: Ordinary Diploma in Cybersecurity and Digital Forensics (NTA Level 06)
- **Institution**: Arusha Technical College
- **Supervisor**: Mr. Peter Simalike
- **Year**: 2026

---

## Project Overview

The Cybersecurity Risk Management Dashboard (CRMD) is a web-based application that provides organizations with a centralized SOC (Security Operations Center) platform for monitoring, analyzing, and managing cybersecurity risks, tracking incidents, and auditing user activities.

### Key Features

**Role-Based Dashboards**
- **Admin**: Full oversight of all risks, incidents, and user activities across the organization
- **Analyst**: Personal workspace showing their own created risks, incidents, and activity log
- **Viewer**: Read-only view of their own contributions and organizational reports

**Authentication & Access Control**
- Secure login with bcrypt password hashing
- Three distinct roles: Admin, Analyst, Viewer
- 30-minute session timeout with auto-logout
- CSRF token protection on all forms
- Login attempt logging (successful and failed)
- Account lockout tracking (failed login attempts)

**Risk Management**
- Record and track cybersecurity risks with auto-calculated risk levels
- Risk scoring: Critical (>=20), High (15-19), Medium (6-14), Low (<=5)
- Filter by status (Open/Mitigated/Closed)
- Track who created and last modified each risk

**Incident Tracking**
- Log security incidents with severity levels (Critical/High/Medium/Low)
- Track resolution status and assignment
- View reported-by information for accountability

**Activity Audit Log**
- Complete audit trail of all user actions
- Admin sees activities across all users
- Regular users see their own activity history
- Tracks: create/update/delete for risks and incidents, login/logout events

**Data Visualization**
- Risk distribution doughnut chart (Critical/High/Medium/Low)
- Incident severity bar chart
- SIEM-style event stream
- Dashboard KPIs and score cards

**Security Reporting**
- Comprehensive filtered reports
- Printable/PDF export
- Date range filtering
- Risk and incident analytics with creator attribution

**Security Features**
- Prepared statements (PDO) for SQL injection prevention
- Output escaping (htmlspecialchars) for XSS prevention
- Password hashing (password_hash/bcrypt)
- CSRF token validation on all forms
- Session regeneration after login
- IP address logging for audit trail

**Responsive UI**
- Bootstrap 5 framework with dark SOC theme
- Mobile-friendly design with collapsible sidebar
- Professional SOC-style styling

---

## Technology Stack

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

## Project Structure

```
crmd/
├── assets/
│   ├── css/
│   │   └── style.css           # Dark SOC-themed styling
│   └── js/
│       └── dashboard.js        # Charts, AJAX, utilities
├── config/
│   └── database.php            # PDO database configuration
├── includes/
│   ├── auth.php                # Authentication, session, CSRF
│   ├── functions.php           # CRUD operations, audit logging
│   ├── header.php              # HTML header + sidebar navigation
│   └── footer.php              # Footer + JS scripts
├── api/
│   └── chart-data.php          # JSON API for chart data
├── sql/
│   ├── database.sql            # Schema + sample data
│   └── database_enhanced.sql   # Enterprise SOC edition schema
├── index.php                   # Role-based dashboard homepage
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── risks.php                   # Risk management (role-filtered)
├── incidents.php               # Incident tracking (role-filtered)
├── reports.php                 # Security reports (role-filtered)
├── Dockerfile                  # Docker configuration
├── .gitignore                  # Git ignore rules
└── README.md                   # This file
```

---

## Role-Based Access Control

| Feature | Admin | Analyst | Viewer |
|---------|-------|---------|--------|
| View all risks/incidents | Yes | Own only | Own only |
| Create risks | Yes | No | No |
| Edit/Delete risks | Yes | No | No |
| Create incidents | Yes | No | No |
| Edit/Delete incidents | Yes | No | No |
| View activity log | All users | Own only | Own only |
| Generate reports | Yes (all data) | Yes (own data) | Yes (own data) |
| Dashboard scope | Global | Personal | Personal |

---

## Local Setup Instructions

### Prerequisites

- **XAMPP** or **WAMP** (Apache + PHP 7.4+ + MySQL)
- **phpMyAdmin** (comes with XAMPP/WAMP)
- Web browser

### Step 1: Clone or Download

```bash
git clone https://github.com/esaumagaro-dev/crmd.git
cd crmd
```

Copy to XAMPP: `C:\xampp\htdocs\crmd`

### Step 2: Import Database

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Create a new database named **`crmd_db`**
3. Select the database, click **"Import"** tab
4. Choose `sql/database.sql` and click **"Import"**

### Step 3: Configure Database Connection

Edit `config/database.php` if needed:

```php
$db_host = 'localhost';
$db_name = 'crmd_db';
$db_user = 'root';
$db_pass = '';
```

### Step 4: Access the Application

Navigate to: `http://localhost/crmd`

### Step 5: Login

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Viewer | `viewer` | `viewer123` |
| Analyst | `analyst` | `analyst123` |
| Manager | `manager` | `admin123` |

---

## Sample Data

The database includes:

- **4 Users**: admin, viewer, analyst, manager
- **8 Risks**: with varying levels and assigned creators
- **6 Incidents**: with severity levels and reporter attribution

---

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| **users** | User accounts with roles (admin, analyst, viewer), login tracking, and failed attempt counter |
| **risks** | Risk register with auto-calculated levels, ownership tracking (created_by/updated_by) |
| **incidents** | Incident records with severity levels, resolution status, and reporter attribution |
| **notifications** | User notification system (scoped per user or global) |
| **login_attempts** | Login audit trail (success/failure, IP, user agent) |
| **audit_log** | Full activity audit trail for all CRUD operations and authentication events |

---

## Security Features

### SQL Injection Prevention
- All queries use PDO prepared statements
- No string concatenation in SQL

### XSS Prevention
- All output escaped with `htmlspecialchars()` via `esc()` helper
- HTML entities properly encoded

### CSRF Protection
- Random 32-byte CSRF tokens on all forms
- Timing-safe comparison with `hash_equals()`

### Authentication
- Passwords hashed with `password_hash()` (bcrypt)
- Session regeneration after login
- 30-minute inactivity timeout
- Failed login attempt tracking

### Authorization (RBAC)
- Admin: Full CRUD operations across all data
- Analyst/Viewer: Scoped to own data only
- Server-side enforcement on all actions

---

## Usage Guide

### Dashboard

- **Admin**: SOC Command Center with global KPIs, all risks/incidents, and Organization Activity Log
- **Analyst**: Analyst Dashboard showing personal contributions and activity
- **Viewer**: My Dashboard with read-only view of personal scope

### Risk Management

1. **View**: All risks (admin) or My Risks (analyst/viewer)
2. **Add** (Admin only): Click "Add New Risk"
   - Fill in Name, Description
   - Set Likelihood (1-5) and Impact (1-5)
   - Risk Level auto-calculates
3. **Edit/Delete** (Admin only)

**Risk Level Calculation**:
- Critical: Likelihood x Impact >= 20
- High: Likelihood x Impact >= 15
- Medium: Likelihood x Impact 6-14
- Low: Likelihood x Impact <= 5

### Incident Tracking

1. **Report** (Admin only): Click "Report Incident"
2. **Fill Details**: Title, Description, Date, Severity
3. **Manage**: Edit or delete incidents (Admin only)
4. **Attribution**: Each incident tracks who reported it

### Reports

1. **Generate**: Click "Reports" menu
2. **Filter** (Optional): Select date range
3. **View**: Role-scoped statistics with creator attribution
4. **Print**: Click "Print" button or Ctrl+P

---

## API Documentation

### Chart Data Endpoint

**URL**: `api/chart-data.php`

**Method**: `GET`

**Authentication**: Required (session login)

**Response**:
```json
{
  "success": true,
  "riskByLevel": {
    "critical": 0,
    "high": 3,
    "medium": 3,
    "low": 2
  },
  "incidentsByMonth": {
    "2026-03": 3,
    "2026-04": 3
  },
  "timestamp": "2026-06-21 10:30:45"
}
```

---

## Troubleshooting

### "Database Connection Failed"
- Check `config/database.php` credentials
- Verify MySQL is running
- Ensure `crmd_db` database exists

### "Login page loops back to login"
- Clear browser cookies
- Verify database has users table with sample data

### Charts not loading
- Check browser console (F12) for errors
- Verify user is authenticated

### "Permission Denied" errors
- Verify user role in database
- Clear session and login again

---

## Responsive Design

| Device | Status |
|--------|--------|
| **Desktop** (>=1024px) | Optimized with sidebar |
| **Tablet** (768-1023px) | Collapsible navigation |
| **Mobile** (<=767px) | Mobile-first layout |

---

## Version History

**Version 2.0** - June 2026
- Role-based dashboards (Admin, Analyst, Viewer)
- Complete audit log for all user activities
- Scoped data views per user role
- Critical risk level support
- Activity tracking for login/logout and CRUD operations
- Creator attribution on risks and incidents

**Version 1.0** - May 2026
- Initial release with basic CRUD and authentication

---

## Academic Credits

This dashboard was developed as a final year project in the Ordinary Diploma in Cybersecurity and Digital Forensics program at Arusha Technical College.

**Methodology**: System Development Life Cycle (SDLC)
**Design Pattern**: Three-Tier Architecture
**Testing**: Functional, Usability, and Security Testing

---

For questions or issues, please contact the ICT Department at Arusha Technical College.

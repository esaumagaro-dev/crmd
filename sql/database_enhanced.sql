-- =============================================
-- Cybersecurity Risk Management Dashboard
-- Enhanced Database Schema (Enterprise SOC Edition)
-- =============================================
-- Compatible with XAMPP/WAMP MySQL
-- Student: Vaileth Aloyce Mkaakaa (23051012318)
-- Arusha Technical College - Final Year Project
-- =============================================

-- NOTE: Create database manually first, then import this SQL
-- CREATE DATABASE crmd_db;

-- =============================================
-- TABLE: users (Enhanced with RBAC)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    role ENUM('super_admin', 'admin', 'analyst', 'viewer', 'incident_responder') NOT NULL DEFAULT 'viewer',
    department VARCHAR(100) DEFAULT 'Security Operations',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL DEFAULT NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: audit_logs (New - Security Monitoring)
-- =============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) DEFAULT NULL,
    resource_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    details TEXT DEFAULT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    INDEX idx_severity (severity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: notifications (New - Alert System)
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: assets (New - Asset Monitoring)
-- =============================================
CREATE TABLE IF NOT EXISTS assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('server', 'workstation', 'network', 'database', 'application', 'cloud', 'iot') DEFAULT 'server',
    ip_address VARCHAR(45) DEFAULT NULL,
    hostname VARCHAR(255) DEFAULT NULL,
    os VARCHAR(100) DEFAULT NULL,
    criticality ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('active', 'inactive', 'maintenance', 'compromised') DEFAULT 'active',
    owner VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    last_scan TIMESTAMP NULL DEFAULT NULL,
    vulnerability_count INT DEFAULT 0,
    risk_score INT DEFAULT 0,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_criticality (criticality)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: vulnerabilities (New - CVE Tracking)
-- =============================================
CREATE TABLE IF NOT EXISTS vulnerabilities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cve_id VARCHAR(50) DEFAULT NULL,
    asset_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    cvss_score DECIMAL(3,1) DEFAULT 0.0,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'patched', 'mitigated', 'accepted') DEFAULT 'open',
    patch_available BOOLEAN DEFAULT FALSE,
    exploited_in_wild BOOLEAN DEFAULT FALSE,
    discovered_date DATE DEFAULT NULL,
    patched_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_asset (asset_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_cvss (cvss_score),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: risks (Enhanced)
-- =============================================
CREATE TABLE IF NOT EXISTS risks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    likelihood INT NOT NULL CHECK (likelihood >= 1 AND likelihood <= 5),
    impact INT NOT NULL CHECK (impact >= 1 AND impact <= 5),
    risk_level ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    status ENUM('Open', 'Mitigated', 'Closed', 'Accepted', 'Transferred') NOT NULL DEFAULT 'Open',
    category ENUM('technical', 'operational', 'compliance', 'strategic', 'reputational') DEFAULT 'technical',
    asset_id INT DEFAULT NULL,
    owner_id INT DEFAULT NULL,
    treatment_plan TEXT DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_risk_level (risk_level),
    INDEX idx_category (category),
    INDEX idx_asset (asset_id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: incidents (Enhanced)
-- =============================================
CREATE TABLE IF NOT EXISTS incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    incident_date DATE NOT NULL,
    detected_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    severity ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    status ENUM('New', 'Investigating', 'Contained', 'Eradicated', 'Recovered', 'Closed') NOT NULL DEFAULT 'New',
    category ENUM('malware', 'phishing', 'data_breach', 'ddos', 'insider', 'unauthorized_access', 'other') DEFAULT 'other',
    resolved BOOLEAN DEFAULT FALSE,
    resolved_date DATE DEFAULT NULL,
    assigned_to INT DEFAULT NULL,
    asset_id INT DEFAULT NULL,
    attack_vector VARCHAR(255) DEFAULT NULL,
    mitre_attack_id VARCHAR(50) DEFAULT NULL,
    ioc_data TEXT DEFAULT NULL,
    evidence_files TEXT DEFAULT NULL,
    root_cause TEXT DEFAULT NULL,
    lessons_learned TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_date (incident_date),
    INDEX idx_category (category),
    INDEX idx_assigned (assigned_to),
    INDEX idx_asset (asset_id),
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: incident_timeline (New - Incident Workflow)
-- =============================================
CREATE TABLE IF NOT EXISTS incident_timeline (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_incident (incident_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: login_attempts (New - Security Monitoring)
-- =============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    success BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_created (created_at),
    INDEX idx_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT DEFAULT USERS
-- =============================================
-- Passwords: admin123, viewer123, analyst123, responder123
INSERT INTO users (username, password_hash, email, full_name, role, department) VALUES 
('admin', '$2y$10$h3dv7hYsEBiT63mcbnzSGewKtr/flNH3k.n2nqRM4h04LXUuWToGO', 'admin@crmd.local', 'System Administrator', 'super_admin', 'IT Security'),
('viewer', '$2y$10$KN3KCCeYXBIG5cBdZEvrYOdeqsdQGqGREa1Fx0Hu0wcaqVwAv6jlK', 'viewer@crmd.local', 'Guest Viewer', 'viewer', 'Security Operations'),
('manager', '$2y$10$h3dv7hYsEBiT63mcbnzSGewKtr/flNH3k.n2nqRM4h04LXUuWToGO', 'manager@crmd.local', 'Security Manager', 'admin', 'IT Security'),
('analyst', '$2y$10$W7D7afDhrMAq94rekwqG2.B6XjrpYdj8Zkk941Xo/Qo37M8hpUejO', 'analyst@crmd.local', 'SOC Analyst', 'analyst', 'Security Operations'),
('responder', '$2y$10$RGAIQaPLv2IU6NME3M8A3ewwfoyI.hOWMLMz0qFOuKRtU5fpEDofG', 'responder@crmd.local', 'Incident Responder', 'incident_responder', 'Incident Response');

-- =============================================
-- INSERT SAMPLE ASSETS
-- =============================================
INSERT INTO assets (name, type, ip_address, hostname, os, criticality, status, owner, location) VALUES 
('Primary Web Server', 'server', '192.168.1.10', 'web-srv-01', 'Ubuntu 22.04', 'critical', 'active', 'IT Team', 'Data Center A'),
('Database Server', 'database', '192.168.1.20', 'db-srv-01', 'Ubuntu 22.04', 'critical', 'active', 'DBA Team', 'Data Center A'),
('Firewall', 'network', '192.168.1.1', 'fw-01', 'PAN-OS 10.2', 'critical', 'active', 'Network Team', 'Data Center A'),
('Admin Workstation', 'workstation', '192.168.1.100', 'admin-ws-01', 'Windows 11', 'high', 'active', 'Admin User', 'Office Floor 2'),
('CRM Application', 'application', '192.168.1.30', 'crm-app-01', 'Linux', 'high', 'active', 'Dev Team', 'Cloud AWS'),
('Employee Laptops', 'workstation', '192.168.2.0/24', 'Various', 'Windows 11/MacOS', 'medium', 'active', 'HR Team', 'Office Building');

-- =============================================
-- INSERT SAMPLE VULNERABILITIES
-- =============================================
INSERT INTO vulnerabilities (cve_id, asset_id, title, description, cvss_score, severity, status, patch_available, exploited_in_wild, discovered_date) VALUES 
('CVE-2024-1234', 1, 'Apache HTTP Server Buffer Overflow', 'A buffer overflow vulnerability in Apache HTTP Server allows remote attackers to execute arbitrary code.', 9.8, 'critical', 'open', TRUE, TRUE, '2026-05-01'),
('CVE-2024-5678', 2, 'MySQL Privilege Escalation', 'A local privilege escalation vulnerability in MySQL Server allows authenticated users to gain DBA privileges.', 7.8, 'high', 'in_progress', TRUE, FALSE, '2026-05-10'),
('CVE-2024-9012', 3, 'Firewall Management Interface XSS', 'Cross-site scripting vulnerability in firewall management interface allows stored XSS attacks.', 5.4, 'medium', 'patched', TRUE, FALSE, '2026-04-15'),
('CVE-2024-3456', 4, 'Windows Print Spooler EoP', 'Windows Print Spooler local elevation of privilege vulnerability (PrintNightmare variant).', 8.8, 'high', 'open', TRUE, TRUE, '2026-05-15'),
('CVE-2024-7890', 5, 'CRM Application SQL Injection', 'SQL injection vulnerability in CRM application search functionality.', 8.5, 'high', 'open', FALSE, TRUE, '2026-05-18');

-- =============================================
-- INSERT SAMPLE RISKS (Enhanced)
-- =============================================
INSERT INTO risks (name, description, likelihood, impact, risk_level, status, category, asset_id, treatment_plan, due_date) VALUES 
('Malware Infection', 'Risk of malware spreading through email attachments and phishing attacks targeting employee workstations.', 4, 4, 'High', 'Open', 'technical', 4, 'Implement advanced email filtering and endpoint protection. Conduct security awareness training.', '2026-06-30'),
('Unauthorized Access', 'Potential unauthorized access to sensitive systems due to weak passwords and lack of MFA.', 3, 5, 'Critical', 'Open', 'technical', 1, 'Implement MFA across all critical systems. Enforce strong password policy.', '2026-06-15'),
('Data Breach', 'Exposure of customer and employee personal data through unencrypted databases or application vulnerabilities.', 2, 5, 'High', 'Mitigated', 'compliance', 2, 'Encrypt all sensitive data at rest and in transit. Implement DLP solutions.', '2026-05-30'),
('System Downtime', 'Critical systems becoming unavailable due to DDoS attacks, hardware failure, or ransomware.', 3, 3, 'Medium', 'Open', 'operational', 1, 'Implement redundant systems and DDoS protection. Establish disaster recovery plan.', '2026-07-31'),
('Insider Threat', 'Disgruntled employees or contractors accessing and stealing confidential information.', 2, 4, 'Medium', 'Open', 'operational', 2, 'Implement least privilege access. Deploy UEBA solution for anomaly detection.', '2026-08-15'),
('Weak Encryption', 'Use of outdated encryption protocols (TLS 1.0/1.1) allowing attackers to intercept data.', 2, 3, 'Medium', 'Closed', 'technical', 3, 'Upgrade all systems to TLS 1.3. Disable legacy protocols.', '2026-04-30'),
('Social Engineering', 'Employees tricked into revealing passwords or sensitive information via phone/email.', 4, 2, 'Medium', 'Open', 'operational', 4, 'Conduct regular security awareness training. Implement phishing simulation program.', '2026-07-15'),
('Ransomware Attack', 'Critical files encrypted by ransomware, demanding payment for decryption.', 2, 5, 'High', 'Open', 'technical', 1, 'Implement robust backup strategy. Deploy EDR solution. Segment network.', '2026-06-30'),
('Supply Chain Attack', 'Compromise through third-party vendors or software supply chain vulnerabilities.', 2, 4, 'Medium', 'Accepted', 'strategic', 5, 'Implement vendor risk assessment program. Monitor supply chain for threats.', '2026-09-30'),
('Cloud Misconfiguration', 'Improperly configured cloud services exposing sensitive data or allowing unauthorized access.', 3, 4, 'High', 'Mitigated', 'technical', 5, 'Implement CSPM solution. Regular cloud security assessments.', '2026-06-15');

-- =============================================
-- INSERT SAMPLE INCIDENTS (Enhanced)
-- =============================================
INSERT INTO incidents (title, description, incident_date, severity, status, category, resolved, asset_id, attack_vector, mitre_attack_id, root_cause) VALUES 
('Phishing Email Campaign', 'Employees received phishing emails targeting corporate credentials. 15 users clicked on malicious links. Credentials were harvested and used for lateral movement.', '2026-04-15', 'High', 'Investigating', 'phishing', FALSE, 4, 'Email phishing with credential harvesting', 'T1566.001', 'Lack of email filtering and user awareness'),
('SQL Injection Attempt', 'Attacker attempted to inject SQL code into the web application login form. Attack was blocked by WAF but indicates active targeting.', '2026-03-20', 'Medium', 'Contained', 'unauthorized_access', TRUE, 5, 'Web application SQL injection', 'T1190', 'Input validation weaknesses in application'),
('Accidental Data Exposure', 'Employee accidentally shared production database credentials on public GitHub repository. Credentials were exposed for 4 hours before detection.', '2026-04-02', 'High', 'Eradicated', 'data_breach', TRUE, 2, 'Human error - credential exposure', 'T1565', 'Lack of secrets management and code review'),
('Network Intrusion Detection', 'IDS detected suspicious network activity from unknown IP addresses in the admin network segment. Possible reconnaissance activity.', '2026-04-10', 'Medium', 'Investigating', 'unauthorized_access', FALSE, 3, 'Network scanning and reconnaissance', 'T1046', 'Insufficient network segmentation'),
('Password Spray Attack', 'Attacker attempted to guess common passwords for multiple user accounts from external IP range. 47 accounts targeted, 3 compromised.', '2026-04-08', 'Medium', 'Recovered', 'unauthorized_access', TRUE, 1, 'External password spray attack', 'T1110.003', 'Weak password policy, no account lockout'),
('Unauthorized USB Device', 'Unknown USB device connected to secure workstation. Device was quarantined and analyzed. No malware detected but policy violation confirmed.', '2026-03-28', 'Low', 'Closed', 'insider', TRUE, 4, 'Physical USB device connection', 'T1091', 'Lack of USB port controls and DLP'),
('DDoS Attack on Web Server', 'Volumetric DDoS attack targeting primary web server. Peak traffic of 15Gbps. Service degraded for 2 hours.', '2026-05-10', 'High', 'Recovered', 'ddos', TRUE, 1, 'Volumetric DDoS attack', 'T1498.002', 'Insufficient DDoS mitigation capacity'),
('Ransomware Detection', 'EDR detected ransomware execution on finance department workstation. File encryption in progress. Workstation isolated immediately.', '2026-05-18', 'Critical', 'Contained', 'malware', FALSE, 4, 'Malware execution via email attachment', 'T1486', 'Outdated endpoint protection, user clicked malicious attachment');

-- =============================================
-- INSERT SAMPLE AUDIT LOGS
-- =============================================
INSERT INTO audit_logs (username, action, resource_type, resource_id, ip_address, details, severity) VALUES 
('admin', 'LOGIN', 'system', NULL, '192.168.1.100', 'Successful login from admin workstation', 'low'),
('admin', 'CREATE', 'risk', 1, '192.168.1.100', 'Created new risk: Malware Infection', 'low'),
('analyst', 'UPDATE', 'incident', 1, '192.168.1.101', 'Updated incident status to Investigating', 'low'),
('system', 'ALERT', 'vulnerability', 1, '127.0.0.1', 'Critical vulnerability CVE-2024-1234 detected', 'critical'),
('admin', 'DELETE', 'user', 99, '192.168.1.100', 'Attempted to delete non-existent user', 'medium');

-- =============================================
-- INSERT SAMPLE NOTIFICATIONS
-- =============================================
INSERT INTO notifications (user_id, title, message, type, link) VALUES 
(1, 'Critical Vulnerability Detected', 'CVE-2024-1234 with CVSS 9.8 has been identified on Primary Web Server.', 'danger', 'vulnerabilities.php?id=1'),
(1, 'High Severity Incident', 'Ransomware detected on finance workstation. Immediate action required.', 'danger', 'incidents.php?id=8'),
(2, 'New Risk Registered', 'A new high-risk item has been added to the risk register.', 'warning', 'risks.php'),
(1, 'Weekly Report Ready', 'Your weekly security metrics report is ready for review.', 'info', 'reports.php');

-- =============================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================
CREATE INDEX idx_users_created ON users(created_at);
CREATE INDEX idx_risks_created ON risks(created_at);
CREATE INDEX idx_incidents_created ON incidents(created_at);
CREATE INDEX idx_audit_logs_created ON audit_logs(created_at);
CREATE INDEX idx_notifications_created ON notifications(created_at);

-- =============================================
-- CREATE VIEWS FOR COMMON QUERIES
-- =============================================
-- Risk Overview View
CREATE OR REPLACE VIEW risk_overview AS
SELECT 
    r.id,
    r.name,
    r.risk_level,
    r.status,
    r.likelihood,
    r.impact,
    (r.likelihood * r.impact) as risk_score,
    r.category,
    a.name as asset_name,
    u.full_name as owner_name,
    r.due_date,
    r.created_at
FROM risks r
LEFT JOIN assets a ON r.asset_id = a.id
LEFT JOIN users u ON r.owner_id = u.id;

-- Incident Overview View
CREATE OR REPLACE VIEW incident_overview AS
SELECT 
    i.id,
    i.title,
    i.severity,
    i.status,
    i.category,
    i.incident_date,
    i.resolved,
    a.name as asset_name,
    u.full_name as assigned_to_name,
    i.root_cause,
    i.created_at
FROM incidents i
LEFT JOIN assets a ON i.asset_id = a.id
LEFT JOIN users u ON i.assigned_to = u.id;

-- Asset Risk View
CREATE OR REPLACE VIEW asset_risk_view AS
SELECT 
    a.id,
    a.name,
    a.type,
    a.criticality,
    a.status,
    a.vulnerability_count,
    a.risk_score,
    COUNT(r.id) as associated_risks,
    COUNT(v.id) as open_vulnerabilities,
    COUNT(i.id) as associated_incidents
FROM assets a
LEFT JOIN risks r ON a.id = r.asset_id AND r.status = 'Open'
LEFT JOIN vulnerabilities v ON a.id = v.asset_id AND v.status IN ('open', 'in_progress')
LEFT JOIN incidents i ON a.id = i.asset_id AND i.resolved = 0
GROUP BY a.id, a.name, a.type, a.criticality, a.status, a.vulnerability_count, a.risk_score;

-- =============================================
-- STORED PROCEDURES FOR DASHBOARD METRICS
-- =============================================
DELIMITER //

-- Get Dashboard Statistics
CREATE PROCEDURE get_dashboard_stats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM risks WHERE status = 'Open') as open_risks,
        (SELECT COUNT(*) FROM risks WHERE risk_level = 'High' OR risk_level = 'Critical') as high_risks,
        (SELECT COUNT(*) FROM incidents WHERE resolved = 0) as open_incidents,
        (SELECT COUNT(*) FROM incidents WHERE severity = 'Critical' AND resolved = 0) as critical_incidents,
        (SELECT COUNT(*) FROM vulnerabilities WHERE status IN ('open', 'in_progress') AND severity = 'critical') as critical_vulns,
        (SELECT COUNT(*) FROM assets WHERE status = 'compromised') as compromised_assets;
END //

-- Get Risk Distribution
CREATE PROCEDURE get_risk_distribution()
BEGIN
    SELECT 
        risk_level,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM risks), 1) as percentage
    FROM risks 
    GROUP BY risk_level
    ORDER BY FIELD(risk_level, 'Critical', 'High', 'Medium', 'Low');
END //

DELIMITER ;

-- Cybersecurity Risk Management Dashboard Database
-- Created for Arusha Technical College Final Year Project
-- Student: Vaileth Aloyce Mkaakaa (23051012318)

-- Drop existing database if exists
DROP DATABASE IF EXISTS crmd_db;

-- Create database
CREATE DATABASE crmd_db;
USE crmd_db;

-- =============================================
-- TABLE: users
-- =============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: risks
-- =============================================
CREATE TABLE risks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    likelihood INT NOT NULL CHECK (likelihood >= 1 AND likelihood <= 5),
    impact INT NOT NULL CHECK (impact >= 1 AND impact <= 5),
    risk_level ENUM('Low', 'Medium', 'High') NOT NULL,
    status ENUM('Open', 'Mitigated', 'Closed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_risk_level (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: incidents
-- =============================================
CREATE TABLE incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    incident_date DATE NOT NULL,
    severity ENUM('High', 'Medium', 'Low') NOT NULL,
    resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_severity (severity),
    INDEX idx_resolved (resolved),
    INDEX idx_date (incident_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert Users (passwords: admin123 and viewer123)
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$h3dv7hYsEBiT63mcbnzSGewKtr/flNH3k.n2nqRM4h04LXUuWToGO', 'admin'),
('viewer', '$2y$10$KN3KCCeYXBIG5cBdZEvrYOdeqsdQGqGREa1Fx0Hu0wcaqVwAv6jlK', 'viewer'),
('manager', '$2y$10$h3dv7hYsEBiT63mcbnzSGewKtr/flNH3k.n2nqRM4h04LXUuWToGO', 'admin');

-- Insert Risks (with auto-calculated risk levels)
INSERT INTO risks (name, description, likelihood, impact, risk_level, status) VALUES 
('Malware Infection', 'Risk of malware spreading through email attachments and phishing attacks', 4, 4, 'High', 'Open'),
('Unauthorized Access', 'Potential unauthorized access to sensitive systems due to weak passwords', 3, 5, 'High', 'Open'),
('Data Breach', 'Exposure of customer and employee personal data through unencrypted databases', 2, 5, 'High', 'Mitigated'),
('System Downtime', 'Critical systems becoming unavailable due to DDoS attacks or hardware failure', 3, 3, 'Medium', 'Open'),
('Insider Threat', 'Disgruntled employees or contractors accessing and stealing confidential information', 2, 4, 'Medium', 'Open'),
('Weak Encryption', 'Use of outdated encryption protocols allowing attackers to intercept data', 2, 3, 'Medium', 'Closed'),
('Social Engineering', 'Employees tricked into revealing passwords or sensitive information via phone/email', 4, 2, 'Medium', 'Open'),
('Ransomware Attack', 'Critical files encrypted by ransomware, demanding payment for decryption', 2, 5, 'High', 'Open');

-- Insert Incidents
INSERT INTO incidents (title, description, incident_date, severity, resolved) VALUES 
('Phishing Email Campaign', 'Employees received phishing emails targeting corporate credentials. 15 users clicked on malicious links.', '2026-04-15', 'High', FALSE),
('SQL Injection Attempt', 'Attacker attempted to inject SQL code into the web application login form. Attack was blocked by WAF.', '2026-03-20', 'High', TRUE),
('Accidental Data Exposure', 'Employee accidentally shared production database credentials on public GitHub repository.', '2026-04-02', 'High', TRUE),
('Network Intrusion Detection', 'IDS detected suspicious network activity from unknown IP addresses in the admin network segment.', '2026-04-10', 'Medium', FALSE),
('Password Spray Attack', 'Attacker attempted to guess common passwords for multiple user accounts. Failed login attempts blocked.', '2026-04-08', 'Medium', TRUE),
('Unauthorized USB Device', 'Unknown USB device connected to secure workstation. Device was quarantined and analyzed.', '2026-03-28', 'Medium', TRUE);

-- =============================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_risks_created ON risks(created_at);
CREATE INDEX idx_incidents_created ON incidents(created_at);

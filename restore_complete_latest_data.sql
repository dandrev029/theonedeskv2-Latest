-- Complete Latest Data Restoration from helpdesk database
-- This script restores ALL data including relationships and settings

USE theonedesk;

-- Backup current state first
CREATE TABLE IF NOT EXISTS backup_users_temp AS SELECT * FROM users;
CREATE TABLE IF NOT EXISTS backup_departments_temp AS SELECT * FROM departments;

-- Clear existing data to avoid conflicts
DELETE FROM user_departments;
DELETE FROM notifications WHERE notifiable_type = 'App\\Models\\User';
DELETE FROM users WHERE id > 1; -- Keep admin
DELETE FROM departments;
DELETE FROM condo_locations;

-- Reset auto increment
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE condo_locations AUTO_INCREMENT = 1;

-- Restore User Roles (including custom ones)
INSERT IGNORE INTO user_roles (id, name, `type`, permissions, dashboard_access, created_at, updated_at)
SELECT id, name, `type`, permissions, dashboard_access, created_at, updated_at
FROM helpdesk.user_roles;

-- Restore Users from helpdesk database
INSERT IGNORE INTO users (id, name, email, role_id, status, password, email_verified_at, created_at, updated_at)
SELECT 
    id, 
    name, 
    email, 
    role_id, 
    1 as status, -- Set all users as active
    password, -- Use original password
    COALESCE(email_verified_at, NOW()) as email_verified_at, -- Verify if not already
    created_at, 
    updated_at
FROM helpdesk.users;

-- Convert locations to departments (maintaining original structure)
INSERT IGNORE INTO departments (id, name, public, all_agents, created_at, updated_at)
SELECT id, name, public, all_agents, created_at, updated_at
FROM helpdesk.locations;

-- Create condo locations based on departments
INSERT IGNORE INTO condo_locations (name, created_at, updated_at)
VALUES 
('DASMA', NOW(), NOW()),
('CAMPA', NOW(), NOW());

-- Restore user-department relationships (convert from location_user to user_departments)
INSERT IGNORE INTO user_departments (user_id, department_id)
SELECT user_id, location_id
FROM helpdesk.location_user;

-- Restore notifications
INSERT IGNORE INTO notifications (id, `type`, notifiable_type, notifiable_id, data, read_at, created_at, updated_at)
SELECT id, `type`, notifiable_type, notifiable_id, data, read_at, created_at, updated_at
FROM helpdesk.notifications;

-- Restore settings (only if they don't exist)
INSERT IGNORE INTO settings (`key`, `value`, is_env, created_at, updated_at)
SELECT `key`, `value`, is_env, created_at, updated_at
FROM helpdesk.settings
WHERE `key` NOT IN (SELECT `key` FROM settings);

-- Restore priorities if they don't exist
INSERT IGNORE INTO priorities (id, name, color, created_at, updated_at)
SELECT id, name, color, created_at, updated_at
FROM helpdesk.priorities;

-- Restore statuses if they don't exist  
INSERT IGNORE INTO statuses (id, name, color, created_at, updated_at)
SELECT id, name, color, created_at, updated_at
FROM helpdesk.statuses;

-- Show restoration summary
SELECT 'COMPLETE RESTORATION SUMMARY:' as summary;
SELECT 'Users restored:' as item, COUNT(*) as count FROM users;
SELECT 'Departments restored:' as item, COUNT(*) as count FROM departments;
SELECT 'User-Department relationships:' as item, COUNT(*) as count FROM user_departments;
SELECT 'Notifications restored:' as item, COUNT(*) as count FROM notifications;
SELECT 'Settings restored:' as item, COUNT(*) as count FROM settings;
SELECT 'Priorities restored:' as item, COUNT(*) as count FROM priorities;
SELECT 'Statuses restored:' as item, COUNT(*) as count FROM statuses;

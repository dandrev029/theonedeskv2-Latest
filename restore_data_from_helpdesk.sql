-- Data Restoration Script: Migrate from helpdesk database to theonedesk database
-- This script will restore your original users, roles, and departments

USE theonedesk;

-- First, clear existing data (except the admin user we just created)
DELETE FROM users WHERE id > 1;
DELETE FROM user_roles WHERE id > 3;
DELETE FROM departments;
DELETE FROM condo_locations;

-- Reset auto increment
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE user_roles AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE condo_locations AUTO_INCREMENT = 1;

-- Restore User Roles from helpdesk database
INSERT INTO user_roles (id, name, type, permissions, dashboard_access, created_at, updated_at)
SELECT id, name, type, permissions, dashboard_access, created_at, updated_at
FROM helpdesk.user_roles
WHERE id > 3; -- Skip the default roles (Admin, User, Customer)

-- Restore Users from helpdesk database (skip the admin to avoid conflicts)
INSERT INTO users (id, name, email, role_id, status, password, email_verified_at, created_at, updated_at)
SELECT 
    id, 
    name, 
    email, 
    role_id, 
    1 as status, -- Set all users as active
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password, -- Default password: 'password'
    NOW() as email_verified_at, -- Mark all as verified
    created_at, 
    updated_at
FROM helpdesk.users
WHERE id > 1; -- Skip the admin user

-- Convert locations to departments (since the structure changed)
INSERT INTO departments (id, name, public, all_agents, created_at, updated_at)
SELECT id, name, public, all_agents, created_at, updated_at
FROM helpdesk.locations;

-- Create default condo locations based on the departments
INSERT INTO condo_locations (name, created_at, updated_at)
VALUES 
('DASMA', NOW(), NOW()),
('CAMPA', NOW(), NOW());

-- Update user passwords to a known value for testing
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE id > 1;

-- Ensure all users are active and email verified
UPDATE users SET status = 1, email_verified_at = NOW() WHERE email_verified_at IS NULL;

-- Show restored data summary
SELECT 'Users restored:' as summary, COUNT(*) as count FROM users;
SELECT 'User roles restored:' as summary, COUNT(*) as count FROM user_roles;
SELECT 'Departments restored:' as summary, COUNT(*) as count FROM departments;
SELECT 'Condo locations created:' as summary, COUNT(*) as count FROM condo_locations;

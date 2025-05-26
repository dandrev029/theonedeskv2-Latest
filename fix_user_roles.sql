USE theonedesk;

-- Insert basic user roles if they don't exist
INSERT IGNORE INTO user_roles (id, name, type, permissions, dashboard_access, created_at, updated_at) VALUES 
(1, 'Admin', 1, '[]', 1, NOW(), NOW()),
(2, 'User', 1, '["App.Http.Controllers.Api.Dashboard.StatsController"]', 0, NOW(), NOW()),
(3, 'Customer', 1, '[]', 0, NOW(), NOW());

-- Insert admin user
INSERT IGNORE INTO users (id, name, email, role_id, status, password, email_verified_at, created_at, updated_at) VALUES 
(1, 'Admin', 'admin@admin.com', 1, 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW(), NOW());

-- Show final user summary
SELECT 'FINAL USER SUMMARY:' as summary;
SELECT id, name, email, role_id FROM users ORDER BY id;
SELECT 'USER ROLES:' as summary;
SELECT id, name, dashboard_access FROM user_roles ORDER BY id;

-- Update or create admin user with known password and verified email
INSERT OR REPLACE INTO users (
    id,
    name, 
    email, 
    password, 
    role, 
    email_verified_at, 
    created_at, 
    updated_at
) VALUES (
    1,
    'Admin User',
    'admin@pageturner.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'admin',
    '2024-01-01 00:00:00',
    '2024-01-01 00:00:00',
    '2024-01-01 00:00:00'
);

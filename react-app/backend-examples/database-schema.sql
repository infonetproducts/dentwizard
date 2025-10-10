-- Database Schema for SSO Support
-- Run these SQL commands to prepare your database for SSO users

-- 1. Modify users table to support SSO
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS auth_type ENUM('standard', 'sso') DEFAULT 'standard' AFTER password,
ADD COLUMN IF NOT EXISTS azure_ad_object_id VARCHAR(255) NULL AFTER auth_type,
ADD COLUMN IF NOT EXISTS last_login DATETIME NULL AFTER azure_ad_object_id,
MODIFY COLUMN password VARCHAR(255) NULL; -- Make password nullable for SSO users

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_auth_type ON users(auth_type);
CREATE INDEX IF NOT EXISTS idx_azure_object_id ON users(azure_ad_object_id);

-- 2. Create SSO audit log table (optional but recommended)
CREATE TABLE IF NOT EXISTS sso_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    success BOOLEAN NOT NULL,
    error_message TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sample: Add an SSO user (replace with actual values)
-- INSERT INTO users (
--     email,
--     first_name,
--     last_name,
--     auth_type,
--     password,
--     budget,
--     department,
--     is_active,
--     created_at
-- ) VALUES (
--     'john.doe@dentwizard.com',  -- Must match Azure AD email
--     'John',
--     'Doe',
--     'sso',                       -- IMPORTANT: Set to 'sso'
--     NULL,                        -- No password needed for SSO
--     500.00,                      -- Set budget
--     'IT',
--     1,                           -- Active
--     NOW()
-- );

-- 4. Sample: Convert existing user to SSO
-- UPDATE users 
-- SET 
--     auth_type = 'sso',
--     password = NULL  -- Clear password since they'll use SSO
-- WHERE email = 'existing.user@dentwizard.com';

-- 5. Sample: Check all SSO users
-- SELECT 
--     user_id,
--     email,
--     first_name,
--     last_name,
--     auth_type,
--     budget,
--     is_active,
--     last_login
-- FROM users
-- WHERE auth_type = 'sso'
-- ORDER BY last_login DESC;

-- 6. Sample: Deactivate an SSO user
-- UPDATE users 
-- SET is_active = 0
-- WHERE email = 'departed.user@dentwizard.com';

-- 7. Sample: View SSO login history
-- SELECT 
--     email,
--     success,
--     error_message,
--     ip_address,
--     created_at
-- FROM sso_audit_log
-- ORDER BY created_at DESC
-- LIMIT 50;

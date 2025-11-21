# MySQL to Supabase Migration Guide

## Overview

This guide will help you migrate your Ebakunado PHP application from MySQL to Supabase. The migration maintains all existing functionality while providing the benefits of Supabase's modern database and authentication system.

## Prerequisites

- Supabase account (free at https://supabase.com)
- Your existing PHP application files
- Basic understanding of PHP and databases

## Step 1: Set Up Supabase Project

### 1.1 Create Supabase Project

1. Go to https://supabase.com and sign up/login
2. Click "New Project"
3. Choose your organization
4. Enter project details:
   - **Name**: `ebakunado`
   - **Database Password**: Choose a strong password (save this!)
   - **Region**: Choose closest to your users
5. Click "Create new project"
6. Wait for the project to be created (2-3 minutes)

### 1.2 Get Your Supabase Credentials

1. Go to your project dashboard
2. Click on "Settings" → "API"
3. Copy the following values:
   - **Project URL** (e.g., `https://your-project-id.supabase.co`)
   - **anon public key** (starts with `eyJ...`)
   - **service_role key** (starts with `eyJ...`)

## Step 2: Configure Your Application

### 2.1 Update Supabase Configuration

1. Open `database/SupabaseConfig.php`
2. Replace the placeholder values:
   ```php
   $supabase_url = "YOUR_SUPABASE_URL"; // Your Project URL
   $supabase_key = "YOUR_SUPABASE_ANON_KEY"; // Your anon public key
   $supabase_service_key = "YOUR_SUPABASE_SERVICE_KEY"; // Your service_role key
   ```

### 2.2 Create Database Tables in Supabase

1. Go to your Supabase dashboard
2. Click on "SQL Editor"
3. Copy and paste the following SQL to create all tables:

```sql
-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    passw VARCHAR(255),
    phone_number VARCHAR(20),
    salt VARCHAR(64),
    profileImg VARCHAR(255),
    failed_attempts INTEGER DEFAULT 0,
    lockout_time TIMESTAMP DEFAULT NULL,
    gender VARCHAR(255),
    place VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated TIMESTAMP DEFAULT NOW(),
    role VARCHAR(255) DEFAULT 'user'
);

-- Midwives table
CREATE TABLE midwives (
    id SERIAL PRIMARY KEY,
    midwife_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    pass VARCHAR(255),
    phone_number VARCHAR(20),
    salt VARCHAR(64),
    profileImg VARCHAR(255),
    gender VARCHAR(255),
    place VARCHAR(255),
    permissions VARCHAR(255) DEFAULT 'view',
    Approve BOOLEAN DEFAULT false,
    last_active TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated TIMESTAMP DEFAULT NOW(),
    role VARCHAR(255)
);

-- BHW table
CREATE TABLE bhw (
    id SERIAL PRIMARY KEY,
    bhw_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    pass VARCHAR(255),
    phone_number VARCHAR(20),
    salt VARCHAR(64),
    profileImg VARCHAR(255),
    gender VARCHAR(255),
    place VARCHAR(255),
    permissions VARCHAR(255) DEFAULT 'view',
    last_active TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated TIMESTAMP DEFAULT NOW(),
    role VARCHAR(255)
);

-- Locations table
CREATE TABLE locations (
    id SERIAL PRIMARY KEY,
    province VARCHAR(100) NOT NULL,
    city_municipality VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    purok VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Child Health Records table
CREATE TABLE child_health_records (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    baby_id VARCHAR(255) NOT NULL,
    child_fname VARCHAR(100) NOT NULL,
    child_lname VARCHAR(100) NOT NULL,
    child_gender VARCHAR(10) CHECK (child_gender IN ('Male', 'Female')),
    child_birth_date DATE NOT NULL,
    place_of_birth VARCHAR(255),
    mother_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100),
    address VARCHAR(255) NOT NULL,
    birth_weight DECIMAL(5,2),
    birth_height DECIMAL(5,2),
    birth_attendant VARCHAR(100),
    babys_card VARCHAR(500),
    date_created TIMESTAMP DEFAULT NOW(),
    date_updated TIMESTAMP DEFAULT NOW(),
    status VARCHAR(50) DEFAULT 'pending'
);

-- Immunization Records table
CREATE TABLE immunization_records (
    id SERIAL PRIMARY KEY,
    baby_id VARCHAR(50),
    vaccine_name VARCHAR(100),
    dose_number INTEGER,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    temperature DECIMAL(5,2),
    status VARCHAR(50),
    schedule_date DATE,
    batch_schedule_date DATE,
    date_given DATE,
    catch_up_date DATE,
    administered_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated TIMESTAMP DEFAULT NOW()
);

-- Admin table
CREATE TABLE admin (
    id SERIAL PRIMARY KEY,
    admin_id VARCHAR(255) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    profileImg VARCHAR(255) DEFAULT 'noprofile.png',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Super Admin table
CREATE TABLE super_admin (
    id SERIAL PRIMARY KEY,
    super_admin_id VARCHAR(255) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Activity Logs table
CREATE TABLE activity_logs (
    log_id SERIAL PRIMARY KEY,
    user_id VARCHAR(255),
    user_type VARCHAR(20) CHECK (user_type IN ('admin', 'super_admin', 'midwife', 'bhw', 'user')),
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Insert default admin accounts
INSERT INTO admin (admin_id, fname, lname, email, pass) VALUES
('ADM001', 'Default', 'Admin', 'admin@gmail.com', MD5('admin123456')),
('ADM002', 'Default', 'Admin', 'iquenxzx@gmail.com', MD5('iquen123456')),
('ADM009', 'Default', 'Admin', 'james@gmail.com', MD5('james123456')),
('ADM008', 'Default', 'Admin', 'jamesjus@gmail.com', MD5('james123456'));

-- Insert default super admin
INSERT INTO super_admin (super_admin_id, fname, lname, email, pass) VALUES
('SADM001', 'Super', 'Admin', 'superadmin@gmail.com', MD5('superadmin123456'));
```

4. Click "Run" to execute the SQL

> **Already live on Supabase?** Run this lightweight migration so existing projects get the new batching field without recreating the table:
>
> ```sql
> ALTER TABLE immunization_records
> ADD COLUMN IF NOT EXISTS batch_schedule_date DATE;
> ```
>
> The column is nullable so legacy data keeps working until health workers start populating batch slots.

## Step 3: Update Your Application Files

### 3.1 Replace Database Connection

1. **Backup your current `database/Database.php`**:

   ```bash
   cp database/Database.php database/Database_mysql_backup.php
   ```

2. **Replace the include statement** in all PHP files:
   - Change `include "../database/Database.php";` to `include "../database/SupabaseConfig.php";`
   - Add `include "../database/DatabaseHelper.php";` after the SupabaseConfig include

### 3.2 Update Login System

1. **Option A**: Use the new simplified login file:

   - Rename `php/login.php` to `php/login_mysql_backup.php`
   - Rename `php/login_supabase.php` to `php/login.php`

2. **Option B**: Update your existing login.php:
   - Replace the complex authentication logic with calls to `supabaseLogin()`
   - Use the helper functions from `DatabaseHelper.php`

### 3.3 Update Other PHP Files

For each PHP file that uses database operations, replace:

**Old MySQL code:**

```php
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
```

**New Supabase code:**

```php
$user = supabaseSelect('users', '*', ['email' => $email]);
if ($user && count($user) > 0) {
    $user = $user[0];
}
```

## Step 4: Test Your Migration

### 4.1 Test Database Connection

1. Create a test file `test_supabase.php`:

   ```php
   <?php
   include "database/SupabaseConfig.php";
   include "database/DatabaseHelper.php";

   echo "<h2>Supabase Connection Test</h2>";

   if ($supabase) {
       echo "<p style='color: green;'>✓ Supabase connection successful!</p>";

       // Test a simple query
       $users = supabaseSelect('users', 'id', [], null, 1);
       if ($users !== false) {
           echo "<p style='color: green;'>✓ Database query successful!</p>";
       } else {
           echo "<p style='color: red;'>✗ Database query failed</p>";
       }
   } else {
       echo "<p style='color: red;'>✗ Supabase connection failed!</p>";
   }
   ?>
   ```

2. Run the test file in your browser

### 4.2 Test Login System

1. Try logging in with the default admin account:

   - Email: `admin@gmail.com`
   - Password: `admin123456`

2. Test different user types (if you have test data)

### 4.3 Test All Features

1. Test user registration
2. Test data insertion/updates
3. Test admin functions
4. Test API endpoints

## Step 5: Common Issues and Solutions

### Issue 1: "Supabase connection failed"

**Solution**: Check your credentials in `SupabaseConfig.php`

- Verify the URL format: `https://your-project-id.supabase.co`
- Ensure keys are copied correctly (no extra spaces)

### Issue 2: "Table doesn't exist"

**Solution**: Make sure you ran the SQL script in Step 2.2

### Issue 3: "Authentication failed"

**Solution**:

- Check if user exists in the correct table
- Verify password hashing method
- Check Supabase logs in the dashboard

### Issue 4: "API calls failing"

**Solution**:

- Check your Supabase project is active
- Verify API keys have correct permissions
- Check network connectivity

## Step 6: Performance Optimization

### 6.1 Enable Row Level Security (RLS)

1. Go to Supabase Dashboard → Authentication → Policies
2. Enable RLS for each table
3. Create appropriate policies for your use case

### 6.2 Set Up Indexes

Add indexes for frequently queried columns:

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone_number);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
```

### 6.3 Configure Caching

Consider implementing caching for frequently accessed data.

## Step 7: Go Live

### 7.1 Final Testing

1. Test all functionality thoroughly
2. Load test with sample data
3. Verify all user types work correctly

### 7.2 Update DNS/Domain

1. Point your domain to your hosting provider
2. Update any hardcoded URLs
3. Test the live application

### 7.3 Monitor Performance

1. Use Supabase dashboard to monitor usage
2. Set up alerts for errors
3. Monitor response times

## Benefits of Supabase Migration

✅ **Better Performance**: Faster queries and responses
✅ **Built-in Authentication**: Secure user management
✅ **Real-time Features**: Live updates without polling
✅ **Better Security**: Row-level security and API keys
✅ **Scalability**: Automatic scaling with usage
✅ **Backup & Recovery**: Automatic backups
✅ **Modern API**: RESTful API with GraphQL support
✅ **Dashboard**: Easy database management

## Support and Resources

- **Supabase Documentation**: https://supabase.com/docs
- **Supabase Community**: https://github.com/supabase/supabase/discussions
- **PHP Examples**: Check the `DatabaseHelper.php` file for usage examples

## Rollback Plan

If you need to rollback to MySQL:

1. Restore `database/Database_mysql_backup.php` as `database/Database.php`
2. Restore `php/login_mysql_backup.php` as `php/login.php`
3. Update include statements back to MySQL
4. Ensure MySQL server is running

---

**Note**: This migration maintains all existing functionality while providing modern database features. The helper functions make the transition smooth and maintain compatibility with your existing code structure.

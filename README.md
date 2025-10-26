# Ebakunado - Immunization Management System

A comprehensive web-based immunization tracking and management system for health workers and parents.

## Features

- **Multi-user System**: Admin, Super Admin, BHW (Barangay Health Worker), Midwife, and User roles
- **Child Health Records**: Complete immunization tracking and management
- **Real-time Notifications**: Vaccination schedules and reminders
- **API Integration**: RESTful API endpoints for mobile app integration
- **Supabase Backend**: Cloud database integration

## Tech Stack

- **Backend**: PHP 8.1+
- **Database**: Supabase (PostgreSQL)
- **Frontend**: HTML, CSS, JavaScript
- **API**: RESTful JSON API
- **Dependencies**: Composer packages (PHPMailer, Cloudinary, PHPWord, etc.)

## Local Development

1. **Prerequisites**:

   - PHP 8.1 or higher
   - Composer
   - Supabase account and project

2. **Setup**:

   ```bash
   # Install dependencies
   composer install

   # Configure Supabase credentials in database/SupabaseConfig.php
   # Set up your database tables using SUPABASE_MIGRATION_GUIDE.md

   # Start local server
   php -S localhost:8000
   ```

3. **Access the application**:
   - Web Interface: http://localhost:8000
   - API Endpoints: http://localhost:8000/api/ and http://localhost:8000/php/supabase/

## Deployment to Render.com

### Step 1: Prepare Your Repository

1. Push your code to GitHub
2. Ensure you have `.gitignore`, `render.yaml`, and `composer.json` files

### Step 2: Deploy on Render

1. Go to [render.com](https://render.com) and sign up/login
2. Click "New" → "Web Service"
3. Connect your GitHub repository
4. Configure the service:
   - **Name**: ebakunado-api
   - **Environment**: PHP
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `php -S 0.0.0.0:$PORT -t ./`

### Step 3: Set Environment Variables

In the Render dashboard, add these environment variables:

- `SUPABASE_URL`: Your Supabase project URL
- `SUPABASE_KEY`: Your Supabase anon key
- `SUPABASE_SERVICE_KEY`: Your Supabase service role key
- `API_KEY`: Your API key (default: "iquen")

### Step 4: Deploy

Click "Create Web Service" and wait for deployment to complete.

## API Endpoints

All API endpoints are accessible via HTTP GET/POST requests:

### Authentication

- `POST /php/supabase/login.php` - User login
- `POST /php/supabase/create_account.php` - User registration

### User APIs

- `GET /php/supabase/users/get_user_notifications.php` - Get user notifications
- `GET /php/supabase/users/get_dashboard_summary.php` - Get dashboard data
- `GET /php/supabase/users/get_child_list.php` - Get children list
- `GET /php/supabase/users/get_immunization_schedule.php` - Get immunization schedule

### Admin APIs

- `GET /api/get_users.php?api_key=iquen` - Get all users
- `GET /api/get_activity_logs.php?api_key=iquen` - Get activity logs

## Flutter Integration

The API is designed to work seamlessly with Flutter apps:

```dart
// Example API call
final response = await http.get(
  Uri.parse('https://your-app.onrender.com/php/supabase/users/get_user_notifications.php'),
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': 'iquen'
  },
);
```

## CORS Support

The API includes CORS headers for cross-origin requests:

- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE`
- `Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key`

## Support

For issues or questions:

1. Check the logs in your Render dashboard
2. Verify Supabase configuration
3. Ensure all environment variables are set correctly

## License

This project is for healthcare management purposes.

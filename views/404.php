<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Ebakunado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e74c3c;
            font-size: 48px;
            margin: 0;
        }
        p {
            font-size: 18px;
            color: #666;
            margin: 20px 0;
        }
        .logo {
            font-size: 24px;
            color: #3498db;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏥 Ebakunado</div>
        <h1>404</h1>
        <p>Page Not Found</p>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>

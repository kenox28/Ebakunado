# Cloudinary Setup Instructions

## 1. Get Cloudinary Account

1. Go to [Cloudinary.com](https://cloudinary.com)
2. Sign up for a free account
3. Go to your Dashboard

## 2. Get Your Credentials

From your Cloudinary Dashboard, copy:

- **Cloud Name**
- **API Key**
- **API Secret**

## 3. Update Configuration

Edit `config/cloudinary.php` and replace:

```php
return [
    'cloud_name' => 'your_actual_cloud_name',
    'api_key' => 'your_actual_api_key',
    'api_secret' => 'your_actual_api_secret',
    'secure' => true
];
```

## 4. Test Upload

- Upload a baby's card through the form
- Images will be stored in: `ebakunado/baby_cards/` folder in Cloudinary
- Database will store the secure HTTPS URL

## Features

✅ **Automatic folder organization**: `ebakunado/baby_cards/`  
✅ **Secure HTTPS URLs**: All images served over HTTPS  
✅ **Auto file type detection**: Supports images and PDFs  
✅ **Unique naming**: `baby_card_[user_id]_[timestamp]`  
✅ **Error handling**: Continues without image if upload fails

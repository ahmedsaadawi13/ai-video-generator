# AI Video Generator

A complete multi-tenant SaaS platform for generating AI-powered videos from text, images, and videos. Built with pure PHP (7.0+) and MySQL using a lightweight custom MVC framework.

## Features

### Core Functionality
- **Text to Video**: Transform scripts and prompts into engaging video content
- **Image to Video**: Bring still images to life with dynamic motion
- **Video Transformation**: Stylize existing videos with AI effects
- **Multi-Image Slideshow**: Create story videos from multiple images

### Multi-Tenant Architecture
- Secure tenant isolation with `tenant_id` filtering
- Role-based access control (platform_admin, tenant_admin, editor, viewer)
- Subscription plans with usage tracking and quota enforcement
- Team collaboration features

### Project Management
- Create and manage video projects
- Scene-based timeline editing
- Multiple platform presets (YouTube, TikTok, Instagram)
- Customizable resolution and aspect ratios

### Media Library
- Upload and organize images, videos, and audio
- Tagging and search functionality
- Storage usage tracking

### Templates & Presets
- Pre-built video templates
- Style presets (Cinematic, Vibrant, Minimal, etc.)
- Template marketplace (public/private)

### Brand Management
- Custom colors, fonts, and logos
- Watermark overlays
- Intro/outro clips

### Render Queue System
- Background job processing
- Progress tracking
- Email notifications
- Retry failed renders

### Subscription & Billing
- Multiple pricing tiers
- Usage tracking (projects, renders, storage)
- Automated invoicing
- Payment simulation

### Public REST API
- Create render jobs programmatically
- Check job status
- API key authentication
- Rate limiting support

### Analytics & Reporting
- Usage statistics
- Project type distribution
- Render success rates
- Storage trends

## Requirements

- PHP 7.0 - 8.x
- MySQL 5.7+ / 8.0+
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, GD, Fileinfo

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/ai-video-generator.git
cd ai-video-generator
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE ai_video_generator"

# Import schema with demo data
mysql -u root -p ai_video_generator < database.sql
```

### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

Update database credentials in `.env`:

```
DB_HOST=localhost
DB_DATABASE=ai_video_generator
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Set Permissions

```bash
# Make storage directories writable
chmod -R 755 storage/
mkdir -p storage/uploads/images storage/uploads/videos storage/uploads/audio
mkdir -p storage/outputs storage/outputs/thumbs
```

### 5. Web Server Configuration

#### Apache

Create `/etc/apache2/sites-available/ai-video-generator.conf`:

```apache
<VirtualHost *:80>
    ServerName ai-video-generator.local
    DocumentRoot /path/to/ai-video-generator/public

    <Directory /path/to/ai-video-generator/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ai-video-generator-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-video-generator-access.log combined
</VirtualHost>
```

Enable site and rewrite module:

```bash
sudo a2ensite ai-video-generator
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Create `.htaccess` in `/public`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx

Add to `/etc/nginx/sites-available/ai-video-generator`:

```nginx
server {
    listen 80;
    server_name ai-video-generator.local;
    root /path/to/ai-video-generator/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable and restart:

```bash
sudo ln -s /etc/nginx/sites-available/ai-video-generator /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

### 6. Access Application

Open browser and navigate to: `http://ai-video-generator.local`

## Demo Credentials

### Tenant Admin (Personal Creator)
- Email: `john@example.com`
- Password: `password`

### Tenant Admin (Agency)
- Email: `mike@digitalagency.com`
- Password: `password`

### Platform Admin
- Email: `admin@aivideogen.com`
- Password: `password`

## Usage

### Creating a Video Project

1. Login to dashboard
2. Click "Projects" → "New Project"
3. Enter project details (name, type, platform preset)
4. Add scenes to timeline
5. Configure each scene (text, images, prompts)
6. Start render job
7. Download completed video

### Managing Team Members

1. Navigate to "Settings" → "Team Members" (tenant_admin only)
2. Click "Add User"
3. Enter user details and assign role
4. User receives email with credentials

### Using Templates

1. Browse "Templates" page
2. Select a template
3. Click "Use Template"
4. Customize scenes and content
5. Render video

### Subscription Management

1. Navigate to "Subscription"
2. View current plan and usage
3. Click "Upgrade Plan" to change subscription
4. Select billing cycle and confirm

## Public API

### Authentication

All API requests require an API key in the header:

```
X-API-KEY: avgen_live_your_api_key_here
```

Generate API keys from the dashboard: Settings → API Keys

### Create Render Job

**Endpoint:** `POST /api/v1/render`

**Request Body:**

```json
{
  "project_id": 123,
  "type": "text_to_video",
  "parameters": {
    "script": "Your video script here",
    "style": "cinematic",
    "duration": 30,
    "aspect_ratio": "16:9",
    "resolution": "1080p"
  }
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "job_id": 456,
    "status": "queued",
    "type": "text_to_video",
    "project_id": 123,
    "created_at": "2025-11-23 10:30:00"
  }
}
```

### Get Render Job Status

**Endpoint:** `GET /api/v1/render/{job_id}`

**Response:**

```json
{
  "success": true,
  "data": {
    "job_id": 456,
    "status": "completed",
    "progress": 100,
    "video": {
      "id": 789,
      "filename": "render_456_final.mp4",
      "duration": 30,
      "file_size": 8388608,
      "thumbnail_url": "/storage/outputs/thumbs/thumb_456.jpg",
      "public_token": "a7f8e9d0c1b2a3f4e5d6c7b8a9f0e1d2"
    }
  }
}
```

### API Error Responses

```json
{
  "error": "Invalid or expired API key"
}
```

Status codes: 400 (Bad Request), 401 (Unauthorized), 404 (Not Found), 429 (Rate Limit)

## Background Jobs

Process queued render jobs:

```bash
php app/console/process-renders.php
```

Set up cron job for automatic processing:

```bash
# Add to crontab
*/5 * * * * php /path/to/ai-video-generator/app/console/process-renders.php >> /var/log/render-processor.log 2>&1
```

## Testing

Run basic tests:

```bash
php tests/BasicTests.php
```

Expected output:
```
✓ PASS: Database connection
✓ PASS: Tenant model can find by ID
✓ PASS: User model can find by email
...
Tests completed!
Passed: 12
Failed: 0
```

## Project Structure

```
ai-video-generator/
├── app/
│   ├── console/           # CLI scripts
│   ├── controllers/       # Controllers (Auth, Project, etc.)
│   ├── core/              # MVC framework core
│   ├── helpers/           # Helper functions
│   ├── models/            # Database models
│   ├── services/          # Business logic services
│   └── views/             # View templates
├── config/                # Configuration files
├── public/                # Public web root
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── index.php          # Application entry point
├── storage/               # File storage
│   ├── uploads/           # Uploaded assets
│   └── outputs/           # Generated videos
├── tests/                 # Test files
├── database.sql           # Database schema
├── .env.example           # Environment template
└── README.md              # This file
```

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **Password Hashing**: Using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements only
- **XSS Protection**: All output escaped with `htmlspecialchars()`
- **File Upload Validation**: Type and size checks
- **Tenant Isolation**: All queries filtered by `tenant_id`
- **API Key Hashing**: Secure key storage with SHA-256

## Scalability Considerations

- **Database Indexes**: All foreign keys and frequently queried columns indexed
- **Pagination**: All list pages support pagination
- **Background Processing**: Render jobs processed asynchronously
- **Caching**: Ready for Redis/Memcached integration
- **CDN**: Static assets can be served from CDN
- **Load Balancing**: Stateless design supports horizontal scaling

## Deployment

### Production Checklist

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Configure production database credentials
4. Set up HTTPS with SSL certificate
5. Enable PHP opcache
6. Configure backup strategy
7. Set up monitoring and logging
8. Configure email service for notifications
9. Set appropriate file permissions (644 for files, 755 for directories)
10. Configure firewall rules

### Docker Deployment (Optional)

Create `Dockerfile`:

```dockerfile
FROM php:7.4-apache
RUN docker-php-ext-install pdo pdo_mysql gd
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/storage
```

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./storage:/var/www/html/storage
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ai_video_generator
    ports:
      - "3306:3306"
```

Run:

```bash
docker-compose up -d
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

This project is open-source and available under the MIT License.

## Support

For issues, questions, or feature requests, please open an issue on GitHub.

## Credits

Built with ♥ by the AI Video Generator Team

---

**Note**: This is a complete SaaS platform with mock AI integration. In production, integrate with actual AI video services like Pika Labs, Runway ML, or similar providers by implementing the `AiVideoService` class methods.

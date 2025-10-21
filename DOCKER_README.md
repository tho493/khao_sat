# Docker Setup cho Khao Sat Project

## Tổng quan

Project này đã được cấu hình để chạy với Docker, bao gồm:

-   **Laravel App** với PHP 8.3-FPM
-   **Nginx** làm web server
-   **MySQL 8.0** làm database
-   **Redis** cho cache và queue
-   **phpMyAdmin** để quản lý database

## Yêu cầu hệ thống

-   Docker Desktop (Windows/Mac) hoặc Docker Engine (Linux)
-   Docker Compose
-   Ít nhất 4GB RAM
-   10GB dung lượng trống

## Cài đặt nhanh

### 1. Clone project và di chuyển vào thư mục

```bash
cd khao_sat
```

### 2. Cấu hình môi trường

```bash
# Copy file cấu hình
cp .env.docker.example .env

# Chỉnh sửa file .env với thông tin của bạn
# Đặc biệt chú ý các API keys:
# - GEMINI_API_KEY
# - RECAPTCHA_SITE_KEY
# - RECAPTCHA_SECRET_KEY
```

### 3. Chạy script build và run

```bash
docker compose build --no-cache app
docker compose up -d
```

### 4. Khởi tạo database

```bash
# Tạo key
docker-compose exec app php artisan key:generate

# Chạy migrations
docker-compose exec app php artisan migrate

# Tạo storage link
docker-compose exec app php artisan storage:link

# Seed database (nếu cần)
docker-compose exec app php artisan db:seed

# Cấp quyền tạo file cho thư mục backup db. Phục vụ mục đích backup database
sudo docker compose exec -it app sh -lc '
  mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/backup/db bootstrap/cache &&
  chown -R www-data:www-data storage bootstrap/cache &&
  chmod -R 775 storage bootstrap/cache
'
```

## Truy cập ứng dụng

-   **Ứng dụng chính**: http://localhost:8080
-   **phpMyAdmin**: http://localhost:8081
    -   Username: `khao_sat_user`
    -   Password: `khao_sat_password`
-   **Database**: localhost:3306
-   **Redis**: localhost:6379

## Các lệnh hữu ích

### Quản lý containers

```bash
# Xem trạng thái containers
docker-compose ps

# Xem logs
docker-compose logs -f

# Xem logs của service cụ thể
docker-compose logs -f app

# Dừng tất cả services
docker-compose down

# Khởi động lại services
docker-compose restart

# Rebuild containers
docker-compose build --no-cache
```

### Làm việc với Laravel

```bash
# Truy cập container app
docker-compose exec app bash

# Chạy Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache

# Chạy Composer
docker-compose exec app composer install
docker-compose exec app composer update

# Chạy tests
docker-compose exec app php artisan test
```

### Quản lý database

```bash
# Backup database sử dụng Laravel command (khuyến nghị)
docker-compose exec app php artisan backup:db --gzip

# Backup với tên file tùy chỉnh
docker-compose exec app php artisan backup:db --name=backup_$(date +%Y%m%d) --gzip

# Restore database từ file backup
docker-compose exec app php artisan restore:db backup_20240101.sql.gz --force

# Backup database trực tiếp từ MySQL container (phương pháp cũ)
docker-compose exec db mysqldump -u khao_sat_user -p khao_sat_db > backup.sql

# Restore database trực tiếp từ MySQL container (phương pháp cũ)
docker-compose exec -T db mysql -u khao_sat_user -p khao_sat_db < backup.sql
```

## Cấu trúc Docker

```
docker/
├── nginx/
│   └── default.conf          # Nginx configuration
├── php/
│   └── local.ini             # PHP configuration
│   └── www.conf      # Process management
└── scripts/
    └── start.sh              # Startup script
    └── setup.sh              # Setup script
```

## Troubleshooting

### Container không khởi động được

```bash
# Kiểm tra logs
docker-compose logs app

# Kiểm tra trạng thái
docker-compose ps

# Rebuild từ đầu
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Lỗi database connection

```bash
# Kiểm tra database container
docker-compose logs db

# Kiểm tra network
docker network ls
docker network inspect khao_sat_khao_sat_network
```

### Lỗi permissions

```bash
# Fix permissions
docker-compose exec app chown -R www:www /var/www/storage
docker-compose exec app chown -R www:www /var/www/bootstrap/cache
```

### Performance issues

```bash
# Tăng memory limit trong docker/php/local.ini
# Tăng resources trong docker-compose.yml
```

## Production Deployment

### 1. Cập nhật cấu hình production

```bash
# Chỉnh sửa .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 2. Build production image

```bash
docker-compose -f docker-compose.prod.yml build
```

### 3. Deploy với SSL

```bash
# Thêm SSL certificates vào docker/nginx/ssl/
# Cập nhật nginx config cho HTTPS
```

## Monitoring và Logs

### Xem logs real-time

```bash
# Tất cả services
docker-compose logs -f

# Service cụ thể
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Monitoring resources

```bash
# Xem resource usage
docker stats

# Xem disk usage
docker system df
```

## Backup và Restore

### Backup toàn bộ (Sử dụng Laravel Commands - Khuyến nghị)

```bash
# Backup database với Laravel command (tự động nén)
docker-compose exec app php artisan backup:db --name=backup_$(date +%Y%m%d) --gzip

# Backup database không nén
docker-compose exec app php artisan backup:db --name=backup_$(date +%Y%m%d)

# Backup volumes (MySQL data)
docker run --rm -v khao_sat_db_data:/data -v $(pwd):/backup alpine tar czf /backup/db_backup_$(date +%Y%m%d).tar.gz -C /data .
```

### Restore (Sử dụng Laravel Commands - Khuyến nghị)

```bash
# Restore database từ file backup Laravel
docker-compose exec app php artisan restore:db backup_20240101.sql.gz --force

# Restore database từ file SQL thường
docker-compose exec app php artisan restore:db backup_20240101.sql --force

# Restore volumes
docker run --rm -v khao_sat_db_data:/data -v $(pwd):/backup alpine tar xzf /backup/db_backup_20240101.tar.gz -C /data
```

### Backup và Restore truyền thống (Phương pháp cũ)

```bash
# Backup database trực tiếp từ MySQL
docker-compose exec db mysqldump -u khao_sat_user -p khao_sat_db > backup_$(date +%Y%m%d).sql

# Restore database trực tiếp từ MySQL
docker-compose exec -T db mysql -u khao_sat_user -p khao_sat_db < backup_20240101.sql
```

## Liên hệ hỗ trợ

Nếu gặp vấn đề, vui lòng:

1. Kiểm tra logs: `docker-compose logs -f`
2. Kiểm tra documentation này
3. Tạo issue trên repository

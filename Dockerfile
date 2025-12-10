FROM php:8.2-apache

# Cài PDO MySQL và mysqli
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Bật mod_rewrite nếu cần
RUN a2enmod rewrite

# Đặt thư mục làm việc
WORKDIR /var/www/html

# Không copy code, sẽ mount trực tiếp từ host
EXPOSE 80
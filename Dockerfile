# Stage 1: Build dependencies
FROM composer:2 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
# Install production dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs
COPY src/classes ./src/classes
COPY src/controllers ./src/controllers
COPY src/views ./src/views
RUN composer dump-autoload --no-dev --optimize

# Stage 2: Final image
FROM php:8.4-fpm-alpine

# Install nginx, supervisor and curl
RUN apk add --no-cache nginx supervisor curl

# Configure Nginx
RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP-FPM to use a unix socket
RUN sed -i 's/listen = 9000/listen = \/run\/php-fpm.sock/' /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen.owner = nginx" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen.group = nginx" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen.mode = 0666" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY src/ .

# Copy vendor from builder
COPY --from=builder /app/vendor ./vendor

# Set permissions
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 755 /var/www/html && \
    mkdir -p /var/www/html/db && \
    chown -R nginx:nginx /var/www/html/db

# Expose port 80
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

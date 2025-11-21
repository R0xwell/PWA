# ---------------------------------------------------------
# 🐘 Imagen base con PHP 8.2 + Apache (ideal para Render)
# ---------------------------------------------------------
FROM php:8.2-apache

# ---------------------------------------------------------
# 🏗 Instalar dependencias del sistema
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd

# ---------------------------------------------------------
# 📌 Activar Apache mod_rewrite (muy importante)
# ---------------------------------------------------------
RUN a2enmod rewrite

# ---------------------------------------------------------
# 📁 Copiar el código del proyecto al servidor Apache
# ---------------------------------------------------------
WORKDIR /var/www/html
COPY . /var/www/html/

# ---------------------------------------------------------
# 🔧 Corregir permisos
# ---------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ---------------------------------------------------------
# 🎼 Instalar Composer dentro del contenedor
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# 📦 Instalar dependencias de /vendor automáticamente
# (solo si existe composer.json)
# ---------------------------------------------------------
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# ---------------------------------------------------------
# 🚪 Puerto que Render usa
# ---------------------------------------------------------
EXPOSE 80

# ---------------------------------------------------------
# 🚀 Arranque de Apache
# ---------------------------------------------------------
CMD ["apache2-foreground"]

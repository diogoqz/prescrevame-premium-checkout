FROM php:8.2-apache

# Instalar extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Ativar mod_rewrite
RUN a2enmod rewrite

# Copiar app
COPY . /var/www/html/
COPY .htaccess /var/www/html/.htaccess

# Config Apache para servir pastas como rotas limpas
RUN printf "<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n" > /etc/apache2/conf-available/app.conf  && a2enconf app

# Desabilitar listing
RUN echo "Options -Indexes" >> /etc/apache2/apache2.conf

# Saúde
HEALTHCHECK --interval=30s --timeout=5s CMD curl -f http://localhost/ || exit 1

EXPOSE 80

CMD ["apache2-foreground"]

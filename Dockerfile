FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

# Install Apache, PHP 7.4 (via PPA), and system dependencies
RUN apt-get update && apt-get install -y \
    software-properties-common \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update && apt-get install -y \
    apache2 \
    php7.4 \
    php7.4-mysql \
    php7.4-curl \
    php7.4-gd \
    php7.4-bcmath \
    php7.4-mbstring \
    php7.4-xml \
    php7.4-json \
    php7.4-zip \
    libapache2-mod-php7.4 \
    # Document generation utilities
    texlive-latex-base \
    texlive-latex-extra \
    texlive-latex-recommended \
    texlive-fonts-recommended \
    texlive-fonts-extra \
    pdftk \
    ghostscript \
    libtiff-tools \
    # Mail utility
    msmtp \
    msmtp-mta \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers deflate expires php7.4

# Configure Apache virtual host to match production rewrite rules
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin admin@mywillonline.com.au\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options FollowSymLinks MultiViews\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    RewriteEngine On\n\
    RewriteCond %{REQUEST_URI} (\\.html|\\.pdf)$  [NC]\n\
    RewriteRule .* /index.php\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Create the temp directory used by pdflatex/pdftk for document generation
RUN mkdir -p /var/www/tmp && chown www-data:www-data /var/www/tmp

# Configure PHP for development
RUN echo "display_errors = Off" >> /etc/php/7.4/apache2/conf.d/99-dev.ini \
    && echo "error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING" >> /etc/php/7.4/apache2/conf.d/99-dev.ini \
    && echo "log_errors = On" >> /etc/php/7.4/apache2/conf.d/99-dev.ini \
    && echo "session.save_path = /tmp" >> /etc/php/7.4/apache2/conf.d/99-dev.ini

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]

FROM php:8.1-fpm

# Instalar extensiones necesarias para SQL Server
RUN apt-get update && apt-get install -y \
    software-properties-common \
    unixodbc-dev \
    gcc \
    g++ \
    make \
    autoconf \
    libc-dev \
    pkg-config \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    && rm -r /var/lib/apt/lists/*

# Instalar extensiones
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mbstring curl zip xml pdo pdo_mysql

# Instalar Microsoft ODBC 18 (para Debian 12)
COPY packages-microsoft-prod.deb /root
RUN dpkg -i /root/packages-microsoft-prod.deb \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18
    # && 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc \
    # && source ~/.bashrc

# install sqlcmd
#curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
#curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | tee /etc/apt/sources.list.d/mssql-tools.list
#apt update
#ACCEPT_EULA=Y apt -y install msodbcsql17
#echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bash_profile
#echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
#source ~/.bashrc
#apt -y install unixodbc-dev

# Actualizar pecl
RUN pecl channel-update pecl.php.net

# Descargar e instalar la extensión pdo_sqlsrv
RUN pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Instalar Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
# Configuración de Xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# php.ini personalizado
COPY php.ini /usr/local/etc/php/conf.d/php-custom.ini

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar sqlcmd
RUN ACCEPT_EULA=Y apt-get install -y mssql-tools
ENV PATH="${PATH}:/opt/mssql-tools/bin"

# Definir el directorio de trabajo
WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

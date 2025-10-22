FROM debian:bookworm

# Set UTF-8 encoding
ENV LANG=C.UTF-8

EXPOSE 80


RUN set -eux; \
    \
    ################################# \
    # Installing of the base system # \
    ################################# \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        nano \
        apache2=2.4.65-1~deb12u1 \
        libapache2-mod-php8.2=8.2.29-1~deb12u1 \
        php8.2-mysql=8.2.29-1~deb12u1 \
        php8.2-mcrypt=3:1.0.5-4 \
        php8.2-mbstring=8.2.29-1~deb12u1 \
        mariadb-client=1:10.11.14-0+deb12u2 \
        mariadb-server=1:10.11.14-0+deb12u2 \
    ; \
    rm -rf /var/lib/apt/lists/* ; \
    ################################### \
    # Configure the webserver and PHP # \
    ################################### \
    rm /var/www/html/index.html ; \
    sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/;extension=openssl/extension=openssl/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/;extension=mbstring/extension=mbstring/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/;extension=exif/extension=exif/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/;extension=json/extension=json/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/display_errors = Off/display_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/display_startup_errors = Off/display_startup_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/log_errors = Off/log_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/html_errors = Off/html_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/file_uploads = Off/file_uploads = On/' /etc/php/8.2/apache2/php.ini ;


# Deploy the website
COPY --chown=www-data:www-data utils/webserver-contents/. /var/www/html
COPY --chown=www-data:www-data /utils/dbms_setup/dati_test/immagini_itinerari/. /var/www/html/immagini/itinerari
COPY --chown=www-data:www-data /utils/dbms_setup/dati_test/immagini_utenti/. /var/www/html/immagini/utenti


# Deploy the utility files
COPY utils/dbms_setup/. /utils/dbms_setup
COPY --chmod=744 utils/run.sh /utils/run.sh


# Finally, run mariadb to restore the DB
RUN set -eux; \
    service mariadb start ; \
    cat /utils/dbms_setup/creazione_db.sql /utils/dbms_setup/dati_test/datitest_db.sql | mysql -u root -proot ; \
    service mariadb stop ;


CMD ["utils/run.sh"]
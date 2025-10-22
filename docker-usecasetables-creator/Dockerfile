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
        ######################################## \
        # Installing some of the main programs # \
        ######################################## \
        bash=5.2.15-2+b9 \
        xclip=0.13-2 \
        xterm=379-1 \
        pandoc=2.17.1.1-2~deb12u1 \
        apache2=2.4.65-1~deb12u1 \
        libapache2-mod-php8.2=8.2.29-1~deb12u1 \
        php8.2-xml=8.2.29-1~deb12u1 \
        default-jre=2:1.17-74 \
        libreoffice-writer=4:7.4.7-1+deb12u9 \
        ########################################## \
        # Installing the dependencies for Writer # \
        ########################################## \
        libreoffice-java-common \
    ; \
    rm -rf /var/lib/apt/lists/* ; \
    \
    ################################### \
    # Configure the webserver and PHP # \
    ################################### \
    rm /var/www/html/index.html ; \
    sed -i 's/display_errors = Off/display_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/display_startup_errors = Off/display_startup_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/log_errors = Off/log_errors = On/' /etc/php/8.2/apache2/php.ini ; \
    sed -i 's/html_errors = Off/html_errors = On/' /etc/php/8.2/apache2/php.ini ;


# Deploy the website
COPY --chown=www-data:www-data utils/webserver-contents/. /var/www/html


# Deploy the utility files
COPY --chmod=744 utils/run.sh /utils/run.sh
COPY --chmod=644 utils/bashrc /root/.bashrc

RUN set -eux; \
    chmod +x /var/www/html/cmdline-scripts/make_tables.sh ; \
    chmod +x /var/www/html/cmdline-scripts/make_tables_en.sh ; \
    chmod +x /var/www/html/cmdline-scripts/make_tables_it.sh ; \
    chmod +x /var/www/html/cmdline-scripts/copy_xmls_to_tmp.sh ; \
    chmod +x /var/www/html/cmdline-scripts/show_docx_tables.sh ; \
    chmod +x /var/www/html/cmdline-scripts/clipboard_helper.sh ;


CMD ["utils/run.sh"]
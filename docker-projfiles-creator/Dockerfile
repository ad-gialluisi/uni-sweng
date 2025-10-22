FROM debian:bookworm

# Set UTF-8 encoding
ENV LANG=C.UTF-8

# Add path to PATH
ENV PATH=$PATH:/root/bin


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
        pandoc=2.17.1.1-2~deb12u1 \
        make=4.3-4.1 \
        mupdf=1.21.1+ds2-1+b4 \
        dia=0.97.3+git20220525-5 \
        dia-shapes=0.6.0-5 \
        firefox-esr=128.14.0esr-1~deb12u1 \
        xterm=379-1 \
        pcmanfm=1.3.2-1 \
        gpicview=0.2.5-3+b2 \
        geany=1.38-1+b1 \
        default-jre=2:1.17-74 \
        libreoffice-writer=4:7.4.7-1+deb12u9 \
        ########################################## \
        # Installing the dependencies for Writer # \
        ########################################## \
        libreoffice-java-common \
        \
        ############################################# \
        # Installing the dependencies for wkhtmltox # \
        ############################################# \
        ca-certificates \
        fontconfig=2.14.1-4 \
        xfonts-base=1:1.0.5+nmu1 \
        xfonts-75dpi=1:1.0.5 \
        libx11-6=2:1.8.4-2+deb12u2 \
        libjpeg62-turbo=1:2.1.5-2 \
        libxrender1=1:0.9.10-1.1 \
        libxext6=2:1.3.4-1+b1 \
        \
        ############################################# \
        # Installing the dependencies for Pencil # \
        ############################################# \
        libglib2.0-0=2.74.6-2+deb12u7 \
        libnss3=2:3.87.1-1+deb12u1 \
        libatk1.0-0=2.46.0-5 \
        libatk-bridge2.0-0=2.46.0-5 \
        libcups2=2.4.2-3+deb12u9 \
        libdbus-1-3=1.14.10-1~deb12u1 \
        libdrm2=2.4.114-1+b1 \
        libgtk-3-0=3.24.38-2~deb12u3 \
        libcairo2=1.16.0-7 \
        libgdk-pixbuf-2.0-0=2.42.10+dfsg-1+deb12u2 \
        libxcomposite1=1:0.4.5-1 \
        libxdamage1=1:1.1.6-1 \
        libxfixes3=1:6.0.0-2 \
        libxrandr2=2:1.5.2-2+b1 \
        libgbm1=22.3.6-1+deb12u1 \
        libxkbcommon0=1.5.0-1 \
        libasound2=1.2.8-1+b1 \
        libatspi2.0-0=2.46.0-5 \
        xvfb=2:21.1.7-3+deb12u10 \
    ; \
    rm -rf /var/lib/apt/lists/* ;


# Deploy the utility files
COPY --chmod=600 utils/mimeapps.list /root/.config/
COPY utils/bin /root/bin
COPY utils/desktop_files/. /root/
COPY utils/installers/. /root/installers
COPY --chmod=744 utils/run.sh /run.sh


# Install wkhtmltox, PlantUML and Pencil
RUN set -eux ; \
    chmod 744 /root/bin/pencil ; \
    chmod 744 /root/bin/plantuml ; \
    cd /root/installers ; \
    tar -xvf Pencil-3.1.1.ga.tar.gz ; \
    dpkg -i wkhtmltox_0.12.6.1-3.bookworm_amd64.deb ;


CMD ["/run.sh"]
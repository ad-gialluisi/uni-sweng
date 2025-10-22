#!/bin/sh

# Copyright (C) 2020 Antonio Daniele Gialluisi

# This file is part of "Piattaforma Cicerone"

# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.


### Effettuare modifiche se necessario
DBMS_UTENTE_ADMIN=root
DBMS_PASSW_ADMIN=root
DIR_SQL=dbms_setup

### Su Fedora l'utente è "apache", su Debian è "www-data"
UTENTE_APACHE=www-data

### Impostazione permessi e contesti per sistemi con SELinux (Fedora e simili)
#find vista controllore modello debug utils immagini -type d \( -exec chmod 755 {} \; -o -exec true \; \) -exec chcon --type httpd_sys_content_t {} \;
#find vista controllore modello debug utils immagini -type f \( -exec chmod 644 {} \; -o -exec true \; \) -exec chcon --type httpd_sys_content_t {} \;
#find -maxdepth 1 -iname "*.php" -type f \( -exec chmod 644 {} \; -o -exec true \; \) -exec chcon --type httpd_sys_content_t {} \;
#chcon --type httpd_user_htaccess_t .htaccess

### Impostazione permessi per sistemi SENZA SELinux (Debian e simili)
find vista controllore modello debug utils immagini -type d -exec chmod 755 {} \;
find vista controllore modello debug utils immagini -type f -exec chmod 644 {} \;
find -maxdepth 1 -iname "*.php" -type f -exec chmod 644 {} \;


chmod 744 inizializza.sh


### Inserisci immagini correlate ai dati di test E
### Cambia il proprietario della cartella immagini

# "sudo" in caso sia stato già cambiato l'utente in $UTENTE_APACHE
sudo cp $DIR_SQL/dati_test/immagini_itinerari/* immagini/itinerari
sudo cp $DIR_SQL/dati_test/immagini_utenti/* immagini/utenti

chown -R $UTENTE_APACHE:$UTENTE_APACHE immagini



### Se si intende utilizzare le feature di debug, togliere il commento
### alle seguenti righe (solo la prima per sistemi SENZA SELinux)
chown -R $UTENTE_APACHE:$UTENTE_APACHE debug/rwdir
# chcon -R --type httpd_sys_rw_content_t debug/rwdir

### Impostazione SELinux per file/cartelle in scrittura (Fedora e simili)
# chcon -R --type httpd_sys_rw_content_t immagini

### Impostazione SELinux affinchè si possano inviare le email
# setsebool httpd_can_network_connect on

### Creazione dei dati persistenti del database (tabelle e vincoli) con
### dati di test
cat $DIR_SQL/creazione_db.sql $DIR_SQL/dati_test/datitest_db.sql | mysql -u $DBMS_UTENTE_ADMIN -p$DBMS_PASSW_ADMIN

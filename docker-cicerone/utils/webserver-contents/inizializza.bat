@echo off

rem // Copyright (C) 2020 Antonio Daniele Gialluisi

rem // This file is part of "Piattaforma Cicerone"

rem // This program is free software: you can redistribute it and/or modify
rem // it under the terms of the GNU General Public License as published by
rem // the Free Software Foundation, either version 3 of the License, or
rem // (at your option) any later version.

rem // This program is distributed in the hope that it will be useful,
rem // but WITHOUT ANY WARRANTY; without even the implied warranty of
rem // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
rem // GNU General Public License for more details.

rem // You should have received a copy of the GNU General Public License
rem // along with this program. If not, see <https://www.gnu.org/licenses/>.


rem Effettuare modifiche se necessario
set MYSQL_BIN="C:\xampp\mysql\bin\mysql.exe"
set DBMS_UTENTE_ADMIN=root
set DBMS_PASSW_ADMIN=root
set DIR_SQL=dbms_setup


rem Inserisci immagini correlate ai dati di test E
xcopy "%DIR_SQL%\dati_test\immagini_itinerari" "immagini\itinerari"
xcopy "%DIR_SQL%\dati_test\immagini_utenti" "immagini\utenti"


rem Creazione dei dati persistenti del database (tabelle e vincoli) con
rem dati di test

rem Rimuovi commento in caso il dbms abbia un utente root con password
type "%DIR_SQL%\creazione_db.sql" "%DIR_SQL%\dati_test\datitest_db.sql" | %MYSQL_BIN% -u %DBMS_UTENTE_ADMIN% --default-character-set=utf8
rem type "%DIR_SQL%\creazione_db.sql" "%DIR_SQL%\dati_test\datitest_db.sql" | %MYSQL_BIN% -u %DBMS_UTENTE_ADMIN% -p%DBMS_PASSW_ADMIN% --default-character-set=utf8

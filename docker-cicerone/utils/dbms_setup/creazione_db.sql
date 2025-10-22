-- Copyright (C) 2020 Antonio Daniele Gialluisi

-- This file is part of "Piattaforma Cicerone"

-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.

-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <https://www.gnu.org/licenses/>.


DROP DATABASE IF EXISTS CiceroneDB;

CREATE DATABASE CiceroneDB;


/*
 * Creazione dell'utente.
 * Si crea un utente apposito per la sola gestione di questo DB, così
 * facendo, tale utente non potrà modificare altri DB, neanche per errore.
 * La procedura qui sotto vale soltanto per il DBMS MySQL.
 */
USE mysql;

DELIMITER //
CREATE PROCEDURE creaUtente()
BEGIN
    IF (SELECT 1 FROM user where User = 'cicero') = 1 then
        DROP USER 'cicero'@'localhost';
    END IF;

    CREATE USER 'cicero'@'localhost' IDENTIFIED BY 'tulliola';
    GRANT DELETE, INSERT, SELECT, UPDATE ON CiceroneDB . * TO 'cicero'@'localhost';
END; //
DELIMITER ;
    
CALL creaUtente();
DROP PROCEDURE creaUtente;





USE CiceroneDB;




CREATE TABLE TipoUtente (
    tipo char(32), 
    PRIMARY KEY (tipo)
);
insert into TipoUtente (tipo) values ("globetrotter");
insert into TipoUtente (tipo) values ("quasicicerone");
insert into TipoUtente (tipo) values ("cicerone");
insert into TipoUtente (tipo) values ("amministratore");


CREATE TABLE StatoUtente (
    stato char(32), 
    PRIMARY KEY (stato)
);
insert into StatoUtente (stato) values ("inserito");
insert into StatoUtente (stato) values ("attivato");
insert into StatoUtente (stato) values ("recuperando");


CREATE TABLE Utente (
    id                INTEGER AUTO_INCREMENT,
    email              varchar(255) NOT NULL,
    nome_utente        varchar(255) NOT NULL,
    password           varchar(255) NOT NULL,
    immagine           varchar(255) NOT NULL,
    descrizione        varchar(255) NOT NULL,
    tipo                   char(32) NOT NULL,
    stato                  char(32) NOT NULL,
    codice_attivazione varchar(255) NOT NULL,

    PRIMARY KEY(id),
    UNIQUE KEY(nome_utente),
    FOREIGN KEY(tipo) REFERENCES TipoUtente(tipo),
    FOREIGN KEY(stato) REFERENCES StatoUtente(stato)
);



CREATE TABLE Anagrafica (
    id               INTEGER AUTO_INCREMENT,
    id_cicerone      integer,
    nome             varchar(255) NOT NULL,
    cognome          varchar(255) NOT NULL,
    data_nascita     date NOT NULL,
    luogo_nascita    varchar(255) NOT NULL,
    residenza        varchar(255) NOT NULL,
    telefono         varchar(255) NOT NULL,
    codice_fiscale   varchar(16) NOT NULL,
    PRIMARY KEY(id),
    UNIQUE KEY(id_cicerone),
    FOREIGN KEY(id_cicerone) REFERENCES Utente(id) ON DELETE CASCADE
);



CREATE TABLE StatoItinerario (
    stato char(32), 
    PRIMARY KEY(stato)
);
insert into StatoItinerario (stato) values ("aperto");
insert into StatoItinerario (stato) values ("itinere");
insert into StatoItinerario (stato) values ("concluso");
insert into StatoItinerario (stato) values ("chiuso");



CREATE TABLE Valuta (
    valuta      char(32),
    centesimale boolean DEFAULT TRUE NOT NULL,
    simbolo     char(10),
    PRIMARY KEY(valuta)
);
insert into Valuta (valuta, centesimale, simbolo) values ("euro", TRUE, "€");
insert into Valuta (valuta, centesimale, simbolo) values ("dollaro", TRUE, "$");
insert into Valuta (valuta, centesimale, simbolo) values ("yen", FALSE, "¥");



CREATE TABLE Itinerario (
    id          INTEGER AUTO_INCREMENT,
    nome        varchar(255) NOT NULL,
    id_cicerone integer,
    data        datetime NOT NULL,
    immagine    varchar(255) NOT NULL,
    descrizione varchar(255) NOT NULL,
    lingua      varchar(255) NOT NULL,
    luogo       varchar(255) NOT NULL,
    popolarità  integer NOT NULL,
    valuta      char(32),
    compenso    integer NOT NULL,
    stato       char(32),
    PRIMARY KEY(id),
    FOREIGN KEY(id_cicerone) REFERENCES Utente(id) ON DELETE CASCADE,
    FOREIGN KEY(valuta) REFERENCES Valuta(valuta),
    FOREIGN KEY(stato) REFERENCES StatoItinerario(stato),
    CHECK (compenso >= 0),
    CHECK (popolarità >= 1 and popolarità <= 5)
);

 

CREATE TABLE StatoPartecipazione (
    stato char(32), 
    PRIMARY KEY(stato)
);
insert into StatoPartecipazione (stato) values ("accordanda");
insert into StatoPartecipazione (stato) values ("accordata");
insert into StatoPartecipazione (stato) values ("annullanda");


CREATE TABLE Partecipazione (
    id              INTEGER AUTO_INCREMENT,
    id_itinerario   integer,
    id_partecipante integer,
    stato           char(32),
    PRIMARY KEY(id),
    UNIQUE KEY(id_itinerario, id_partecipante),
    FOREIGN KEY(id_itinerario) REFERENCES Itinerario(id) ON DELETE CASCADE,
    FOREIGN KEY(id_partecipante) REFERENCES Utente(id) ON DELETE CASCADE,
    FOREIGN KEY(stato) REFERENCES StatoPartecipazione(stato)
);



CREATE TABLE TipoFeedback (
    tipo char(32),
    PRIMARY KEY(tipo)
);
insert into TipoFeedback (tipo) values ("partecipante-organizzatore");
insert into TipoFeedback (tipo) values ("organizzatore-partecipante");



CREATE TABLE Feedback (
    id              INTEGER AUTO_INCREMENT,
    id_itinerario   integer,
    id_partecipante integer,
    descrizione     varchar(255) NOT NULL,
    voto            integer NOT NULL,
    tipo            char(32),
    PRIMARY KEY(id),
    UNIQUE KEY(id_itinerario, id_partecipante, tipo),
    FOREIGN KEY(id_itinerario) REFERENCES Itinerario(id) ON DELETE CASCADE,
    FOREIGN KEY(id_partecipante) REFERENCES Utente(id) ON DELETE CASCADE,
    FOREIGN KEY(tipo) REFERENCES TipoFeedback(tipo),
    CHECK (voto >= 0 and voto <= 5)
);


/*
Erano già state create le tabelle

CREATE TABLE Notifica (
    id             INTEGER AUTO_INCREMENT,
    id_utente      integer,
    descrizione    varchar(255) NOT NULL,
    link           varchar(255) NOT NULL,
    letta          boolean DEFAULT FALSE NOT NULL,
    data_creazione timestamp NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(id_utente) REFERENCES Utente(id) ON DELETE CASCADE
);



CREATE TABLE PreferenzeNotifica (
    id                        INTEGER AUTO_INCREMENT,
    id_utente                 integer,
    partecipazione_itinerario boolean DEFAULT TRUE NOT NULL,
    annullamento_itinerario   boolean DEFAULT TRUE NOT NULL,
    declino_itinerario        boolean DEFAULT TRUE NOT NULL,
    ricezione_feedback        boolean DEFAULT TRUE NOT NULL,
    via_mail                  boolean DEFAULT TRUE NOT NULL,
    PRIMARY KEY(id),
    UNIQUE KEY(id_utente),
    FOREIGN KEY(id_utente) REFERENCES Utente(id) ON DELETE CASCADE
);
*/


CREATE TABLE RichiestaAggiornamento (
  id            INTEGER AUTO_INCREMENT,
  id_anagrafica integer,
  PRIMARY KEY(id),
  UNIQUE KEY(id_anagrafica),
  FOREIGN KEY(id_anagrafica) REFERENCES Anagrafica(id) ON DELETE CASCADE
);



CREATE TABLE RichiestaDisiscrizione (
  id          INTEGER AUTO_INCREMENT,
  id_fruitore integer, 
  descrizione varchar(255) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id_fruitore),
  FOREIGN KEY(id_fruitore) REFERENCES Utente(id) ON DELETE CASCADE
);


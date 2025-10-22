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


USE CiceroneDB;

/*
 * NOTA: Le password sono uguali al nome utente
 */
 
/*
 * Amministratore
 */
insert into Utente (email, nome_utente, password, immagine, descrizione, tipo, stato, codice_attivazione)
    values ("antoniodaniele@antoniodaniele.com", "antoniodaniele", "$2y$10$O8cAqbWQ.3OIEvNPn0oYg.3vfKpMbL9vDcdVmkExJoKazIyDkyi0S", "img7UWwPF.jpeg", "Matricola 617833 presente.\r\nSono Antonio Daniele Gialluisi, lo studente che si è messo d\'impegno e spera di passare l\'esame.\r\nIl sistemino che state usando è interamente fatto da me.", "amministratore", "attivato", "");

/* Cicerone */
insert into Utente (email, nome_utente, password, immagine, descrizione, tipo, stato, codice_attivazione)
    values ("caligola@caligola.com", "caligola", "$2y$10$qSFd2Vg62ghDe9SkYSgxIu3PgYl0eCgM3kuywuWhLbtPu/nzqMgem", "imgy9PeMV.jpeg", "Purtroppo non mi è andata granchè bene come imperatore.\r\nMi hanno ucciso a soli 28 anni, pare che avessi iniziato a dare di matto.\r\nStudi recenti, credono la mia pazzia sia dovuta alla mia vita di eccessi, nella realtà non lo ricordo neanch\'io.", "cicerone", "attivato", "");

/* QuasiCicerone */
insert into Utente (email, nome_utente, password, immagine, descrizione, tipo, stato, codice_attivazione)
    values ("giuliocesare@giuliocesare.com", "giuliocesare", "$2y$10$DluAipkOyEWzrmCRWfZzge1xjLgCYjE5CJklsmzpFp0HZMbDR8WJm", "imgjkwNHI.jpeg", "Sono stato uno dei più grandi condottieri di Roma.\r\nVengo ucciso alle idi di marzo nel 44 a.C per mano di congiurati a cui non andavo a genio.\r\nHo conquistato la Gallia ed ho narrato le mie imprese nel famoso \"De bello Gallico\"", "quasicicerone", "attivato", "");

/* Globetrotter */
insert into Utente (email, nome_utente, password, immagine, descrizione, tipo, stato, codice_attivazione)
    values ("nerone@nerone.com", "neroneclaudio", "$2y$10$MVexuNyjnbWYupm6L3n2meLaMQw32wR8F1q2Zm2a5RNhgKPyAvH72", "imgX4dy69.jpeg", "Di me hanno detto di tutto, che ho bruciato Roma, che ero matto, che ho fatto assassinare mia madre e mia moglie...\r\nLa verità è che volevo fare l\'attore e la bella vita, non me n\'è mai importato nulla di governare.", "globetrotter", "attivato", "");


/*
 * Anagrafiche
 */
insert into Anagrafica (id_cicerone, nome, cognome, data_nascita, luogo_nascita, residenza, telefono, codice_fiscale)
    values (2, "Gaius Caesar", "Iulius Germanicus", "0012-08-31", "Anzio", "Via dalle scatole N17", "080802286386686", "LSIGCS00A01H501O");

insert into Anagrafica (id_cicerone, nome, cognome, data_nascita, luogo_nascita, residenza, telefono, codice_fiscale)
    values (3, "Gaius Caesar", "Iulius", "0000-07-12", "Roma", "Via dalle scatole N17", "080802286386686", "LSIGCS00A01H501O");


/*
 * Itinerari
 */
insert into Itinerario (nome, id_cicerone, data, immagine, descrizione, lingua, luogo, popolarità, valuta, compenso, stato) values ('Visita al Foro Romano', 2, '2020-01-16 11:11:00', 'imgyjt53w.jpeg', 'Si va in giro a vedere il Foro romano, dove gran parte di noi ha passato la sua vita.\r\nIl motivo?\r\nQuesto perchè è qui che facevamo i comizi.', 'Latino', 'Roma', 2, 'euro', 50000, 'concluso');
insert into Itinerario (nome, id_cicerone, data, immagine, descrizione, lingua, luogo, popolarità, valuta, compenso, stato) values ('Visita del Colle Palatino', 2, '2020-01-17 09:00:00', 'imgr5tJiR.jpeg', 'È qui che gli imperatori Romani partendo da Ottaviano Augusto, che per primo dette inizio a questa tradizione, risiedevano una volta divenuti imperatori.\r\nSi dice che Roma venne fondata nel 753 a.C proprio partendo dal Colle Palatino.', 'Latino', 'Roma', 5, 'euro', 0, 'aperto');
insert into Itinerario (nome, id_cicerone, data, immagine, descrizione, lingua, luogo, popolarità, valuta, compenso, stato) values ('Visita al Circo Massimo', 2, '2020-02-07 09:00:00', 'imgL5wfxn.jpeg', "Vedremo il circo massimo com\'era prima e com\'è adesso, dopodichè ci esibiremo noi stessi, nella speranza di superare l\'esame.", 'Latino', 'Roma', 2, 'yen', 7000, 'aperto');


/*
 * Partecipazioni
 */
insert into Partecipazione (id_itinerario, id_partecipante, stato) values (3, 3, 'annullanda');
insert into Partecipazione (id_itinerario, id_partecipante, stato) values (1, 3, 'accordata');
insert into Partecipazione (id_itinerario, id_partecipante, stato) values (2, 4, 'annullanda');
insert into Partecipazione (id_itinerario, id_partecipante, stato) values (1, 4, 'accordata');


/*
 * Feedbacks
 */
insert into Feedback (id_itinerario, id_partecipante, descrizione, voto, tipo) values (1, 3, "Personalmente non credevo che tra tre persone l\'unico sano di mente qui è Giulio Cesare.\r\nMolto soddisfatto della sua retorica.", 4, 'organizzatore-partecipante');
insert into Feedback (id_itinerario, id_partecipante, descrizione, voto, tipo) values (1, 4, "Personalmente non credevo che tra tre persone l\'unico sano di mente qui è Giulio Cesare.\r\nMolto soddisfatto della sua retorica.", 4, 'organizzatore-partecipante');
insert into Feedback (id_itinerario, id_partecipante, descrizione, voto, tipo) values (1, 4, "Personalmente lo reputo un pazzo, e lo dice uno come me, voglio di\', so\' Nerone!", 1, 'partecipante-organizzatore');

<?php
// Copyright (C) 2020 Antonio Daniele Gialluisi

// This file is part of "Piattaforma Cicerone"

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <https://www.gnu.org/licenses/>.


namespace modello;

require_once "ModelloUtente.php";
require_once "entità/Anagrafica.php";
require_once "entità/RichiesteAmministrazione.php";


use modello\entità\RichiesteAmministrazione;
use modello\entità\RichiestaDisiscrizione;
use modello\entità\Anagrafica;
use modello\entità\Utente;
use modello\entità\RichiestaAggiornamento;


/**
 * Questo è il modello che tratta le informazioni che riguardano le richieste d'amministrazione.
 * <p>In particolare, esegue i casi d'uso inerenti:</p>
 * <ul><li>L'invio della richiesta di disiscrizione.</li>
 * <li>La disiscrizione di un fruitore.</li>
 * <li>L'invio della richiesta d'aggiornamento.</li>
 * <li>La trasformazione di un Globetrotter a QuasiCicerone.</li>
 * <li>La transizione del QuasiCicerone a Cicerone</li></ul>
 */
class ModelloAmministrazione extends ModelloUtente {
    /*
     * Queste costanti servono ad indicare gli stati che un operazione
     * raggiunge in seguito al successo/fallimento/evoluzione della stessa.
     * Fornirò commenti solo agli stati che possono risultare "ambigui" ad una
     * prima lettura.
     */
    /**
     * Stato raggiunto quando viene inviata una richiesta
     * di disiscrizione
     */
    public const STATO_RICHIESTA_DISISCRIZIONE_INVIATA       = 3;
    
    /**
     * Stato raggiunto quando viene viene disiscritto
     * un utente
     */
    public const STATO_DISISCRIZIONE_RIUSCITA                = 4;

    /**
     * Stato raggiunto quando viene inviata una richiesta
     * di aggiornamento
     */
    public const STATO_RICHIESTA_AGGIORNAMENTO_INVIATA       = 5;

    /**
     * Stato raggiunto quando viene effettuata la trasformazione
     * in QuasiCcierone
     */
    public const STATO_TRASFORMAZIONE_QUASICICERONE_RIUSCITA = 6;

    /**
     * Stato raggiunto quando viene effettuata la transizione
     * a Cicerone
     */
    public const STATO_TRANSIZIONE_CICERONE_RIUSCITA         = 7;
    
    /**
     * Stato raggiunto quando l'id di una richiesta risulta
     * valido
     */
    public const STATO_RICHIESTA_AMMINISTRAZIONE_VALIDA      = 8;
    
    /**
     * Stato raggiunto quando l'id di una richiesta risulta
     * NON valido
     */
    public const STATO_RICHIESTA_AMMINISTRAZIONE_NON_VALIDA  = 9;


    /*
     * Costanti utili a definire le regex per i tipi di dato passati.
     */
    public const REGEX_CODICE_FISCALE     = "#^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$#i";


    
    /**
     * Restituisce una certa Richiesta di disiscrizione dato l'id.
     * @param int $id
     * @param RichiestaDisiscrizione $richiesta
     * @return int lo stato dell'operazione, che può essere: STATO_RICHIESTA_AMMINISTRAZIONE_NON_VALIDA,
     * STATO_RICHIESTA_AMMINISTRAZIONE_VALIDA
     */
    public function getRichiestaDisiscrizione(int $id, ?RichiestaDisiscrizione& $richiesta) : int {
        $richiesta = NULL;

        $this->ciceroneDatabase->apri();

        $colonneRichiesta = array(
            "id", "id_fruitore", "descrizione"
        );

        $colonneFruitore = array(
            "id", "nome_utente"
        );

        $query = self::creaQueryConColonneRiconoscibili("select %s from RichiestaDisiscrizione, " .
            "Utente where RichiestaDisiscrizione.id_fruitore = Utente.id and " .
            "RichiestaDisiscrizione.id = ?",
            array(RichiestaDisiscrizione::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneRichiesta, $colonneFruitore));

        $righe = $this->ciceroneDatabase->query($query, $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_RICHIESTA_AMMINISTRAZIONE_NON_VALIDA;

        if ($nRighe === 1) {
            $richiesta = RichiestaDisiscrizione::daArray($righe[0]);
            $codiceStato = self::STATO_RICHIESTA_AMMINISTRAZIONE_VALIDA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Restituisce una certa Richiesta di aggiornamento dato l'id.
     * @param int $id
     * @param RichiestaAggiornamento $richiesta
     * @return int lo stato dell'operazione, che può essere: STATO_RICHIESTA_AMMINISTRAZIONE_NON_VALIDA,
     * STATO_RICHIESTA_AMMINISTRAZIONE_VALIDA
     */
    public function getRichiestaAggiornamento(int $id, ?RichiestaAggiornamento& $richiesta) : int {
        $richiesta = NULL;

        $this->ciceroneDatabase->apri();
        
        $colonneRichiesta = array(
            "id", "id_anagrafica"
        );

        $colonneAnagrafica = array("id", "nome", "cognome", "data_nascita", "luogo_nascita",
            "residenza", "telefono", "codice_fiscale"
        );

        $colonneUtente = array(
            "id", "nome_utente"
        );

        $query = self::creaQueryConColonneRiconoscibili("select %s from RichiestaAggiornamento, " .
            "Anagrafica, Utente where RichiestaAggiornamento.id_anagrafica = Anagrafica.id " .
            "and Anagrafica.id_cicerone = Utente.id and RichiestaAggiornamento.id = ?",
            array(RichiestaAggiornamento::NOME_TABELLA, Anagrafica::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneRichiesta, $colonneAnagrafica, $colonneUtente));

        $righe = $this->ciceroneDatabase->query($query, $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_RICHIESTA_AMMINISTRAZIONE_NON_VALIDA;

        if ($nRighe === 1) {
            $richiesta = RichiestaAggiornamento::daArray($righe[0]);
            $codiceStato = self::STATO_RICHIESTA_AMMINISTRAZIONE_VALIDA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'invio della richiesta di disiscrizione.
     * @param int $id
     * @param string $descrizione
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RICHIESTA_DISISCRIZIONE_INVIATA
     */
    public function inviaRichiestaDisiscrizione(int $id, string $descrizione) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ?", $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola("insert into RichiestaDisiscrizione " .
                "(id_fruitore, descrizione) values (?, ?)", $id, $descrizione);
            $codiceStato = self::STATO_RICHIESTA_DISISCRIZIONE_INVIATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la disiscrizione di un fruitore.
     * @param int $id
     * @return int
     */
    public function disiscriviFruitore(int $id, ?string& $immagineFruitore) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select immagine from Utente where id = ?", $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $immagineFruitore = $righe[0]["immagine"];
            $this->ciceroneDatabase->manipola("delete from Utente where id = ?", $id);
            $codiceStato = self::STATO_DISISCRIZIONE_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Restituisce tutte le richieste d'amministrazione attualmente memorizzate.
     * @param RichiesteAmministrazione $richieste conterrà tutte le richieste d'amministrazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getRichiesteAmministrazione(?RichiesteAmministrazione& $richieste) : int {
        $this->ciceroneDatabase->apri();

        $richieste = new RichiesteAmministrazione();

        $colonneUtente = array("id", "nome_utente");
        $colonneAnagrafica = array("id", "nome", "cognome", "data_nascita", "luogo_nascita",
             "residenza", "telefono", "codice_fiscale"
        );
        $colonneDisiscrizione = array("id", "descrizione");
        $colonneAggiornamento = array("id");

        $query = self::creaQueryConColonneRiconoscibili("select %s from RichiestaDisiscrizione, " .
            "Utente where id_fruitore = Utente.id",
            array(RichiestaDisiscrizione::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneDisiscrizione, $colonneUtente));

        $righeDisiscrizione = $this->ciceroneDatabase->query($query);

        foreach ($righeDisiscrizione as $rigaDisiscrizione) {
            $richieste->addRichiesta(RichiestaDisiscrizione::daArray($rigaDisiscrizione));
        }

        $query = self::creaQueryConColonneRiconoscibili("select %s from RichiestaAggiornamento, " .
            "Anagrafica, Utente where id_anagrafica = Anagrafica.id and Anagrafica.id_cicerone = Utente.id",
            array(RichiestaAggiornamento::NOME_TABELLA, Utente::NOME_TABELLA, Anagrafica::NOME_TABELLA),
            array($colonneAggiornamento, $colonneUtente, $colonneAnagrafica));

        $righeAggiornamento = $this->ciceroneDatabase->query($query);

        foreach ($righeAggiornamento as $rigaAggiornamento) {
            $richieste->addRichiesta(RichiestaAggiornamento::daArray($rigaAggiornamento));
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_NO_SEGNALAZIONE;
    }


    /**
     * Esegue il caso d'uso inerente l'invio della richiesta di aggiornamento.
     * @param int $id
     * @param string $nome
     * @param string $cognome
     * @param string $dataNascita
     * @param string $luogoNascita
     * @param string $residenza
     * @param string $telefono
     * @param string $codiceFiscale
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RICHIESTA_AGGIORNAMENTO_INVIATA
     */
    public function inviaRichiestaAggiornamento(int $id, string $nome,
        string $cognome, string $dataNascita, string $luogoNascita, string $residenza,
        string $telefono, string $codiceFiscale) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ?", $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $this->ciceroneDatabase->iniziaTransazione();

            $this->ciceroneDatabase->manipola("insert into Anagrafica (id_cicerone, nome, cognome, " .
                "data_nascita, luogo_nascita, residenza, telefono, codice_fiscale) values " .
                "(?, ?, ?, ?, ?, ?, ?, ?)", $id, $nome, $cognome, $dataNascita, $luogoNascita,
                $residenza, $telefono, $codiceFiscale
            );

            $idAnagrafica = $this->ciceroneDatabase->getLastInsertID();

            $this->ciceroneDatabase->manipola("insert into RichiestaAggiornamento (id_anagrafica) " .
                "values (?)", $idAnagrafica
            );

            $this->ciceroneDatabase->commit();

            $codiceStato = self::STATO_RICHIESTA_AGGIORNAMENTO_INVIATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la trasformazione in QuasiCicerone di un certo utente di tipo Globetrotter.
     * @param int $id
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_TRASFORMAZIONE_QUASICICERONE_RIUSCITA
     */
    public function trasformaInQuasiCicerone(int $id) : int {
        $this->ciceroneDatabase->apri();

        $codiceStato = self::STATO_NO_SEGNALAZIONE;
        
        $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ? and tipo = ?",
            $id, Utente::TIPO_GLOBETROTTER);
        $nRighe = count($righe);


        if ($nRighe === 1) {
            $this->ciceroneDatabase->iniziaTransazione();

            $this->ciceroneDatabase->manipola("update Utente set tipo = ? where id = ?",
                Utente::TIPO_QUASICICERONE, $id);

            $this->ciceroneDatabase->manipola("delete from RichiestaAggiornamento where " .
                "(select id_cicerone from Anagrafica where id_anagrafica = Anagrafica.id " . 
                "and id_cicerone = ?)", $id);

            $this->ciceroneDatabase->commit();

            $codiceStato = self::STATO_TRASFORMAZIONE_QUASICICERONE_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la transizione a Cicerone per un certo utente di tipo QuasiCicerone.
     * @param int $id
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_TRANSIZIONE_CICERONE_RIUSCITA
     */
    public function effettuaTransizioneACicerone(int $id) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ? " .
            "and tipo = ?", $id, Utente::TIPO_QUASICICERONE);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola("update Utente set tipo = ? where id = ?",
                Utente::TIPO_CICERONE, $id);

            $codiceStato = self::STATO_TRANSIZIONE_CICERONE_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
}

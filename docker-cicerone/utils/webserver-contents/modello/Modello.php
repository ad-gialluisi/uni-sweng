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

require_once "CiceroneDatabase.php";
require_once "entità/Utente.php";

use modello\entità\Utente;


/**
 * Rappresenta un modello di questo sistema.
 * <p>Ogni classe che rappresenta un modello specifico deve essere figlia
 * di questa classe.<br>
 * Un qualunque modello in questo sistema si occupa esclusivamente di:</p>
 * <ul><li>Eseguire i casi d'uso</li>
 * <li>Manipolare e ottenere i dati dal database Cicerone.</li>
 * <li>Comunicare con il corrispondente Controllore.<br>
 * La comunicazione avviene mediante codici di stato (cioè che indicano
 * come è andata una certa operazione).<br>
 * I controllori otterrano dei codici che poi tramuteranno in messaggi
 * significativi o in dati da restituire alle viste.</li></ul>
 */
abstract class Modello {
    /**
     * Questo stato è utilizzato ogniqualvolta non si vuole segnalare
     * qualcosa.
     * È da notare che a seguito di questo stato, è possibile che l'operazione
     * sia andata a buon fine, però può anche essere l'opposto.
     * @var int
     */
    public const STATO_NO_SEGNALAZIONE = 0;

    /**
     * Stato raggiunto quando la ricerca dell'utente per id
     * ha avuto esito positivo.
     */
    public const STATO_UTENTE_TROVATO     = 1;
    
    /**
     * Stato raggiunto quando la ricerca dell'utente per id
     * ha avuto esito negativo.
     */
    public const STATO_UTENTE_NON_TROVATO = 2;
    

    /*
     * Regex fornite per validare alcuni tipi di dato.
     * Inserite qui perchè sono comuni a molti modelli più
     * specifici.
     */
    /**
     * Regex per un id numerico
     */
    public const REGEX_ID = "#[0-9]+#";
    
    /**
     * Regex per una data qualunque nel formato YYYY-MM-DD
     */
    public const REGEX_DATA = "#[1-9][0-9]{3}\-(?:0[1-9]|1[0-2])\-(?:0[1-9]|[1-2][0-9]|3[0-1])#";

    
    /**
     * Massima lunghezza disponibile nel DB per il valore
     * di un campo di tipo stringa.
     */
    public const MAX_LUNGHEZZA_CAMPO_TESTUALE = 255;
    
    

    //Gestore della connessione al DB
    protected $ciceroneDatabase;



    

    public function __construct() {
        $this->ciceroneDatabase = new CiceroneDatabase();
    }


    /**
     * Consente di caricare le informazioni di un particolare utente.
     * @param string $id
     * @param Utente $utente
     * @return int lo stato dell'operazione, che può essere: STATO_UTENTE_TROVATO,
     * STATO_UTENTE_NON_TROVATO.
     */
    public function getUtente(int $id, ?Utente& $utente) : int {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select id, nome_utente, email, descrizione, " .
            "immagine, tipo, stato from Utente where id = ?", $id);

        if (count($righe) === 1) {
            $utente = Utente::daArray($righe[0]);
            $codiceStato = self::STATO_UTENTE_TROVATO;
        } else {
            $codiceStato = self::STATO_UTENTE_NON_TROVATO;
        }
        
        $this->ciceroneDatabase->chiudi();
        
        return $codiceStato;
    }


    /**
     * Verifica se l'utente (di cui è stato fornito l'id) risulta
     * essere in disiscrizione (=ha inviato una richiesta di disiscrizione
     * e non è stata ancora accordata).
     * @param int $idUtente
     * @return bool true, se è in disiscrizione, false altrimenti.
     */
    public function isUtenteInDisiscrizione(int $idUtente) : bool {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select 1 from RichiestaDisiscrizione " .
            "where id_fruitore = ?", $idUtente);

        $this->ciceroneDatabase->chiudi();

        return count($righe) === 1;
    }


    /**
     * Verifica se l'utente (di cui è stato fornito l'id) risulta
     * essere in aggiornamento (=ha inviato una richiesta di aggiornamento
     * e non è stata ancora accordata).
     * @param int $idUtente
     * @return bool true, se è in aggiornamento, false altrimenti.
     */
    public function isUtenteInAggiornamento(int $idUtente) : bool {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select 1 from RichiestaAggiornamento, Anagrafica " .
            "where id_anagrafica = Anagrafica.id and Anagrafica.id_cicerone = ?",
            $idUtente);

        $this->ciceroneDatabase->chiudi();

        return count($righe) === 1;
    }


    /**
     * Metodo di utilità utilizzato per le query.
     * Serve a creare una stringa rappresentante una lista di colonne
     * con clausola "as" che antepone per ogni singola colonna il nome
     * della tabella a cui appartengono, seguita da un underscore.
     * @param array $colonne i nomi delle colonne
     * @param string $tabella il nome della tabella
     * @return string una parte della query che presenta le colonne con i relativi
     * nomi modificati per includere il nome della tabella.
     */
    protected  static function inserisciNomeTabellaAColonne(array $colonne, string $tabella) {
        $queryPart = array();

        foreach ($colonne as $colonna) {
            $queryPart[]= sprintf("%s.%s as %s_%s", $tabella, $colonna, $tabella, $colonna);
        }

        return implode(", ", $queryPart);
    }


    /**
     * Metodo di utilità utilizzato per le query.
     * Serve per creare una query con una lista di colonne con clausola
     * "as" che antepone per ogni singola colonna il nome della tabella
     * d'appartenenza, seguita da un underscore.<br>
     * Viene utilizzato per associare in maniera univoca ad ogni colonna
     * la tabella corrispondente, lo scopo è semplificare il caricamento
     * delle entità.<br>
     * Contrariamente alla precedente, questo metodo accetta colonne di tabelle
     * MULTIPLE consentendo dunque la creazione di query con appunto, colonne
     * di tabelle multiple facilmente analizzabili.
     * @param string $queryPart la query con un parametro segnaposto "%s"
     * @param array $colonne i diversi insiemi di colonne
     * @param string $tabella i diversi nomi delle tabelle
     * @return string la query che presenta le colonne con i relativi
     * nomi modificati per includere il nome della rispettiva tabella.
     */
    protected static function creaQueryConColonneRiconoscibili(string $queryFormat, array $tabelle, array $colonne) : string {
        $queryPart = array();

        foreach ($colonne as $idx => $famigliaColonne) {
            if (!is_array($famigliaColonne)) {
                $famigliaColonne = array($famigliaColonne);
            }
            $queryPart[]= self::inserisciNomeTabellaAColonne($famigliaColonne, $tabelle[$idx]);
        }

        return sprintf($queryFormat, implode(", ", $queryPart));
    }
}
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


namespace controllore;

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/GestoreSession.php";
require_once "ScrittoreSessione.php";

use utils\GestoreSession;


/**
 * La classe Sessione rappresenta appunto una sessione che avviene
 * utilizzando il sistema Cicerone.
 * 
 * <p>Implementa ScrittoreSessione, poichè deve essere in grado, concretamente,
 * di leggere e scrivere dati di sessione.<br>
 * Questa è l'unica classe capace di gestire dati che:</p>
 * 
 * <ol><li>Non si trova nel namespace "modello"</li>
 * <li>Conseguenza della 1, non è gestita da un'apposita classe
 * figlia di Modello.</li></ol>
 * 
 * <p>La gestione dei dati di sessione è un compito esclusivo dei controllori.<br>
 * La decisione di NON dedicare una classe figlia di Modello per la gestione
 * dei dati di sessione è dovuta alla seguente constatazione:<br>
 * I dati di sessione riguardano l'aspetto <i>interattivo</i> del sistema,
 * ovvero come esso permette ad un attore di interagire e quindi di richiedere
 * l'esecuzione di determinate funzioni/operazioni/procedure.</p>
 * 
 * <p>Se si volesse modificare completamente questo aspetto del sistema (ergo,
 * viste + controllori), tralasciando però la logica di business e dunque i dati
 * persistenti (modello), si avrebbero diversi dati di sessione, perchè
 * cambierebbe il modo con cui il sistema consente all'attore di interagire,
 * ma non si avrebbero diversi dati persistenti, poichè le funzionalità di base
 * sono sempre quelle.<br>
 * Conseguenza, non ha molto senso creare un Modello capace di gestire la sessione.</p>
 * 
 * @see Controllore
 * @see \modello\Modello
 */
class Sessione implements ScrittoreSessione {
    /**
     * Proprietà id utente
     */
    private const ID_UTENTE = "idUtente";

    /**
     * Proprietà nome utente
     */
    private const NOME_UTENTE = "nomeUtente";

    /**
     * Proprietà immagine utente
     */
    private const IMMAGINE_UTENTE = "immagineUtente";

    /**
     * Proprietà tipo utente
     */
    private const TIPO_UTENTE = "tipoUtente";

    /**
     * Proprietà stato utente
     */
    private const STATO_UTENTE = "statoUtente";

    /**
     * Proprietà stato operazione
     */
    private const STATO_OPERAZIONE = "statoOperazione";

    /**
     * Proprietà utente in disiscrizione
     */
    private const UTENTE_IN_DISISCRIZIONE = "inDisiscrizione";

    /**
     * Proprietà utente in aggiornamento
     */
    private const UTENTE_IN_AGGIORNAMENTO = "inAggiornamento";


    /*
     * Sono proprietà utilizzate per impostare la messaggistica
     */
    /**
     * Proprietà che riguarda il messaggio
     */
    private const MESSAGGIO = "messaggio";
    
    /**
     * Proprietà che riguarda il messaggio di backup
     */
    private const MESSAGGIO_BACKUP = "messaggioBackup";

    /**
     * Proprietà che riguarda lo stato della messaggistica
     */
    private const STATO_MESSAGGISTICA = "statoMessaggistica";
    
    /**
     * Proprietà che riguarda la schermata associata ai messaggi.
     */
    private const MESSAGGIO_SCHERMATA = "schermata";

    /**
     * Stato 's' di statoMessaggistica
     */
    private const STATO_MESSAGGISTICA_S = "s";
    
    /**
     * Stato 't' di statoMessaggistica
     */
    private const STATO_MESSAGGISTICA_T = "t";

    
    
    /**
     * istanza di GestoreSession
     * @var GestoreSession
     */
    private $gestoreSession;


    public function __construct() {
        $this->gestoreSession = new GestoreSession();
    }

    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getIDUtente()
     */
    public function getIDUtente(): ?int {
        return $this->gestoreSession->get(self::ID_UTENTE);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getNomeUtente()
     */
    public function getNomeUtente(): ?string {
        return $this->gestoreSession->get(self::NOME_UTENTE);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getTipoUtente()
     */
    public function getTipoUtente(): ?string {
        return $this->gestoreSession->get(self::TIPO_UTENTE);
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getStatoOperazione()
     */
    public function getStatoOperazione(): ?int {
        return $this->gestoreSession->get(self::STATO_OPERAZIONE);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getImmagineUtente()
     */
    public function getImmagineUtente(): ?string {
        return $this->gestoreSession->get(self::IMMAGINE_UTENTE);
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getStatoUtente()
     */
    public function getStatoUtente(): ?string {
        return $this->gestoreSession->get(self::STATO_UTENTE);
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteInAggiornamento()
     */
    public function isUtenteInAggiornamento() : ?bool {
        return $this->gestoreSession->get(self::UTENTE_IN_AGGIORNAMENTO);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteInDisiscrizione()
     */
    public function isUtenteInDisiscrizione() : ?bool {
        return $this->gestoreSession->get(self::UTENTE_IN_DISISCRIZIONE);
    }

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setIDUtente()
     */
    public function setIDUtente(?int $idUtente) : void {
        $this->gestoreSession->set(self::ID_UTENTE, $idUtente);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setNomeUtente()
     */
    public function setNomeUtente(?string $nomeUtente) : void {
        $this->gestoreSession->set(self::NOME_UTENTE, $nomeUtente);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setTipoUtente()
     */
    public function setTipoUtente(?string $tipoUtente) : void {
        $this->gestoreSession->set(self::TIPO_UTENTE, $tipoUtente);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setStatoUtente()
     */
    public function setStatoUtente(?string $statoUtente) : void {
        $this->gestoreSession->set(self::STATO_UTENTE, $statoUtente);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setImmagineUtente()
     */
    public function setImmagineUtente(?string $immagineUtente) : void {
        $this->gestoreSession->set(self::IMMAGINE_UTENTE, $immagineUtente);
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setStatoOperazione()
     */
    public function setStatoOperazione(?int $statoOperazione) : void {
        $this->gestoreSession->set(self::STATO_OPERAZIONE, $statoOperazione);
    }

    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setUtenteInAggiornamento()
     */
    public function setUtenteInAggiornamento(?bool $inAggiornamento) : void {
        $this->gestoreSession->set(self::UTENTE_IN_AGGIORNAMENTO, $inAggiornamento);
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setUtenteInDisiscrizione()
     */
    public function setUtenteInDisiscrizione(?bool $inDisiscrizione) : void {
        $this->gestoreSession->set(self::UTENTE_IN_DISISCRIZIONE, $inDisiscrizione);
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::clear()
     */
    public function clear() : void {
        $this->setIDUtente(NULL);
        $this->setNomeUtente(NULL);
        $this->setTipoUtente(NULL);
        $this->setStatoUtente(NULL);
        $this->setStatoOperazione(NULL);
        $this->setImmagineUtente(NULL);
        $this->distruggiMessaggistica();
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteConnesso()
     */
    public function isUtenteConnesso() : bool {
        return $this->getIDUtente() !== NULL;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::ciSonoMessaggi()
     */
    public function ciSonoMessaggi() : bool {
        $statoMessaggio = $this->gestoreSession->get(self::STATO_MESSAGGISTICA);

        $ciSono = $this->gestoreSession->get(self::MESSAGGIO) !== NULL;

        if ($statoMessaggio !== NULL && !$ciSono) {
            $ciSono = $this->gestoreSession->get(self::MESSAGGIO_BACKUP) !== NULL;
        }

        return $ciSono;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getMessaggi()
     */
    public function getMessaggi() : array {
        $messaggi = $this->gestoreSession->get(self::MESSAGGIO);
        $messaggiBackup = $this->gestoreSession->get(self::MESSAGGIO_BACKUP);

        $messaggioFinale = array();

        if ($messaggi !== NULL && $messaggiBackup !== NULL) {
            $messaggioFinale = array_merge($messaggiBackup, $messaggi);

        } else if ($messaggi !== NULL || $messaggiBackup !== NULL) {
            if ($messaggi !== NULL) {
                $messaggioFinale = $messaggi;
            }
            if ($messaggiBackup !== NULL) {
                $messaggioFinale = $messaggiBackup;
            }
        }

        return $messaggioFinale;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::setMessaggio()
     */
    public function setMessaggio(?string $messaggio) : void {
        if ($messaggio !== NULL) {
            $this->gestoreSession->set(self::MESSAGGIO, array($messaggio));
        } else {
            $this->gestoreSession->set(self::MESSAGGIO, NULL);
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::addMessaggio()
     */
    public function addMessaggio(string $messaggio) : void {
        $esiste = $this->gestoreSession->esiste(self::MESSAGGIO);
        
        if ($esiste) {
            $arr = $this->gestoreSession->get(self::MESSAGGIO);
            if ($arr !== NULL) {
                $arr[]= $messaggio;
                $this->gestoreSession->set(self::MESSAGGIO, $arr);
            } else {
                $this->setMessaggio($messaggio);
            }
        } else {
            $this->setMessaggio($messaggio);
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::copiaMessaggio()
     */
    private function copiaMessaggio() : void {
        $this->gestoreSession->set(self::MESSAGGIO_BACKUP,
            $this->gestoreSession->get(self::MESSAGGIO));
        $this->setMessaggio(NULL);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::copiaMessaggisticaPerSchermata()
     */
    public function copiaMessaggisticaPerSchermata() : void {
        $this->copiaMessaggio();
        $this->gestoreSession->set(self::STATO_MESSAGGISTICA, self::STATO_MESSAGGISTICA_S);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::associaSchermataPerMessaggistica()
     */
    public function associaSchermataPerMessaggistica(string $schermata) : void {
        $statoMessaggio = $this->gestoreSession->get(self::STATO_MESSAGGISTICA);

        if ($statoMessaggio === self::STATO_MESSAGGISTICA_S) {
            $this->gestoreSession->set(self::MESSAGGIO_SCHERMATA, $schermata);
            $this->gestoreSession->set(self::STATO_MESSAGGISTICA, self::STATO_MESSAGGISTICA_T);

        } else if ($statoMessaggio === self::STATO_MESSAGGISTICA_T) {
            $currSchermata = $this->gestoreSession->get(self::MESSAGGIO_SCHERMATA);

            if ($currSchermata !== $schermata) {
                $this->setMessaggio(NULL);
                $this->copiaMessaggio();
                $this->gestoreSession->unset(self::STATO_MESSAGGISTICA);
            }
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\ScrittoreSessione::distruggiMessaggistica()
     */
    public function distruggiMessaggistica() : void {
        $this->setMessaggio(NULL);
        $this->copiaMessaggio();
    }


}
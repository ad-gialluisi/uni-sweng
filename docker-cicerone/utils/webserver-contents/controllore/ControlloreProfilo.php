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

require_once "ControlloreUtente.php";

define ("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);

require_once DOCUMENT_ROOT . "/modello/ModelloProfilo.php";
require_once DOCUMENT_ROOT . "/modello/entità/Anagrafica.php";

use modello\ModelloProfilo;
use modello\entità\Anagrafica;


/**
 * Rappresenta il controllore associato alla VistaProfilo e al ModelloProfilo.
 * 
 * @see \vista\VistaProfilo
 * @see \modello\ModelloProfilo
 */
class ControlloreProfilo extends ControlloreUtente {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva elaborazione.
     */
    public const CAMPO_VECCHIA_PASSWORD = "vecchiapassword";
    public const CAMPO_NUOVA_PASSWORD = "nuovapassword";
    public const CAMPO_CONFERMA_NUOVA_PASSWORD = "confermanuovapassword";


    /*
     * Tipi di validazione
     */
    private const VALIDAZIONE_PROFILO = "profilo";
    private const VALIDAZIONE_ANAGRAFICA = "anagrafica";

    
    /**
     * Dimensione massima consentita per un'immagine di profilo in byte
     */
    public const MAX_DIMENSIONE_IMMAGINE = 150000;


    /**
     * Larghezza massima per un'immagine di profilo in pixel
     */
    public const MAX_WIDTH_IMMAGINE = 500;


    /**
     * Altezza massima per un'immagine di profilo in pixel
     */
    public const MAX_HEIGHT_IMMAGINE = 500;




    /**
     * Crea un nuovo ControlloreAccesso con un ModelloProfilo sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloProfilo());
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati di un'anagrafica
     * associata ad un utente.
     * @param int $idUtente L'id dell'utente da reperire
     * @param Anagrafica $anagrafica un'istanza che conterrà alla fine dell'operazione,
     * i dati dell'anagrafica cercata, se esiste.
     */
    public function richiediAnagrafica(int $idUtente, ?Anagrafica& $anagrafica) : void {
        $this->modello->getAnagrafica($idUtente, $anagrafica);
    }


    /**
     * Esegue l'elaborazione per effettuare la modifica del profilo di un utente.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini un percorso alla cartella che contiene le
     * immagini di profilo degli utenti
     */
    public function richiediModificaProfilo(array $params,
        string $percorsoImmagini, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_PROFILO);

        if ($validazioneRiuscita) {
            $validazioneRiuscita = $this->validaImmagineUpload($params, $percorsoImmagini);

            if ($validazioneRiuscita) {
                $idUtente = $this->sessione->getIDUtente();
                $descrizione = $params[self::CAMPO_DESCRIZIONE];
                $email = $params[self::CAMPO_EMAIL];
                $vecchiaPassword = $params[self::CAMPO_VECCHIA_PASSWORD];
                $nuovaPassword = $params[self::CAMPO_NUOVA_PASSWORD];
                $nuovaImmagineUtente = $params[self::CAMPO_IMMAGINE_UPLOAD];
                $ripristinaImmagineUtente = $params[self::CAMPO_RIPRISTINA_IMMAGINE];

                $vecchiaImmagine = NULL;
                if ($nuovaImmagineUtente === NULL && $ripristinaImmagineUtente) {
                    $nuovaImmagineUtente = $immagineDefault;
                }

                $codiceStato = $this->modello->modificaProfilo($idUtente,
                    $email, $descrizione, $nuovaImmagineUtente, $vecchiaImmagine,
                    $vecchiaPassword, $nuovaPassword);

                switch($codiceStato) {
                    case ModelloProfilo::STATO_VECCHIA_PASSWORD_NON_VALIDA:
                        $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                        $messaggio = "La vecchia password non è valida";
                    break;
                    case ModelloProfilo::STATO_VECCHIA_NUOVA_PASSWORD_UGUALI:
                        $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                        $messaggio = "La nuova password è uguale a quella vecchia.<br>" .
                            "Cambiarla prego!";
                    break;
                    case ModelloProfilo::STATO_EMAIL_USATA:
                        $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                        $messaggio = "L'email inserita è già in uso.<br>" .
                            "Cambiarla prego!";
                    break;
                    case ModelloProfilo::STATO_PROFILO_MODIFICATO:
                        if ($nuovaImmagineUtente !== NULL) {
                            $this->sessione->setImmagineUtente($nuovaImmagineUtente);

                            //Cancella vecchia immagine estratta se necessario
                            if ($vecchiaImmagine !== $immagineDefault) {
                                $this->cancellaImmagine($percorsoImmagini, $vecchiaImmagine);
                            }
                        }
                        $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                        $messaggio = "Profilo modificato con successo!";
                    break;
                    case ModelloProfilo::STATO_UTENTE_NON_TROVATO:
                        $codiceStato = self::STATO_NO_SEGNALAZIONE;
                        $messaggio = NULL;
                    break;
                    default:
                        //mai eseguito
                    break;
                }

                $this->setInfoOperazione($codiceStato, $messaggio);
            } else {
                $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
            }
        }
    }


    /**
     * Esegue l'elaborazione per effettuare la modifica dell'anagrafica di un utente.
     * @param array $params i parametri della richiesta
     */
    public function richiediModificaAnagrafica(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ANAGRAFICA);

        if ($validazioneRiuscita) {
            $idUtente = $this->getIDUtente();
            $residenza = $params[self::CAMPO_RESIDENZA];
            $telefono = trim($params[self::CAMPO_TELEFONO]);

            $codiceStato = $this->modello->modificaAnagrafica($idUtente, $residenza, $telefono);

            switch ($codiceStato) {
                case ModelloProfilo::STATO_ANAGRAFICA_MODIFICATA;
                    $messaggio = sprintf("L'anagrafica è stata modificata con successo!");
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;
                case ModelloProfilo::STATO_ID_UTENTE_NO_ANAGRAFICA:
                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected function validaParametri(array& $params, string $tipo) : bool {
        if ($tipo === self::VALIDAZIONE_ANAGRAFICA) {
            $isCicerone = $this->isUtenteCicerone();

            if ($isCicerone) {
                $valido = $this->validaParametriModificaAnagrafica($params);
            } else {
                $valido = false;
            }
        } else if ($tipo === self::VALIDAZIONE_PROFILO) {
            if ($this->isUtenteConnesso()) {
                $valido = $this->validaParametriModificaProfilo($params);
            } else {
                $valido = false;
            }
        }

        if (!$valido) {
            $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
        }

        return $valido;
    }


    /**
     * Effettua la validazione dei parametri per la modifica del profilo.
     * @param array $params i parametri da validare
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriModificaProfilo(array& $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_DESCRIZIONE,
            self::CAMPO_EMAIL, self::CAMPO_VECCHIA_PASSWORD,
            self::CAMPO_NUOVA_PASSWORD, self::CAMPO_CONFERMA_NUOVA_PASSWORD);

        if ($valido) {
            $valido = $this->validaCampoSemplice($params[self::CAMPO_DESCRIZIONE], "descrizione", 0);
        }

        if ($valido) {
            //Non credo ci sia bisogno di fare dei check sulla descrizione
            $email = $params[self::CAMPO_EMAIL];
            $vecchiaPassword = $params[self::CAMPO_VECCHIA_PASSWORD];
            $nuovaPassword = $params[self::CAMPO_NUOVA_PASSWORD];
            $confermaNuovaPassword = $params[self::CAMPO_CONFERMA_NUOVA_PASSWORD];

            $validazioni = array(
                "validaCampoEmail" => array($email)
            );

            if ($vecchiaPassword === "" && $nuovaPassword === "" &&
                $confermaNuovaPassword === "") {
                /*
                 * Non è stata richiesta la modifica della password.
                 * Modifica i parametri corrispondenti perchè la situazione
                 * venga interpretata correttamente.
                 */
                $params[self::CAMPO_VECCHIA_PASSWORD] = NULL;
                $params[self::CAMPO_NUOVA_PASSWORD] = NULL;
            } else {
                /*
                 * In caso contrario, procedi alla validazione delle password
                 * inserite.
                 */
                $validazioni["validaCampoPassword"] = array($vecchiaPassword);
                $validazioni["validaCampiPassword"] = array($nuovaPassword, $confermaNuovaPassword);
            }

            foreach ($validazioni as $metodo => $operandi) {
                $valido = call_user_func_array(array($this, $metodo), $operandi);
                
                if (!$valido) {
                    break;
                }
            }
        }

        return $valido;
    }


    /**
     * Effettua la validazione dei parametri per la modifica dell'anagrafica.
     * @param array $params i parametri da validare
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriModificaAnagrafica(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_RESIDENZA, self::CAMPO_TELEFONO);

        if ($valido) {
            $residenza = $params[self::CAMPO_RESIDENZA];
            $telefono = trim($params[self::CAMPO_TELEFONO]);

            $valido = $this->validaCampoSemplice($residenza, "residenza", 3);
            
            if ($valido) {
                $valido = $this->validaCampoTelefono($telefono);
            }
        }

        return $valido;
    }
}
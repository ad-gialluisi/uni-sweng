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

require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloProfilo.php";
require_once "ControlloreUtente.php";

use modello\ModelloProfilo;
use modello\entità\Utente;


/**
 * Rappresenta il controllore associato alla VistaAccesso e al ModelloProfilo.
 * 
 * @see \vista\VistaAccesso
 * @see \modello\ModelloProfilo
 */
class ControlloreAccesso extends ControlloreUtente {
    /*
     * Questi sono i campi che questo controllore è in grado di validare
     * e di utilizzare per la successiva elaborazione.
     */
    public const CAMPO_NOME_UTENTE = "nomeutente";
    public const CAMPO_PASSWORD = "password";
    public const CAMPO_CONFERMA_PASSWORD = "confermapassword";
    public const CAMPO_CODICE_ATTIVAZIONE = "codice";

    /*
     * Tipi di validazione
     */
    private const VALIDAZIONE_REGISTRAZIONE = "registrazione";
    private const VALIDAZIONE_ACCESSO = "accesso";
    private const VALIDAZIONE_RECUPERO = "recupero";
    private const VALIDAZIONE_ATTIVAZIONE = "attivazione";


    /**
     * Stato che indica che è avvenuto un errore durante la
     * reimpostazione della password (recupero accesso).
     */
    public const STATO_ERRORE_REIMPOSTAZIONE_PASSWORD = 3;


    /**
     * Crea un nuovo ControlloreAccesso con un ModelloProfilo sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloProfilo());
    }


    /**
     * Esegue l'elaborazione per effettuare la registrazione.
     * @param array $params parametri da validare
     * @param string $urlAttivazione L'URL da utilizzare per creare l'indirizzo di attivazione
     * @param string $immagineDefault il percorso all'immagine di default per gli utenti
     */
    public function richiediRegistrazione(array $params, string $urlAttivazione, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_REGISTRAZIONE);

        if ($validazioneRiuscita) {
            $nomeUtente = $params[self::CAMPO_NOME_UTENTE];
            $email = $params[self::CAMPO_EMAIL];
            $password = $params[self::CAMPO_PASSWORD];

            $utente = NULL;
            $codiceAttivazione = NULL;
            $codiceStato = $this->modello->registraUtente($nomeUtente, $password, $email,
                $utente, $codiceAttivazione, $immagineDefault);

            switch ($codiceStato) {
                case ModelloProfilo::STATO_NOME_UTENTE_EMAIL_USATI;
                    $messaggio = sprintf("Sia il nome utente \"%s\" che l'email \"%s\" sono già in uso!",
                        $nomeUtente, $email);
                    $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                break;

                case ModelloProfilo::STATO_NOME_UTENTE_USATO:
                    $messaggio = sprintf("Il nome utente \"%s\" è già in uso!", $nomeUtente);
                    $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                    break;

                case ModelloProfilo::STATO_EMAIL_USATA:
                    $messaggio = sprintf("L'email \"%s\" è già in uso!", $email);
                    $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                break;

                case ModelloProfilo::STATO_UTENTE_INSERITO:
                    $messaggio = sprintf("Se l'indirizzo e-mail è valido, riceverai" .
                        " a breve un'email con un link.<br>" .
                        "Clicca sul link per attivare l'utente appena registrato.<br>" .
                        "Finchè l'utente non verrà attivato, non sarà possibile accedere.");

                    self::inviaCodiceAttivazione($utente->getID(),
                        $utente->getNomeUtente(),
                        $utente->getEmail(),
                        $utente->getStato(),
                        $codiceAttivazione,
                        $urlAttivazione);

                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;

                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione per fare l'accesso.
     * @param array $params i parametri della richiesta
     */
    public function richiediAccesso(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ACCESSO);

        if ($validazioneRiuscita) {
            $nomeUtente = $params[self::CAMPO_NOME_UTENTE];
            $password = $params[self::CAMPO_PASSWORD];

            
            $utente = NULL; //conterrà l'utente reperito dal modello
            $codiceStato = $this->modello->consentiAccesso($nomeUtente, $password, $utente);

            switch ($codiceStato) {
                case ModelloProfilo::STATO_NOME_UTENTE_NON_TROVATO:
                case ModelloProfilo::STATO_UTENTE_DA_ATTIVARE:
                case ModelloProfilo::STATO_ACCESSO_FALLITO:
                    $messaggio = "Errore: Nome utente o password non validi.";
                    $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                break;

                case ModelloProfilo::STATO_ACCESSO_RIUSCITO:
                    $this->sessione->setIDUtente($utente->getID());
                    $this->sessione->setNomeUtente($utente->getNomeUtente());
                    $this->sessione->setTipoUtente($utente->getTipo());
                    $this->sessione->setImmagineUtente($utente->getImmagine());
                    $this->sessione->setStatoUtente($utente->getStato());
                    $messaggio = NULL;
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;
                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione per effettuare il recupero dell'accesso.
     * @param array $params i parametri della richiesta
     * @param string $urlAttivazione il template dell'URL da utilizzare per creare l'indirizzo di attivazione
     */
    public function richiediRecuperoAccesso(array $params, string $urlAttivazione) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_RECUPERO);

        if ($validazioneRiuscita) {
            $email = $params[self::CAMPO_EMAIL];

            $utente = NULL;
            $codiceAttivazione = NULL;
            $codiceStato = $this->modello->richiediRecuperoAccesso($email, $utente, $codiceAttivazione);

            if ($codiceStato === ModelloProfilo::STATO_RICHIESTA_RECUPERO_RIUSCITA) {
                $messaggio = "Riceverai a breve un'email con un link.<br>" .
                    "Clicca sul link per accedere al menù di reimpostazione della password.";

                self::inviaCodiceAttivazione($utente->getID(),
                    $utente->getNomeUtente(),
                    $email,
                    $utente->getStato(),
                    $codiceAttivazione,
                    $urlAttivazione);
                
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;

            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione per l'attivazione di un utente.
     * @param array $params i parametri della richiesta
     */
    public function richiediAttivazioneUtente(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ATTIVAZIONE);

        if ($validazioneRiuscita) {
            $id = $params[self::CAMPO_ID];
            $codiceAttivazione = $params[self::CAMPO_CODICE_ATTIVAZIONE];

            if (isset($params[self::CAMPO_PASSWORD])) {
                $password = $params[self::CAMPO_PASSWORD];
            } else {
                $password = NULL;
            }

            $codiceStato = $this->modello->attivaUtente($id, $codiceAttivazione, $password);

            switch ($codiceStato) {
                case ModelloProfilo::STATO_RECUPERO_RIUSCITO:
                    $messaggio = "Il recupero è riuscito, ora è possibile accedere con " .
                        "le nuove credenziali.";
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;
                case ModelloProfilo::STATO_ATTIVAZIONE_RIUSCITA:
                    $messaggio = "L'attivazione è riuscita, ora è possibile accedere.";
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;
                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue la disconnessione.
     */
    public function richiediDisconnessione() : void {
        if ($this->isUtenteConnesso()) {
            $this->sessione->clear();
            $this->sessione->setStatoOperazione(self::STATO_NO_SEGNALAZIONE);
        } else {
            $this->setInfoOperazione(self::STATO_NO_SEGNALAZIONE, NULL);
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected function validaParametri(array& $params, string $tipo) : bool {
        $valido = !$this->isUtenteConnesso(); //NON BISOGNA ESSERE CONNESSI!
        $impostaOperazioneFallita = true;

        if ($valido) {
            switch ($tipo) {
                case self::VALIDAZIONE_ACCESSO:
                    $valido = $this->validaParametriAccesso($params);
                break;
                case self::VALIDAZIONE_RECUPERO:
                    $valido = $this->validaParametriRecupero($params);
                break;
                case self::VALIDAZIONE_REGISTRAZIONE:
                    $valido = $this->validaParametriRegistrazione($params);
                break;
                case self::VALIDAZIONE_ATTIVAZIONE:
                    $valido = $this->validaParametriAttivazione($params);
                    $impostaOperazioneFallita = false;
                break;
                default:
                    //mai eseguito
                break;
            }
        }

        if (!$valido && $impostaOperazioneFallita) {
            $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
        }

        return $valido;
    }


    /**
     * Effettua la validazione dei parametri utilizzati per compiere
     * l'operazione di registrazione
     * @param array $params i parametri
     * @return bool true, se i pararmetri sono validi, false altrimenti
     */
    private function validaParametriRegistrazione(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_NOME_UTENTE,
            self::CAMPO_PASSWORD, self::CAMPO_CONFERMA_PASSWORD, self::CAMPO_EMAIL);

        if ($valido) {
            $nomeUtente = $params[self::CAMPO_NOME_UTENTE];
            $password = $params[self::CAMPO_PASSWORD];
            $confermaPassword = $params[self::CAMPO_CONFERMA_PASSWORD];
            $email = $params[self::CAMPO_EMAIL];

            $valido = $this->validaCampoSemplice($nomeUtente, "nome utente", 4);
            
            if ($valido) {
                $validazioni = array(
                    "validaCampiPassword" => array($password, $confermaPassword),
                    "validaCampoEmail" => array($email)
                );

                foreach ($validazioni as $metodo => $operandi) {
                    $valido = call_user_func_array(array($this, $metodo), $operandi);
                    
                    if (!$valido) {
                        break;
                    }
                }
            }
        }

        return $valido;
    }
    

    /**
     * Effettua la validazione dei parametri utilizzati per compiere
     * l'operazione di recupero dell'accesso
     * @param array $params i parametri
     * @return bool true, se i pararmetri sono validi, false altrimenti
     */
    private function validaParametriRecupero(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_EMAIL);

        if ($valido) {
            $email = $params[self::CAMPO_EMAIL];
            $valido = $this->validaCampoEmail($email);
        }

        return $valido;
    }
    

    /**
     * Effettua la validazione dei parametri utilizzati per compiere
     * l'operazione di accesso
     * @param array $params i parametri
     * @return bool true, se i pararmetri sono validi, false altrimenti
     */
    private function validaParametriAccesso(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_NOME_UTENTE,
            self::CAMPO_PASSWORD);

        if ($valido) {
            $nomeUtente = $params[self::CAMPO_NOME_UTENTE];
            $password = $params[self::CAMPO_PASSWORD];

            $valido = $this->validaCampoSemplice($nomeUtente, "nome utente", 4);
            
            if ($valido) {
                $valido = $this->validaCampoPassword($password);
            }
        }

        return $valido;
    }
    

    /**
     * Effettua la validazione dei parametri utilizzati per compiere
     * l'operazione di attivazione
     * @param array $params i parametri
     * @return bool true, se i pararmetri sono validi, false altrimenti
     */
    private function validaParametriAttivazione(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_ID,
            self::CAMPO_CODICE_ATTIVAZIONE);
        $isRecupero = isset($params[self::CAMPO_PASSWORD]) && isset($params[self::CAMPO_CONFERMA_PASSWORD]);

        //Assumi non vada segnalato
        $this->sessione->setStatoOperazione(self::STATO_NO_SEGNALAZIONE);

        if ($valido) {
            $id = $params[self::CAMPO_ID];
            $codiceAttivazione = $params[self::CAMPO_CODICE_ATTIVAZIONE];

            if ($isRecupero) {
                $password = $params[self::CAMPO_PASSWORD];
                $confermaPassword = $params[self::CAMPO_CONFERMA_PASSWORD];
            }

            $validazioni = array(
                "preg_match" => array(ModelloProfilo::REGEX_ID, $id),
                "preg_match" => array(ModelloProfilo::REGEX_CODICE_ATTIVAZIONE, $codiceAttivazione),
            );

            foreach ($validazioni as $funzione => $operandi) {
                $valido = call_user_func_array($funzione, $operandi);
                
                if (!$valido) {
                    break;
                }
            }

            /*
             * Se trattasi dell'attivazione inerente il recupero dell'accesso
             * continua con la validazione
             */
            if ($valido && $isRecupero) {
                $valido = $this->validaCampiPassword($password, $confermaPassword);

                if (!$valido) {
                    /*
                     * Bisogna segnalare quest'altro errore, perchè in questo caso
                     * abbiamo una buona richiesta d'attivazione, però le password
                     * inserite per la reimpostazione non vanno bene.
                     * Si usa un errore ad hoc per evitare problemi di calcolo in più.
                     */
                    $this->sessione->setStatoOperazione(self::STATO_ERRORE_REIMPOSTAZIONE_PASSWORD);
                    $valido = false;
                }
            }
        }

        return $valido;
    }

    
    /*
     * Quest'altro metodo è di convenienza.
     * Utilizzati dai metodi dichiarati qui sopra.
     */
    /**
     * Questo metodo invia un'email (all'indirizzo specificato) per fornire il codice d'attivazione
     * dell'utente.
     * @param int $id
     * @param string $nomeUtente
     * @param string $email
     * @param string $statoUtente
     * @param string $codiceAttivazione
     * @param string $urlAttivazione
     */
    private static function inviaCodiceAttivazione(int $id, string $nomeUtente, string $email,
        string $statoUtente, string $codiceAttivazione, string $urlAttivazione) {

            if ($statoUtente === Utente::STATO_RECUPERANDO) {

                $oggetto = "Richiesta recupero dell'accesso al sistema";
                $corpoHTML = <<<CORPO
<p>È stato richiesto l'invio di un codice d'attivazione che consenta il recupero dell'accesso per l'utente "%s".<br>
Utilizzare <a href="$urlAttivazione" _target="_blank">questo link</a> per procedere alla reimpostazione della password.</p>
CORPO;

                $corpoTesto = <<<CORPO
È stato richiesto l'invio di un codice d'attivazione che consenta il recupero dell'accesso per l'utente "%s".
Copiare il link "$urlAttivazione" (senza virgolette) nella barra degli indirizzi del browser per procedere.
CORPO;
            } else {

                $oggetto = "Richiesta attivazione del profilo Globetrotter";
                $corpoHTML = <<<CORPO
<p>È stato inviato il codice d'attivazione per il nuovo utente "%s".<br>
Utilizzare <a href="$urlAttivazione" _target="_blank">questo link</a> per effettuare l'attivazione.</p>
CORPO;
                $corpoTesto = <<<CORPO
È stato richiesto l'invio di un codice d'attivazione per il nuovo "%s".
Copiare il link "$urlAttivazione" (senza virgolette) nella barra degli indirizzi del browser per procedere.
CORPO;
            }

            $corpoHTML = sprintf($corpoHTML, $nomeUtente, $id, $codiceAttivazione);
            $corpoTesto = sprintf($corpoTesto, $nomeUtente, $id, $codiceAttivazione);

            self::inviaMail($email, $nomeUtente, $oggetto, $corpoHTML, $corpoTesto);
    }
}
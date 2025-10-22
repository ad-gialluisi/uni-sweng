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
require_once "entità/Utente.php";


use modello\entità\Utente;
use modello\entità\Anagrafica;


/**
 * Questo è il modello che tratta le informazioni che riguardano gli utenti.
 * <p>In particolare, esegue i casi d'uso inerenti:</p>
 * <ul><li>L'accesso al sistema.</li>
 * <li>La registrazione di un utente.</li>
 * <li>Il recupero dell'accesso.</li>
 * <li>L'attivazione di un utente.</li>
 * <li>La visualizzazione del profilo.</li>
 * <li>La modifica del profilo di un utente</li>
 * <li>La modifica dell'anagrafica di un Cicerone</li></ul>
 * 
 * <p>È importare notare che a questo modello sono associate anche l'accoppiata
 * VistaAccesso e ControlloreAccesso.</p>
 */
class ModelloProfilo extends ModelloUtente {
    /*
     * Queste costanti servono ad indicare gli stati che un operazione
     * raggiunge in seguito al successo/fallimento/evoluzione della stessa.
     * Fornirò commenti solo agli stati che possono risultare "ambigui" ad una
     * prima lettura.
     */
    /**
     * Nome utente ed email già usati
     */
    public const STATO_NOME_UTENTE_EMAIL_USATI       =  3;

    /**
     * Nome utente già usato
     */
    public const STATO_NOME_UTENTE_USATO             =  4;

    /**
     * Email già usata
     */
    public const STATO_EMAIL_USATA                   =  5;

    /**
     * Questo stato viene raggiunto quando si crea con successo la riga
     * inerente l'utente nel database, alla fine della fase di registrazione.
     */
    public const STATO_UTENTE_INSERITO               =  6;
    
    /**
     * Utente non trovato
     */
    public const STATO_NOME_UTENTE_NON_TROVATO       =  7;

    /**
     * Questo stato viene raggiunto quando si cerca di accedere con un utente
     * che è nello stato "registrato".
     * @var int
     */
    public const STATO_UTENTE_DA_ATTIVARE            =  8;
    
    /**
     * Accesso riuscito da parte dell'utente
     */
    public const STATO_ACCESSO_RIUSCITO              =  9;

    /**
     * Accesso fallito da parte dell'utente
     */
    public const STATO_ACCESSO_FALLITO               = 10;
    
    /**
     * Questo stato viene raggiunto quando la richiesta di recupero dell'accesso
     * (ovvero, la generazione del codice d'attivazione per il recupero e il cambiamento
     * dello stato dell'utente da "attivato" a "recuperando" ha luogo.
     * @var int
     */
    public const STATO_RICHIESTA_RECUPERO_RIUSCITA   = 11;

    /**
     * Questo stato viene raggiunto quando si riesce ad attivare con successo
     * un utente che è nello stato "recuperando".
     * @var int
     */
    public const STATO_RECUPERO_RIUSCITO             = 12;
    
    /**
     * Questo stato viene raggiunto quando si riesce ad attivare con successo
     * un utente che è nello stato "inserito".
     * @var int
     */
    public const STATO_ATTIVAZIONE_RIUSCITA          = 13;
    
    /**
     * Stato raggiunto quando la ricerca dell'anagrafica in base all'utente
     * d'appartenenza ha esito positivo.
     */
    public const STATO_ANAGRAFICA_TROVATA            = 14;
    
    /**
     * Stato raggiunto quando la ricerca dell'anagrafica in base all'utente
     * d'appartenenza ha esito negativo.
     */
    public const STATO_ANAGRAFICA_NON_TROVATA        = 15;
    
    /**
     * Stato raggiunto quando viene eseguita correttamente la modifica
     * dell'anagrafica.
     */
    public const STATO_ANAGRAFICA_MODIFICATA         = 16;
    
    /**
     * Stato raggiunto quando viene scovato un utente a cui non è associata
     * alcuna anagrafica.
     */
    public const STATO_ID_UTENTE_NO_ANAGRAFICA       = 17;
    
    /**
     * Stato raggiunto quando durante la modifica del profilo, si riscontra
     * che si è fornita la password corrente, ma non è corretta.
     */
    public const STATO_VECCHIA_PASSWORD_NON_VALIDA   = 18;
    
    /**
     * Stato raggiunto quando il profilo è stato modificato con successo.
     */
    public const STATO_PROFILO_MODIFICATO            = 19;
    
    /**
     * Stato raggiunto quando durante la modifica del profilo, si
     * riscontra che la vecchia e nuova password forniti sono la
     * stessa.
     */
    public const STATO_VECCHIA_NUOVA_PASSWORD_UGUALI = 20;


    /*
     * Costanti utili a definire le regex per i tipi di dato passati.
     */
    public const REGEX_CODICE_ATTIVAZIONE = "#[A-Za-z0-9]{12}#";


    /*
     * Costanti utili per la creazione dei codici d'attivazione.
     */
    private const LUNGHEZZA_CODICE_ATTIVAZIONE = 12;
    private const SPAZIO_CODICE_ATTIVAZIONE = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";


    /*
     * I seguenti sono metodi sfruttati per effettuare l'accesso/registrazione/recupero/attivazione
     * degli utenti
     */
    /**
     * Esegue il caso d'uso della registrazione di un utente.
     * @param string $nomeUtente nome dell'utente
     * @param string $password password inserita dall'utente
     * @param string $email email inserita dall'utente
     * @param ?Utente $utente riferimento all'utente caricato, questo sarà reperibile all'esterno
     * @param ?string $codiceAttivazione riferimento al codice d'attivazione generato, questo sarà
     * reperibile all'esterno
     * @return int lo stato dell'operazione, che può essere: STATO_NOME_UTENTE_EMAIL_USATI,
     * STATO_NOME_UTENTE_USATO, STATO_EMAIL_USATA, STATO_UTENTE_INSERITO.
     */
    public function registraUtente(string $nomeUtente, string $password, string $email, ?Utente &$utente, ?string &$codiceAttivazione, string $immagineDefault) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Utente where nome_utente = ?", $nomeUtente);
        $nomeUtenteUsato = count($righe) > 0;
        
        $righe = $this->ciceroneDatabase->query("select 1 from Utente where email = ?", $email);
        $emailUsata = count($righe) > 0;

        if ($nomeUtenteUsato || $emailUsata) {
            if ($nomeUtenteUsato && $emailUsata) {
                $codiceStato = self::STATO_NOME_UTENTE_EMAIL_USATI;

            } else {
                $codiceStato = ($nomeUtenteUsato ? self::STATO_NOME_UTENTE_USATO : self::STATO_EMAIL_USATA);
            }
        } else {
            $nuovoUtente = new Utente();
            $nuovoUtente->setNomeUtente($nomeUtente);
            $nuovoUtente->setEmail($email);
            $nuovoUtente->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $nuovoUtente->setDescrizione("");
            $nuovoUtente->setImmagine($immagineDefault);
            $nuovoUtente->setTipo(Utente::TIPO_GLOBETROTTER);
            $nuovoUtente->setStato(Utente::STATO_INSERITO);

            $codiceAttivazione = self::creaCodiceAttivazione(self::LUNGHEZZA_CODICE_ATTIVAZIONE);
            $nuovoUtente->setCodiceAttivazione(password_hash($codiceAttivazione, PASSWORD_DEFAULT));

            $this->ciceroneDatabase->manipola("insert into Utente (nome_utente, email, password, descrizione, " .
                "immagine, tipo, stato, codice_attivazione) values (?, ?, ?, ?, ?, ?, ?, ?)",
                $nuovoUtente->getNomeUtente(), $nuovoUtente->getEmail(),
                $nuovoUtente->getPassword(),   $nuovoUtente->getDescrizione(),
                $nuovoUtente->getImmagine(),   $nuovoUtente->getTipo(),
                $nuovoUtente->getStato(),      $nuovoUtente->getCodiceAttivazione());

            $nuovoUtente->setID($this->ciceroneDatabase->getLastInsertID());

            /*
             * Manda al chiamante l'utente appena creato.
             */
            $utente = $nuovoUtente;

            $codiceStato = self::STATO_UTENTE_INSERITO;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso dell'accesso di un utente.
     * @param string $nomeUtente nome dell'utente
     * @param string $password password inserita dall'utente
     * @param ?Utente $utente riferimento all'utente caricato, questo sarà reperibile all'esterno
     * @return int lo stato dell'operazione, che può essere: STATO_NOME_UTENTE_NON_TROVATO,
     * STATO_UTENTE_DA_ATTIVARE, STATO_ACCESSO_RIUSCITO, STATO_ACCESSO_FALLITO.
     */
    public function consentiAccesso(string $nomeUtente, string $password, ?Utente &$utente) : int {
        $this->ciceroneDatabase->apri();
        $righe = $this->ciceroneDatabase->query("select id, nome_utente, password, immagine, tipo, " .
            "stato from Utente where nome_utente = ?", $nomeUtente);
        $nRighe = count($righe);

        if ($nRighe !== 1) {
            $codiceStato = self::STATO_NOME_UTENTE_NON_TROVATO;

        } else {
            $utenteRecuperato = Utente::daArray($righe[0]);
            $statoUtente = $utenteRecuperato->getStato();


            if ($statoUtente === Utente::STATO_INSERITO) {
                /* 
                 * Se lo stato dell'utente è lo stato STATO_REGISTRATO, è necessario
                 * indicare che l'utente è ancora da attivare.
                 */
                $codiceStato = self::STATO_UTENTE_DA_ATTIVARE;

            } else {
                 /*
                  * In caso contrario, procedi ai controlli soliti.
                  */
                if (password_verify($password, $utenteRecuperato->getPassword())) {
                    //Crea un nuovo hash e memorizzalo, per garantire più sicurezza.
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    if ($statoUtente === Utente::STATO_RECUPERANDO) {
                        /*
                         * Se l'utente è nello stato STATO_RECUPERANDO e incredibilmente
                         * riesce ad accedere, si assume che abbia ricordato le credenziali.
                         * Il suo stato viene reimpostato su STATO_ATTIVATO.
                         */ 
                        $utenteRecuperato->setStato(Utente::STATO_ATTIVATO);

                        //memorizza di conseguenza
                        $this->ciceroneDatabase->manipola('update Utente set password = ?, stato = ? where id = ?',
                            $hash, $utenteRecuperato->getStato(), $utenteRecuperato->getID());
                    } else {
                        $this->ciceroneDatabase->manipola('update Utente set password = ? where id = ?', $hash,
                            $utenteRecuperato->getID());
                    }

                    //"Manda" l'utente all'esterno.
                    $utente = $utenteRecuperato;
                    $codiceStato = self::STATO_ACCESSO_RIUSCITO;
                } else {
                    $codiceStato = self::STATO_ACCESSO_FALLITO;
                }
            }
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso del recupero dell'accesso di un utente.
     * @param string $email email inserita dall'utente
     * @param ?Utente $utente riferimento all'utente caricato, questo sarà reperibile all'esterno
     * @param ?string $codiceAttivazione riferimento al codice d'attivazione generato, questo sarà
     * reperibile all'esterno
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RICHIESTA_RECUPERO_RIUSCITA
     */
    public function richiediRecuperoAccesso(string $email, ?Utente &$utente, ?string &$codiceAttivazione) : int {
        /*
         * Partiamo dal presupposto che se qualcosa va male durante questa operazione
         * è necessario NON SEGNALARE per evitare che attaccanti possano avere
         * informazioni che possano aiutarli.
         */
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $this->ciceroneDatabase->apri();
        $righe = $this->ciceroneDatabase->query("select id, nome_utente from Utente where email = ?",
            $email);
        $nRighe = count($righe);

        if ($nRighe === 1) {
            //e-mail dell'utente trovata
            $utenteRecuperato = Utente::daArray($righe[0]);

            $codiceAttivazione = self::creaCodiceAttivazione(self::LUNGHEZZA_CODICE_ATTIVAZIONE);
            $utenteRecuperato->setCodiceAttivazione(password_hash($codiceAttivazione, PASSWORD_DEFAULT));
            $utenteRecuperato->setStato(Utente::STATO_RECUPERANDO);

            $this->ciceroneDatabase->manipola("update Utente set stato = ?, codice_attivazione = ? where id = ?",
                $utenteRecuperato->getStato(), $utenteRecuperato->getCodiceAttivazione(),
                $utenteRecuperato->getID());

            //"Manda" l'utente all'esterno
            $utente = $utenteRecuperato;

            $codiceStato = self::STATO_RICHIESTA_RECUPERO_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();
        
        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso dell'attivazione dell'utente.
     * @param string $id
     * @param string $codiceAttivazione
     * @param string $password
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RECUPERO_RIUSCITO, STATO_ATTIVAZIONE_RIUSCITA
     */
    public function attivaUtente(string $id, string $codiceAttivazione, ?string $password=NULL) : int {
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $this->ciceroneDatabase->apri();
        $righe = $this->ciceroneDatabase->query("select codice_attivazione, stato from Utente where id = ?", $id);
        $nRighe = count($righe);

        if ($nRighe === 1) {
            $utente = Utente::daArray($righe[0]);

            if (password_verify($codiceAttivazione, $utente->getCodiceAttivazione())) {
                $statoUtente = $utente->getStato();

                //"Sanity check", per evitare furbetti
                $attivazioneValida = ($statoUtente === Utente::STATO_RECUPERANDO && $password !== NULL ||
                    $statoUtente === Utente::STATO_INSERITO && $password === NULL);

                if ($attivazioneValida) {
                    $utente->setCodiceAttivazione("");
                    $utente->setStato(Utente::STATO_ATTIVATO);

                    //Se questo è vero, significa che lo stato dell'utente è "recuperando"
                    if ($statoUtente === Utente::STATO_RECUPERANDO) {
                        $password = password_hash($password, PASSWORD_DEFAULT);

                        $this->ciceroneDatabase->manipola("update Utente set codice_attivazione = ?, stato = ?, " .
                            "password = ? where id = ?", $utente->getCodiceAttivazione(), $utente->getStato(),
                            $password, $id);
                        $codiceStato = self::STATO_RECUPERO_RIUSCITO;

                    } else {
                        $this->ciceroneDatabase->manipola("update Utente set codice_attivazione = ?, stato = ? " .
                            "where id = ?", $utente->getCodiceAttivazione(), $utente->getStato(), $id);
                        $codiceStato = self::STATO_ATTIVAZIONE_RIUSCITA;
                    }
                }
            }
        }

        return $codiceStato;
    }


    /**
     * Metodo utile per la creazione di un codice d'attivazione (alla pari di una password)
     * @param int $lunghezza lunghezza del codice d'attivazione
     * @return string
     */
    private static function creaCodiceAttivazione(int $lunghezza) : string {
        $nChars = mb_strlen(self::SPAZIO_CODICE_ATTIVAZIONE, '8bit') - 1;
        $passwordCasuale = "";

        for ($i = 0; $i < $lunghezza; $i++) {
            $passwordCasuale .= self::SPAZIO_CODICE_ATTIVAZIONE[random_int(0, $nChars)];
        }
        return $passwordCasuale;
    }


    /*
     * I seguenti metodi sono utilizzati per mostrare e modificare profili e
     * anagrafiche
     */
    /**
     * Consente di caricare le informazioni anagrafiche di un particolare utente.
     * @param string $idUtente
     * @param Anagrafica $anagrafica
     * @return int lo stato dell'operazione, che può essere: STATO_ANAGRAFICA_TROVATA,
     * STATO_ANAGRAFICA_NON_TROVATA.
     */
    public function getAnagrafica(int $idUtente, ?Anagrafica& $anagrafica) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Anagrafica.id, id_cicerone, nome, cognome, data_nascita, " .
            "luogo_nascita, residenza, telefono, codice_fiscale from Utente, Anagrafica " .
            "where Utente.id = id_cicerone and Utente.id = ?", $idUtente);

        if (count($righe) === 1) {
            $anagrafica = Anagrafica::daArray($righe[0]);
            $codiceStato = self::STATO_ANAGRAFICA_TROVATA;
        } else {
            $codiceStato = self::STATO_ANAGRAFICA_NON_TROVATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la modifica del profilo di un utente.
     * @param string $idUtente id dell'utente da aggiornare
     * @param string $email email da inserire
     * @param string $descrizione descrizione da inserire
     * @param string $vecchiaPassword vecchia password utilizzata (per stabilire se ha
     * il permesso di sostituire i suoi dati)
     * @param string $nuovaPassword nuova password (eventualmente) da inserire
     * @param string $nuovaImmagine percorso all'eventuale nuova immagine di profilo caricata.
     * @return int lo stato dell'operazione, che può essere: STATO_VECCHIA_PASSWORD_NON_VALIDA,
     * STATO_VECCHIA_NUOVA_PASSWORD_UGUALI, STATO_PROFILO_MODIFICATO, STATO_UTENTE_NON_TROVATO.
     */
    public function modificaProfilo(int $id, string $email, string $descrizione, ?string $nuovaImmagine, ?string& $vecchiaImmagine, ?string $vecchiaPassword, ?string $nuovaPassword) : int {
        $this->ciceroneDatabase->apri();

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $righe = $this->ciceroneDatabase->query("select immagine from Utente where id = ?", $id);
        $nRighe = count($righe);

        if ($nRighe === 1) {
            //Passiamola, in caso dovesse servire
            $vecchiaImmagine = $righe[0]["immagine"];

            $passwordDaAggiornare = false;

            /*
             * Se è richiesta la modifica della password
             */
            if ($vecchiaPassword !== NULL) {
                $righe = $this->ciceroneDatabase->query("select password from Utente where id = ?",
                    $id);

                $immagazzinata = $righe[0]["password"];

                $verificaPassword = password_verify($vecchiaPassword, $immagazzinata);
                $vecchiaNuovaUguali = $vecchiaPassword === $nuovaPassword;

                if ($verificaPassword && !$vecchiaNuovaUguali) {
                    $passwordDaAggiornare = true;

                } else if (!$verificaPassword) {
                    $codiceStato = self::STATO_VECCHIA_PASSWORD_NON_VALIDA;

                } else if ($vecchiaNuovaUguali) {
                    $codiceStato = self::STATO_VECCHIA_NUOVA_PASSWORD_UGUALI;
                }
             }
             
             /*
              * Verifica che l'indirizzo email richiesto non sia stato già usato...
              */
             $righe = $this->ciceroneDatabase->query("select 1 from Utente where email = ? and id != ?",
                 $email, $id);
             $nRighe = count($righe);

             if ($nRighe > 0) {
                 $codiceStato = self::STATO_EMAIL_USATA;
             }

             /*
              * Se l'utente non ha richiesto la modifica della password
              * OPPURE
              * È stata richiesta e la vecchia password risulta valida.
              */
             if ($codiceStato === self::STATO_NO_SEGNALAZIONE) {
                $campi = array("email = ?", "descrizione = ?");
                $valori = array($email, $descrizione);

                if ($nuovaImmagine !== NULL) {
                    $campi[]= "immagine = ?";
                    $valori[]= $nuovaImmagine;
                }

                if ($passwordDaAggiornare) {
                    $campi[]= "password = ?";
                    $valori[]= password_hash($nuovaPassword, PASSWORD_DEFAULT);
                }

                $valori[]= $id;

                $query = sprintf("update Utente set %s where id = ?", implode(", ", $campi));
                call_user_func_array(array($this->ciceroneDatabase, "manipola"),
                    array_merge(array($query), $valori));

                $codiceStato = self::STATO_PROFILO_MODIFICATO;
            }
        } else {
            $codiceStato = self::STATO_UTENTE_NON_TROVATO;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
    
    
    /**
     * Esegue il caso d'uso inerente la modifica dell'anagrafica associata ad un Cicerone.
     * @param string $idUtente id dell'utente a cui bisogna aggiornare
     * l'anagrafica
     * @param string $residenza l'eventuale nuova residenza
     * @param string $telefono l'eventuale nuovo numero di telefono
     * @return int lo stato dell'operazione, che può essere: STATO_ANAGRAFICA_MODIFICATA,
     * STATO_ID_UTENTE_NO_ANAGRAFICA.
     */
    public function modificaAnagrafica(int $idUtente, string $residenza, string $telefono) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Anagrafica where " .
            "id_cicerone = ?", $idUtente);

        if (count($righe) === 1) {
            $this->ciceroneDatabase->manipola("update Anagrafica set residenza=?, telefono=? " .
                "where id_cicerone = ?", $residenza, $telefono, $idUtente);
            $codiceStato = self::STATO_ANAGRAFICA_MODIFICATA;

        } else {
            $codiceStato = self::STATO_ID_UTENTE_NO_ANAGRAFICA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
}

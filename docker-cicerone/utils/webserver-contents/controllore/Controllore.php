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


require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/Modello.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/Spedizioniere.php";
require_once "Sessione.php";


use modello\Modello;
use modello\entità\Utente;
use utils\Spedizioniere;


/**
 * Rappresenta un controllore di questo sistema.
 * <p>
 * Ogni classe che rappresenta un controllore specifico deve essere figlia
 * di questa classe qui.<br>
 * I controllori sono responsabili dell'interazione tra vista e modello.<br>
 * Essi hanno i seguenti obiettivi (comuni):</p>
 * <ul>
 * <li>Mappare gli elementi visibili "interattivi" (pulsanti e link)
 * della vista con le operazioni da eseguire (i metodi del modello).</li>
 * <li>Ottenere i codici di stato dal modello, in base alle operazioni eseguite.</li>
 * <li>Tramutare i codici di stato in messaggi significativi e fornirli alla vista
 * affinchè li mostri all'utente.</li>
 * <li>Passare eventuali dati ottenuti dal modello, alla vista, affinchè li mostri.</li> 
 * <li>Gestire la sessione dell'utente.</li>
 * <li>Spedire email, quando richiesto.</li>
 * <li>Fare upload di file di immagini, quando richiesto.</li>
 * </ul>
 */
abstract class Controllore implements LettoreSessione {
    /**
     * Questo stato è utilizzato ogniqualvolta non si vuole segnalare qualcosa.
     * È da notare che a seguito di questo stato, è possibile che l'operazione
     * sia andata a buon fine, però può anche essere l'opposto.
     */
    public const STATO_NO_SEGNALAZIONE = 0;


    /**
     * Questo stato è utilizzato ogniqualvolta si vuole segnalare mediante
     * messaggio che l'operazione è andata a buon fine.
     */
    public const STATO_OPERAZIONE_RIUSCITA = 1;


    /**
     * Questo stato è utilizzato ogniqualvolta si vuole segnalare mediante
     * messaggio che l'operazione è fallita.
     */
    public const STATO_OPERAZIONE_FALLITA = 2;


    /**
     * Messaggio d'errore standard
     */
    protected const MESSAGGIO_RICHIESTA_NON_VALIDA = "Richiesta non valida";


    /**
     * Un'istanza di un modello (o NULL se non è fornita).
     * @var Modello
     */
    protected $modello;


    /**
     * Un'istanza di Sessione
     * @var Sessione
     */
    protected $sessione;


    /*
     * Di seguito si presentano alcuni "campi" che possono
     * tornare utili a Controllori più specifici.
     * È da notare che in questo sistema, è compito delle viste
     * mappare eventuali elementi dei form ai campi che un certo
     * Controllore si aspetta di gestire.
     * È un modo di disaccoppiare come effettivamente si presenta
     * il sistema (le viste), da ciò che effettivamente si necessita
     * per eseguire operazioni (controllori che contattano il modello).
     */
    /**
     * In generale, indica un campo contentente un id.
     * Può essere usato per qualunque cosa.
     */
    public const CAMPO_ID = "id";


    /**
     * In generale, indica un campo contentente una descrizione.
     * Può essere usato per qualunque cosa.
     */
    public const CAMPO_DESCRIZIONE = "descrizione";


    /**
     * Indica il campo (input type=file) che conterrà le informazioni
     * sull'immagine caricata
     */
    public const CAMPO_IMMAGINE_UPLOAD = "immagine-upload";
    
    
    /**
     * Indica il campo (input type=hidden) che conterrà un valore
     * che stabilirà, una volta valutato server-side, se si intende
     * ripristinare l'immagine di default
     */
    public const CAMPO_RIPRISTINA_IMMAGINE = "ripristino-immagine";


    /*
     * I seguenti campi servono a stabilire i limiti per l'upload
     * dei file di immagine.
     */
    /**
     * Dimensione massima consentita per un'immagine in byte.
     * Eventuali controllori sono invitati a ridefinire questo attributo
     * se necessario.
     */
    public const MAX_DIMENSIONE_IMMAGINE = 150;


    /**
     * Larghezza massima consentita per un'immagine, in pixel.
     * Eventuali controllori sono invitati a ridefinire questo attributo
     * se necessario.
     */
    public const MAX_WIDTH_IMMAGINE = 15;
    
    
    /**
     * Altezza massima consentita per un'immagine, in pixel.
     * Eventuali controllori sono invitati a ridefinire questo attributo
     * se necessario.
     */
    public const MAX_HEIGHT_IMMAGINE = 15;


    /**
     * Costruisce un nuovo controllore fornendo una certa istanza di Modello
     * (si sfrutta il polimorfismo per garantire ulteriori funzionalità).
     * 
     * <p>Se il modello fornito è NULL, semplicemente, avremo un Controllore
     * senza Modello, cosa possibile sebbene limitativa.<br>
     * Un controllore siffatto può solo i dati di sessione.</p>
     * @param ?Modello $modello 
     */
    public function __construct(?Modello $modello) {
        $this->modello = $modello;
        $this->sessione = new Sessione();
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati di un utente.
     * È presente qui perchè è un'operazione molto comune tra tutti
     * i controllori.
     * @param int $id L'id dell'utente da reperire
     * @param Utente $utente un'istanza che conterrà alla fine dell'operazione,
     * i dati dell'utente cercato, se esiste, altrimenti NULL.
     */
    public function richiediUtente(int $id, ?Utente& $utente) : void {
        $this->modello->getUtente($id, $utente);
    }


    /**
     * Effettua l'aggiornamento dei dati di sessione, verificando
     * l'esistenza dell'utente corrente nel database e aggiornando alcuni
     * valori che sono suscettibili a cambiamenti.
     * Il metodo è stato creato per garantire che in caso di approvazione
     * di una richiesta di disiscrizione/aggiornamento, si subiscano subito
     * gli effetti delle stesse.
     */
    public function aggiornaSessione() : void {
        if ($this->modello !== NULL && $this->isUtenteConnesso()) {
            $utente = NULL;
            $this->richiediUtente($this->getIDUtente(), $utente);
            
            if ($utente !== NULL) {
                $utenteInDisiscrizione = $this->modello->isUtenteInDisiscrizione(
                    $utente->getID());
                $utenteInAggiornamento = $this->modello->isUtenteInAggiornamento(
                    $utente->getID());
                
                $this->sessione->setTipoUtente($utente->getTipo());
                $this->sessione->setUtenteInAggiornamento($utenteInAggiornamento);
                $this->sessione->setUtenteInDisiscrizione($utenteInDisiscrizione);
            } else {
                /*
                 * Pare che l'utente non esista più, è possibile che
                 * la disiscrizione abbia avuto luogo.
                 * Cancella sessione ed effettua una copia del
                 * messaggio, per garantire la visualizzazione
                 * nella schermata d'arrivo.
                 */
                $this->sessione->clear();
                $this->sessione->addMessaggio("Pare che l'utente non esista più, è possibile " .
                    "che abbia avuto luogo la disiscrizione");
                $this->copiaMessaggisticaPerSchermata();
            }
        }
    }


    /*
     * ***************************************************
     * **************************************************
     * Metodi a supporto della validazione dei dati passati
     * dalle viste
     * **************************************************
     * **************************************************
     */
    /**
     * Metodo d'utilità per effettuare la validazione dei parametri passati.
     * <p>In caso di errori, verranno impostati sia lo stato che il messaggio
     * dell'interazione.<br>
     * Ognuno dei figli di Controllore definirà una propria funzione di validazione.</p>
     * @param array la lista dei parametri
     * @param tipo il tipo di validazione da effettuare
     * @return bool restituisce true se i parametri passati sono validi, false in caso contrario
     */
    protected abstract function validaParametri(array& $params, string $tipo) : bool;


    /**
     * Questo metodo verifica se il parametro array passato ha le chiavi specificate
     * come valori mediante argomenti variadici ($args).
     * In caso di errori, verranno impostati sia lo stato che il messaggio dell'interazione
     * come richiesta non valida.<br>
     * Può essere utilizzato per assicurarsi che ci siano determinati campi quando
     * si ottengono richieste GET o POST.
     * @param array $params i parametri da verificare
     * @param string[] ...$args le chiavi da controllare
     * @return bool true se l'array risulta avere tutte le chiavi passate, false al contrario
     */
    protected function isImpostato(array $params, ...$args) : bool {
        $valido = true;
        $nArgs = count($args);

        for ($i = 0; $valido && $i < $nArgs; $i++) {
            $chiave = $args[$i];
            $valido = isset($params[$chiave]);
        }

        if (!$valido) {
            $this->sessione->setMessaggio(self::MESSAGGIO_RICHIESTA_NON_VALIDA);
        }

        return $valido;
    }


    /**
     * Effettua la validazione di una data.
     * Tenuto qui perchè torna utile in diversi Controllori.
     * @param string $data
     * @return bool true, se la data risulta valida, false al contrario
     */
    protected function validaCampoData(string $data) : bool {
        $valido = preg_match(Modello::REGEX_DATA, $data);

        if ($valido) {
            $data = explode("-", $data);

            $valido = checkdate($data[1], $data[2], $data[0]);

            if (!$valido) {
                $this->sessione->addMessaggio("La data non è valida");
            }
        } else {
            $this->sessione->addMessaggio("La data è malformata");
        }

        return $valido;
    }


    /**
     * Effettua la validazione di un semplice campo la cui richiesta è
     * quella di avere un valore maggiore o pari ad un tot di caratteri.
     * Tenuto qui perchè torna utile in diversi Controllori.
     * @param string $valore
     * @param string $nomeCampo
     * @param lunghezza il numero di caratteri che il valore deve contenere
     * @return bool true, se risulta valido, false al contrario
     */
    protected function validaCampoSemplice(string $valore, string $nomeCampo, int $lunghezza) : bool {
        $lunghezzaStringa = strlen($valore);

        if ($lunghezzaStringa < $lunghezza) {
            $this->sessione->addMessaggio("Il campo \"" . $nomeCampo . "\" deve avere " . $lunghezza . " o più caratteri");
            $valido = false;

        } else if ($lunghezzaStringa > Modello::MAX_LUNGHEZZA_CAMPO_TESTUALE) {
            $this->sessione->addMessaggio("Il campo \"" . $nomeCampo . "\" non può avere più di " . Modello::MAX_LUNGHEZZA_CAMPO_TESTUALE . " caratteri");
            $valido = false;

        } else {
            $valido = true;
        }

        return $valido;
    }


    /**
     * Valida i parametri di upload di un'immagine.
     * Tenuto qui perchè torna utile in diversi Controllori.
     * @param array $params i parametri della richiesta (saranno modificati se necessario)
     * @param string $percorsoImmagini il percorso assoluto alla cartella che deve contenere l'immagine.
     * @return bool true, se il file supera il test di validità, false al contrario
     */
    protected function validaImmagineUpload(array& $params, string $percorsoImmagini) : bool {
        $definito = isset($_FILES[static::CAMPO_IMMAGINE_UPLOAD]);
        
        if ($definito) {
            /*
             * È presente il file richiesto, processiamolo
             */
            $file = $_FILES[static::CAMPO_IMMAGINE_UPLOAD];
            $errorType = $file["error"];

            if ($errorType === UPLOAD_ERR_OK) {
                /*
                 * C'è stato un upload effettivo, processiamo
                 */
                $valido = $this->processaFileCaricato($params, $file, $percorsoImmagini);

            } else if ($errorType === UPLOAD_ERR_NO_FILE) {
                /*
                 * Nessun file caricato, imposta i parametri in modo
                 * da ignorare questa circostanza.
                 */
                $params[static::CAMPO_IMMAGINE_UPLOAD] = NULL;

                if (isset($params[static::CAMPO_RIPRISTINA_IMMAGINE])) {
                    $params[static::CAMPO_RIPRISTINA_IMMAGINE] =
                    ($params[static::CAMPO_RIPRISTINA_IMMAGINE] === "true");
                } else {
                    $params[static::CAMPO_RIPRISTINA_IMMAGINE] = false;
                }
                $valido = true;

            } else {
                $this->sessione->addMessaggio(
                    sprintf("Errore durante l'upload del file, provare di nuovo"));
                $valido = false;
            }
        } else {
            /*
             * Nessun file richiesto è stato trovato, imposta i parametri in modo
             * da ignorare questa circostanza.
             */
            $params[static::CAMPO_IMMAGINE_UPLOAD] = NULL;
            
            if (isset($params[static::CAMPO_RIPRISTINA_IMMAGINE])) {
                $params[static::CAMPO_RIPRISTINA_IMMAGINE] =
                ($params[static::CAMPO_RIPRISTINA_IMMAGINE] === "true");
            }
            
            $valido = true;
        }
        
        return $valido;
    }


    /**
     * Processa il file caricato, eventualmente modifica i parametri se necessario.
     * @param array $params i parametri della richiesta
     * @param array $file i dati del file caricato
     * @param string $percorsoImmagini il percorso assoluto alla cartella che deve contenere l'immagine.
     * @return bool true, se il file supera il test di validità, false al contrario
     */
    protected function processaFileCaricato(array& $params, array $file, string $percorsoImmagini) : bool {
        $valido = true;

        $percorsoFile = $file["tmp_name"];
        $imageType = exif_imagetype($percorsoFile);

        if ($imageType === false) {
            /*
             * Non è un'immagine, segnalalo
             */
            $this->sessione->addMessaggio("Il file caricato non è un'immagine");
            $valido = false;

        } else {
            /*
             * Ottieni le informazioni di base
             */
            $size = filesize($percorsoFile);
            $dimensions = getimagesize($percorsoFile);


            if ($size > static::MAX_DIMENSIONE_IMMAGINE) {
                $this->sessione->addMessaggio(
                    sprintf("L'immagine non può superare i %d byte di dimensione",
                        static::MAX_DIMENSIONE_IMMAGINE));
                $valido = false;

            } else if ($dimensions[0] > static::MAX_WIDTH_IMMAGINE ||
                $dimensions[1] > static::MAX_HEIGHT_IMMAGINE) {

                $this->sessione->addMessaggio(
                    sprintf("L'immagine non può superare la dimensione di " .
                        " %dx%d", static::MAX_WIDTH_IMMAGINE, static::MAX_HEIGHT_IMMAGINE));
                $valido = false;

            } else if (is_uploaded_file($percorsoFile)) {
                $extension = image_type_to_extension($imageType, false);

                /*
                 * Crea file temporaneo per garantire un nome casuale
                 */
                $tmpFileName = tempnam($percorsoImmagini, "img");
                $percorsoAssolutoImmagini = sprintf("%s/%s", $_SERVER["DOCUMENT_ROOT"],
                    $percorsoImmagini);

                $valido = ($tmpFileName !== false &&
                    strpos($percorsoAssolutoImmagini, $tmpFileName) == 0);

                if ($valido) {
                    if ($valido = move_uploaded_file($percorsoFile, $tmpFileName)) {
                        /*
                         * Lo spostamento del file è andato bene,
                         * effettua il rinomino e il cambiamento di permessi.
                         */
                        $dirName = dirname($tmpFileName);
                        $baseName = basename($tmpFileName);

                        $params[static::CAMPO_IMMAGINE_UPLOAD] = sprintf("%s.%s", $baseName, $extension);
                        $fullPathNewName = sprintf("%s/%s", $dirName, $params[static::CAMPO_IMMAGINE_UPLOAD]);
                        rename($tmpFileName, $fullPathNewName);
                        chmod($fullPathNewName, 0644);
                    } else {
                        /*
                         * È andata male... cancella file temporaneo.
                         */
                        unlink($tmpFileName);
                        
                        $this->sessione->addMessaggio("Errore nello spostamento del " .
                            "file, riportare immediatamente all'amministratore!");
                    }
                } else {
                    $this->sessione->addMessaggio(
                        sprintf("Errore d'accesso alla cartella di upload, " .
                            "riportare immediatamente all'amministratore!"));
                }
            } else {
                $this->sessione->addMessaggio("Errore durante l'upload del file, " .
                    "provare di nuovo");
                $valido = false;
            }
        }
        
        return $valido;
    }


    /*
     * ***************************************************
     * **************************************************
     * Altri metodi d'utilità
     * **************************************************
     * **************************************************
     */
    /**
     * Metodo d'utilità per inviare e-mail
     * @param string $mittente
     * @param string $destinatario
     * @param string $oggetto
     * @param string $corpo
     */
    protected static function inviaMail(string $destinatario, string $nomeDestinatario,
        string $oggetto, string $corpoHTML, string $corpoTesto) : void {
            Spedizioniere::invia($destinatario, $nomeDestinatario, $oggetto, $corpoHTML,
                $corpoTesto);
    }


    /**
     * Metodo utilizzato per cancellare un'immagine da un certo percorso.
     * @param string $percorsoImmagini
     * @param string $nomeFile
     */
    protected function cancellaImmagine(string $percorsoImmagini, string $nomeFile) : void {
        unlink(sprintf("%s/%s", $percorsoImmagini, $nomeFile));
    }


    /**
     * Metodo d'utilità per impostare al volo sia il messaggio che il codice di stato.
     * @param int $codiceStato
     * @param string $messaggio
     */
    protected function setInfoOperazione(int $codiceStato, ?string $messaggio) : void {
        $this->sessione->setStatoOperazione($codiceStato);
        if ($messaggio !== NULL) { //Consenti l'aggiunta di ulteriori messaggi
            $this->sessione->addMessaggio($messaggio);
        } else {
            $this->sessione->setMessaggio($messaggio);
        }
    }
    
    
    /*
     * I seguenti metodi consentono di ottenere da subito
     * il risultato di alcuni controlli.
     * Personalmente non reputo opportuno commentarli,
     * sono abbastanza auto-esplicativi
     */
    public function isUtenteGlobetrotter() : bool {
        return $this->isUtenteConnesso() &&
            $this->getTipoUtente() === Utente::TIPO_GLOBETROTTER;
    }
    
    public function isUtenteQuasiCicerone() : bool {
        return $this->isUtenteConnesso() &&
        $this->getTipoUtente() === Utente::TIPO_QUASICICERONE;
    }
    
    public function isUtenteCicerone() : bool {
        return $this->isUtenteConnesso() &&
            $this->getTipoUtente() === Utente::TIPO_CICERONE;
    }

    public function isUtenteAmministratore() : bool {
        return $this->isUtenteConnesso() &&
            $this->getTipoUtente() === Utente::TIPO_AMMINISTRATORE;
    }

    public function isUtenteFruitore() : bool {
        return $this->isUtenteConnesso() &&
            $this->getTipoUtente() !== Utente::TIPO_AMMINISTRATORE;
    }


    /*
     * I metodi seguenti consentono di gestire in maniera semplificata
     * eventuali errori o messaggi da mostrare nelle schermate.
     */
    /**
     * Distrugge tutti i dati inerenti i messaggi.
     */
    public function distruggiMessaggistica() : void {
        $this->sessione->distruggiMessaggistica();
    }
    
    
    /**
     * Effettua eventuali pulizie di messaggi (se necessario) ed imposta
     * i valori necessari affinchè i messaggi vengano mostrati nella
     * schermata passata come parametro.
     * @param string $schermata la schermata
     */
    public function associaSchermataPerMessaggistica(string $schermata) : void {
        $this->sessione->associaSchermataPerMessaggistica($schermata);
    }
    
    
    /**
     * Esegue una copia del messaggio ottenuto per consentire
     * la sua visualizzazione in un'altra schermata.
     * Questo evita la cancellazione del messaggio in caso dovesse
     * essere impostato nella schermata d'arrivo.
     */
    public function copiaMessaggisticaPerSchermata() : void {
        $this->sessione->copiaMessaggisticaPerSchermata();
    }


    /**
     * Restituisce i messaggi sotto forma di array
     * @return string
     */
    public function getMessaggi() : array {
        return $this->sessione->getMessaggi();
    }


    /**
     * Effettua il reset dei messaggi.
     * NOTA: Non elimina messaggi di backup.
     */
    public function resetMessaggi() : void {
        $this->sessione->setMessaggio(NULL);
    }


    /*
     * ***************************************************
     * **************************************************
     * Metodi ereditati da LettoreSessione
     * **************************************************
     * **************************************************
     */
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getIDUtente()
     */
    public function getIDUtente() : ?int {
        return $this->sessione->getIDUtente();
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getNomeUtente()
     */
    public function getNomeUtente() : ?string {
        return $this->sessione->getNomeUtente();
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getTipoUtente()
     */
    public function getTipoUtente() : ?string {
        return $this->sessione->getTipoUtente();
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getStatoUtente()
     */
    public function getStatoUtente() : ?string {
        return $this->sessione->getStatoUtente();
    }
    

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getImmagineUtente()
     */
    public function getImmagineUtente() : ?string {
        return $this->sessione->getImmagineUtente();
    }

    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::getStatoOperazione()
     */
    public function getStatoOperazione() : ?int {
        return $this->sessione->getStatoOperazione();
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteConnesso()
     */
    public function isUtenteConnesso(): bool {
        return $this->sessione->isUtenteConnesso();
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteInAggiornamento()
     */
    public function isUtenteInAggiornamento() : bool {
        return $this->sessione->isUtenteInAggiornamento();
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::isUtenteInDisiscrizione()
     */
    public function isUtenteInDisiscrizione() : bool {
        return $this->sessione->isUtenteInDisiscrizione();
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\LettoreSessione::ciSonoMessaggi()
     */
    public function ciSonoMessaggi() : bool {
        return $this->sessione->ciSonoMessaggi();
    }
}
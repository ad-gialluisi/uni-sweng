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


namespace vista;

require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreProfilo.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once "TriplettaSemplice.php";
require_once "VistaAmministrazione.php";
require_once "Vista.php";
require_once "VistaItinerario.php";


use controllore\ControlloreProfilo;
use modello\entità\Utente;


/**
 * Rappresenta la vista associata al ControlloreProfilo e al ModelloProfilo.
 * 
 * <p>Ha tre schermate, la prima per la visualizzazione di un profilo arbitrario,
 * la seconda per la modifica del profilo di un utente e la terza per la modifica
 * di alcune voci dell'anagrafica di un Cicerone.</p>
 * 
 * @see \controllore\ControlloreProfilo
 * @see \modello\ModelloUtente
 */
class VistaProfilo extends Vista {
    protected const PAGINA_VISTA = "profilo.php";

    /*
     * Richieste che questa vista prende in considerazione
     */
    /**
     * Richiesta fatta quando nella schermata SCHERMATA_MODIFICA_PROFILO
     * si chiede l'invio dei cambiamenti.
     */
    private const RICHIESTA_MODIFICA_PROFILO = "modProfilo";
    
    /**
     * Richiesta fatta quando nella schermata SCHERMATA_MODIFICA_ANAGRAFICA
     * si chiede l'invio dei cambiamenti.
     */
    private const RICHIESTA_MODIFICA_ANAGRAFICA = "modAnagrafica";


    /*
     * Le diverse schermate che la vista mostra.
     */
    /**
     * Schermata visualizzazione del profilo.
     */
    private const SCHERMATA_PROFILO = "profilo";


    /**
     * Schermata di modifica del profilo
     */
    private const SCHERMATA_MODIFICA_PROFILO = "modProfilo";


    /**
     * Schermata di modifica dell'anagrafica
     */
    private const SCHERMATA_MODIFICA_ANAGRAFICA = "modAnagrafica";

    /**
     * È il nome del file immagine di default associabile al profilo di un utente
     */
    public const FILE_IMMAGINE_DEFAULT = "default.png";
    


    /**
     * Crea una VistaProfilo con un ControlloreProfilo sottostante
     */
    public function __construct() {
        parent::__construct(new ControlloreProfilo());
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::isRichiesta()
     */
    public function isRichiesta() : bool {
        return isset($this->getParams[self::GET_RICHIESTA]) &&
            !isset($this->getParams[self::GET_SCHERMATA]);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::elabora()
     */
    public function elabora() : void {
        $richiesta = $this->getParams[self::GET_RICHIESTA];

        /*
         * Distruggi tutta la messaggistica, dato che
         * stiamo facendo una richiesta nuova.
         */
        $this->controllore->distruggiMessaggistica();


        $isUtenteConnesso = $this->controllore->isUtenteConnesso();
        $isUtenteCicerone = $this->controllore->isUtenteCicerone();
        $isModificaProfilo = false;
        $isModificaAnagrafica = false;
        $richiestaValida = true;

        switch ($richiesta) {
            case self::RICHIESTA_MODIFICA_PROFILO:
                $this->controllore->richiediModificaProfilo($this->postParams,
                    self::getPercorsoImmaginiUtenti(), self::FILE_IMMAGINE_DEFAULT);
                $isModificaProfilo = true;
            break;
            case self::RICHIESTA_MODIFICA_ANAGRAFICA:
                $this->controllore->richiediModificaAnagrafica($this->postParams);
                $isModificaAnagrafica = true;
            break;
            default:
                $richiestaValida = false;
            break;
        }


        $paginaRedirect = self::getURLHOME();


        if ($richiestaValida) {
            $statoOperazione = $this->controllore->getStatoOperazione();

            /*
             * Ora copia la messaggistica ottenuta, così, nell'eventuale schermata
             * di destinazione, è possibile aggiungere ulteriori messaggi.
             */
            $this->controllore->copiaMessaggisticaPerSchermata();

            switch ($statoOperazione) {
                case ControlloreProfilo::STATO_OPERAZIONE_FALLITA:
                    if ($isModificaProfilo && $isUtenteConnesso) {
                        $paginaRedirect = self::getSchermata(self::SCHERMATA_MODIFICA_PROFILO);

                    } else if ($isModificaAnagrafica && $isUtenteCicerone) {
                        $paginaRedirect = self::getSchermata(self::SCHERMATA_MODIFICA_ANAGRAFICA);
                    }
                break;
                case ControlloreProfilo::STATO_OPERAZIONE_RIUSCITA:
                    $paginaRedirect = self::getURLProfilo($this->controllore->getIDUtente());
                break;
                default:
                    //Mai eseguito
                break;
            }
        }


        $this->mandaA($paginaRedirect);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        $schermata = isset($this->getParams[self::GET_SCHERMATA]) ?
            $this->getParams[self::GET_SCHERMATA] : self::SCHERMATA_PROFILO;


        $isSchermataValida = true;

        switch ($schermata) {
            case self::SCHERMATA_PROFILO:
                $isSchermataValida = isset($this->getParams[ControlloreProfilo::CAMPO_ID]);
                $metodoSchermata = "schermataProfilo";
            break;
            case self::SCHERMATA_MODIFICA_PROFILO:
                $isSchermataValida = $this->controllore->isUtenteConnesso();
                $metodoSchermata = "schermataModificaProfilo";
            break;
            case self::SCHERMATA_MODIFICA_ANAGRAFICA:
                $isSchermataValida = $this->controllore->isUtenteCicerone();
                $metodoSchermata = "schermataModificaAnagrafica";
            break;
            default:
                $isSchermataValida = false;
            break;
        }


        if ($isSchermataValida) {
            $this->tripletta = new TriplettaSemplice("profilo", "form/utente");
            
            /*
             * Associa la schermata ottenuta alla messaggistica, così da permettere
             * di mantenere i messaggi finchè non si lascia la pagina.
             */
            $this->controllore->associaSchermataPerMessaggistica($schermata);
            $this->$metodoSchermata();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Crea schermata che rappresenta il profilo.
     */
    private function schermataProfilo() : void {
        $id = $this->getParams[ControlloreProfilo::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $utente = NULL;
        $this->controllore->richiediUtente($id, $utente);

        if ($utente !== NULL) {
            $isUtenteSeStesso = $this->controllore->isUtenteConnesso() &&
                $id == $this->controllore->getIDUtente();
            $isAmministratore = $utente->getTipo() === Utente::TIPO_AMMINISTRATORE;

            if ($isUtenteSeStesso || !$isAmministratore) {
                /*
                 * Se è se stesso oppure non è amministratore, il profilo è disponibile!
                 * Ricordiamo che i fruitori non possono vedere il profilo di amministratori!
                 */
                if ($isUtenteSeStesso) {
                    $isUtenteInDisiscrizione = $this->controllore->isUtenteInDisiscrizione();
                    $isUtenteInAggiornamento = $this->controllore->isUtenteInAggiornamento();
                } else {
                    $isUtenteInDisiscrizione = false;
                    $isUtenteInAggiornamento = false;
                }

                $tipo = $utente->getTipo();
                $isGlobetrotterQuasiCicerone = ($tipo === Utente::TIPO_GLOBETROTTER ||
                    $tipo === Utente::TIPO_QUASICICERONE);
                
                $this->tripletta->applica(
                    array(
                        "schermata-profilo" => true,
                        "utente-trovato" => true,
                        "nomeutente" => $utente->getNomeUtente(),
                        "percorso-immagine" => self::calcolaPercorsoImmagineUtente($utente->getImmagine()),
                        "email" => $utente->getEmail(),
                        "descrizione" => self::newlineToBrTag($utente->getDescrizione()),
                        "tipo" => ($tipo === Utente::TIPO_QUASICICERONE ? "QuasiCicerone" : ucfirst($tipo)),
                        "me-stesso" => $isUtenteSeStesso,
                        "pulsante-richiesta-disiscrizione" => $isUtenteSeStesso &&
                            !$isUtenteInDisiscrizione && !$isUtenteInAggiornamento,
                        "pulsante-richiesta-aggiornamento" => $isUtenteSeStesso &&
                            !$isUtenteInDisiscrizione && !$isUtenteInAggiornamento,
                        "utente-in-disiscrizione" => $isUtenteSeStesso &&
                            $isUtenteInDisiscrizione,
                        "utente-in-aggiornamento" => $isUtenteSeStesso &&
                            $isUtenteInAggiornamento,
                        "form-scr-itinerari-organizzatore" => VistaItinerario::getURLItinerariOrganizzatoreFruitore($id),
                        "form-scr-itinerari-partecipante" => VistaItinerario::getURLItinerariPartecipanteFruitore($id),
                        "form-scr-feedback" => VistaFeedback::getURLFeedbacksFruitore($id),
                        "is-globetrotter-quasicicerone" => $isGlobetrotterQuasiCicerone,
                        "is-" . $tipo => true,
                        "form-scr-modprofilo" => self::getSchermata(self::SCHERMATA_MODIFICA_PROFILO),
                        "form-scr-modanagrafica" => self::getSchermata(self::SCHERMATA_MODIFICA_ANAGRAFICA),
                        "form-req-disiscrizione" => VistaAmministrazione::getURLCreazioneRichiestaDisiscrizione(),
                        "form-scr-update" => VistaAmministrazione::getURLCreazioneRichiestaAggiornamento(),
                        "form-req-transcicerone" => VistaAmministrazione::getURLTransizioneACicerone(),
                        
                    ), TriplettaSemplice::HTML
                );

                /*
                 * Bisogna mostrare l'anagrafica solo se l'utente che mostriamo è un Cicerone
                 * ($tipo === Utente::TIPO_CICERONE) E:
                 *
                 * - Siamo noi stessi quel Cicerone ($isUtenteSeStesso)
                 * - Siamo un utente amministratore ($this->controllore->isUtenteAmministratore())
                 */
                $visualizzaAnagrafica = ($tipo === Utente::TIPO_CICERONE &&
                    ($isUtenteSeStesso || $this->controllore->isUtenteAmministratore()));

                if ($visualizzaAnagrafica) {
                    $anagrafica = NULL;
                    $this->controllore->richiediAnagrafica($id, $anagrafica);
                    
                    if ($anagrafica === NULL) {
                        //Situazione anomala, rimanda ad home
                        $this->mandaA(self::getURLHOME());
                    }

                    $this->tripletta->applica(array(
                        "is-cicerone-amministratore" => true,
                        "nome" => $anagrafica->getNome(),
                        "cognome" => $anagrafica->getCognome(),
                        "data-nascita" => $anagrafica->getDataNascita(),
                        "luogo-nascita" => $anagrafica->getLuogoNascita(),
                        "residenza" => $anagrafica->getResidenza(),
                        "telefono" => $anagrafica->getTelefono(),
                        "codice-fiscale" => $anagrafica->getCodiceFiscale(),
                    ), TriplettaSemplice::HTML);
                }
                $titolo = "Profilo di \"" . $utente->getNomeUtente() . "\"";
                    
            } else {
                //NON MOSTRARE, profilo off limits
                $this->tripletta->applica(array("schermata-profilo" => true,
                    "utente-non-trovato" => true), TriplettaSemplice::HTML);
                $titolo = "Utente non trovato!";
            }
        } else {
            $this->tripletta->applica(array("schermata-profilo" => true,
                "utente-non-trovato" => true), TriplettaSemplice::HTML);
            $titolo = "Utente non trovato!";
        }
        

        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la modifica del profilo.
     */
    private function schermataModificaProfilo() : void {
        $utente = NULL;
        $idUtente = $this->controllore->getIDUtente();
        $this->controllore->richiediUtente($idUtente, $utente);


        $this->tripletta->applica(array(
            "schermata-modprofilo" => true,
            "percorso-immagine" => self::calcolaPercorsoImmagineUtente($utente->getImmagine()),
            "descrizione-contenuto" => $utente->getDescrizione(),
            "form-req-modprofilo" => self::getRichiesta(self::RICHIESTA_MODIFICA_PROFILO),
            "email-contenuto" => $utente->getEmail(),
            "campo-vecchia-password" => ControlloreProfilo::CAMPO_VECCHIA_PASSWORD,
            "campo-nuova-password" => ControlloreProfilo::CAMPO_NUOVA_PASSWORD,
            "campo-conferma-nuova-password" => ControlloreProfilo::CAMPO_CONFERMA_NUOVA_PASSWORD,
            "campo-email" => ControlloreProfilo::CAMPO_EMAIL,
            "campo-descrizione" => ControlloreProfilo::CAMPO_DESCRIZIONE,
            "campo-immagine-upload" => ControlloreProfilo::CAMPO_IMMAGINE_UPLOAD,
            "campo-ripristina-immagine" => ControlloreProfilo::CAMPO_RIPRISTINA_IMMAGINE,
        ), TriplettaSemplice::HTML);

        //Crea popup per chiedere conferma se effettuare il reset dell'immagine
        $percorsoImmagineDefault = self::calcolaPercorsoImmagineUtente(self::FILE_IMMAGINE_DEFAULT);
        $this->aggiungiGestoreAnteprimaImmagine($percorsoImmagineDefault);
        
        $this->setTitolo("Modifica del profilo di \"" . $utente->getNomeUtente() . "\"");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la modifica dell'anagrafica.
     */
    private function schermataModificaAnagrafica() : void {
        $idUtente = $this->controllore->getIDUtente();

        $anagrafica = NULL;
        $this->controllore->richiediAnagrafica($idUtente, $anagrafica);

        /*
         * Se non è stata trovata l'anagrafica, rimanda ad HOME, dato
         * che è una situazione non valida.
         */
        if ($anagrafica !== NULL) {
            $this->tripletta->applica(array(
                "schermata-modanagrafica" => true,
                "residenza-contenuto" => $anagrafica->getResidenza(),
                "telefono-contenuto" => $anagrafica->getTelefono(),
                "form-req-modanagrafica" => self::getRichiesta(self::RICHIESTA_MODIFICA_ANAGRAFICA),
                "campo-residenza" => ControlloreProfilo::CAMPO_RESIDENZA,
                "campo-telefono" => ControlloreProfilo::CAMPO_TELEFONO,
            ), TriplettaSemplice::HTML);
            
            $this->setTitolo("Modifica dell'anagrafica di \"" . $this->controllore->getNomeUtente() . "\"");
            $this->mostraErrori();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Metodo di servizio per aggiungere i codici necessari affinchè si possa procedere a
     * cambiare l'immagine dei profili.
     * @param string $percorsoImmagineDefault percorso all'imagine di default
     */
    private function aggiungiGestoreAnteprimaImmagine(string $percorsoImmagineDefault) : void {
        /*
         * Aggiungi codice di supporto per l'upload di immagini
         */
        $this->tripletta->add("id-input-file", "image-load", TriplettaSemplice::HTML);
        $this->tripletta->add("id-input-restore", "ripristino", TriplettaSemplice::HTML);
        
        //Aggiungi codice Javascript per consentire l'anteprima.
        $this->addJSCodice(
            sprintf("var anteprimaImmagine = new GestoreAnteprimaImmagine(%d, %d, %d);",
                ControlloreProfilo::MAX_DIMENSIONE_IMMAGINE, ControlloreProfilo::MAX_WIDTH_IMMAGINE,
                ControlloreProfilo::MAX_HEIGHT_IMMAGINE));
        $this->tripletta->add("azione-mostra-anteprima",
            "anteprimaImmagine.mostraAnteprima('image-load', 'anteprima', 'ripristino', 'messaggi')",
            TriplettaSemplice::HTML);

        //Crea popup per chiedere conferma se effettuare il reset dell'immagine
        $this->addJSCodice(<<<JS
function confermaScelta() {
    anteprimaImmagine.resetAnteprima('image-load', 'anteprima', 'messaggi', 'ripristino', '$percorsoImmagineDefault');
    new Popup('popup-reset-immagine').nascondi();
}
JS);

        $dialog = new TriplettaSemplice("popup_dialog", "layout");
        $dialog->applica(array(
            "testo" => "Vuoi davvero reimpostare l'immagine di default?",
            "testo-pulsante" => "Procedi",
            "azione" => "confermaScelta()"
        ), TriplettaSemplice::HTML);
        
        $popupReset = new Popup("popup-reset-immagine");
        $popupReset->add($dialog);
        
        $this->tripletta->add("azione-conferma-reset-anteprima",
            "new Popup('popup-reset-immagine').mostra()",
            TriplettaSemplice::HTML);
        $this->associaTripletta($popupReset);
    }


    /**
     * Restituisce l'URL per la visualizzazione di un profilo, in base all'id passato.
     * @param int $id l'id dell'utente da visualizzare, se NULL, restituisce l'url escluso l'id
     * @return string
     */
    public static function getURLProfilo(?int $id = NULL) {
        $url = sprintf("%s&%s=", self::getSchermata(self::SCHERMATA_PROFILO), ControlloreProfilo::CAMPO_ID);

        if ($id !== NULL) {
            $url = sprintf("%s%d", $url, $id);
        }

        return $url;
    }
}

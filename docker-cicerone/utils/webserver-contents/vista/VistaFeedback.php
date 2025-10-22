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

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreFeedback.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Feedback.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Partecipazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloFeedback.php";
require_once "TriplettaSemplice.php";
require_once "VistaProfilo.php";
require_once "Vista.php";


use controllore\ControlloreFeedback;
use modello\ModelloFeedback;
use modello\entità\Feedback;
use modello\entità\Partecipazione;
use modello\entità\Utente;
use modello\entità\Itinerario;


/**
 * Rappresenta la vista associata al ControlloreFeedback e al ModelloFeedback.
 * 
 * <p>Ha quattro schermate, la prima per il rilascio di un feedback partecipante-organizzatore,
 * la seconda per il rilascio di un feedback organizzatore-partecipante, la terza per
 * la visualizzazione dei dettagli di un feedback e la quarta per la visualizzazione
 * dei feedback associati ad un fruitore.</p>
 * 
 * @see \controllore\ControlloreFeedback
 * @see \modello\ModelloFeedback
 */
class VistaFeedback extends Vista {
    protected const PAGINA_VISTA = "feedback.php";


    /*
     * Richieste che questa vista prende in considerazione
     */
    /**
     * Richiesta che consente il rilascio di un feedback partecipante-organizzatore.
     */
    private const RICHIESTA_RILASCIO_FEEDBACK_PO = "creaFeedbackPO";

    /**
     * Richiesta che consente il rilascio di un feedback organizzatore-partecipante.
     */
    private const RICHIESTA_RILASCIO_FEEDBACK_OP = "creaFeedbackOP";

    /*
     * Le diverse schermate che la vista mostra.
     */
    /**
     * Schermata che consente il rilascio di un feedback partecipante-organizzatore.
     */
    private const SCHERMATA_RILASCIO_FEEDBACK_PO = "creaFeedbackPO";
    
    /**
     * Schermata che consente il rilascio di un feedback organizzatore-partecipante.
     */
    private const SCHERMATA_RILASCIO_FEEDBACK_OP = "creaFeedbackOP";

    /**
     * Schermata che consente la visualizzazione dei dettagli di un feedback.
     */
    private const SCHERMATA_VISUALIZZAZIONE_FEEDBACK = "feedback";

    /**
     * Schermata che consente la visualizzazione dei feedback associati ad un fruitore.
     */
    private const SCHERMATA_VISUALIZZAZIONE_FEEDBACKS_FRUITORE = "lsFeedback";


    /**
     * Crea una VistaFeedback con un ControlloreFeedback sottostante
     */
    public function __construct() {
        parent::__construct(new ControlloreFeedback());
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

        /*
         * Queste variabili vengono utilizzate per semplificare il flusso
         * d'esecuzione delle elaborazioni.
         * Lo scopo è eseguire le elaborazioni correttamente rigettando quelle
         * non valide, sfruttando poche condizioni.
         */
        $richiestaValida = true;
        $isRichiestaPO = false;
        $isRichiestaOP = false;
        $paginaRedirect = self::getURLHOME();

        $isIDItinerarioDefinito = isset($this->postParams[ControlloreFeedback::CAMPO_ID_ITINERARIO]);
        if ($isIDItinerarioDefinito) {
            $idItinerario = $this->postParams[ControlloreFeedback::CAMPO_ID_ITINERARIO];
            $isIDItinerarioDefinito = preg_match(ModelloFeedback::REGEX_ID, $idItinerario);
        }
        $isFruitore = $this->controllore->isUtenteFruitore();
        $isCicerone = $this->controllore->isUtenteCicerone();


        switch($richiesta) {
            case self::RICHIESTA_RILASCIO_FEEDBACK_PO:
                $this->controllore->richiediRilascioFeedbackPO($this->postParams);
                $isRichiestaPO = true;
            break;
            case self::RICHIESTA_RILASCIO_FEEDBACK_OP:
                $this->controllore->richiediRilascioFeedbackOP($this->postParams);
                $isRichiestaOP = true;
            break;
            default:
                $richiestaValida = false;
            break;
        }

        if ($richiestaValida) {
            $statoOperazione = $this->controllore->getStatoOperazione();
            $this->controllore->copiaMessaggisticaPerSchermata();

            switch ($statoOperazione) {
                case ControlloreFeedback::STATO_OPERAZIONE_FALLITA:
                    if ($isRichiestaPO && $isIDItinerarioDefinito && $isFruitore) {
                        $paginaRedirect = self::getURLRilascioFeedbackPO($idItinerario);

                    } else if ($isRichiestaOP && $isIDItinerarioDefinito && $isCicerone) {
                        $paginaRedirect = self::getURLRilascioFeedbackOP($idItinerario);

                    }
                break;
                case ControlloreFeedback::STATO_OPERAZIONE_RIUSCITA:
                    if ($isIDItinerarioDefinito) {
                        $paginaRedirect = ($isRichiestaOP ? VistaItinerario::getURLVisualizzazioneRichiestePartecipazione($idItinerario)
                            : VistaItinerario::getURLItinerario($idItinerario));
                    }
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
            $this->getParams[self::GET_SCHERMATA] : self::SCHERMATA_RICERCA_ITINERARI;

        /*
         * Associa la schermata ottenuta alla messaggistica, così da permettere
         * di mantenere i messaggi finchè non si lascia la pagina.
         */
        $this->controllore->associaSchermataPerMessaggistica($schermata);

        $isFruitore = $this->controllore->isUtenteFruitore();
        $isCicerone = $this->controllore->isUtenteCicerone();
        $isIDDefinito = isset($this->getParams[ControlloreFeedback::CAMPO_ID]);
        if ($isIDDefinito) {
            $id = $this->getParams[ControlloreFeedback::CAMPO_ID];
            $isIDDefinito = preg_match(ModelloFeedback::REGEX_ID, $id);
        }

        $isSchermataValida = true;

        $metodoSchermata = "";

        switch ($schermata) {
            case self::SCHERMATA_RILASCIO_FEEDBACK_PO:
                if ($isFruitore && $isIDDefinito) {
                    $feedbackEsiste = $this->controllore->esisteFeedback($id, $this->controllore->getIDUtente(),
                        Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE);                    

                    $statoPartecipazioneFruitore = $this->controllore->getStatoPartecipazioneFruitoreAdItinerario($id,
                        $this->controllore->getIDUtente());

                    $statoItinerario = $this->controllore->richiediStatoItinerario($id);

                    $isSchermataValida = $statoItinerario === Itinerario::STATO_CONCLUSO && !$feedbackEsiste &&
                        ($statoPartecipazioneFruitore === Partecipazione::STATO_ACCORDATA);
                } else {
                    $isSchermataValida = false;
                }

                $metodoSchermata = "schermataRilascioFeedbackPO";
            break;
            case self::SCHERMATA_RILASCIO_FEEDBACK_OP:
                if ($isCicerone && $isIDDefinito) {
                    $id = $this->getParams[ControlloreFeedback::CAMPO_ID];

                    $feedbackEsiste = $this->controllore->esisteFeedback($id, $this->controllore->getIDUtente(),
                        Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE);

                    $statoItinerario = $this->controllore->richiediStatoItinerario($id);

                    $isSchermataValida = $statoItinerario === Itinerario::STATO_CONCLUSO && !$feedbackEsiste && $isCicerone && 
                        $this->controllore->isCiceroneOrganizzatoreDiItinerario($id, $this->controllore->getIDUtente());
                } else {
                    $isSchermataValida = false;
                }

                $metodoSchermata = "schermataRilascioFeedbackOP";
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_FEEDBACK:
                $metodoSchermata = "schermataVisualizzazioneFeedback";
                $isSchermataValida = $isIDDefinito;
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_FEEDBACKS_FRUITORE:
                $metodoSchermata = "schermataVisualizzazioneFeedbacksFruitore";
                $isSchermataValida = $isIDDefinito;
            break;
            default:
                $isSchermataValida = false;
            break;
        }

        if ($isSchermataValida) {
            $this->tripletta = new TriplettaSemplice("feedback", "form/feedback");
            $this->$metodoSchermata();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Crea schermata che consente il rilascio di un feedback partecipante-organizzatore
     */
    private function schermataRilascioFeedbackPO() : void {
        $id = $this->getParams[ControlloreFeedback::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-rilascio-feedback-PO",
            true, TriplettaSemplice::HTML);

        $itinerario = NULL;
        $this->controllore->richiediItinerario($id, $itinerario);

        $cicerone = $itinerario->getCicerone();
        
        $this->tripletta->applica(
            array("form-scr-itinerario" => VistaItinerario::getURLItinerario($itinerario->getID()),
                "nome-itinerario" => $itinerario->getNome(),
                "form-req-rilascia-feedback-po" => self::getRichiesta(self::RICHIESTA_RILASCIO_FEEDBACK_PO),
                "form-scr-profilo" => VistaProfilo::getURLProfilo($cicerone->getID()),
                "nome-utente" => $cicerone->getNomeUtente(),
                "campo-descrizione" => ControlloreFeedback::CAMPO_DESCRIZIONE,
                "campo-voto" => ControlloreFeedback::CAMPO_VOTO,
                "campo-id-itinerario" => ControlloreFeedback::CAMPO_ID_ITINERARIO,
                "id-itinerario" => $id,
            ),
            TriplettaSemplice::HTML
        );

        $this->setTitolo("Creazione di un feedback");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente il rilascio di un feedback organizzatore-partecipante
     */
    private function schermataRilascioFeedbackOP() : void {
        $id = $this->getParams[ControlloreFeedback::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-rilascio-feedback-OP",
            true, TriplettaSemplice::HTML);

        $itinerario = NULL;
        $this->controllore->richiediItinerario($id, $itinerario);

        $this->tripletta->applica(
            array("form-scr-itinerario" => VistaItinerario::getURLItinerario($itinerario->getID()),
                "nome-itinerario" => $itinerario->getNome(),
                "form-req-rilascia-feedback-po" => self::getRichiesta(self::RICHIESTA_RILASCIO_FEEDBACK_OP),
                "form-scr-profilo" => VistaProfilo::getURLProfilo($this->controllore->getIDUtente()),
                "nome-utente" => $this->controllore->getNomeUtente(),
                "campo-descrizione" => ControlloreFeedback::CAMPO_DESCRIZIONE,
                "campo-voto" => ControlloreFeedback::CAMPO_VOTO,
                "campo-id-partecipante" => ControlloreFeedback::CAMPO_ID_PARTECIPANTE,
                "campo-id-itinerario" => ControlloreFeedback::CAMPO_ID_ITINERARIO,
                "id-itinerario" => $id,
            ),
        TriplettaSemplice::HTML);

        $popupSelezionePartecipanti = new Popup("popup-selezione-partecipanti");
        $this->tripletta->add("azione-mostra-partecipanti",
            "new Popup('popup-selezione-partecipanti').mostra();",
            TriplettaSemplice::HTML);
        $this->associaTripletta($popupSelezionePartecipanti);

        $popupSelezionePartecipanti->add("<h3 class=\"screen-title centered-text-screen\">Seleziona i partecipanti che riceveranno lo stesso feedback</h3>");
        $popupSelezionePartecipanti->add("<div class=\"list-holder\">");

        $partecipanti = NULL;
        $this->controllore->richiediPartecipantiAdItinerario($id, $partecipanti);

        foreach ($partecipanti as $partecipante) {
            $triplettaListaElemento = new ElementoLista("elemento_profilo", "form/feedback");
            $triplettaListaElemento->applica(
                array("nome-utente" => $partecipante->getNomeUtente(),
                    "immagine-utente" => self::calcolaPercorsoImmagineUtente($partecipante->getImmagine()),
                    "campo-input-partecipante" => ControlloreFeedback::CAMPO_ID_PARTECIPANTE,
                    "id-input-partecipante" => $partecipante->getID(),
                )
            );

            $popupSelezionePartecipanti->add($triplettaListaElemento);
        }
        $popupSelezionePartecipanti->add("</div>");
        
        
        $this->setTitolo("Creazione di un feedback");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione dei dettagli di un feedback
     */
    private function schermataVisualizzazioneFeedback() : void {
        $id = $this->getParams[ControlloreFeedback::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-visualizzazione-feedback",
            true, TriplettaSemplice::HTML);

        $feedback = NULL;
        $this->controllore->richiediFeedback($id, $feedback);

        if ($feedback !== NULL) {
            $itinerario = $feedback->getItinerario();
            $partecipante = $feedback->getPartecipante();

            $organizzatore = NULL;
            $this->controllore->richiediUtente($itinerario->getIDCicerone(),
                $organizzatore);

            $this->tripletta->applica(
                array("feedback-trovato" => true,
                    "is-po" => $feedback->getTipo() === Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE,
                    "is-op" => $feedback->getTipo() === Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE,
                    "form-scr-profilo-organizzatore" => VistaProfilo::getURLProfilo($organizzatore->getID()),
                    "form-scr-profilo-partecipante" => VistaProfilo::getURLProfilo($partecipante->getID()),
                    "nome-utente-organizzatore" => $organizzatore->getNomeUtente(),
                    "nome-utente-partecipante" => $partecipante->getNomeUtente(),
                    "form-scr-itinerario" => VistaItinerario::getURLItinerario($itinerario->getID()),
                    "nome-itinerario" => $itinerario->getNome(),
                    "voto" => $feedback->getVoto(),
                    "descrizione" => self::newlineToBrTag($feedback->getDescrizione()),
                    "form-scr-lista-feedback-partecipante" => self::getURLFeedbacksFruitore($partecipante->getID()),
                    "form-scr-lista-feedback-organizzatore" => self::getURLFeedbacksFruitore($organizzatore->getID()),
                ), TriplettaSemplice::HTML
            );

            $titolo = "Visualizzazione del feedback #" . $feedback->getID();
        } else {
            $titolo = "Feedback non trovato!";
            $this->tripletta->setPulsante("feedback-non-trovato", true,
                TriplettaSemplice::HTML);
        }


        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione dei feedback associati
     * ad un fruitore
     */
    private function schermataVisualizzazioneFeedbacksFruitore() : void {
        $id = $this->getParams[ControlloreFeedback::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-visualizzazione-feedbacks-fruitore",
            true, TriplettaSemplice::HTML);

        $fruitore = NULL;
        $this->controllore->richiediUtente($id, $fruitore);

        if ($fruitore !== NULL &&
            $fruitore->getTipo() !== Utente::TIPO_AMMINISTRATORE) {

            $isCicerone = $fruitore->getTipo() === Utente::TIPO_CICERONE;

            $feedbacks = NULL;
            $this->controllore->richiediFeedbacksFruitore($id, $feedbacks);

            $this->tripletta->applica(
                array(
                    "fruitore-trovato" => true,
                    "is-cicerone" => $isCicerone,
                    "is-partecipante" => !$isCicerone,
                    "form-scr-profilo" => VistaProfilo::getURLProfilo($fruitore->getID()),
                    "nome-fruitore" => $fruitore->getNomeUtente(),
                ),
            TriplettaSemplice::HTML);

            $nFeedbacks = array(
                count($feedbacks[0]),
                $feedbacks[1] === NULL ? 0 : count($feedbacks[1])
            );

            for ($i = 0; $i < 2; $i++) {
                if ($nFeedbacks[$i] > 0) {
                    foreach ($feedbacks[$i] as $feedback) {
                        $tipoFeedback = $feedback->getTipo();
                        $itinerario = $feedback->getItinerario();

                        if ($i === 0) {
                            $partecipante = $fruitore;
                            $organizzatore = $itinerario->getCicerone();
                        } else {
                            $partecipante = $feedback->getPartecipante();
                            $organizzatore = $fruitore;
                        }

                        $triplettaListaElemento = new ElementoLista("elemento_feedback", "form/feedback");

                        $triplettaListaElemento->applica(array(
                            "is-po" => $tipoFeedback === Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE,
                            "is-op" => $tipoFeedback === Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE,
                            "voto" => $feedback->getVoto(),
                            "descrizione" => self::newlineToBrTag(substr($feedback->getDescrizione(), 0, 20) . "..."),
                            "nome-itinerario" => $itinerario->getNome(),
                            "form-scr-itinerario" => VistaItinerario::getURLItinerario($itinerario->getID()),
                            "form-scr-feedback" => self::getURLFeedback($feedback->getID()),
                            "form-scr-profilo-partecipante" => VistaProfilo::getURLProfilo($partecipante->getID()),
                            "nome-utente-partecipante" => $partecipante->getNomeUtente(), 
                            "form-scr-profilo-organizzatore" => VistaProfilo::getURLProfilo($organizzatore->getID()),
                            "nome-utente-organizzatore" => $organizzatore->getNomeUtente(), 
                        ));

                        $this->associaTripletta($triplettaListaElemento);

                        $this->tripletta->add(
                            ($i === 0 ? "feedback-come-partecipante" : "feedback-come-organizzatore"),
                            $triplettaListaElemento, TriplettaSemplice::HTML);
                    }
                } else {
                    $this->tripletta->add(
                        ($i === 0 ? "feedback-come-partecipante" : "feedback-come-organizzatore"), "<p>Nessun feedback trovato</p>",
                        TriplettaSemplice::HTML);
                }
            }

            $titolo = "Lista feedbacks di \"" . $fruitore->getNomeUtente() . "\"";
        } else {
            $titolo = "Utente non trovato!";
            $this->tripletta->setPulsante("fruitore-non-trovato", true, TriplettaSemplice::HTML);
        }


        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Restituisce l'URL per la visualizzazione dei feedback associati ad un
     * fruitore
     * @param id l'id del fruitore
     * @return string
     */
    public static function getURLFeedbacksFruitore(int $id) : string {
        return sprintf("%s&%s=%d",
            self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_FEEDBACKS_FRUITORE),
            ControlloreFeedback::CAMPO_ID, $id);
    }


    /**
     * Restituisce l'URL per la schermata di rilascio di un feedback organizzatore-partecipante,
     * in base all'id passato.
     * @param int $id l'id dell'itinerario, se NULL, restituisce l'url escluso l'id
     * @return string
     */
    public static function getURLRilascioFeedbackOP(?int $id = NULL) : string {
        $url = sprintf("%s&%s=", self::getSchermata(self::SCHERMATA_RILASCIO_FEEDBACK_OP), ControlloreFeedback::CAMPO_ID);

        if ($id !== NULL) {
            $url = sprintf("%s%d", $url, $id);
        }

        return $url;
    }


    /**
     * Restituisce l'URL per la schermata di rilascio di un feedback partecipante-organizzatore,
     * in base all'id passato.
     * @param int $id l'id dell'itinerario, se NULL, restituisce l'url escluso l'id
     * @return string
     */
    public static function getURLRilascioFeedbackPO(?int $id = NULL) : string {
        $url = sprintf("%s&%s=", self::getSchermata(self::SCHERMATA_RILASCIO_FEEDBACK_PO), ControlloreFeedback::CAMPO_ID);
        
        if ($id !== NULL) {
            $url = sprintf("%s%d", $url, $id);
        }

        return $url;
    }
    
    
    /**
     * Restituisce l'URL per la visualizzazione dei dettagli di un feedback
     * @param id l'id del feedback
     * @return string
     */
    private static function getURLFeedback(int $id) : string {
        return sprintf("%s&%s=%d", self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_FEEDBACK),
            ControlloreFeedback::CAMPO_ID, $id);
    }
}

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

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreItinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Partecipazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";

require_once "Vista.php";
require_once "TriplettaSemplice.php";

use controllore\ControlloreItinerario;
use modello\entità\Partecipazione;
use modello\entità\Itinerario;

/**
 * Rappresenta la schermata home del sito.
 * Questa schermata mostra gli ultimi 20 itinerari realizzati in ordine di tempo
 * (dal più nuovo al più vecchio).
 */
class SchermataHOME extends Vista {
    protected const PAGINA_VISTA = "index.php";


    /**
     * Schermata home (creata solo fare l'associazione con il
     * sistema di messaggistica)
     */
    private const SCHERMATA_HOME = "home";


    /**
     * Costruisci un'istanza di SchermatHOME utilizzando
     * un ControlloreItinerario.
     */
    public function __construct() {
        parent::__construct(new ControlloreItinerario());
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        //Imposta contenuti
        $this->tripletta = new TriplettaSemplice("itinerario", "form/itinerario", true);
        $this->tripletta->setPulsante("schermata-home", true, TriplettaSemplice::HTML);
        $this->tripletta->applica(array(
            "url-invio-richiesta-partecipazione" => VistaItinerario::getURLInvioRichiestaPartecipazione(),
            "url-invio-richiesta-annullamento" => VistaItinerario::getURLInvioRichiestaAnnullamento(),
            "param-id-partecipante" => ControlloreItinerario::CAMPO_ID_PARTECIPANTE,
            "param-id-itinerario" => ControlloreItinerario::CAMPO_ID_ITINERARIO,
        ), TriplettaSemplice::JAVASCRIPT);

        $itinerari = NULL;
        $this->controllore->richiediUltimiItinerari($itinerari);

        $nItinerari = count($itinerari);

        if ($nItinerari > 0) {
            $isFruitore = $this->controllore->isUtenteFruitore();
            
            foreach($itinerari as $itinerario) {
                $elementoLista = new ElementoLista("elemento_itinerario", "form/itinerario");

                $cicerone = $itinerario->getCicerone();

                if ($isFruitore) {
                    $isCiceroneOrganizzatore = ($this->controllore->getIDUtente() === $cicerone->getID());

                    if (!$isCiceroneOrganizzatore) {
                        $statoPartecipazione =
                            $this->controllore->getStatoPartecipazioneFruitoreAdItinerario($itinerario->getID(),
                                $this->controllore->getIDUtente());
                    } else {
                        $statoPartecipazione = NULL;
                    }
                } else {
                    $isCiceroneOrganizzatore = false;
                    $statoPartecipazione = NULL;
                }

                $feedbackRilasciato =
                    ($isCiceroneOrganizzatore && $this->controllore->isFeedbackOPRilasciato($itinerario->getID())) ||
                    ($isFruitore && $this->controllore->isFeedbackPORilasciato($itinerario->getID(), $this->controllore->getIDUtente()));

                $statoItinerario = $itinerario->getStato();

                $elementoLista->applica(array(
                    "descrizione" => self::newlineToBrTag(substr($itinerario->getDescrizione(), 0, 20) . "..."),
                    "organizzatore-nome-utente" => $cicerone->getNomeUtente(),
                    "form-scr-profilo-organizzatore" => VistaProfilo::getURLProfilo($cicerone->getID()),
                    "form-scr-itinerario" => VistaItinerario::getURLItinerario($itinerario->getID()),
                    "form-scr-modifica-itinerario" => VistaItinerario::getURLModificaItinerario($itinerario->getID()),
                    "id-partecipante" => $isFruitore ? $this->controllore->getIDUtente() : 0,
                    "id-itinerario" => $isFruitore ? $itinerario->getID() : 0,
                    "nome-itinerario" => $itinerario->getNome(),
                    "percorso-immagine" => self::calcolaPercorsoImmagineItinerario($itinerario->getImmagine()),
                    "luogo" => $itinerario->getLuogo(),
                    "compenso" => VistaItinerario::formattaCompenso($itinerario->getCompenso(), $itinerario->getValuta()),
                    "is-utente" => !$isCiceroneOrganizzatore,
                    "is-cicerone-organizzatore" => $isCiceroneOrganizzatore,
                    "no-partecipazione" => $isFruitore && !$isCiceroneOrganizzatore && $statoPartecipazione === NULL,
                    "partecipazione-accordata" => $statoPartecipazione === Partecipazione::STATO_ACCORDATA,
                    "partecipazione-accordanda" => $statoPartecipazione === Partecipazione::STATO_ACCORDANDA,
                    "partecipazione-annullanda" => $statoPartecipazione === Partecipazione::STATO_ANNULLANDA,
                    "stato-aperto" => $statoItinerario === Itinerario::STATO_APERTO,
                    "stato-itinere" => $statoItinerario === Itinerario::STATO_ITINERE,
                    "stato-concluso" => $statoItinerario === Itinerario::STATO_CONCLUSO,
                    "stato-chiuso" => $statoItinerario === Itinerario::STATO_CHIUSO,
                    "form-scr-crea-feedback" => ($isCiceroneOrganizzatore ? VistaFeedback::getURLRilascioFeedbackOP($itinerario->getID()) : VistaFeedback::getURLRilascioFeedbackPO($itinerario->getID())),
                    "feedback-non-rilasciato" => !$feedbackRilasciato,
                ));

                $this->tripletta->add("itinerari", $elementoLista, TriplettaSemplice::HTML);

                $this->associaTripletta($elementoLista);
            }
        } else {
            $this->tripletta->add("itinerari", "<p>Nessun itinerario trovato.<br>Siamo spiacenti.</p>", TriplettaSemplice::HTML);
        }

        /*
         * Associa la schermata ottenuta alla messaggistica, così da permettere
         * di mantenere i messaggi finchè non si lascia la pagina.
         */
        $this->controllore->associaSchermataPerMessaggistica(self::SCHERMATA_HOME);

        $this->setTitolo("HOME");
        $this->mostraErrori();
    }


    /**
     * Restituisce l'URL di questa schermata
     * @return string
     */
    public static function getURLSchermataHOME() : string {
        return self::getURLHOME();
    }

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::elabora()
     */
    public function elabora(): void {}

    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::isRichiesta()
     */
    public function isRichiesta(): bool {
        return false;   
    }
}

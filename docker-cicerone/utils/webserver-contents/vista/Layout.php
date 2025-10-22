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

require_once "VistaTest.php";
require_once "VistaAccesso.php";
require_once "VistaProfilo.php";
require_once "VistaAmministrazione.php";
require_once "VistaItinerario.php";
require_once "SchermataChiSiamo.php";
require_once "SchermataHOME.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreProfilo.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once "Popup.php";
require_once "TriplettaSemplice.php";


require_once "VistaFeedback.php";


use debug\DebugSettings;
use controllore\ControlloreProfilo;
use utils\Outputtabile;
use modello\entità\Utente;


define("DEBUG_TEMPLATE_DUMP", $_SERVER["DOCUMENT_ROOT"] . "/debug/rwdir/template_result.html");


/**
 * Rappresenta la layout (disposizione) del sito, pertanto ha tutti i metodi
 * che le servono per agganciare i vari elementi necessari a comporre l'interfaccia.
 * Funge anche da entrypoint.
 */
class Layout extends TriplettaTemplate {
    /*
     * Modalità debug
     */
    private const DEBUG = false;


    /*
     * Queste sono le chiavi contenute nella tripletta "layout"
     */
    private const CHIAVE_TITOLO = "titolo";
    private const CHIAVE_FOGLIO_STILE = "stile";
    private const CHIAVE_HEAD_JAVASCRIPT = "headjavascript";
    private const CHIAVE_BODY_JAVASCRIPT = "bodyjavascript";
    private const CHIAVE_CONTENUTI = "contenuti";
    private const CHIAVE_POPUPS = "popups";
    private const CHIAVE_SIDEBAR = "sidebar";
    private const CHIAVE_HOME_LINK_IMG = "home-href";


    /**
     * Percorso per raggiungere le triplette template "layout" e
     * "sidebar".
     */
    private const LAYOUT_TRIPLETTA_PATH = "layout";


    /**
     * Mantiene un riferimento alla tripletta template che riguarda la
     * sidebar.
     * @var TriplettaSemplice
     */
    private $risorsePopupCaricate;
    

    private function __construct() {
        parent::__construct("layout", self::LAYOUT_TRIPLETTA_PATH);
        $this->html->add(self::CHIAVE_FOGLIO_STILE, $this->foglioStile);
        $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $this->jsScript);
        $this->html->add(self::CHIAVE_HOME_LINK_IMG, Vista::getURLHOME());
        
        $this->risorsePopupCaricate = false;
    }


    /**
     * Imposta il titolo della pagina.
     * @param string $titolo
     */
    private function setTitolo(string $titolo) : void {
        $this->html->add(self::CHIAVE_TITOLO, $titolo);
    }


    /**
     * Aggiunge un foglio di stile come risorsa esterna
     * @param string $percorsoFoglioStile  percorso assoluto al foglio di stile
     */
    private function addFoglioStile(string $percorsoFoglioStile) : void {
        $this->html->addStringa(self::CHIAVE_FOGLIO_STILE,
            sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\"/>",
                $percorsoFoglioStile));
    }


    /**
     * Aggiunge del codice CSS
     * @param string $codiceStile
     */
    private function addStileCodice(string $codiceStile) : void {
        $this->html->addStringa(self::CHIAVE_FOGLIO_STILE,
            sprintf("<style>%s</style>", $codiceStile));
    }


    /**
     * Aggiunge uno script javascript come risorsa esterna
     * @param string $percorsoJavascript percorso assoluto allo script
     * @param bool $head se impostato su true, carica lo script all'interno del
     * tag &lt;head&gt;, in caso contrario, verrà caricato in fondo al tag &lt;body&gt;.
     */
    private function addJSScript(string $percorsoJavascript, bool $head=true) : void {
        $this->html->addStringa($head ? self::CHIAVE_HEAD_JAVASCRIPT : self::CHIAVE_BODY_JAVASCRIPT,
            sprintf("<script src=\"%s\"></script>", $percorsoJavascript));
    }


    /**
     * Aggiunge del codice javascript
     * @param string $codiceJS
     * @param bool $head se impostato su true, inserirà il codice all'interno del
     * tag &lt;head&gt;, in caso contrario, verrà inserito in fondo al tag &lt;body&gt;.
     */
    private function addJSCodice(string $codiceJS, bool $head=true) : void {
        $this->html->add($head ? self::CHIAVE_HEAD_JAVASCRIPT : self::CHIAVE_BODY_JAVASCRIPT,
            sprintf("<script>%s</script>", $codiceJS));
    }


    /**
     * Aggiunge una stringa ai contenuti del layout
     * @param string $contenuti
     */
    private function addStringa(string $contenuti) : void {
        $this->html->add(self::CHIAVE_CONTENUTI, $contenuti);
    }


    /**
     * Aggiunge un Outputtabile al Layout, in base al particolare
     * tipo di Outputtabile.
     * @param Outputtabile $outputtabile
     */
    private function addOutputtabile(Outputtabile $outputtabile) : void {
        if ($outputtabile instanceof Popup) {
            $tripletteAssociate = $outputtabile->getTripletteAssociate();

            foreach ($tripletteAssociate as $currTripletta) {
                $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT,
                    $currTripletta->getJSScript());
                 $this->html->add(self::CHIAVE_FOGLIO_STILE,
                     $currTripletta->getFoglioStile());
            }
            $this->html->add(self::CHIAVE_POPUPS, $outputtabile);

            if (!$this->risorsePopupCaricate) {
                $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
                $this->html->add(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
                $this->risorsePopupCaricate = true;
            }

        } else if ($outputtabile instanceof Vista) {
            $this->setTitolo($outputtabile->getTitolo());
            $jsCodici = $outputtabile->getJSCodici();

            foreach ($jsCodici as $jsCodice) {
                $this->addJSCodice($jsCodice, false);
            }

            //Aggiungi risorse della Vista
            $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
            $this->html->add(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());

            //Aggiungi risorse di eventuali istanze di TriplettaTemplate associate alla vista
            $tripletteAssociate = $outputtabile->getTripletteAssociate();
            foreach ($tripletteAssociate as $tripletta) {
                if ($tripletta instanceof Popup) {
                    $this->addOutputtabile($tripletta);
                } else {
                    $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $tripletta->getJSScript());
                    $this->html->add(self::CHIAVE_FOGLIO_STILE, $tripletta->getFoglioStile());
                }
            }

            $this->html->add(self::CHIAVE_CONTENUTI, $outputtabile);

        } else if ($outputtabile instanceof TriplettaTemplate) {
            $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
            $this->html->add(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
            $this->html->add(self::CHIAVE_CONTENUTI, $outputtabile);

        } else { //Se non è un'istanza conosciuta, aggiungila semplicemente ai contenuti
            $this->html->add(self::CHIAVE_CONTENUTI, $outputtabile);
        }
    }


    /**
     * Crea la sidebar ed in generale, imposta alcuni valori
     * per consentirne l'uso, inoltre assicura che i dati
     * di sessione siano aggiornati, rispondendo dunque
     * ad eventuali eventi (ad esempio, richieste di disiscrizione
     * e richieste d'aggiornamento).
     */
    private function avviaSessioneUtente() : void {
        //Non è che cambi molto usare ControlloreProfilo o ControlloreItinerario
        $controlloreSessione = new ControlloreProfilo();
        $controlloreSessione->aggiornaSessione(); //Aggiorna i dati di sessione

        $idUtente = $controlloreSessione->getIdUtente();
        $tipoUtente = $controlloreSessione->getTipoUtente();

        //Imposta tripletta template inerente la sidebar
        $sidebar = new TriplettaSemplice("sidebar", self::LAYOUT_TRIPLETTA_PATH);
        $this->html->add(self::CHIAVE_SIDEBAR, $sidebar);
        $this->html->add(self::CHIAVE_HEAD_JAVASCRIPT, $sidebar->getJSScript());
        $this->html->add(self::CHIAVE_FOGLIO_STILE, $sidebar->getFoglioStile());


        if ($idUtente !== NULL) {
            $nomeUtente = $controlloreSessione->getNomeUtente();
            $immagineUtente = $controlloreSessione->getImmagineUtente();

            $sidebar->applica(array(
                "profilo-id-utente" => VistaProfilo::getURLProfilo($idUtente),
                "nome-utente" => $nomeUtente,
                "immagine-utente" => sprintf("%s/%s", Vista::getPercorsoImmaginiUtenti(), $immagineUtente),
                "is-connesso" => true,
                "is-fruitore" => ($tipoUtente === Utente::TIPO_GLOBETROTTER ||
                    $tipoUtente === Utente::TIPO_QUASICICERONE ||
                    $tipoUtente === Utente::TIPO_CICERONE),
                "is-cicerone" => ($tipoUtente === Utente::TIPO_CICERONE),
                "is-amministratore" => ($tipoUtente === Utente::TIPO_AMMINISTRATORE),
            ), TriplettaSemplice::HTML);

            $sidebar->applica(array(
                "form-itinerari-organizzatore" => VistaItinerario::getURLItinerariOrganizzatoreFruitore($idUtente),
                "form-itinerari-partecipante" => VistaItinerario::getURLItinerariPartecipanteFruitore($idUtente),
                "form-feedback-utente" => VistaFeedback::getURLFeedbacksFruitore($idUtente),
            ), TriplettaSemplice::HTML);

        } else {
            $sidebar->setPulsante("is-ospite", true, TriplettaSemplice::HTML);
        }
        
        $sidebar->applica(array(
            "form-home" => SchermataHOME::getURLSchermataHOME(),
            "form-accesso" => VistaAccesso::getURLMenuAccesso(),
            "form-registrazione" => VistaAccesso::getURLMenuRegistrazione(),
            "form-disconnessione" => VistaAccesso::getURLRichiestaDisconnessione(),
            "form-recupero-accesso" => VistaAccesso::getURLMenuRecupero(),
            "form-chi-siamo" => SchermataChiSiamo::getURLSchermataChiSiamo(),
            "form-richieste-ammin" => VistaAmministrazione::getURLRichiesteAmministrazione(),
            "form-ricerca-itinerari" => VistaItinerario::getURLRicercaItinerari()
        ), TriplettaSemplice::HTML);
    }


    /*
     * L'entrypoint da utilizzare in ogni pagina da visitare.
     */
    public static function entrypoint(string $pagina) {
        $visteDisponibili = array("vista\SchermataHOME", "vista\VistaTest", "vista\VistaAccesso",
            "vista\VistaProfilo", "vista\SchermataChiSiamo", "vista\VistaAmministrazione",
            "vista\VistaItinerario", "vista\VistaFeedback", //"vista\VistaNotifica",
        );

        $vistaTrovata = NULL;
        foreach ($visteDisponibili as $vista) {
            if ($vista::getPaginaVista() === $pagina) {
                $vistaTrovata = $vista;
                break;
            }
        }

        if ($vistaTrovata === NULL) {
            //fallback
            $vistaTrovata = "vista\SchermataHOME";
        }

        $vista = new $vistaTrovata();
        if ($vista->isRichiesta()) {
            $vista->elabora();
        } else {
            $layout = new Layout();
            $layout->avviaSessioneUtente();
            $vista->disegna();
            $layout->addOutputtabile($vista);

            if (Vista::DEBUG) {
                $popupDebug = new Popup("popup-debug", Popup::CHIUSURA_INTERACTIVE);
                $popupDebug->add(new DebugSettings());
                $layout->addOutputtabile($popupDebug);
            }

            $output = $layout->output();
            if (self::DEBUG) {
                $f = fopen(DEBUG_TEMPLATE_DUMP, "w");
                fwrite($f, $output);
                fclose($f);
            }
            echo $output;
        }
    }
}

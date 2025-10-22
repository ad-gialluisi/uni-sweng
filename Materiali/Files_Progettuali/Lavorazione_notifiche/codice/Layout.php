<?php

namespace vista;

require_once "VistaTest.php";
require_once "VistaAccesso.php";
require_once "VistaUtente.php";
require_once "VistaNotifica.php";
require_once "SchermataChiSiamo.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreSessione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/LettoreSessione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/Utente.php";
require_once "Popup.php";
require_once "TriplettaSemplice.php";


use debug\DebugSettings;
use controllore\ControlloreSessione;
use controllore\LettoreSessione;
use utils\Outputtabile;
use modello\Utente;


define("DEBUG_DUMP_FILE", false);
define("TEMPLATE_RESULT", $_SERVER["DOCUMENT_ROOT"] . "/debug/rwdir/template_result.html");




/**
 * Rappresenta la layout (disposizione) del sito, pertanto ha tutti i metodi
 * che le servono per agganciare i vari elementi necessari a comporre l'interfaccia.
 */
class Layout extends TriplettaTemplate {
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
    private $risorseVistaNotificaCaricate;
    

    public function __construct() {
        parent::__construct("layout", self::LAYOUT_TRIPLETTA_PATH);
        $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $this->foglioStile);
        $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $this->jsScript);

        $this->risorseVistaNotificaCaricate = false;
        $this->risorsePopupCaricate = false;
    }


    /**
     * Imposta il titolo della pagina.
     * @param string $titolo
     */
    public function setTitolo(string $titolo) : void {
        $this->html->addStringa(self::CHIAVE_TITOLO, $titolo);
    }


    /**
     * Aggiunge un foglio di stile come risorsa esterna
     * @param string $percorsoFoglioStile  percorso assoluto al foglio di stile
     */
    public function addFoglioStile(string $percorsoFoglioStile) : void {
        $this->html->addStringa(self::CHIAVE_FOGLIO_STILE,
            sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\"/>",
                $percorsoFoglioStile));
    }


    /**
     * Aggiunge del codice CSS
     * @param string $codiceStile
     */
    public function addStileCodice(string $codiceStile) : void {
        $this->html->addStringa(self::CHIAVE_FOGLIO_STILE,
            sprintf("<style>%s</style>", $codiceStile));
    }


    /**
     * Aggiunge uno script javascript come risorsa esterna
     * @param string $percorsoJavascript percorso assoluto allo script
     * @param bool $head se impostato su true, carica lo script all'interno del
     * tag &lt;head&gt;, in caso contrario, verrà caricato in fondo al tag &lt;body&gt;.
     */
    public function addJSScript(string $percorsoJavascript, bool $head=true) : void {
        $this->html->addStringa($head ? self::CHIAVE_HEAD_JAVASCRIPT : self::CHIAVE_BODY_JAVASCRIPT,
            sprintf("<script src=\"%s\"></script>", $percorsoJavascript));
    }


    /**
     * Aggiunge del codice javascript
     * @param string $codiceJS
     * @param bool $head se impostato su true, inserirà il codice all'interno del
     * tag &lt;head&gt;, in caso contrario, verrà inserito in fondo al tag &lt;body&gt;.
     */
    public function addJSCodice(string $codiceJS, bool $head=true) : void {
        $this->html->addStringa($head ? self::CHIAVE_HEAD_JAVASCRIPT : self::CHIAVE_BODY_JAVASCRIPT,
            sprintf("<script>%s</script>", $codiceJS));
    }


    /**
     * Aggiunge una stringa ai contenuti del layout
     * @param string $contenuti
     */
    public function addStringa(string $contenuti) : void {
        $this->html->addStringa(self::CHIAVE_CONTENUTI, $contenuti);
    }


    /**
     * Aggiunge un Outputtabile al Layout, in base al particolare
     * tipo di Outputtabile.
     * @param Outputtabile $outputtabile
     */
    public function addOutputtabile(Outputtabile $outputtabile) : void {
        if ($outputtabile instanceof Popup) {
            $outputtabiliAssociati = $outputtabile->getOutputtabiliAssociati();

            foreach ($outputtabiliAssociati as $currOutputtabile) {
                //Alloca risorse correttamente
                //La questione della VistaNotifica serve perchè
                //in caso contrario ci sono problmi
                if ($currOutputtabile instanceof TriplettaTemplate) {
                    $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT,
                        $currOutputtabile->getJSScript());
                    $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE,
                        $currOutputtabile->getFoglioStile());

                } else if ($currOutputtabile instanceof VistaNotifica) {
                    $currOutputtabile->disegna();

                    if (!$this->risorseVistaNotificaCaricate) {
                        $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT,
                            $currOutputtabile->getJSScript());
                        $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE,
                            $currOutputtabile->getFoglioStile());
                        $this->risorseVistaNotificaCaricate = true;
                    }
                }
            }
            $this->html->addOutputtabile(self::CHIAVE_POPUPS, $outputtabile);

            if (!$this->risorsePopupCaricate) {
                $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
                $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
                $this->risorsePopupCaricate = true;
            }

        } else if ($outputtabile instanceof Vista) {
            $this->setTitolo($outputtabile->getTitolo());
            $jsCodici = $outputtabile->getJSCodici();
            foreach ($jsCodici as $jsCodice) {
                $this->addJSCodice($jsCodice, false);
            }

            if ($outputtabile instanceof VistaNotifica) {
                if (!$this->risorseVistaNotificaCaricate) {
                    $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
                    $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
                    $this->risorseVistaNotificaCaricate = true;
                }
            } else {
                $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
                $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
            }

            $this->html->addOutputtabile(self::CHIAVE_CONTENUTI, $outputtabile);

        } else if ($outputtabile instanceof TriplettaTemplate) {
            $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $outputtabile->getJSScript());
            $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $outputtabile->getFoglioStile());
            
        } else { //Se non è un'istanza conosciuta, aggiungila ai contenuti
            $this->html->addOutputtabile(self::CHIAVE_CONTENUTI, $outputtabile);
        }
    }


    /**
     * Imposta la sidebar in questa layout.
     * @param LettoreSessione $lettoreSessione
     */
    public function creaUISessioneUtente(/*LettoreSessione $lettoreSessione,
        VistaNotifica $notifica, string $urlHOME, string $urlChiSiamo,
        string $urlMenuAccesso, string $urlMenuRecupero, string $urlMenuRegistrazione,
        string $urlDisconnessione*/) : void {

        $vistaNotifica = new VistaNotifica(true);
        $lettoreSessione = new ControlloreSessione();

        $idUtente = $lettoreSessione->getIdUtente();
        $tipoUtente = $lettoreSessione->getTipoUtente();

        //Imposta tripletta template inerente la sidebar
        $sidebar = new TriplettaSemplice("sidebar", self::LAYOUT_TRIPLETTA_PATH);
        $this->html->addOutputtabile(self::CHIAVE_SIDEBAR, $sidebar);
        $this->html->addOutputtabile(self::CHIAVE_HEAD_JAVASCRIPT, $sidebar->getJSScript());
        $this->html->addOutputtabile(self::CHIAVE_FOGLIO_STILE, $sidebar->getFoglioStile());


        if ($idUtente !== NULL) {
            $nomeUtente = $lettoreSessione->getNomeUtente();
            $immagineUtente = $lettoreSessione->getImmagineUtente();

            $sidebar->applica(array(
                "id-utente" => $idUtente,
                "nomeutente" => $nomeUtente,
                "immagine-utente" => $immagineUtente,
                "is-connesso" => true,
                "is-fruitore" => ($tipoUtente === Utente::TIPO_GLOBETROTTER ||
                    $tipoUtente === Utente::TIPO_QUASICICERONE ||
                    $tipoUtente === Utente::TIPO_CICERONE),
                "is-cicerone" => ($tipoUtente === Utente::TIPO_CICERONE),
                "is-amministratore" => ($tipoUtente === Utente::TIPO_AMMINISTRATORE),
            ), TriplettaSemplice::HTML);

            $this->html->setPulsante("is-connesso", true);

            $popupNotifiche = new Popup("popup-notifiche", Popup::CHIUSURA_INTERACTIVE);
            $popupNotifiche->addOutputtabile($vistaNotifica);
            $this->addOutputtabile($popupNotifiche);
            $vistaNotifica->disegna();
        } else {
            $sidebar->setPulsante("is-ospite", true, TriplettaSemplice::HTML);
        }
        
        $sidebar->applica(array(
            "form-home" => Vista::getURLHOME(),
            "form-accesso" => VistaAccesso::getURLMenuAccesso(),
            "form-registrazione" => VistaAccesso::getURLMenuRegistrazione(),
            "form-disconnessione" => VistaAccesso::getURLRichiestaDisconnessione(),
            "form-recupero-accesso" => VistaAccesso::getURLMenuRecupero(),
            "form-chi-siamo" => SchermataChiSiamo::getURLSchermataChiSiamo()
        ), TriplettaSemplice::HTML);
    }
    
    
    
    
    
    
    
    
    
    public const PAGINA_INDEX = 0;
    public const PAGINA_TEST = 1;
    public const PAGINA_ACCESSO = 2;
    public const PAGINA_PROFILO = 3;
    public const PAGINA_NOTIFICA = 4;
    public const PAGINA_CHISIAMO = 5;
    /*	public const PAGINA_FEEDBACK = "feedback.php";
     public const PAGINA_ITINERARIO = "itinerario.php";
     public const PAGINA_RICHIESTA = "richiesta.php";
     public const PAGINA_PARTECIPAZIONE = "partecipazione.php";
     */
    public static function entrypoint(int $pagina) {
        $vista = null;

        $viste = array(
            self::PAGINA_INDEX    => "vista\SchermataChiSiamo",
            self::PAGINA_CHISIAMO => "vista\SchermataChiSiamo",
            self::PAGINA_TEST     => "vista\VistaTest",
            self::PAGINA_ACCESSO  => "vista\VistaAccesso",
            self::PAGINA_PROFILO  => "vista\VistaUtente",
            self::PAGINA_NOTIFICA => "vista\VistaNotifica",
            //self::FEEDBACK => "VistaFeedback",
            //self::ITINERARIO => "VistaItinerario",
            //self::RICHIESTA => "VistaRichiesta",
            //self::PARTECIPAZIONE => "VistaPartecipazione",
        );


        if (isset($viste[$pagina])) {
            $costruttore = $viste[$pagina];

            $vista = new $costruttore();
            if ($vista->isRichiesta()) {
                $vista->elabora();
            } else {
                $vista->disegna();

                $layout = new Layout();
                $layout->addOutputtabile($vista);

                //Creazione interfaccia utente per la sessione
                //$vistaNotifica = new VistaNotifica(true);
                //$controlloreSessione =  new ControlloreSessione();

                $layout->creaUISessioneUtente(/*new ControlloreSessione(),
                    $vistaNotifica,
                    Vista::getURLHome(),
                    SchermataChiSiamo::getURLSchermataChiSiamo(),
                    VistaAccesso::getURLMenuAccesso(),
                    VistaAccesso::getURLMenuRecupero(),
                    VistaAccesso::getURLMenuRegistrazione(),
                    VistaAccesso::getURLRichiestaDisconnessione(),
                    */);

                if (Vista::DEBUG_ABILITATO) {
                    $popupDebug = new Popup("popup-debug", Popup::CHIUSURA_INTERACTIVE);
                    $popupDebug->addOutputtabile(new DebugSettings());
                    $layout->addOutputtabile($popupDebug);
                }
                
                $output = $layout->output();
                if (DEBUG_DUMP_FILE) {
                    $f = fopen(TEMPLATE_RESULT, "w");
                    fwrite($f, $output);
                    fclose($f);
                } else {
                    echo $output;
                }
            }
        }
    }
    
}





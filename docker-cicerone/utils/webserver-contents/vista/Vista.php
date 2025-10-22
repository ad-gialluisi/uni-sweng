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


require_once "Layout.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/Outputtabile.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/template/Template.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/Controllore.php";

use debug\DebugSettings;
use controllore\Controllore;
use utils\Outputtabile;


/**
 * Rappresenta una vista di questo sistema.
 *
 * <p>L'obiettivo delle viste è quello di mostrare l'interfaccia e
 * richiedere ai controllori eventuali servizi (che a loro volta
 * delegheranno al modello).</p>
 * <p>È da notare come sia responsabilità delle viste mappare correttamente
 * eventuali form ai campi che un controllore si aspetta di gestire.</p>
 */
abstract class Vista implements Outputtabile {
    /**
     * La pagina home
     * @var string
     */
    private const PAGINA_HOME = "index.php";


    /**
     * La pagina associata alla vista corrente.
     * È bene che ogni classe figlia RIDEFINISCA questa costante.
     */
    protected const PAGINA_VISTA = "index.php";


    /**
     * Nome del parametro (GET) che riguarda la schermata
     */
    protected const GET_SCHERMATA = "schermata";


    /**
     * Nome del parametro (GET) che riguarda la richiesta
     */
    protected const GET_RICHIESTA = "richiesta";
    
    
    /**
     * Nome dei parametro (GET) che riguarda l'eventuale menù di una schermata
     */
    protected const GET_MENU = "menu";


    /**
     * Un'istanza di un controllore (o NULL se non è fornito).
     * @var Controllore
     */
    protected $controllore;


    /**
     * Tripletta Semplice associata a questa vista, viene utilizzata
     * per inserire gli elementi che poi verranno disegnati.
     * @var TriplettaSemplice
     */
    protected $tripletta;


    /**
     * Stabilisce se il debug è abilitato (verranno utilizzati dei
     * parametri GET per sfruttare XDEBUG ad ogni redirezione mediante
     * header(location: ...).
     */
    public const DEBUG = true;


    /**
     * Immagazzina i parametri ottenuti mediante metodo GET
     * @var array
     */
    protected $getParams;


    /**
     * Immagazzina i parametri ottenuti mediante metodo POST
     * @var array
     */
    protected $postParams;
    

    /**
     * Codici JS che è necessario eseguire quando la vista
     * verrà disegnata
     * @var array
     */
    private $codiciJS;
    
    
    /**
     * Triplette che è necessario considerare quando la vista
     * verrà disegnata.
     * Prevalentemente ha lo scopo di semplificare il caricamento
     * delle risorse associate ad esse.
     * @var array
     */
    private $tripletteAssociate;


    /**
     * Titolo di questa vista
     * @var array
     */
    private $titolo;



    /**
     * Costruisce una vista.
     * Se DEBUG è true, aggiungerà gli elementi previsti da DebugSettings
     */
    public function __construct(?Controllore $controllore) {
        $this->controllore = $controllore;
        
        $this->getParams = filter_input_array(INPUT_GET);
        $this->postParams = filter_input_array(INPUT_POST);

        //Se non ci sono argomenti, imposta array vuoti
        if ($this->getParams === false || $this->getParams === NULL) {
            $this->getParams = array();
        }

        if ($this->postParams === false || $this->postParams === NULL) {
            $this->postParams = array();
        }

        $this->codiciJS = array();
        $this->tripletteAssociate = array();
        $this->tripletta = NULL;
    }


    /**
     * Metodo che rimanda ad un'altra pagina.
     * Se il DEBUG è abilitato fa in modo che il rimando possa essere DEBUGGATO.
     * @param string $pagina
     */
    protected function mandaA(string $pagina) : void {
        if (self::DEBUG && DebugSettings::isDebugHeaderLocationSet()) {
            $pagina = DebugSettings::transformURLDebugSymbols($pagina);
        }

        header("location: $pagina");
        exit();
    }


    /**
     * Metodo d'utilità per mostrare gli errori segnalati dai controllori
     * all'interno di un template con una chiave segnaposto chiamata
     * "messaggistica".
     * Usarlo, in genere, alla fine di ogni metodo che riguarda le schermate.
     */
    protected function mostraErrori() : void {
        if ($this->controllore->ciSonoMessaggi()) {
            $this->tripletta->add("messaggistica",
                implode("<br>", $this->controllore->getMessaggi()),
                TriplettaSemplice::HTML);
        }
    }


    /**
     * Aggiunge un frammento di codice Javascript a questa vista.
     * Servono per agganciare codici da avviare non appena
     * la vista verrà disegnata.
     * @param string $codiceJS
     */
    protected function addJSCodice(string $codiceJS) : void {
        $this->codiciJS[]= $codiceJS;
    }


    /**
     * Restituisce tutti i codici Javascript aggiunti.
     * @return array
     */
    public function getJSCodici() : array {
        return $this->codiciJS;
    }


    /**
     * Metodo che ha lo scopo di aggiungere Triplette per poter
     * semplificare il caricamento delle risorse a loro associate
     * (perlopiù Javascript e CSS).
     * Sfrutta la firma per stabilire se una tripletta è diversa
     * da un'altra.
     * @param TriplettaTemplate $tripletta
     */
    protected function associaTripletta(TriplettaTemplate $tripletta) : void {
        $firma = $tripletta->getFirmaTripletta();
        if (!isset($this->tripletteAssociate[$firma])) {
            $this->tripletteAssociate[$firma] = $tripletta;
        }
    }
    
    
    /**
     * Restituisce un array contenente l'insieme delle triplette
     * associate
     * @return array
     */
    public function getTripletteAssociate() : array {
        return $this->tripletteAssociate;
    }


    /**
     * Restituisce la componente Javascript della tripletta
     * utilizzata dalla vista.
     * @return Outputtabile
     */
    public function getJSScript() : Outputtabile {
        return $this->tripletta->getJSScript();
    }


    /**
     * Restituisce la componente Foglio di stile della tripletta
     * utilizzata dalla vista.
     * @return Outputtabile
     */
    public function getFoglioStile() : Outputtabile {
        return $this->tripletta->getFoglioStile();
    }
    

    /**
     * Imposta un valore per il titolo della Vista.
     * @param string $titolo
     */
    protected function setTitolo(string $titolo) {
        $this->titolo = $titolo;
    }
    
    
    /**
     * Restituisce il titolo associato alla Vista.
     * @return string
     */
    public function getTitolo() : string {
        return $this->titolo;
    }
   

    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\Outputtabile::output()
     */
    public function output() : string {
        return $this->tripletta !== NULL ? $this->tripletta->output() : "";
    }


    /*
     * Metodi statici
     */
    /**
     * Restituisce l'URL di una certa schermata specificata come parametro
     * @param string $tipo la schermata
     * @return string l'URL della schermata richiesta
     */
    protected static function getSchermata(string $tipo) : string {
        return sprintf("%s?%s=%s", static::PAGINA_VISTA, self::GET_SCHERMATA, $tipo);
    }


    /**
     * Restituisce l'URL di una certa richiesta specificata come parametro
     * @param string $tipo
     * @return string
     */
    protected static function getRichiesta(string $tipo) : string {
        return sprintf("%s?%s=%s", static::PAGINA_VISTA, self::GET_RICHIESTA, $tipo);
    }


    /**
     * Restituisce l'URL di un certo menù di una schermata, forniti ambedue
     * come parametri
     * @param string $schermata
     * @param string $menu
     * @return string
     */
    protected static function getMenu(string $schermata, string $menu) : string {
        return sprintf("%s&%s=%s", self::getSchermata($schermata), self::GET_MENU, $menu);
    }


    /**
     * Restituisce il percorso alla cartella che conterrà le immagini associate agli utenti.
     * @return string
     */
    public static function getPercorsoImmaginiUtenti() : string {
        return "immagini/utenti";
    }


    /**
     * Ottiene il percorso alla cartella che conterrà le immagini associate agli itinerari.
     * @return string
     */
    public static function getPercorsoImmaginiItinerari() : string {
        return "immagini/itinerari";
    }


    /**
     * Ottiene il percorso all'immagine supponendo che essa sia nella cartella delle immagini degli utenti.
     * @param string $immagine
     * @return string
     */
    public static function calcolaPercorsoImmagineUtente(string $immagine) : string {
        return sprintf("%s/%s", self::getPercorsoImmaginiUtenti(), $immagine);
    }


    /**
     * Ottiene il percorso all'immagine supponendo che essa sia nella cartella delle immagini degli itinerari.
     * @param string $immagine
     * @return string
     */
    public static function calcolaPercorsoImmagineItinerario(string $immagine) : string {
        return sprintf("%s/%s", self::getPercorsoImmaginiItinerari(), $immagine);
    }


    /**
     * Restituisce l'URL per l'indice del sito.
     * @return string
     */
    public static function getURLHOME() : string {
        return self::PAGINA_HOME;
    }

    
    /**
     * Restituisce la pagina associata alla particolare vista.
     * @return string
     */
    public static function getPaginaVista() : string {
        return static::PAGINA_VISTA;
    }


    /**
     * Trasforma una stringa sostituendo i caratteri newline in &lt;br&gt;.
     * @param string $testo
     * @return string
     */
    public static function newlineToBrTag(string $testo) : string {
        return str_replace("\n", "<br>", $testo);
    }



    /*
     * Metodi astratti
     */
    /**
     * Questo metodo verrà utilizzato appunto per "disegnare",
     * cioè, creare tutti gli elementi grafici della particolare vista.
     * Si presuppone che si chiami questo metodo nell'entrypoint della
     * vista.
     */
    public abstract function disegna() : void;


    /**
     * Stabilisce se è stata fatta una richiesta.
     * @return bool
     */
    public abstract function isRichiesta() : bool;
    
    
    /**
     * Effettua la richiesta, qualunque essa sia.
     * Si presuppone che si chiami questo metodo nell'entrypoint
     * della vista.
     */
    public abstract function elabora() : void;
}

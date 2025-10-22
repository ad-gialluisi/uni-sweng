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


namespace debug;


require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/GestoreSession.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/vista/TriplettaTemplate.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/template/Template.php";

use utils\GestoreSession;
use vista\TriplettaTemplate;
use utils\template\Template;


/**
 * L'intera classe è stata creata a causa di problemi durante il debugging
 * con Eclipse.
 * <p>Difatti, normalmente, non è permesso fare debugging delle operazioni dovute
 * ai submit.<br>
 * Con questa classe e con la controparte javascript risiedente nei template,
 * si risolve il problema.</p>
 */
class DebugSettings extends TriplettaTemplate {
    /*
     * Questi consentono di far riferimento ai singoli campo impostati da
     * "parse_url".
     */
    private const QUERY = "query";
    private const FRAGMENT = "fragment";
    private const SCHEME = "scheme";
    private const HOST = "host";
    private const PORT = "port";
    private const PATH = "path";


    // DEBUG GET parameters
    private const XDEBUG_SESSION_START_VALUE = "ECLIPSE_DBGP";
    private const XDEBUG_KEY_VALUE = "157270718956123";


    /*
     * Alcune chiavi segnaposto della tripletta template "debug_utils"
     * che questa classe utilizza.
     */
    private const DEBUG_HEADER_LOCATION_ENTRY = "debug-header-location-status";
    private const DEBUG_HEADER_LOCATION_MESSAGE_ENTRY = "debug-header-location-status-message";
    private const DEBUG_PAGES_PRESET_ENTRY = "pages-and-presets";


    public function __construct() {
        parent::__construct("debug_utils", "debug", true);

        //Quick fix, in fondo, trattasi di debugging
        define("PRESET_PAGES_FILE", $_SERVER["DOCUMENT_ROOT"] . "/debug/pagesAndPresets.js");

        $isSet = self::isDebugHeaderLocationSet();
        $this->jsScript->applica(array(
            self::DEBUG_HEADER_LOCATION_ENTRY => ($isSet ? "true" : "false"),
            self::DEBUG_PAGES_PRESET_ENTRY => Template::daFile(PRESET_PAGES_FILE) 
        ));
        $this->html->add(self::DEBUG_HEADER_LOCATION_MESSAGE_ENTRY,
            $isSet ? "Disabilita" : "Abilita");
    }

    
    /**
     * Stabilisce se è abilitato il debug per la funzione header(location: <url>)
     * @return bool
     */
    public static function isDebugHeaderLocationSet() : bool {
        $gestoreSession = new GestoreSession();
        $valore = $gestoreSession->get(self::DEBUG_HEADER_LOCATION_ENTRY);
        return $valore === true;
    }

    
    /**
     * Imposta il debug per la funzione header(location: <url>)
     * @param bool $enabled
     */
    public static function setDebugHeaderLocation(bool $enabled) : void {
        $gestoreSession = new GestoreSession();
        $gestoreSession->set(self::DEBUG_HEADER_LOCATION_ENTRY, $enabled);
    }


    /**
     * Inserisce i parametri "XDEBUG_SESSION_START=<val1>&KEY=<val2>"
     * ad un qualunque URL.
     * @param string $url
     */
     public static function transformURLDebugSymbols(string $url) : string {
        $urlComponents = parse_url($url);

        /*
         * Verifica se ci sono parametri, se sì, trasformali in array
         * per facilitare la gestione.
         */
        if (isset($urlComponents[self::QUERY])) {
            $params = array();
            parse_str($urlComponents[self::QUERY], $params);
            $urlComponents[self::QUERY] = $params;
        }

        /*
         * Stabilisci se è necessario aggiungere i parametri di Debug.
         * Serve ond'evitare di "patchare" url che già contengono i parametri di debug.
         */
        $needsDefinition = 
            (!isset($urlComponents[self::QUERY]) || (isset($urlComponents[self::QUERY]) &&
                !isset($urlComponents[self::QUERY]["XDEBUG_SESSION_START"])));


        if ($needsDefinition) {
            /*
             * Se ce n'è bisogno, procedi a "patchare"
             */
            if (!isset($urlComponents[self::QUERY])) {
                $urlComponents[self::QUERY] = array();
            }

            $urlComponents[self::QUERY]["XDEBUG_SESSION_START"] = DebugSettings::XDEBUG_SESSION_START_VALUE;
            $urlComponents[self::QUERY]["KEY"] = DebugSettings::XDEBUG_KEY_VALUE;
        }

        /*
         * Componi l'URL finale
         */
        $finalURL = "";
        if (isset($urlComponents[self::SCHEME])) {
            $finalURL .= sprintf("%s://", $urlComponents[self::SCHEME]);
        }
        if (isset($urlComponents[self::HOST])) {
            $finalURL .= $urlComponents[self::HOST];
        }
        if (isset($urlComponents[self::PORT])) {
            $finalURL .= sprintf(":%d", $urlComponents[self::PORT]);
        }
        if (isset($urlComponents[self::PATH])) {
            $finalURL .= $urlComponents["path"];
        }

        if (isset($urlComponents[self::QUERY])) {
            $pairs = array();

            foreach ($urlComponents[self::QUERY] as $key => $value) {
                $pairs[]= sprintf("%s=%s", $key, $value);
            }
            $finalURL .= "?" . implode("&", $pairs);
        }
        if (isset($urlComponents[self::FRAGMENT])) {
            $finalURL .= "#" . $urlComponents[self::FRAGMENT];
        }

        return $finalURL;
    }
}

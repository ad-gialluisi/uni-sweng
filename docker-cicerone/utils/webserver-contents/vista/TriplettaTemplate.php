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

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/template/Template.php";

use utils\template\Template;
use utils\Outputtabile;

/**
 * Rappresenta una <i>tripletta Template</i>.
 * <p>Per <i>tripletta Template</i> si intende una tripla di file.<br>
 * Dato il nome di template "template", la tripletta è rappresentata dai file:
 * <ul>
 * <li>template.html (che fornisce la rappresentazione HTML)</li>
 * <li>template.js (che fornisce la funzionalità, mediante javascript)</li>
 * <li>template.css (che fornisce la grafica, mediante fogli di stile)</li>
 * </ul>
 * 
 * <p>Questa classe consente il caricamento di <i>triplette Template</i> da
 * qualunque percorso relativo rispetto alla cartella radice del sito stesso.<br>
 * Di default utilizza il percorso "vista/template", poichè è dove risiedono i
 * vari template di questo sistema.</p>
 * 
 * <p>Nonostante il nome della classe, i file "template.js" e "template.css"
 * NON SONO obbligatori.<br>
 * Se non esistono, essi diventareanno Outputtabili vuoti (cioè, che quando
 * viene richiesto l'output, mostreranno una stringa vuota).</p>
 * 
 * <p>Lo scopo di questa classe è quello di semplificare il caricamento dei
 * file Template creati per il sistema Cicerone.<br>
 * Per questo sistema infatti è stato pensato di realizzare le schermate
 * mediante file Template che però contengono codice HTML.</p>
 * 
 * <p>I file Template HTML vengono infatti accompagnati anche da file javascript
 * e css che, aggiungono, nel caso di javascript, funzionalità di interazione
 * con gli elementi del file HTML e, nel caso di css, aggiungono stili agli
 * elementi grafici presenti.</p>
 * 
 * <p>Quasi tutti i file Template di questo sistema sono pensati come <i>triplette
 * Template</i>, sebbene non tutti abbiano i tre file previsti.<br>
 * Anche i file js e css possono essere a loro volta file Template che contengono
 * rispettivamente codice javascript e css.</p>
 * 
 * @see \utils\Outputtabile
 */
abstract class TriplettaTemplate implements Outputtabile {
    /**
     * Percorso di default (relativo) per raggiungere la cartella dei
     * file Template di questo sistema.
     * @var string
     */
    private const PERCORSO_RELATIVO_TEMPLATE = "vista/template";

    private const ESTENSIONE_HTML = "html";
    private const ESTENSIONE_JAVASCRIPT = "js";
    private const ESTENSIONE_CSS = "css";
    
    private const PERCORSO_ASSOLUTO = "assoluto";
    private const PERCORSO_RELATIVO = "relativo";
    

    /**
     * Questo elemento conterrà il file ".html", ovvero il file Template
     * html
     * @var Template
     */
    protected $html;


    /**
     * Questo elemento conterrà il file ".js", ovvero il file Template
     * javascript
     * @var Template
     */
    protected $jsScript;


    /**
     * Questo elemento conterrà il file ".css", ovvero il file Template
     * del foglio di stile
     * @var Template
     */
    protected $foglioStile;


    /**
     * Questo elemento contiene la "firma" della tripletta, un modo
     * per differenziarla dalle altre.
     * Il suo formato è: nomeTripletta"/"percorsoTripletta
     */
    protected $firma;
    

    /**
     * Costruisce una nuova tripletta Template.
     * @param string $nomeTripletta Il nome della <i>tripletta Template</i>
     * @param string $percorsoTripletta Dove si trova la tripletta
     * @param bool $templateJS <p>Se impostato su true, il file verrà caricata
     * in memoria, affinchè possa essere trattato come file Template.<br>
     * Se impostato su falso, non sarà possibile effettuare sostituzioni, poichè
     * verrà caricato come risorsa esterna (quindi non modificabile).</p>
     * @param bool $templateCSS idem come sopra
     */
    public function __construct(string $nomeTripletta, ?string $percorsoTripletta=NULL,
        bool $templateJS=false, bool $templateCSS=false) {

        $this->firma = sprintf("%s/%s", $percorsoTripletta !== NULL ? $percorsoTripletta : "",
            $nomeTripletta);

        $percorsi = $this->getPercorsiTripletta($nomeTripletta, $percorsoTripletta);

        $percorsoRelativoJS = $percorsi[self::ESTENSIONE_JAVASCRIPT][self::PERCORSO_RELATIVO];
        $percorsoRelativoCSS = $percorsi[self::ESTENSIONE_CSS][self::PERCORSO_RELATIVO];
        $percorsoAssolutoJS = $percorsi[self::ESTENSIONE_JAVASCRIPT][self::PERCORSO_ASSOLUTO];
        $percorsoAssolutoCSS = $percorsi[self::ESTENSIONE_CSS][self::PERCORSO_ASSOLUTO];
        $percorsoAssolutoHTML = $percorsi[self::ESTENSIONE_HTML][self::PERCORSO_ASSOLUTO];

        //Crea l'elemento outputtabile inerente il template HTML
        $this->html = Template::daFile($percorsoAssolutoHTML);

        if (file_exists($percorsoAssolutoJS)) {
            //se il file .js esiste
            if ($templateJS) {
                //Carica in memoria come stringa, affinchè si possano applicare sostiuzioni
                $this->jsScript = Template::daStringa(
                    sprintf("<script>%s</script>", file_get_contents($percorsoAssolutoJS)));
            } else {
                //Carica come risorsa esterna
                $this->jsScript = Template::daStringa(
                    sprintf("<script src=\"%s\"></script>", $percorsoRelativoJS));
            }
        } else {
            //Dato che non esiste, rendilo una stringa vuota
            $this->jsScript = Template::daStringa("");
        }


        //Similmente a ciò che è successo per il javascript
        if (file_exists($percorsoAssolutoCSS)) {
            if ($templateCSS) {
                $this->foglioStile =
                    Template::daStringa(sprintf("<style>%s</style>",
                        file_get_contents($percorsoAssolutoCSS)));
            } else {
                $this->foglioStile = Template::daStringa(
                     sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\"/>", $percorsoRelativoCSS));
            }
        } else {
            $this->foglioStile = Template::daStringa("");
        }
    }


    /**
     * Restituisce un riferimento all'Outputtabile inerente il file javascript
     * @return Outputtabile
     */
    public function getJSScript() : Outputtabile {
        return $this->jsScript;
    }


    /**
     * Restituisce un riferimento all'Outputtabile inerente il foglio di stile
     * @return Outputtabile
     */
    public function getFoglioStile() : Outputtabile {
        return $this->foglioStile;
    }


    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see \utils\Outputtabile::output()
     */
    public function output() : string {
        return $this->html->output();
    }


    /**
     * Effettua il reset delle chiavi impostate in tutti gli elementi
     * della tripletta.
     */
    public function clear() : void {
        $this->html->clear();
        $this->jsScript->clear();
        $this->foglioStile->clear();
    }


    /**
     * Restiuisce la firma di questa tripletta
     * @return string
     */
    public function getFirmaTripletta() : string {
        return $this->firma;
    }


    /**
     * Metodo d'ausilio che calcola i percorsi per i file della tripletta
     * @param string $nomeTripletta
     * @param string $percorsoTripletta
     * @return array
     */
    private function getPercorsiTripletta(string $nomeTripletta, ?string $percorsoTripletta=NULL) : array {
        //Se non è stato indicato in maniera precisa dove si trova, assumi PERCORSO_RELATIVO_TEMPLATE
        $percorsoRelativo = sprintf("%s/%s%s", self::PERCORSO_RELATIVO_TEMPLATE,
            ($percorsoTripletta === NULL ? "" : "$percorsoTripletta/"), $nomeTripletta);

        $chiavi = array(self::ESTENSIONE_HTML, self::ESTENSIONE_JAVASCRIPT, self::ESTENSIONE_CSS);
        $percorsiTemplate = array();

        foreach ($chiavi as $chiave) {
            $percorsiTemplate[$chiave] = array(
                self::PERCORSO_RELATIVO => sprintf("%s.%s", $percorsoRelativo,
                    $chiave),
                self::PERCORSO_ASSOLUTO => sprintf("%s/%s.%s", $_SERVER["DOCUMENT_ROOT"],
                    $percorsoRelativo, $chiave)
            );
        }

        return $percorsiTemplate;
    }
}

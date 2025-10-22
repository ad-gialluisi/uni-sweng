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
require_once "TriplettaTemplate.php";

use utils\CustomException;
use utils\template\Template;


/**
 * Eccezione sollevata quando viene utilizzato un componente non definito.
 */
class ComponenteTriplettaNonValidoException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Rappresenta un <i>template Tripletta</i> "semplice" (in contrapposizione agli
 * elementi più complessi come Popup e Layout che hanno un comportamento più
 * specifico).
 * Questo tipo di elemento può effettuare l'aggiunta di valori alle chiavi di un
 * componente qualunque della tripletta.
 */
class TriplettaSemplice extends TriplettaTemplate {
    public const HTML = "html";
    public const JAVASCRIPT = "javascript";
    public const CSS = "css";


    /**
     * Si aggiunge un valore ad una determinata chiave segnaposto del
     * componente specificato
     * @param string $chiave chiave segnaposto da impostare
     * @param mixed $valore valore da impostare
     * @param string $componente un valore tra HTML, JAVASCRIPT e CSS
     */
    public function add(string $chiave, $valore, string $componente) : void {
        $ref = $this->verificaComponente($componente);
        $ref->add($chiave, $valore);
    }


    /**
     * Applica una serie di coppie chiave-valore al componente specificato.
     * Valgono le stesse regole del metodo "applica" presente in Template.
     * pulsante.
     * @param array $coppie 
     * @param string $componente un valore tra HTML, JAVASCRIPT e CSS
     * @see Template::applica
     */
    public function applica(array $coppie, string $componente, bool $boolAsPulsanti=true) : void {
        $ref = $this->verificaComponente($componente);
        $ref->applica($coppie, $boolAsPulsanti);
    }
    
    
    /**
     * Consente l'impostazione di una chiave pulsante.
     * @param string $chiave chiave pulsante da impostare
     * @param bool $valore valore
     * @param string $componente un valore tra HTML, JAVASCRIPT e CSS
     */
    public function setPulsante(string $chiave, bool $valore, string $componente) : void {
        $ref = $this->verificaComponente($componente);
        $ref->setPulsante($chiave, $valore);
    }


    /**
     * Verifica che il nome del componente specificato sia valido
     * @param string $componente nome del componente della tripletta
     * @throws ComponenteTriplettaNonValidoException
     * @return Template Il riferimento al particolare componente richiesto
     */
    private function verificaComponente(string $componente) : Template {
        switch ($componente) {
            case self::HTML:
                $ref = $this->html;
            break;
            case self::JAVASCRIPT:
                $ref = $this->jsScript;
            break;
            case self::CSS:
                $ref = $this->foglioStile;
            break;
            default:
                throw new ComponenteTriplettaNonValidoException("Componente tripletta " .
                "\"%s\" sconosciuto.", $componente);
            break;
        }

        return $ref;
    }
}





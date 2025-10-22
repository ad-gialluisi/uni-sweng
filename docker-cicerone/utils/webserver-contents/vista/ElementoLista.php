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


require_once "TriplettaTemplate.php";


/**
 * Rappresenta un elemento di una lista qualunque.
 * Sfrutta una tripletta ben precisa, tuttavia, è possibile personalizzare
 * ulteriormente i contenuti dell'elemento.
 */
class ElementoLista extends TriplettaTemplate {
    /**
     * La tripletta che l'elemento lista visualizzerà
     */
    private $triplettaContenuta;

    /**
     * Crea un un nuovo elemento di una lista, fornendo il nome e il percorso
     * delle tripletta Template d utilizzare.
     */
    public function __construct(string $nomeTripletta, ?string $percorsoTripletta=NULL) {
        parent::__construct("elemento_lista", "layout");
        $this->triplettaContenuta = new TriplettaSemplice($nomeTripletta, $percorsoTripletta);
        $this->html->add("contenuto", $this->triplettaContenuta);
    }


    /**
     * Aggiunge un elemento come contenuto dell'elemento.
     * @param string $chiave
     * @param mixed $valore
     */
    public function add(string $chiave, $valore) : void {
        $this->triplettaContenuta->add($chiave, $valore, TriplettaSemplice::HTML);
    }

    
    /**
     * Applica una serie di coppie chiave-valore all'elemento
     * @param array $coppie
     * @param bool $boolAsPulsanti
     */
    public function applica(array $coppie, bool $boolAsPulsanti=true) : void {
        $this->triplettaContenuta->applica($coppie, TriplettaSemplice::HTML,
            $boolAsPulsanti);
    }


    /**
     * Imposta una certa chiave pulsante
     * @param string $chiave
     * @param bool $valore
     */
    public function setPulsante(string $chiave, bool $valore) : void {
        $this->triplettaContenuta->setPulsante($chiave, $valore, TriplettaSemplice::HTML);
    }
}


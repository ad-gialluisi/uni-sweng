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

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreProfilo.php";
require_once "Vista.php";
require_once "TriplettaSemplice.php";

use controllore\ControlloreProfilo;


/**
 * Rappresenta la schermata "chi siamo" del sito.
 * È una vista statica.
 */
class SchermataChiSiamo extends Vista {
    protected const PAGINA_VISTA = "chisiamo.php";

    /**
     * Schermata chi siamo (creata solo fare l'associazione con il
     * sistema di messaggistica)
     */
    private const SCHERMATA_CHI_SIAMO = "chi_siamo";
    
    
    /**
     * Costruisci un'istanza di SchermataChiSiamo utilizzando
     * un ControlloreProfilo.
     */
    public function __construct() {
        /*
         * Non è che cambi molto usare ControlloreProfilo o
         * ControlloreItinerario in questo caso.
         */
        parent::__construct(new ControlloreProfilo());
    }
    
   
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        $this->tripletta = new TriplettaSemplice("chi_siamo");
        $this->setTitolo("Chi siamo");

        /*
         * Associa la schermata ottenuta alla messaggistica, così da permettere
         * di mantenere i messaggi finchè non si lascia la pagina.
         */
        $this->controllore->associaSchermataPerMessaggistica(self::SCHERMATA_CHI_SIAMO);
        $this->mostraErrori();
    }


    /**
     * Restituisce l'URL di questa schermata
     * @return string
     */
    public static function getURLSchermataChiSiamo() : string {
        return self::PAGINA_VISTA;
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

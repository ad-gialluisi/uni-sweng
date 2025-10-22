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

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreTest.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once "TriplettaSemplice.php";
require_once "Vista.php";


use controllore\ControlloreTest;


/**
 * Classe vista di test
 */
class VistaTest extends Vista {
    protected const PAGINA_VISTA = "test.php";


    /**
     * Crea una VistaUtente con un ControlloreUtente sottostante
     */
    public function __construct() {
        parent::__construct(new ControlloreTest());
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
        /*
         * Distruggi tutta la messaggistica, dato che
         * stiamo facendo una richiesta nuova.
         */
        $this->controllore->distruggiMessaggistica();
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        $this->tripletta = new TriplettaSemplice("test", "debug");
            
        $elementoLista = new ElementoLista("elemento_itinerario", "form/itinerario");
        $this->associaTripletta($elementoLista);

        $this->schermataTest();
    }


    /**
     * Schermata di test.
     * C'è altro da dire?
     */
    private function schermataTest() : void {
        $popup = new Popup("testpopup");
        $popup->add("hello nurse!");

        $this->tripletta->add("testcontenuto", self::serverInfo(), TriplettaSemplice::HTML);
        $this->tripletta->add("popup", $popup, TriplettaSemplice::HTML);
        $this->tripletta->add("testcontenuto", getcwd(), TriplettaSemplice::HTML);

        $this->setTitolo("Pagina di test");
    }



    public static function serverInfo() : string {
        $strTable = '<h4>Dump di $_SERVER</h4>';
        $strTable .= '<table cellpadding="10">' ;
        foreach ($_SERVER as $key => $value) {
            $strTable .= '<tr><td>'.$key.'</td><td>' . $value . '</td></tr>' ;
        }
        
        return $strTable . '</table>';
    }
}

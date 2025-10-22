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


namespace controllore;

require_once "Controllore.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloTest.php";

use modello\ModelloTest;


/**
 * Controllore di Test, nient'altro da segnalare
 */
class ControlloreTest extends Controllore {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva elaborazione.
     */

    /*
     * Tipi di validazione
     */

    
    /*
     * Messaggi che appaiono in più zone
     */

    /**
     * Crea un nuovo ControlloreAccesso con un ModelloUtente sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloTest());
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected function validaParametri(array& $params, string $tipo) : bool {
        return false;
    }
}
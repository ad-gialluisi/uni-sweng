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


namespace utils\template\parser;

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/parser/Token.php";

use \utils\parser\Token;

/**
 * Classe token utilizzata per considerare i singoli lessemi.
 */
class TemplateToken extends Token {
    /**
     * Token di tipo CONTENUTO
     */
    public const CONTENUTO = self::EOI + 1;
    
    /**
     * Token di tipo CHIAVE_SEGNAPOSTO
     */
    public const CHIAVE_SEGNAPOSTO = self::EOI + 2;
    
    /**
     * Token di tipo CHIAVE_PULSANTE_OPEN
     */
    public const CHIAVE_PULSANTE_OPEN = self::EOI + 3;
    
    /**
     * Token di tipo CHIAVE_PULSANTE_CLOSE
     */
    public const CHIAVE_PULSANTE_CLOSE = self::EOI + 4;


    /**
     * Nomi dei token
     */
    public const NOMI = array(
        self::CONTENUTO => "CONTENUTO",
        self::CHIAVE_SEGNAPOSTO => "CHIAVE_SEGNAPOSTO",
        self::CHIAVE_PULSANTE_OPEN => "CHIAVE_PULSANTE_OPEN",
        self::CHIAVE_PULSANTE_CLOSE => "CHIAVE_PULSANTE_CLOSE",
        self::EOI => "EOI",
        self::SCONOSCIUTO => "SCONOSCIUTO"
    );

    
    /**
     * Restituisce una rappresentazione testuale di TemplateToken nella sua interezza.
     * @return string
     */
    public function __toString() : string {
        return sprintf("[%s, %d, %d] \"%s\"", self::NOMI[$this->getTipo()],
            $this->getRiga(), $this->getColonna(), $this->getValore());
    }
}
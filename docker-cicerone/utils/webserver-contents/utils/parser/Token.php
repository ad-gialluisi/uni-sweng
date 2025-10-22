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


namespace utils\parser;


/**
 * Classe token utilizzata per considerare i singoli lessemi.
 */
abstract class Token {
    public const EOI = -1;
    
    public const SCONOSCIUTO = -2;

    /**
     * Nomi dei token
     */
    public const NOMI = array(
        self::EOI => "EOI",
        self::SCONOSCIUTO => "SCONOSCIUTO"
    );
    
    
    
    /**
     * Tipo di token
     */
    private $tipo;
    
    /**
     * Valore del token (contenuto)
     * @var string
     */
    private $valore;
    
    /**
     * Riga dalla quale parte il token, nel documento
     * @var int
     */
    private $riga;
    
    
    /**
     * Colonna dalla quale parte il token, nel documento
     * @var int
     */
    private $colonna;
    
    
    /**
     *
     * @param int $tipo il tipo di token
     * @param ?string $valore il valore del token, può anche essere NULL
     * @param int $colonna la colonna nel file di testo da cui è stato estratto
     * @param int $riga la riga nel file di testo da cui è stato estratto
     */
    public function __construct(int $tipo, ?string $valore, int $colonna, int $riga) {
        $this->tipo = $tipo;
        $this->valore = $valore;
        $this->riga = $riga;
        $this->colonna = $colonna;
    }
    
    
    /**
     * Restituisce il tipo di Token
     * @return int
     */
    public function getTipo() : int {
        return $this->tipo;
    }


    /**
     * Restituisce il valore del Token
     * @return string
     */
    public function getValore() : string {
        return $this->valore;
    }
    
    
    /**
     * Restituisce la riga da cui è stato estratto il Token
     * @return int
     */
    public function getRiga() : int {
        return $this->riga;
    }
    
    
    /**
     * Restituisce la colonna da cui è stato estratto il Token
     * @return int
     */
    public function getColonna() : int {
        return $this->colonna;
    }
}
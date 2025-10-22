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


namespace modello\entità;

require_once "EntitàDB.php";


/**
 * Classe che rappresenta una qualunque Richiesta d'amministrazione
 * (tabelle RichiestaDisiscrizione e RichiestaAggiornamento)
 */
abstract class RichiestaAmministrazione extends EntitàDB {
    private $id;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public abstract static function daArray(array $coppie) : EntitàDB;


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public abstract function setDaArray(array $coppie) : void;
    
    
    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }

    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::jsonSerialize()
     */
    public function jsonSerialize() {
        return array();
    }
}
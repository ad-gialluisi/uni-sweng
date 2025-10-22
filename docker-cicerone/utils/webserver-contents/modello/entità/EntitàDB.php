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


use \JsonSerializable;


/**
 * Classe che rappresenta una qualunque entità presente nel DB.
 * Ogni entità particolare deve ereditare da questa.
 */
abstract class EntitàDB implements JsonSerializable {
    public const NOME_TABELLA = "EntitàDB";
    
    /**
     * Crea l'entità particolare partendo da un array che possiede delle coppie
     * "chiave-valore".
     * @see EntitàDB::setDaArray
     * @param array $coppie
     * @return \modello\entità\EntitàDB
     */
    public abstract static function daArray(array $coppie) : EntitàDB;

    /**
     * Imposta i dati della particolare entità, in base ad una array associativo
     * che contiene delle coppie "chiave-valore".
     * È importante notare che vengono impostate solo le proprietà che corrispondono
     * a chiavi dell'array che sono proprietà valide dell'entità, le altre vengono
     * ignorate.
     * @param array $coppie l'array contenente delle coppie "chiave-valore".
     */
    public abstract function setDaArray(array $coppie) : void;
    
    
    /**
     * Questo metodo va utilizzato all'interno di "setDaArray".
     * Lo scopo è quello d'automatizzare l'impostazione delle proprietà della particolare
     * figlia di EntitàDB, in base alle chiavi presenti in $coppie E $campiDBSetters
     * @param array $campiDBSetters un array associativo che contiene le coppie "colonna db" => "metodo setter"
     * @param array $coppie un array associativo che contiene le coppie "colonna db" => "valore proprietà"
     */
    protected function applicaCampiDBSetters(array $campiDBSetters, array $coppie) : void {
        foreach ($campiDBSetters as $campoDB => $setter) {
            $isSetStd = isset($coppie[$campoDB]);

            //Riconosci se è impostato anche il nome della tabella nelle proprietà.
            $isSetTab = isset($coppie[static::NOME_TABELLA . "_" . $campoDB]);

            if ($isSetStd || $isSetTab) {
                $this->$setter($isSetStd ? $coppie[$campoDB] : $coppie[static::NOME_TABELLA . "_" . $campoDB]);
            }
        }
    }


    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see JsonSerializable::jsonSerialize()
     */
    public abstract function jsonSerialize();
}
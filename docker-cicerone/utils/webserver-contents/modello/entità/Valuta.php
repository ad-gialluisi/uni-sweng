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
 * Classe che rappresenta una singola riga di database della
 * tabella Valuta
 */
class Valuta extends EntitàDB {
    public const NOME_TABELLA = "Valuta";
    
    /*
     * Proprietà della tabella Valuta
     */
    private $valuta;
    private $centesimale;
    private $simbolo;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Valuta
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Valuta();
        $entità->setDaArray($coppie);
        return $entità;
    }
    
    
    /*
     * Getters
     */
    public function getValuta() : string {
        return $this->valuta;
    }

    public function getCentesimale() : bool {
        return $this->centesimale;
    }
    
    public function getSimbolo() : string {
        return $this->simbolo;
    }
    
    
    /*
     * Setters
     */
    public function setValuta(string $valuta) : void {
        $this->valuta = $valuta;
    }

    public function setCentesimale(bool $centesimale) : void {
        $this->centesimale = $centesimale;
    }
    
    public function setSimbolo(string $simbolo) : void {
        $this->simbolo = $simbolo;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "valuta" => "setValuta",
            "centesimale" => "setCentesimale",
            "simbolo" => "setSimbolo"
        );

        $this->applicaCampiDBSetters($campiDBSetters, $coppie);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::jsonSerialize()
     */
    public function jsonSerialize() {
        $corrispondenzaProprietà = array(
            "valuta", "centesimale", "simbolo"
        );

        $arr = array();
        foreach ($corrispondenzaProprietà as $proprietà) {
            if ($this->$proprietà !== NULL) {
                $arr[$proprietà] = $this->$proprietà;
            }
        }

        return $arr;
    }
}
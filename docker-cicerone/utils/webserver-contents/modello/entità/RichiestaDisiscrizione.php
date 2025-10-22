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
 * Classe che rappresenta una singola riga di database della tabella
 * RichiestaDisiscrizione
 */
class RichiestaDisiscrizione extends RichiestaAmministrazione {
    public const NOME_TABELLA = "RichiestaDisiscrizione";
    
    /*
     * Proprietà della tabella RichiestaDisiscrizione
     */
    private $idFruitore;
    private $fruitore;
    private $descrizione;
    
    
    /**
     * Metodo ereditato
     * @param array $coppie
     * @return RichiestaDisiscrizione
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new RichiestaDisiscrizione();
        $entità->setDaArray($coppie);
        $entità->setFruitore(Utente::daArray($coppie));
        return $entità;
    }


    /*
     * Getters
     */
    public function getIDFruitore() : int {
        return $this->idFruitore;
    }
    
    public function getFruitore() : Utente {
        return $this->fruitore;
    }

    public function getDescrizione() : string {
        return $this->descrizione;
    }



    /*
     * Setters
     */
    public function setIDFruitore(int $idFruitore) : void {
        $this->idFruitore = $idFruitore;
    }

    public function setFruitore(Utente $fruitore) : void {
        $this->fruitore = $fruitore;
    }
    
    public function setDescrizione(string $descrizione) : void {
        $this->descrizione = $descrizione;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "id" => "setID",
            "id_fruitore" => "setidFruitore",
            "descrizione" => "setDescrizione",
        );

        $this->applicaCampiDBSetters($campiDBSetters, $coppie);
    }
}
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
 * RichiestaAggiornamento
 */
class RichiestaAggiornamento extends RichiestaAmministrazione {
    public const NOME_TABELLA = "RichiestaAggiornamento";
    
    /*
     * Proprietà della tabella RichiestaAggiornamento
     */
    private $idAnagrafica;
    private $accordata;
    
    
    /**
     * Metodo ereditato
     * @param array $coppie
     * @return RichiestaAggiornamento
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new RichiestaAggiornamento();
        $entità->setDaArray($coppie);
        $entità->setAnagrafica(Anagrafica::daArray($coppie));
        return $entità;
    }
    
    
    /*
     * Getters
     */
    public function getIDAnagrafica() : int {
        return $this->idAnagrafica;
    }

    public function getAnagrafica() : Anagrafica {
        return $this->anagrafica;
    }

    public function getAccordata() : bool {
        return $this->accordata;
    }
    
    
    
    /*
     * Setters
     */
    public function setIDAnagrafica(int $idAnagrafica) : void {
        $this->idAnagrafica = $idAnagrafica;
    }

    public function setAnagrafica(Anagrafica $anagrafica) : void {
        $this->anagrafica = $anagrafica;
    }

    public function setAccordata(bool $accordata) : void {
        $this->accordata = $accordata;
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "id" => "setID",
            "id_anagrafica" => "setIDAnagrafica",
            "accordata" => "setAccordata",
        );

        $this->applicaCampiDBSetters($campiDBSetters, $coppie);
    }
}
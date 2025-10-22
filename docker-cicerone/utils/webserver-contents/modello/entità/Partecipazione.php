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
 * Classe che rappresenta una singola riga di database della tabella Partecipazione
 */
class Partecipazione extends EntitàDB {
    public const NOME_TABELLA = "Partecipazione";
    
    /*
     * Questi sono gli stati possibili della Partecipazione (richiesta di partecipazione).
     */
    public const STATO_ACCORDANDA = "accordanda";
    public const STATO_ACCORDATA = "accordata";
    public const STATO_ANNULLANDA = "annullanda";
    
    /*
     * Proprietà della tabella Partecipazione
     */
    private $id;
    private $idItinerario;
    private $itinerario;
    private $idPartecipante;
    private $partecipante;
    private $stato;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Partecipazione();
        $entità->setDaArray($coppie);
        $entità->setItinerario(Itinerario::daArray($coppie));
        $entità->setPartecipante(Utente::daArray($coppie));
        return $entità;
    }
    
    
    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }
    
    public function getIDItinerario() : int {
        return $this->idItinerario;
    }
    
    public function getItinerario() : Itinerario {
        return $this->itinerario;
    }
    
    public function getIDPartecipante() : ?int {
        return $this->idPartecipante;
    }

    public function getPartecipante() : Utente {
        return $this->partecipante;
    }
    
    public function getStato() : ?string {
        return $this->stato;
    }


    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }
    
    public function setIDItinerario(int $idItinerario) : void {
        $this->idItinerario = $idItinerario;
    }
    
    public function setItinerario(Itinerario $itinerario) : void {
        $this->itinerario = $itinerario;
    }
    
    public function setIDPartecipante(?int $idPartecipante) : void {
        $this->idPartecipante = $idPartecipante;
    }

    public function setPartecipante(Utente $partecipante) : void {
        $this->partecipante = $partecipante;
    }
    
    public function setStato(?string $stato) : void {
        $this->stato = $stato;
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "id" => "setID",
            "id_itinerario" => "setIDItinerario",
            "id_partecipante" => "setIDPartecipante",
            "stato" => "setStato",
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
            "id", "idItinerario", "idPartecipante", "stato"
        );

        $arr = array();
        foreach ($corrispondenzaProprietà as $proprietà) {
            if ($this->$proprietà !== NULL) {
                $arr[$proprietà] = $this->$proprietà;
            }
        }

        if ($this->itinerario !== NULL) {
            $arr["itinerarioInstance"] = $this->itinerario->jsonSerialize();
        }

        if ($this->partecipante !== NULL) {
            $arr["partecipanteInstance"] = $this->partecipante->jsonSerialize();
        }

        return $arr;
    }
}
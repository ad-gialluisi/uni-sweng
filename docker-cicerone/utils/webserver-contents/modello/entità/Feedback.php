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
 * Classe che rappresenta una singola riga di database della tabella Feedback
 */
class Feedback extends EntitàDB {
    public const NOME_TABELLA = "Feedback";
    
    /*
     * Questi sono i tipi possibili di feedback
     * (corrispondono alla tabella TipoFeedback).
     */
    public const TIPO_ORGANIZZATORE_PARTECIPANTE = "organizzatore-partecipante";
    public const TIPO_PARTECIPANTE_ORGANIZZATORE = "partecipante-organizzatore";


    /*
     * Proprietà della tabella Feedback
     */
    private $id;
    private $idItinerario;
    private $itinerario;
    private $idPartecipante;
    private $partecipante;
    private $descrizione;
    private $voto;
    private $tipo;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Feedback
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Feedback();
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

    public function getIDPartecipante() : int {
        return $this->idPartecipante;
    }
    
    public function getPartecipante() : Utente {
        return $this->partecipante;
    }

    public function getDescrizione() : string {
        return $this->descrizione;
    }

    public function getVoto() : string {
        return $this->voto;
    }
    
    public function getTipo() : string {
        return $this->tipo;
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
    
    public function setIDPartecipante(int $idPartecipante) : void {
        $this->idPartecipante = $idPartecipante;
    }
    
    public function setPartecipante(Utente $partecipante) : void {
        $this->partecipante = $partecipante;
    }
    
    public function setDescrizione(string $descrizione) : void {
        $this->descrizione = $descrizione;
    }
    
    public function setVoto(int $voto) : void {
        $this->voto = $voto;
    }
    
    public function setTipo(string $tipo) : void {
        $this->tipo = $tipo;
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
            "descrizione" => "setDescrizione",
            "voto" => "setVoto",
            "tipo" => "setTipo",
        );
        
        $this->applicaCampiDBSetters($campiDBSetters, $coppie);
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
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
 * Classe che rappresenta una singola riga di database della tabella Anagrafica
 */
class Anagrafica extends EntitàDB {
    public const NOME_TABELLA = "Anagrafica";
    
    /*
     * Proprietà della tabella Anagrafica
     */
    private $id;
    private $idCicerone;
    private $cicerone;
    private $nome;
    private $cognome;
    private $dataNascita;
    private $luogoNascita;
    private $residenza;
    private $telefono;
    private $codiceFiscale;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Anagrafica();
        $entità->setDaArray($coppie);
        $entità->setCicerone(Utente::daArray($coppie));
        return $entità;
    }


    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }
    
    public function getIDCicerone() : int {
        return $this->idCicerone;
    }

    public function getCicerone() : Utente {
        return $this->cicerone;
    }

    public function getNome() : string {
        return $this->nome;
    }
    
    public function getCognome() : string {
        return $this->cognome;
    }
    
    public function getDataNascita() : string {
        return $this->dataNascita;
    }
    
    public function getLuogoNascita() : string {
        return $this->luogoNascita;
    }
    
    public function getResidenza() : string {
        return $this->residenza;
    }
 
    public function getTelefono() : string {
        return $this->telefono;
    }

    public function getCodiceFiscale() : string {
        return $this->codiceFiscale;
    }

    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }

    public function setCicerone(Utente $cicerone) : void {
        $this->cicerone = $cicerone;
    }
    
    public function setIDCicerone(int $idCicerone) : void {
        $this->idCicerone = $idCicerone;
    }
    
    public function setNome(string $nome) : void {
        $this->nome = $nome;
    }
    
    public function setCognome(string $cognome) : void {
        $this->cognome = $cognome;
    }

    public function setDataNascita(string $dataNascita) : void {
        $this->dataNascita = $dataNascita;
    }

    public function setLuogoNascita(string $luogoNascita) : void {
        $this->luogoNascita = $luogoNascita;
    }

    public function setResidenza(string $residenza) : void {
        $this->residenza = $residenza;
    }

    public function setTelefono(string $telefono) : void {
        $this->telefono = $telefono;
    }

    public function setCodiceFiscale(string $codiceFiscale) : void {
        $this->codiceFiscale = $codiceFiscale;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "id" => "setID",
            "id_cicerone" => "setIDCicerone",
            "nome" => "setNome",
            "cognome" => "setCognome",
            "data_nascita" => "setDataNascita",
            "luogo_nascita" => "setLuogoNascita",
            "residenza" => "setResidenza",
            "telefono" => "setTelefono",
            "codice_fiscale" => "setCodiceFiscale"
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
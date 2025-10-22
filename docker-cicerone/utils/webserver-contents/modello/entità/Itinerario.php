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
 * Classe che rappresenta una singola riga di database della tabella Itinerario
 */
class Itinerario extends EntitàDB {
    public const NOME_TABELLA = "Itinerario";

    /*
     * Questi sono gli stati possibili dell'Itinerario.
     */
    public const STATO_APERTO = "aperto";
    public const STATO_ITINERE = "itinere";
    public const STATO_CONCLUSO = "concluso";
    public const STATO_CHIUSO = "chiuso";


    /*
     * Proprietà della tabella Itinerario
     */
    private $id;
    private $nome;
    private $idCicerone;
    private $cicerone;
    private $immagine;
    private $data;
    private $luogo;
    private $lingua;
    private $descrizione;
    private $popolarità;
    private $nomeValuta;
    private $valuta;
    private $compenso;
    private $stato;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Itinerario();
        $entità->setDaArray($coppie);
        $entità->setCicerone(Utente::daArray($coppie));
        $entità->setValuta(Valuta::daArray($coppie));
        return $entità;
    }
    
    
    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }
    
    public function getNome() : string {
        return $this->nome;
    }
    
    public function getIDCicerone() : int {
        return $this->idCicerone;
    }
    
    public function getCicerone() : Utente {
        return $this->cicerone;
    }
    
    public function getData() : string {
        return $this->data;
    }
    
    public function getImmagine() : string {
        return $this->immagine;
    }
    
    public function getLuogo() : string {
        return $this->luogo;
    }
    
    public function getLingua() : string {
        return $this->lingua;
    }
    
    public function getDescrizione() : string {
        return $this->descrizione;
    }
    
    public function getPopolarità() : int {
        return $this->popolarità;
    }
    
    public function getNomeValuta() : string {
        return $this->nomeValuta;
    }
    
    public function getValuta() : Valuta {
        return $this->valuta;
    }
    
    public function getCompenso() : int {
        return $this->compenso;
    }

    public function getStato() : string {
        return $this->stato;
    }

    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }
    
    public function setNome(string $nome) : void {
        $this->nome = $nome;
    }
    
    public function setIDCicerone(int $idCicerone) : void {
        $this->idCicerone = $idCicerone;
    }
    
    public function setCicerone(Utente $cicerone) : void {
        $this->cicerone = $cicerone;
    }
    
    public function setData(string $data) : void {
        $this->data = $data;
    }
    
    public function setImmagine(string $immagine) : void {
        $this->immagine = $immagine;
    }
    
    public function setLuogo(string $luogo) : void {
        $this->luogo = $luogo;
    }
    
    public function setLingua(string $lingua) : void {
        $this->lingua = $lingua;
    }
    
    public function setDescrizione(string $descrizione) : void {
        $this->descrizione = $descrizione;
    }
    
    public function setPopolarità(string $popolarità) : void {
        $this->popolarità = $popolarità;
    }
    
    public function setNomeValuta(string $nomeValuta) : void {
        $this->nomeValuta = $nomeValuta;
    }
    
    public function setValuta(Valuta $valuta) : void {
        $this->valuta = $valuta;
    }
    
    public function setCompenso(int $compenso) : void {
        $this->compenso = $compenso;
    }
    
    public function setStato(string $stato) : void {
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
            "nome" => "setNome",
            "id_cicerone" => "setIDCicerone",
            "data" => "setData",
            "immagine" => "setImmagine",
            "luogo" => "setLuogo",
            "lingua" => "setLingua",
            "descrizione" => "setDescrizione",
            "popolarità" => "setPopolarità",
            "valuta" => "setNomeValuta",
            "compenso" => "setCompenso",
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
            "id", "nome", "idCicerone", "data", "immagine",
            "luogo", "lingua", "descrizione", "popolarità",
            "nomeValuta", "compenso", "stato",
        );

        $arr = array();
        foreach ($corrispondenzaProprietà as $proprietà) {
            if ($this->$proprietà !== NULL) {
                $arr[$proprietà] = $this->$proprietà;
            }
        }
        
        if ($this->valuta !== NULL) {
            $arr["valutaInstance"] = $this->valuta->jsonSerialize();
        }

        if ($this->cicerone !== NULL) {
            $arr["ciceroneInstance"] = $this->cicerone->jsonSerialize();
        }

        return $arr;
    }
}
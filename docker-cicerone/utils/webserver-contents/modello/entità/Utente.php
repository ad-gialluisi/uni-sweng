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
 * tabella Utente
 */
class Utente extends EntitàDB {
    public const NOME_TABELLA = "Utente";

    /*
     * Proprietà della tabella Utente
     */
    private $id;
    private $email;
    private $nomeUtente;
    private $password;
    private $descrizione;
    private $immagine;
    private $tipo;
    private $stato;


    /*
     * Questi sono i tipi possibili di utente
     * (corrispondono alla tabella TipoUtente).
     */
    public const TIPO_GLOBETROTTER = "globetrotter";
    public const TIPO_QUASICICERONE = "quasicicerone";
    public const TIPO_CICERONE = "cicerone";
    public const TIPO_AMMINISTRATORE = "amministratore";


    /*
     * Questi sono gli stati interni possibili di un utente
     * (corrispondono alla tabella StatoUtente).
     */
    public const STATO_INSERITO = "inserito";
    public const STATO_ATTIVATO = "attivato";
    public const STATO_RECUPERANDO = "recuperando";
    

    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $utente = new Utente();
        $utente->setDaArray($coppie);
        return $utente;
    }


    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }
    
    public function getEmail() : string {
        return $this->email;
    }

    public function getNomeUtente() : string {
        return $this->nomeUtente;
    }
    
    public function getPassword() : string {
        return $this->password;
    }

    public function getDescrizione() : string {
        return $this->descrizione;
    }
    
    public function getImmagine() : string {
        return $this->immagine;
    }
    
    public function getTipo() : string {
        return $this->tipo;
    }
 
    public function getStato() : string {
        return $this->stato;
    }
    
    public function getCodiceAttivazione() : string {
        return $this->codiceAttivazione;
    }

    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }

    public function setEmail(string $email) : void {
        $this->email = $email;
    }
    
    public function setNomeUtente(string $nomeUtente) : void {
        $this->nomeUtente = $nomeUtente;
    }
    
    public function setPassword(string $password) : void {
        $this->password = $password;
    }

    public function setDescrizione(string $descrizione) : void {
        $this->descrizione = $descrizione;
    }

    public function setImmagine(string $immagine) : void {
        $this->immagine = $immagine;
    }

    public function setTipo(string $tipo) : void {
        $this->tipo = $tipo;
    }

    public function setStato(string $stato) : void {
        $this->stato = $stato;
    }

    public function setCodiceAttivazione(string $codiceAttivazione) : void {
        $this->codiceAttivazione = $codiceAttivazione;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $campiDBSetters = array(
            "id" => "setID",
            "email" => "setEmail",
            "nome_utente" => "setNomeUtente",
            "password" => "setPassword",
            "descrizione" => "setDescrizione",
            "immagine" => "setImmagine",
            "tipo" => "setTipo",
            "stato" => "setStato",
            "codice_attivazione" => "setCodiceAttivazione"
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
            "id", "email", "nomeUtente", "descrizione", "immagine",
            "tipo", "stato"
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
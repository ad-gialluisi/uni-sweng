<?php

namespace modello\entità;

require_once "EntitàDB.php";


/**
 * Classe che rappresenta una singola riga di database della tabella Notifica
 */
class Notifica extends EntitàDB {
    /*
     * Proprietà della tabella Notifica
     */
    private $id;
    private $idUtente;
    private $descrizione;
    private $link;
    private $letta;
    private $dataCreazione;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new Notifica();
        $entità->setDaArray($coppie);
        return $entità;
    }


    /*
     * Getters
     */
    public function getID() : int {
        return $this->id;
    }
    
    public function getIDUtente() : int {
        return $this->idUtente;
    }

    public function getDescrizione() : string {
        return $this->descrizione;
    }
    
    public function getLink() : string {
        return $this->link;
    }
    
    public function getLetta() : bool {
        return $this->letta;
    }
    
    public function getDataCreazione() : string {
        return $this->dataCreazione;
    }


    /*
     * Setters
     */
    public function setID(int $id) : void {
        $this->id = $id;
    }

    public function setIDUtente(int $idUtente) : void {
        $this->idUtente = $idUtente;
    }
    
    public function setDescrizione(string $descrizione) : void {
        $this->descrizione = $descrizione;
    }
    
    public function setLink(string $link) : void {
        $this->link = $link;
    }

    public function setLetta(bool $letta) : void {
        $this->letta = $letta;
    }
    
    public function setDataCreazione(string $dataCreazione) : void {
        $this->dataCreazione = $dataCreazione;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $corrispondenzaCampiDBSetters = array(
            "id" => "ID",
            "id_utente" => "IDUtente",
            "descrizione" => "Descrizione",
            "link" => "Link",
            "letta" => "Letta",
            "data_creazione" => "DataCreazione"
        );

        foreach ($corrispondenzaCampiDBSetters as $campoDB => $setter) {
            if (isset($coppie[$campoDB])) {
                $setter = "set$setter";
                $this->$setter($coppie[$campoDB]);
            }
        }
    }
    
    
    public function toArray() : array {
        $corrispondenzaCampiDBGetters = array(
            "id" => "ID",
            "id_utente" => "IDUtente",
            "descrizione" => "Descrizione",
            "link" => "Link",
            "letta" => "Letta",
            "data_creazione" => "DataCreazione"
        );
        
        $arr = array();
        
        foreach ($corrispondenzaCampiDBGetters as $campoDB => $getter) {
            $getter = "get$getter";
            $arr[$campoDB] = $this->$getter();
        }

        return $arr;
    }
}
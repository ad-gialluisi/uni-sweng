<?php

namespace modello\entità;

require_once "EntitàDB.php";


/**
 * Classe che rappresenta una singola riga di database della tabella PreferenzeNotifica
 */
class PreferenzeNotifica extends EntitàDB {
    /*
     * Proprietà della tabella PreferenzeNotifica
     */
    private $id;
    private $idUtente;
    private $partecipazioneItinerario;
    private $annullamentoItinerario;
    private $declinoItinerario;
    private $ricezioneFeedback;
    private $viaMail;


    /**
     * Metodo ereditato
     * @param array $coppie
     * @return Anagrafica
     */
    public static function daArray(array $coppie) : EntitàDB {
        $entità = new PreferenzeNotifica();
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

    public function getPartecipazioneItinerario() : bool {
        return $this->partecipazioneItinerario;
    }
    
    public function getAnnullamentoItinerario() : bool {
        return $this->annullamentoItinerario;
    }
    
    public function getDeclinoItinerario() : bool {
        return $this->declinoItinerario;
    }
    
    public function getRicezioneFeedback() : bool {
        return $this->ricezioneFeedback;
    }
    
    public function getViaMail() : bool {
        return $this->viaMail;
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
    
    public function setPartecipazioneItinerario(bool $partecipazioneItinerario) : void {
        $this->partecipazioneItinerario = $partecipazioneItinerario;
    }
    
    public function setAnnullamentoItinerario(bool $annullamentoItinerario) : void {
        $this->annullamentoItinerario = $annullamentoItinerario;
    }

    public function setDeclinoItinerario(bool $declinoItinerario) : void {
        $this->declinoItinerario = $declinoItinerario;
    }

    public function setRicezioneFeedback(bool $ricezioneFeedback) : void {
        $this->ricezioneFeedback = $ricezioneFeedback;
    }

    public function setViaMail(bool $viaMail) : void {
        $this->viaMail = $viaMail;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \modello\entità\EntitàDB::setDaArray()
     */
    public function setDaArray(array $coppie) : void {
        $corrispondenzaCampiDBSetters = array(
            "id" => "ID",
            "id_fruitore" => "IDUtente",
            "partecipazione_itinerario" => "PartecipazioneItinerario",
            "annullamento_itinerario" => "AnnullamentoItinerario",
            "declino_itinerario" => "DeclinoItinerario",
            "ricezione_feedback" => "RicezioneFeedback",
            "via_mail" => "ViaMail",
        );

        foreach ($corrispondenzaCampiDBSetters as $campoDB => $setter) {
            if (isset($coppie[$campoDB])) {
                $setter = "set$setter";
                $this->$setter($coppie[$campoDB]);
            }
        }
    }
}
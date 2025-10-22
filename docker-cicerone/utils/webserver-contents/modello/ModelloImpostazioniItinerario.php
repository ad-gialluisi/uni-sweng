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


namespace modello;

require_once "Modello.php";

require_once "entità/Itinerario.php";
require_once "entità/Valuta.php";
require_once "entità/Utente.php";

use modello\entità\Itinerario;
use modello\entità\Utente;
use modello\entità\Valuta;


/**
 * Macro-modello che raggruppa tutte le funzionalità comuni utili
 * per gestire gli itinerari.
 */
abstract class ModelloImpostazioniItinerario extends Modello {
    /**
     * Stato raggiunto quando un certo itinerario non viene trovato
     */
    public const STATO_ITINERARIO_NON_TROVATO = 3;
    
    /**
     * Stato raggiunto quando un certo itinerario viene trovato
     */
    public const STATO_ITINERARIO_TROVATO = 4;



    /**
     * Stabilisce se un certo itinerario è stato organizzato da un certo Cicerone
     * @param int $idItinerario id dell'itinerario
     * @param int $idCicerone id del Cicerone
     * @return bool true, se trattasi del Cicerone organizzatore, false al contrario
     */
    public function isCiceroneOrganizzatoreDiItinerario(int $idItinerario, int $idCicerone) : bool {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select 1 from Itinerario where id = ? and " .
            "id_cicerone = ?", $idItinerario, $idCicerone);
        
        $this->ciceroneDatabase->chiudi();
        
        return count($righe) === 1;
    }


    /**
     * Restituisce lo stato della richiesta di partecipazione di un certo
     * fruitore ad un particolare itinerario
     * @param int $idItinerario l'id dell'itinerario
     * @param int $idFruitore l'id del fruitore
     * @return string|NULL restituisce la stringa che rappresenta lo stato della
     * richiesta di partecipazione, o NULL, se non esiste alcuna richiesta di
     * partecipazione fatta dal suddetto fruitore.
     */
    public function getStatoPartecipazioneFruitoreAdItinerario(int $idItinerario,
        int $idFruitore) : ?string {
            $this->ciceroneDatabase->apri();

            $righe = $this->ciceroneDatabase->query("select Partecipazione.stato as stato from " . 
                "Itinerario, Partecipazione where Itinerario.id = Partecipazione.id_itinerario and " . 
                "Itinerario.id = ? and Partecipazione.id_partecipante = ?",
                $idItinerario, $idFruitore);

            $nRighe = count($righe);
            
            if ($nRighe === 1) {
                $stato = $righe[0]["stato"];
            } else {
                $stato = NULL;
            }
            
            $this->ciceroneDatabase->chiudi();
            
            return $stato;
    }


    /**
     * Restituisce un'istanza di Itinerario dato il suo id.
     * @param int $id
     * @param Itinerario $itinerario
     * @return int lo stato dell'operazione, che può essere: STATO_ITINERARIO_NON_TROVATO,
     * STATO_ITINERARIO_TROVATO
     */
    public function getItinerario(int $id, ?Itinerario& $itinerario) : int {
        $this->ciceroneDatabase->apri();
        
        $itinerario = NULL;
        
        $colonneUtente = array("id", "nome_utente");
        $colonneValuta = array("valuta", "centesimale", "simbolo");
        $colonneItineario = array("id", "nome", "id_cicerone", "data", "descrizione",
            "immagine", "lingua", "luogo", "popolarità", "valuta", "compenso", "stato"
        );

        $query = self::creaQueryConColonneRiconoscibili(
            "select %s from Itinerario, Utente, Valuta where " .
            "id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta " .
            "and Itinerario.id = ?",
            array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneItineario, $colonneValuta, $colonneUtente)
        );
        
        $righe = $this->ciceroneDatabase->query($query, $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_ITINERARIO_NON_TROVATO;

        if ($nRighe === 1) {
            $itinerario = Itinerario::daArray($righe[0]);
            $codiceStato = self::STATO_ITINERARIO_TROVATO;
        }
        
        $this->ciceroneDatabase->chiudi();
        
        return $codiceStato;
    }
}
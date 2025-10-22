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


namespace controllore;

require_once "Controllore.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloImpostazioniItinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";


use modello\ModelloImpostazioniItinerario;
use modello\entità\Itinerario;


/**
 * Macro-controllore che raggruppa tutte le funzionalità comuni utili
 * per gestire gli itinerari.
 */
abstract class ControlloreImpostazioniItinerario extends Controllore {
    /*
     * Campi comuni ai controllori che gestiscono gli utenti
     */
    /**
     * Campo id dell'itinerario (utilizzato in quelle operazioni
     * che richiedono due o più id).
     */
    public const CAMPO_ID_ITINERARIO = "idItinerario";

    /**
     * Campo id del partecipante (utilizzato in quelle operazioni
     * che richiedono due o più id)
     */
    public const CAMPO_ID_PARTECIPANTE = "idPartecipante";


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected abstract function validaParametri(array& $params, string $tipo) : bool;


    /**
     * Costruisce un nuovo ControlloreImpostazioniItinerario con un'istanza
     * di ModelloImpostazioniItinerario
     * @param ModelloImpostazioniItinerario $modello
     */
    public function __construct(?ModelloImpostazioniItinerario $modello) {
        parent::__construct($modello);
    }


    /**
     * Stabilisce se un certo itinerario è stato organizzato da un certo Cicerone
     * @param int $idItinerario id dell'itinerario
     * @param int $idCicerone id del Cicerone
     * @return bool true, se trattasi del Cicerone organizzatore, false al contrario
     */
    public function isCiceroneOrganizzatoreDiItinerario(int $idItinerario, int $idCicerone) : bool {
        return $this->modello->isCiceroneOrganizzatoreDiItinerario($idItinerario, $idCicerone);
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
    public function getStatoPartecipazioneFruitoreAdItinerario(int $idItinerario, int $idFruitore) : ?string {
            return $this->modello->getStatoPartecipazioneFruitoreAdItinerario($idItinerario, $idFruitore);
    }


    /**
     * Restituisce un'istanza di Itinerario dato il suo id.
     * @param int $id
     * @param Itinerario $itinerario
     */
    public function richiediItinerario(int $id, ?Itinerario& $itinerario) : void {
        $this->modello->getItinerario($id, $itinerario);
    }
}
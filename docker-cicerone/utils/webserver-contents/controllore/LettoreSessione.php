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

/**
 * Quest'interfaccia definisce quali sono i metodi che caratterizzano
 * una classe capace di leggere i dati di una Sessione.
 * @see Sessione
 */
interface LettoreSessione {
    /**
     * Restituisce l'id dell'utente collegato
     * @return int|NULL
     */
    public function getIDUtente() : ?int;
    
    /**
     * Restituisce il nome utente
     * @return string|NULL
     */
    public function getNomeUtente() : ?string;
    
    /**
     * Restituisce il tipo dell'utente
     * @return string|NULL
     */
    public function getTipoUtente() : ?string;
    
    /**
     * Restituisce lo stato dell'utente
     * @return string|NULL
     */
    public function getStatoUtente() : ?string;

    /**
     * Restituisce il percorso (parziale) all'immagine dell'utente
     * @return string|NULL
     */
    public function getImmagineUtente() : ?string;

    /**
     * Restituisce lo stato dell'operazione corrente sotto forma
     * di codice.
     * @return int|NULL
     */
    public function getStatoOperazione() : ?int;

    /**
     * Indica se l'utente è connesso oppure no
     * @return bool
     */
    public function isUtenteConnesso() : bool;
    
    /**
     * Indica se l'utente ha spedito una richiesta di disiscrizione
     * non ancora accordata
     * @return bool
     */
    public function isUtenteInDisiscrizione() : ?bool;

    /**
     * Indica se l'utente ha spedito una rchiesta d'aggiornamento
     * non ancora accordata
     * @return bool
     */
    public function isUtenteInAggiornamento() : ?bool;

    /**
     * Indica se ci sono dei messaggi presenti.
     * @return bool
     */
    public function ciSonoMessaggi() : bool;

    /**
     * Restituisce i messaggi immagazzinati.
     * Sono pensati per essere mostrati nelle interfacce.
     * @return array
     */
    public function getMessaggi() : array;
}
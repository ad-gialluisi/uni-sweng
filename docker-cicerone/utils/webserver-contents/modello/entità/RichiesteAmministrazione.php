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

require_once "RichiestaAmministrazione.php";
require_once "RichiestaDisiscrizione.php";
require_once "RichiestaAggiornamento.php";
require_once "Utente.php";
require_once "Anagrafica.php";


/**
 * Classe che rappresenta una collezione di istanze
 * di RichiestaAmministrazione, che possono essere
 * sia istanze di RichiestaAggiornamento che RichiestaDisiscrizione
 */
class RichiesteAmministrazione {
    //qui vengono memorizzate le richieste
    private $richiesteDisiscrizione;
    private $richiesteAggiornamento;

    //qui i numeri di richieste per ciascun tipo
    private $nRichiestaDisiscrizione;
    private $nRichiestaAggiornamento;



    public function __construct() {
        $this->richiesteDisiscrizione = array();
        $this->richiesteAggiornamento = array();
        $this->nRichiestaDisiscrizione = 0;
        $this->nRichiestaAggiornamento = 0;
    }


    /**
     * Aggiunge una qualunque Richiesta d'amministrazione alla collezione
     * @param RichiestaAmministrazione $richiesta
     */
    public function addRichiesta(RichiestaAmministrazione $richiesta) {
        if ($richiesta instanceof RichiestaDisiscrizione) {
            $this->richiesteDisiscrizione[]= $richiesta;
            $this->nRichiestaDisiscrizione++;

        } else if ($richiesta instanceof RichiestaAggiornamento) {
            $this->richiesteAggiornamento[]= $richiesta;
            $this->nRichiestaAggiornamento++;
        }
    }


    /**
     * Restiusce una certa richiesta di discrizione presente
     * ad un certo indice
     * @param int $idx l'indice dove si trova la richiesta di disiscrizione
     * @return RichiestaDisiscrizione
     */
    public function getRichiestaDisiscrizione(int $idx) : RichiestaDisiscrizione {
        return $this->richiesteDisiscrizione[$idx];
    }


    /**
     * Restiusce una certa richiesta di discrizione presente
     * ad un certo indice
     * @param int $idx l'indice dove si trova la richiesta di disiscrizione
     * @return RichiestaDisiscrizione
     */
    public function getRichiestaAggiornamento(int $idx) : RichiestaAggiornamento {
        return $this->richiesteAggiornamento[$idx];
    }


    /**
     * Restituisce il numero di richieste d'aggiornamento presenti
     * @return int
     */
    public function getNRichiesteAggiornamento() : int {
        return $this->nRichiestaAggiornamento;
    }


    /**
     * Restituisce il numero di richieste di disiscrizione presenti
     * @return int
     */
    public function getNRichiesteDisiscrizione() : int {
        return $this->nRichiestaDisiscrizione;
    }
}
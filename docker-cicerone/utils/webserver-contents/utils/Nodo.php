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


namespace utils;

require_once "CustomException.php";


/**
 * Eccezione sollevata in caso venga rimosso un figlio inesistente.
 */
class NodoFiglioInesistenteException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * A dispetto del nome, questa classe consente la rappresentazione
 * di alberi a n figli.
 * <p>Nella realtà sarebbe ideale creare una classe Albero in maniera tale
 * da stabilire la "proprietà dei nodi", ovvero, stabilire a quale Albero
 * appartengono, ed evitare dunque determinate operazioni in base a questo.<br>
 * Non ho voluto creare una classe Albero a parte, per non complicare
 * troppo le cose, dato che non ho bisogno di questo tipo di controllo.</p>
 */
abstract class Nodo {
    /**
     * Nodo genitore
     * @var Nodo
     */
    private $genitore;
    
    /**
     * I figli
     * @var array
     */
    private $figli;
    
    /**
     * Numero dei figli
     * @var int
     */
    private $nFigli;


    /**
     * Costruisce un nuovo Nodo, eventualmente specificando un genitore.
     * @param ?Nodo $genitore
     */
    public function __construct(?Nodo $genitore) {
        $this->figli = NULL;
        $this->nFigli = 0;
        $this->genitore = $genitore;
    }


    /**
     * Consente l'aggiunta di nodi figli
     * @param Nodo $nodo nodo figlio da aggiungere.
     */
    public function addNodoFiglio(Nodo $nodo) : void {
        if ($this->figli === NULL) {
            $this->figli = array();
        }

        $nodo->genitore = $this;
        $this->figli[]= $nodo;
        $this->nFigli++;
    }


    /**
     * Restituisce il riferimento al nodo figlio numero $idx (parte da 0).
     * @param int $idx
     * @return ?Nodo un'istanza di nodo se esiste il figlio $idx o NULL in caso contrario.
     */
    public function getNodoFiglio(int $idx) : ?Nodo {
        return ($this->checkIdxFigli($idx)) ? $this->figli[$idx] : NULL;
    }


    /**
     * 
     * @param int $idx
     * @throws NodoFiglioInesistenteException
     */
    public function removeNodoFiglio(int $idx) : void {
        if (!$this->checkIdxFigli($idx)) {
            throw new NodoFiglioInesistenteException("Questo nodo non ha un figlio di indice %d!", $idx);
        }
        
        array_splice($this->figli, $idx, 1);
        $this->nFigli--;
    }


    /**
     * Restituisce il nodo genitore
     * @return Nodo
     */
    public function getNodoGenitore() {
        return $this->genitore;
    }


    /**
     * Restituisce il numero di figli di questo nodo
     * @return int
     */
    public function getNFigli() : int {
        return $this->nFigli;
    }


    /**
     * Indica se l'attuale nodo è un nodo radice.
     * @return bool true se è radice, false altrimenti.
     */
    public function isRadice() : bool {
        return $this->genitore === NULL;
    }


    /**
     * Verifica che l'indice passato rientri tra le posizioni occupate
     * dai figli del nodo corrente.
     * @param int $idx posizione
     * @return bool true, se rientra, false altrimenti.
     */
    private function checkIdxFigli(int $idx) : bool {
        return 0 <= $idx && $idx <= $this->nFigli - 1;
    }
}
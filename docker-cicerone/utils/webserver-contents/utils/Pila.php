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
 * Eccezione sollevata in caso lo Stack sia vuoto e vengano utilizzate
 * le operazioni top o pop.
 */
class PilaVuotaException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Classe che rappresenta la struttura dati pila (o stack).
 */
class Pila {
    /**
     * La struttura dati che conterrà gli elementi, un array PHP.
     * @var array
     */
    private $stack;
    
    /**
     * La dimensione dell'array PHP, fungerà anche da indice per la cima.
     * @var int
     */
    private $size;
    
    
    /**
     * Costruisce una pila vuota
     */
    public function __construct() {
        $this->stack = array();
        $this->size = 0;
    }
    
    
    /**
     * Inserisce un elemento in cima alla pila
     * @param mixed $elemento un elemento qualunque
     */
    public function push($elemento) : void {
        $this->stack[]= $elemento;
        $this->size++;
    }
    
    
    /**
     * Rimuove l'elemento attualmente in cima allo stack e lo restituisce
     * @return l'elemento che era in cima
     */
    public function pop() {
        $elemento = $this->top();
        
        array_splice($this->stack, $this->size - 1, 1);
        $this->size--;
        
        return $elemento;
    }
    
    
    /**
     * Restituisce l'elemento attualmente in cima allo stack
     * @return l'elemento in cima
     * @throws PilaVuotaException se lo stack è vuoto.
     */
    public function top() {
        if ($this->isVuota()) {
            throw new PilaVuotaException("La pi è vuoto!");
        }
        
        return $this->stack[$this->size - 1];
    }
    
    
    /**
     * Ottiene la dimensione della pila (quanti elementi ci sono)
     * @return int numero degli elementi nella pila
     */
    public function size() : int {
        return $this->size;
    }
    
    
    /**
     * Stabilisce se la pila è vuota.
     * @return bool true, se è vuota, false altrimenti.
     */
    public function isVuota() : bool {
        return $this->size === 0;
    }
}
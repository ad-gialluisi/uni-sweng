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

require_once "Nodo.php";

use Iterator;

/**
 * Classe che effettua una Depth-First search di tipo previsita (ordine anticipato)
 * su un albero (o sottoalbero) n-ario rappresentato da un'istanza della classe Nodo
 * che rappresenta la radice dell'albero.
 */
class NodoPrevisitaIterator implements Iterator {
    /**
     * È il nodo che rappresenta la radice.
     * <p>Attenzione, non è necessario che un nodo rappresenti una radice "vera".<br>
     * In tal caso infatti, verrà percorso un sottoalbero.</p>
     * @var Nodo
     */
    private $radice;
    
    /**
     * Struttura dati che si utilizza quando si vuol implementare la Depth-Fist Search.
     * @var Pila
     */
    protected $pila;
    
    /**
     * Come sopra, ma serve perlopiù per restituire qualcosa di utile come chiave.
     * Come chiavi, saranno restituiti il numero del particolare figlio rispetto
     * al genitore.
     */
    protected $pilaIDX;

    
    /**
     * Crea un iteratore con un dato nodo
     * @param Nodo $radice
     */
    public function __construct(Nodo $radice) {
        $this->radice = $radice;
        $this->rewind();
    }

    
    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see Iterator::next()
     */
    public function next() : void {
        $cima = $this->pila->pop();
        $this->pilaIDX->pop();
        $nFigli = $cima->getNFigli();

        if ($nFigli > 0) {
            for($i = $nFigli - 1; $i >= 0; $i--) {
                $this->pila->push($cima->getNodoFiglio($i));
                $this->pilaIDX->push($i);
            }
        }
    }

    
    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see Iterator::valid()
     */
    public function valid() : bool {
        return !$this->pila->isVuota();
    }

    
    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function current() : Nodo {
        return $this->pila->top();
    }

    
    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see Iterator::rewind()
     */
    public function rewind() : void {
        $this->pila = new Pila();
        $this->pilaIDX = new Pila();
        $this->pila->push($this->radice);
        $this->pilaIDX->push(0);
    }


    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see Iterator::key()
     */
    public function key() : int {
        return $this->pilaIDX->top();
    }
}
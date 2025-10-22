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


namespace utils\template;

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/NodoPrevisitaIterator.php";

use utils\NodoPrevisitaIterator;


/**
 * Classe che effettua una Depth-First search di tipo previsita (ordine anticipato)
 * su un albero (o sottoalbero) n-ario rappresentato da un'istanza della classe
 * NodoOutput che rappresenta la radice dell'albero di output creato 
 * da un'istanza di Template.
 * @see Template
 */
class NodoOutputIterator extends NodoPrevisitaIterator {
    /**
     * Una copia delle coppie chiavi-valore inerenti le chiavi pulsante
     * @var array
     */
    private $attivazioni;
    
    
    /**
     * Costruisce un nuovo NodoOutputIterator fornendo la radice
     * dell'albero di output e le rispettive attivazioni delle chiavi pulsante.
     * @param NodoOutput $radice nodo che rappresenta la radice dell'albero
     * di output
     * @param array $attivazioni array contenente le informazioni sulle
     * chiavi pulsante
     */
    public function __construct(NodoOutput $radice, array $attivazioni) {
        parent::__construct($radice);
        $this->attivazioni = $attivazioni;
    }

    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\NodoPrevisitaIterator::next()
     */
    public function next() : void {
        /*
         * Va fatta una leggera modifica alla previsita
         * per garantire il meccanismo delle chiavi pulsante.
         */

        
        //Prendi il nodo corrente
        $cima = $this->current();

        if ($cima->getTipo() === NodoOutput::CHIAVE_PULSANTE) {
            //Se rappresenta una chiave pulsante
            
            //Ottieni il nome
            $chiave = $cima->getNome();

            if ($this->attivazioni[$chiave]) {
                /*
                 * Se la chiave pulsante è attivata, procedi normalmente,
                 * il metodo next ereditato da NodoPrevisitaIterator
                 * provvederà ad aggiungere i nodi figli
                 */
                parent::next();
            } else {
                /*
                 * Se la chiave pulsante È DISATTIVATA, bisogna evitare
                 * che il metodo next ereditato da NodoPrevisitaIterator
                 * possa espandere, nella pila, gli eventuali figli del
                 * nodo corrente.
                 * Per evitare ciò, è sufficiente eliminare il nodo
                 * corrente dalla cima della pila e NON ESEGUIRE next.
                 */
                $this->pila->pop();
                $this->pilaIDX->pop();
            }
        } else {
            /*
             * Se non trattasi di una chiave pulsante, procedi normalmente
             * perchè la logica alternativa ha un senso solo per le chiavi
             * pulsante.
             */
            parent::next();
        }
    }
}
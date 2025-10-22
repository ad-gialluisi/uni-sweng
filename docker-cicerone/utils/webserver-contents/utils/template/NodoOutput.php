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

require_once "NodoOutputIterator.php";

use utils\Nodo;

/**
 * Classe che rappresenta un nodo dell'albero di output creato
 * dalla classe Template.
 * @see Template
 */
class NodoOutput extends Nodo {
    /**
     * Tipo di nodo: RADICE
     */
    public const RADICE = 0;
    
    /**
     * Tipo di nodo: CONTENUTO
     */
    public const CONTENUTO = 1;
    
    /**
     * Tipo di nodo: CHIAVE_PULSANTE
     */
    public const CHIAVE_PULSANTE = 2;
    
    /**
     * Tipo di nodo: CHIAVE_SEGNAPOSTO
     */
    public const CHIAVE_SEGNAPOSTO = 3;

    
    /**
     * Il tipo di nodo
     * @var int
     */
    private $tipo;


    /**
     * Il nome del nodo
     * @var string
     */
    private $nome;


    /**
     * Il valore associato al nodo
     * @var string
     */
    private $valore;


    /**
     * Costruisci un nuovo NodoOutput
     * @param int $tipo il tipo di questo nodo
     * @param string $nome il nome di questo nodo
     * @param string $valore il valore di questo nodo
     * @param NodoOutput $genitore il genitore di questo nodo
     */
    public function __construct(int $tipo, ?string $nome, ?string $valore=NULL, ?NodoOutput $genitore=NULL) {
        parent::__construct($genitore);
        $this->tipo = $tipo;
        $this->nome = $nome;
        $this->valore = $valore;
    }

    
    /**
     * Restituisce il valore del nodo
     * @return string il valore
     */
    public function getValore() : string {
        return $this->valore;
    }

    
    /**
     * Restituisce il nome del nodo
     * @return string il nome del nodo o NULL se non è impostato
     */
    public function getNome() : ?string {
        return $this->nome;
    }

    
    /**
     * Restituisce il tipo del nodo
     * @return int il tipo del nodo
     */
    public function getTipo() : int {
        return $this->tipo;
    }

    
    /**
     * Restituisce una rappresentazione testuale di NodoOutput nella sua
     * interezza.
     * @return string
     */
    public function __toString() : string {
        $nomeNodo = $this->getNome();

        return sprintf("[tipo: %s, nome: %s, valore: %s]",
            $this->getTipo(), $nomeNodo === NULL ? "NULL" : $nomeNodo,
            $this->getValore());
    }
}
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

/**
 * Questa interfaccia rappresenta un elemento che può essere trasformato in contenuto.
 * 
 * <p>Il senso della frase è questo: un elemento Outputtabile, rappresenta
 * un qualunque elemento che in seguito all'esecuzione di zero o più operazioni
 * sullo stesso, definisce quello che sarà il suo "output" cioè il suo risultato
 * finale.<br>
 * Col metodo output, si otterrà appunto, questo risultato finale.</p>
 */
interface Outputtabile {
    /**
     * Ottieni il risultato finale dell'elemento.
     * @return string
     */
    public function output() : string;
}

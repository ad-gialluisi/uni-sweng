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


namespace utils\parser;

require_once "Lexer.php";


/**
 * È un parser LL1, consente di effettuare l'analisi sintattica
 * di un file, in base alle produzioni e alla predictive parse table
 * a cui fa riferimento.
 * C'è da dire che non costruisce un albero sintattico, difatti è pensato
 * per linguaggi molto semplici.
 */
abstract class Parser {
    /**
     * Terminale EOI
     * @var int
     */
    protected const T_EOI = Token::EOI;

    /**
     * Terminale SCONOSCIUTO
     * @var int
     */
    protected const T_SCONOSCIUTO = Token::SCONOSCIUTO;

    
    /**
     * Stringa di input
     */
    protected $input;
    

    /**
     * Costruisce un nuovo parser
     * @param string $input input da analizzare
     */
    public function __construct(string $input) {
        $this->input = $input;
    }


    /**
     * Effettua il parse dell'input.
     * Il risultato dell'operazione viene passato per riferimento.
     * <p>Se il parsing va a buon fine, il risultato sarà un array di token scovati
     * (solitamente si tende a realizzare un albero sintattico), al contrario,
     * in caso di parsing fallito, si avrà un array di messaggi d'errore.</p>
     * @param ?array& $risultato
     * @return bool true, se il parsing è andato a buon fine, false altrimenti.
     */
    public abstract function parse(?array& $risultato) : bool;


    /**
     * Calcola la produzione da applicare in base al simbolo corrente
     * e al token passato.
     * @param int $simbolo il simbolo corrente
     * @param Token $token il token corrente
     * @return ?array restituisce l'array che rappresenta una certa produzione,
     * o NULL se non esiste una produzione per la combinazione simbolo-token passata
     * come parametri.
     */
    protected abstract function getProduzione(int $simbolo, Token $token) : ?array;


    /**
     * Stabilisce se il simbolo passato rappresenta un NT (non-terminale) o un terminale.
     * @param int $simbolo Il simbolo da verificare
     * @return boolean true se trattasi di un NT, false altrimenti.
     */
    protected abstract function isNT(int $simbolo) : bool;


    /**
     * Consente di ottenere facilmente il nome dato un tipo di token
     * @param int $tipoToken il tipo di token
     * @return string il nome del tipo di token
     */
    protected abstract function getNomeTipoToken(int $tipoToken) : string;


    /**
     * Restituisce l'NT (non terminale) di partenza.
     * @return int il simbolo non terminale di partenza
     */
    protected abstract function getNTPartenza() : int;
}




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

require_once "Token.php";


/**
 * È un lexer, consente di effettuare l'analisi lessicale
 * di un file, in base alle regole del DFA a cui fa riferimento.
 * NOTA: Il conteggio delle righe e colonne funziona a patto di analizzare un file
 * con newline di tipo Unix (cioè ogni newline è il solo carattere 0x0A o '\n')
 */
abstract class Lexer {
    /**
     * Rappresenta l'input da analizzare
     * @var string
     */
    private $input;
    
    /**
     * Rappresenta la lunghezza dell'input da analizzare
     * @var int
     */
    private $lunghezza;
    
    /**
     * Indica dove il lexer si trova nell'analisi dell'input.
     * @var int
     */
    private $idx;

    /**
     * Indica dove il lexer si trova nell'analisi dell'input in termini
     * di colonne del file di testo sorgente.
     * @var int
     */
    private $colonna;

    /**
     * Indica dove il lexer si trova nell'analisi dell'input in termini
     * di righe del file di testo sorgente.
     * @var int
     */
    private $riga;

    /**
     * Indica dove il lexer si trovava quando è stato scovato il token più
     * recente.
     * @var int
     */
    private $lastTokenIdx;

    /**
     * Indica dove il lexer si trovava quando è stato scovato il token più
     * recente in termini di colonne del file di testo sorgente.
     * @var int
     */
    private $lastTokenColonna;

    /**
     * Indica dove il lexer si trovava quando è stato scovato il token più
     * recente in termini di righe del file di testo sorgente.
     * @var int
     */
    private $lastTokenRiga;


    /**
     * Immagazzina il numero di colonne per riga.
     * Serve in caso si necessiti di retrocedere con l'input.
     * @var array
     */
    private $nColonne;



    public function __construct($text) {
        $this->input = $text;
        $this->lunghezza = strlen($text);
        $this->lastTokenIdx = $this->idx = 0;
        $this->lastTokenColonna = $this->colonna = 1;
        $this->lastTokenRiga = $this->riga = 1;
        $this->nColonne = array(); //Numero di colonne per riga
    }


    /**
     * Restituisce il successivo token scovato nel file.
     * Il tipo di Token "EOI" viene restituito in caso non ci siano
     * più token.
     * @see Token::EOI
     * @return Token
     */
    public function getNextToken() : Token {
        $stato = 0;
        $nextStato = 0;

        $tok = NULL;
            
        $analisiNecessaria = ($this->lookahead() !== false);

        if (!$analisiNecessaria) {
            $tok = $this->creaToken(Token::EOI, "", $this->lastTokenColonna,
            $this->lastTokenRiga);
        }

        while ($analisiNecessaria) {
            $ch = $this->lookahead();
            $stato = $nextStato;
            $nextStato = static::getNextStato($stato, $ch);
            $analisiNecessaria = $this->eseguiAzioneStato($stato, $nextStato, $ch, $tok);
        }

        return $tok;
    }


    /**
     * Consente l'estrazione del token, il passaggio per riferimento
     * dello stesso e l'aggiornamento degli indici relativi all'ultimo
     * token.
     * @param int $stato stato in cui si è capitati
     * @param ?Token& $tok
     */
    protected function estraiToken(string $tipoToken, ?Token& $tok) : void {
        $estrazione = substr($this->input, $this->lastTokenIdx,
            $this->idx - $this->lastTokenIdx);
        
        $tok = $this->creaToken($tipoToken, $estrazione,
            $this->lastTokenColonna, $this->lastTokenRiga);

        $this->lastTokenIdx = $this->idx;
        $this->lastTokenColonna = $this->colonna;
        $this->lastTokenRiga = $this->riga;
    }

    
    /**
     * Consente di avanzare la lettura dell'input al prossimo carattere.
     * @param mixed $ch il carattere corrente, serve per il conteggio delle righe/colonne.
     */
    protected function avanza($ch) : void {
        /*
         * Facciamo il conto inerente le righe e le colonne.
         * Da notare come siano supportate solo le newline Unix.
         */
        if ($ch === '\n') {
            $this->nColonne[$this->riga] = $this->colonna;
            $this->riga++;
            $this->colonna = 1;
        } else {
            $this->colonna++;
        }
        $this->idx++; //avanza nell'analisi
    }


    /**
     * Consente di retrocedere l'indice dell'input per consentire eventualmente
     * una ri-analisi del carattere appena analizzato.
     */
    protected function retrocedi() : void {
        if ($this->colonna === 1) {
            $this->riga--;
            $this->colonna = $this->nColonne[$this->riga];
        }
        $this->idx--;
    }


    /**
     * Consente di effettuare un lookahead di caratteri nella stringa
     * di input.
     * @param int $steps consente di leggere il carattere alla distanza specificata
     * rispetto alla posizione corrente dell'input.
     * @return mixed restituisce un carattere se c'è dell'input alla posizione specificata, false altrimenti.
     */
    protected function lookahead(int $steps=0) {
        $ch = false;
        if ($this->idx + $steps < $this->lunghezza) {
            $ch = $this->input[$this->idx + $steps];
        }
        
        return $ch;
    }
    

    /**
     * Consente di creare un token, prescindendo dalla particolare istanza di classe
     * @param int $tipo il tipo di Token
     * @param string $estrazione il contenuto che forma il token
     * @param int $colonna la colonna dove si trova il token nel documento
     * @param int $riga la riga dove si trova il token nel documento
     * @return Token
     */
    protected abstract function creaToken(int $tipo, ?string $estrazione,
        int $colonna, int $riga) : Token;


    /**
     * Partendo dallo stato e dal carattere passati come parametri, calcola lo
     * stato da raggiungere in base al DFA.
     * @param int $stato stato corrente
     * @param string $ch carattere di input corrente
     * @return int il nuovo stato da raggiungere
     */
    protected abstract static function getNextStato(int $stato, string $ch) : int;


    /**
     * Restituisce il codice identificativo dell'azione associata ad un particolare stato.
     * @param int $stato
     * @return int
     */
    protected abstract static function getAzioneStato(int $stato) : int;


    /**
     * Effettua l'esecuzione dell'azione richiesta nello stato analizzato ($nextStato)
     * @param int $stato stato corrente
     * @param int $nextStato stato successivo, si eseguirà l'azione associata a questo.
     * @param mixed $ch carattere corrente (false se è finito l'input)
     * @param Token $tok riferimento al token che verrà eventualmente estratto
     * @return bool true, se non è necessario effettuare ulteriori analisi (=token trovato), false altrimenti.
     */
    protected abstract function eseguiAzioneStato(int& $stato, int& $nextStato, $ch, ?Token& $tok) : bool;
}


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


namespace utils\template\parser;

require_once "TemplateToken.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/parser/Lexer.php";

use utils\parser\Lexer;
use utils\parser\Token;

/**
 * È il lexer che consente di effettuare l'analisi lessicale sul testo
 * template.
 * NOTA: Il conteggio delle righe e colonne funziona a patto di analizzare un file
 * con newline di tipo Unix (cioè ogni newline è il solo carattere 0x0A o '\n')
 */
class TemplateLexer extends Lexer {
    /**
     * Nessuna azione
     */
    private const NO_AZIONE = 0;

    /**
     * Restituzione Token CONTENUTO, tipo A
     */
    private const AZIONE_CONTENUTO_A = 1;

    
    /**
     * Restituzione Token CONTENUTO, tipo B
     */
    private const AZIONE_CONTENUTO_B = 2;

    
    /**
     * Restituzione Token CHIAVE_SEGNAPOSTO
     */
    private const AZIONE_CHIAVE_SEGNAPOSTO = 3;

    
    /**
     * Restituzione Token CHIAVE_PULSANTE_OPEN
     */
    private const AZIONE_CHIAVE_PULSANTE_OPEN = 4;

    
    /**
     * Restituzione Token CHIAVE_PULSANTE_CLOSE
     */
    private const AZIONE_CHIAVE_PULSANTE_CLOSE = 5;

    
    /**
     * Restituzione Token EOI
     */
    private const AZIONE_EOI = 6;


    /**
     * Restituzione Token SCONOSCIUTO
     */
    private const AZIONE_SCONOSCIUTO = 7;


    /**
     * Il DFA che questo lexer può interpretare
     * @var array
     */
    private const DFA = array(
        /*
         * Legenda:
         * C = Caratteri di un nome di chiave (corrispondente a [A-Za-z0-9\.-_])
         * Q = Altri caratteri
         * A = Azione da eseguire raggiunto lo stato
         * $ = EOI (end of input)
         */
        //            \   @   {   }   C   Q   $         A
        /* 0*/ array( 2,  3,  3,  3,  3,  3, 15, self::NO_AZIONE),
        /* 1*/ array( 3,  9,  9,  9,  9,  9, 13, self::NO_AZIONE),
        /* 2*/ array( 3,  4, 13, 13, 13, 13, 13, self::NO_AZIONE),
        /* 3*/ array( 1,  3,  3,  3,  3,  3, 14, self::NO_AZIONE),
        /* 4*/ array(13,  8,  8,  6,  5,  8, 13, self::NO_AZIONE),
        /* 5*/ array(11, 11,  7, 11,  5, 11, 11, self::NO_AZIONE),
        /* 6*/ array(10, 10, 10, 10, 10, 10, 10, self::NO_AZIONE),
        /* 7*/ array(12, 12, 12, 12, 12, 12, 12, self::NO_AZIONE),
        /* 8*/ array(13,  8,  8,  8,  8,  8, 13, self::NO_AZIONE),
        /* 9*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_CONTENUTO_B),
        /*10*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_CHIAVE_PULSANTE_CLOSE),
        /*11*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_CHIAVE_SEGNAPOSTO),
        /*12*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_CHIAVE_PULSANTE_OPEN),
        /*13*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_SCONOSCIUTO),
        /*14*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_CONTENUTO_A),
        /*15*/ array(-1, -1, -1, -1, -1, -1, -1, self::AZIONE_EOI)
    );

    
    /**
     * La corrispondenza tra azioni e token prodotti.
     */
    private const ACTION_TOKEN = array(
        self::AZIONE_CONTENUTO_A => TemplateToken::CONTENUTO,
        self::AZIONE_CONTENUTO_B => TemplateToken::CONTENUTO,
        self::AZIONE_CHIAVE_PULSANTE_CLOSE => TemplateToken::CHIAVE_PULSANTE_CLOSE,
        self::AZIONE_CHIAVE_PULSANTE_OPEN => TemplateToken::CHIAVE_PULSANTE_OPEN,
        self::AZIONE_CHIAVE_SEGNAPOSTO => TemplateToken::CHIAVE_SEGNAPOSTO,
        self::AZIONE_SCONOSCIUTO => TemplateToken::SCONOSCIUTO,
        self::AZIONE_EOI => TemplateToken::EOI,
    );


    /**
     * Ottieni il tipo di token prodotto se ci si ferma in un certo stato.
     * @param int $stato stato corrente
     * @return int tipo di token
     */
    protected function eseguiAzioneStato(int& $stato, int& $nextStato, $ch, ?Token& $tok) : bool {
        $analisiNecessaria = true;
        $nextStatoAzione = $this->getAzioneStato($nextStato);

        if ($nextStatoAzione === self::NO_AZIONE) {
            $this->avanza($ch);
        } else {
            if ($nextStatoAzione === self::AZIONE_CONTENUTO_B) {
                $this->retrocedi();
                $stato = 3;
            }
            $this->estraiToken(self::ACTION_TOKEN[$nextStatoAzione], $tok);
            $analisiNecessaria = false;
        }
        
        return $analisiNecessaria;
    }


    /**
     * Partendo dallo stato e dal carattere passati come parametri, calcola lo
     * stato da raggiungere in base al DFA.
     * @param int $stato stato corrente
     * @param string $ch carattere di input corrente
     * @return int il nuovo stato da raggiungere
     */
    protected static function getNextStato(int $stato, $ch) : int {
        /*
         * Assegna la colonna corretta in base al carattere passato.
         * Teoricamente converrebbe avere un metodo apposta, ma considerando
         * la semplicità del linguaggio, non ne vale la pena.
         */
        if (is_string($ch)) {
            if (ctype_alnum($ch)) {
                $col = 4;
            } else  {
                switch ($ch) {
                    case '\\': $col = 0; break;
                    case  '@': $col = 1; break;
                    case  '{': $col = 2; break;
                    case  '}': $col = 3; break;
                    case  '.':
                    case  '-':
                    case  '_': $col = 4; break;
                    default: $col = 5; break;
                }
            }
        } else if (is_bool($ch)) {
            $col = 6;
        }

        return self::DFA[$stato][$col];
    }
    
    
    /**
     * Ottieni il tipo di stato (NORMALE, FINALE o POZZO) dato lo stato.
     * @param int $stato stato corrente
     * @return int il tipo di stato
     */
    protected static function getAzioneStato(int $stato) : int {
        return self::DFA[$stato][7];
    }
 

    /**
     * Consente di creare un token, prescindendo dalla particolare istanza di classe
     * @param int $tipo il tipo di Token
     * @param string $estrazione il contenuto che forma il token
     * @param int $colonna la colonna dove si trova il token nel documento
     * @param int $riga la riga dove si trova il token nel documento
     * @return Token
     */
    protected function creaToken(int $tipo, ?string $estrazione,
        int $colonna, int $riga) : Token {
            return new TemplateToken($tipo, $estrazione, $colonna, $riga);
    }
}



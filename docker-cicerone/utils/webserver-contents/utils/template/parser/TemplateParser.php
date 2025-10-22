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

require_once "TemplateLexer.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/parser/Parser.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/Pila.php";


use utils\parser\Parser;
use utils\parser\Token;
use utils\Pila;


/**
 * È il parser di tipo LL1 che consente di effettuare l'analisi sintattica
 * sul testo template.
 */
class TemplateParser extends Parser {    
    /**
     * Terminale CONTENUTO
     * @var int
     */
    private const T_CONTENUTO = TemplateToken::CONTENUTO;
    
    /**
     * Terminale CHIAVE_SEGNAPOSTO
     * @var int
     */
    private const T_CHIAVE_SEGNAPOSTO = TemplateToken::CHIAVE_SEGNAPOSTO;
    
    /**
     * Terminale CHIAVE_PULSANTE_OPEN
     * @var int
     */
    private const T_CHIAVE_PULSANTE_OPEN = TemplateToken::CHIAVE_PULSANTE_OPEN;
    
    /**
     * Terminale CHIAVE_PULSANTE_CLOSE
     * @var int
     */
    private const T_CHIAVE_PULSANTE_CLOSE = TemplateToken::CHIAVE_PULSANTE_CLOSE;
    

    /**
     * Non terminale T
     */
    private const NT_T = self::T_CHIAVE_PULSANTE_CLOSE + 1;
    
    
    
    /*
     * Grammatica:
     * 0) T -> CONTENUTO T
     * 1) T -> CHIAVE_SEGNAPOSTO T
     * 2) T -> CHIAVE_PULSANTE_OPEN T CHIAVE_PULSANTE_CLOSE T
     * 3) T -> epsilon
     */
    /**
     * Rappresentazione della grammatica che il parser è in grado
     * di interpretare.
     */
    private const PRODUZIONI = array(
        /*0) T -> */ array(self::T_CONTENUTO, self::NT_T),
        /*1) T -> */ array(self::T_CHIAVE_SEGNAPOSTO, self::NT_T),
        /*2) T -> */ array(self::T_CHIAVE_PULSANTE_OPEN, self::NT_T, self::T_CHIAVE_PULSANTE_CLOSE, self::NT_T),
        /*3) T -> */ array()
    );
    
    
    /*
     *
     * Tabella funzioni Nullable, First e Follow (NOTA: $ = EOI)
     *  -------------------------------------------------------------------------------------------------------
     * |          |NULLABLE|                          FIRST                         |             FOLLOW           |
     *  ----------|--------|--------------------------------------------------------|------------------------------
     * |     T    |  true  | { CONTENUTO, CHIAVE_SEGNAPOSTO, CHIAVE_PULSANTE_OPEN } | { CHIAVE_PULSANTE_CLOSE, $ } |
     *  -------------------------------------------------------------------------------------------------------
     * -
     *
     * Predictive Parse Table ottenuta
     *  -----------------------------------------------------------------------------------------------------
     * |    | CONTENUTO | CHIAVE_SEGNAPOSTO | CHIAVE_PULSANTE_OPEN | CHIAVE_PULSANTE_CLOSE | SCONOSCIUTO | $ |
     *  -----------------------------------------------------------------------------------------------------
     * | T  |     0     |          1        |           2          |            3          |    ERRORE   | 3 |
     *  -----------------------------------------------------------------------------------------------------
     */
    /**
     * Rappresentazione della predictive parse table che il parser utilizza
     * per stabilire quale produzione utilizzare.
     * Il valore NULL indica che non esiste una produzione da utilizzare, quindi siamo
     * in una condizione d'errore.
     */
    private const PARSE_TABLE = array(
        /*T*/ array(0, 1, 2, 3, NULL, 3)
    );


    /**
     * Metodo ereditato.
     * {@inheritDoc}
     * @see \utils\parser\Parser::parse()
     */
    public function parse(?array& $risultato) : bool {
        $risultato = array();
        
        $nessunErrore = true;
        
        /*
         * Verrà utilizzato il Parsing LL1.
         * Per questo tipo di parsing viene usata la struttura dati pila
         * (o stack, se si preferisce), e si ragiona in termini di elementi
         * che si trovano in cima.
         * In pratica il prossimo simbolo da analizzare si trova sempre
         * in cima alla pila.
         * In caso di NT con token valido, si provvederà a rimuovere dalla cima
         * (pop) l'NT per piazzarci i simboli della produzione scelta (push).
         * Poichè parliamo di una pila, nel momento in cui si vanno ad inserire
         * i simboli di una produzione, occorre ricordarsi di inserirli AL CONTRARIO,
         * perchè per effetto dell'operazione push, se li inseriamo normalmente,
         * avremo che il primo elemento diventerà l'ultimo (citazione biblica
         * voluta), il secondo il penultimo e così via.
         * In caso di Terminale valido (cioè quello che è in cima, è uguale a quello
         * scovato), si provvede alla sola rimozione della cima (pop), in caso
         * contrario si gestisce l'errore.
         */

        /*
         * Si parte sempre inserendo il simbolo non terminale di partenza (T
         * in questo caso) e il token di fine input (EOI).
         * Al contrario per la logica spiegata prima.
         */
        $pila = new Pila();

        $pila->push(self::T_EOI);
        $pila->push($this->getNTPartenza());

        $lexer = new TemplateLexer($this->input);

        $token = NULL;

        do {
            $cima = $pila->top();
            
            if ($this->isNT($cima)) {
                //Se trattasi di un NT
                $token = $lexer->getNextToken();
                $produzione = $this->getProduzione($cima, $token);
                
                if ($produzione === NULL) {
                    /*
                     * Messaggio d'errore in caso di token sconosciuto
                     */
                    if ($nessunErrore) {
                        //Svuota risultato, e piuttosto, raccogli errori
                        $risultato = array();
                        $nessunErrore = false;
                    }
                    
                    $risultato[]= sprintf("Trovato token %s a riga %d, colonna %d!\n" .
                        "Valore (troncato): \"%s\"", $this->getNomeTipoToken($token->getTipo()),
                        $token->getRiga(), $token->getColonna(),
                        substr($token->getValore(), 0, 20));
                } else {
                    //Espandi NT con gli elementi della produzione
                    $pila->pop();
                    
                    /*
                     * AL CONTRARIO SEMPER
                     */
                    $nSimboli = count($produzione);
                    for ($i = $nSimboli - 1; $i >= 0; $i--) {
                        $pila->push($produzione[$i]);
                    }
                }
            } else {
                //Se trattasi di un terminale
                if ($cima === $token->getTipo()) {
                    //In cima c'è il token che ci si aspettava
                    
                    //Non inserire il token EOI però.
                    if ($cima !== self::T_EOI && $nessunErrore) {
                        $risultato[]= $token;
                    }
                    
                    $pila->pop(); //Consuma terminale

                } else {
                    //In cima NON C'È il token che ci si aspettava
                    if ($nessunErrore) {
                        //Svuota risultato, e piuttosto, raccogli errori
                        $risultato = array();
                        $nessunErrore = false;
                    }
                    
                    $risultato[]=
                    sprintf("Trovato token %s a riga %d, colonna %d.\n" .
                        "Ci si aspettava un token di tipo %s.\n" . 
                        "Valore (troncato): \"%s\"",
                        $this->getNomeTipoToken($token->getTipo()),
                        $token->getRiga(), $token->getColonna(),
                        $this->getNomeTipoToken($cima),
                        substr($token->getValore(), 0, 20));

                    //Richiedi nuovo token, altrimenti rimaniamo bloccati qui
                    $token = $lexer->getNextToken();
                }
            }
        } while (($nessunErrore && !$pila->isVuota()) ||
            (!$nessunErrore && $token->getTipo() !== Token::EOI));
        
        
        if (!$pila->isVuota()) {
            if ($nessunErrore) {
                //Svuota risultato, e piuttosto, raccogli errori
                $risultato = array();
                $nessunErrore = false;
            }
            $risultato[]= sprintf("Fine prematura dell'input.");
        }
        
        return $nessunErrore;
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\parser\Parser::getProduzione()
     */
    protected function getProduzione(int $simbolo, Token $token) : ?array {
        /*
         * Normalmente qui dovrebbe esserci uno switch in cui si sceglie
         * la riga inerente l'NT in base a $simbolo.
         * Poichè il linguaggio possiede un solo NT, si può evitare.
         */
        $rigaNT = 0;
        
        $tipo = $token->getTipo();
        if ($tipo === self::T_EOI) {
            $cella = 5;
        } else if ($tipo === self::T_SCONOSCIUTO) {
            $cella = 4;
        } else {
            $cella = $tipo;
        }
        $idProduzione = self::PARSE_TABLE[$rigaNT][$cella];

        if ($idProduzione === NULL) {
            $produzione = NULL;
        } else {
            $produzione = self::PRODUZIONI[$idProduzione];
        }
        
        return $produzione;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\parser\Parser::isNT()
     */
    protected function isNT(int $simbolo) : bool {
        $responso = false;

        switch ($simbolo) {
            default:
            case self::T_CONTENUTO:
            case self::T_CHIAVE_SEGNAPOSTO:
            case self::T_CHIAVE_PULSANTE_OPEN:
            case self::T_CHIAVE_PULSANTE_CLOSE:
            case self::T_SCONOSCIUTO:
            case self::T_EOI:
                $responso = false;
            break;
            case self::NT_T:
                $responso = true;
            break;
        }

        return $responso;
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\parser\Parser::getNomeTipoToken()
     */
    protected function getNomeTipoToken(int $tipoToken) : string {
        return TemplateToken::NOMI[$tipoToken];
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\parser\Parser::getNTPartenza()
     */
    protected function getNTPartenza() : int {
        return self::NT_T;
    }
}




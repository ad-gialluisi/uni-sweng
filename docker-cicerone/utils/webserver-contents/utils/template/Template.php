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

require_once "NodoOutput.php";
require_once "parser/TemplateParser.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/Outputtabile.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/utils/Pila.php";

use utils\CustomException;
use utils\Outputtabile;
use utils\Pila;
use utils\template\parser\TemplateParser;
use utils\template\parser\TemplateToken;



/**
 * Eccezione sollevata quando durante l'uso del metodo "applica", si utilizza un tipo
 * di valore non supportato.
 */
class TemplateValoreNonSupportatoException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Eccezione sollevata quando la creazione di un'istanza di Template fallisce
 * perchè il file non è stato trovato.
 */
class TemplateFileNonTrovatoException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Eccezione sollevata quando si cerca di aggiungere contenuti ad una chiave
 * segnaposto inesistente oppure quando si tenta di impostare una chiave
 * pulsante inesistente.
 */
class TemplateChiaveNonTrovataException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Eccezione sollevata quando si è verificato un errore durante l'interpretazione
 * di un testo template.
 */
class TemplateErroreInterpretazioneException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}


/**
 * Un classe capace di interpretare il linguaggio Template inventato dal
 * sottoscritto e permettere così, la generazione di testi come risultato della
 * sostituzione e/o abilitazione delle entità definite all'interno del file (o stringa)
 * template.
 * 
 * <p>Queste entità vengono dette chiavi.</p>
 * 
 * <p>Il linguaggio Template stabilisce due tipologie di chiavi specificabili
 * all'interno di un file o stringa Template:</p>
 * <ol>
 * <li>Chiavi segnaposto:<br>
 * Sono chiavi che, possono essere sostituite con del contenuto arbitrario.<br>
 * Fungono appunto da segnaposto (o placeholder, se si preferisce).<br>
 * Possono esserci più occorrenze delle stessa chiave, però, se questo
 * accade, il contenuto assegnato alla chiave, verrà, al momento della creazione
 * dell'output, piazzato in ogni occorrenza.</li>
 * <li>Chiavi pulsante:<br>
 * Sono chiavi che, contrariamente alle prime, raggruppano dei contenuti
 * (che possono essere testo semplice, ulteriori chiavi segnaposto e chiavi pulsante).
 * e possono essere "attivate" o "disattivate".<br>
 * Anche le chiavi pulsante possono avere più occorrenze, ma contrariamente
 * alle chiavi segnaposto, possono inglobare, per ogni occorrenza, diversi contenuti.<br>
 * In pratica, se una certa chiave pulsante è attiva, al momento della creazione
 * dell'output, verranno inseriti tutti i contenuti delle varie occorrenze della
 * stessa, man mano che vengono incontrate.<br>
 * Di standard tutte le chiavi pulsante sono disattivate.<br>
 * Se una chiave pulsante A risulta attiva, ma inglobata all'interno di una chiave
 * pulsante B disattiva, al momento della creazione dell'output, quella particolare
 * occorrenza di A non verrà mostrata.</li>
 * </ol>
 * 
 * <p>Le regole di sintassi per i file template sono:</p>
 * 
 * <ul>
 * <li>Tutti e due i tipi di chiavi iniziano con i caratteri "\@" seguiti da
 * un nome.<br>
 * Si possono usare cifre, maiuscole, minuscole, l'underscore, il punto e il
 * trattino, in breve vale l'espressione regolare [A-Za-z0-9\._-]+.<br>
 * Una chiave segnaposto necessita solo di quanto già spiegato.<br>
 * Ad esempio: \@chiavesegnaposto</li>
 * <li>Per una chiave pulsante, oltre a quanto spiegato nel punto precedente,
 * va aggiunto il carattere { ATTACCATO al nome (non devono esserci
 * caratteri di spaziatura tra nome e parentesi) poi dei contenuti ed infine
 * i caratteri \@}.<br>
 * Ad esempio: \@chiavepulsante{ &lt;contenuti&gt; \@}</li>
 * <li>Nel testo semplice non può essere usato direttamente il carattere \, va effettuato
 * l'escape, scrivendo dunque \\.</li>
 * </ul>
 *
 * <p>La classe utilizza il pattern Factory semplificato poichè l'uso di un costruttore
 * avrebbe portato ad ambiguità (poichè sia nel caso di una stringa semplice, che nel caso
 * di un file, si sarebbe dovuta specificare una stringa).<br>
 * Mi sento in dovere inoltre di far notare che in nessuno modo questa classe si avvicina
 * ai framework presenti in giro per la rete a livello di funzionalità e complessità.<br>
 * L'idea principale era vedere se potevo riuscirci da solo.<br>
 * Sì, lo so, è reinventare la ruota.</p>
 * @see Outputtabile
 */
/*
 * Esempio di contenuto template:
 *
 * <p>Io sono Antonio Daniele</p>
 * \@contenuto_arbitrario
 *
 * \@achi-importa{
 * <p>A noi che ce frega!?</p>
 * se \\@achi-importa è attivata, si vedranno queste scritte.
 * \@}
 *
 * \@salve-professore{
 * Se attiva questa chiave, la saluterò.
 * Salve, come va?
 * \@>
 * 
 * \@contenuto_arbitrario
 */
class Template implements Outputtabile {
    /**
     * La variabile che conterrà l'albero di output, ovvero quello che
     * verrà visitato per stabilire l'ordine di output dei vari elementi.
     * @var array
     */
    private $alberoOutput;


    /**
     * La variabile che conterrà le chiavi segnaposto trovate nel template.
     * @var array
     */
    private $chiaviSegnaposto;


    /**
     * La variabile che conterrà le chiavi pulsante trovate nel template.
     * @var array
     */
    private $chiaviPulsante;


    /*
     * Applichiamo il pattern Factory proibendo l'istanzazione esplicita.
     */
    private function __construct() {}


    /**
     * Crea un'istanza di Template fornendo un percorso ad un file
     * @param string $percorsoTemplate
     * @return Template
     */
    public static function daFile(string $percorsoTemplate) : Template {
        if (file_exists($percorsoTemplate)) {
            return self::daStringa(file_get_contents($percorsoTemplate));
        } else {
            throw new TemplateFileNonTrovatoException("Il file template \"%s\" non è stato trovato!",
                $percorsoTemplate);
        }
    }
    
    
    /**
     * Crea un'istanza di Template fornendo una stringa che contenga comandi nel
     * linguaggio di template.
     * @param string $contenutoTemplate
     * @return Template
     */
    public static function daStringa(string $contenutoTemplate) : Template {
        $elemento = new Template();
        $elemento->interpreta($contenutoTemplate);
        return $elemento;
    }


    /**
     * Aggiunge un valore alla chiave segnaposto specificata
     * @param string $chiave chiave segnaposto a cui aggiungere il valore
     * @param mixed $valore il valore da aggiungere
     * @throws TemplateValoreNonSupportatoException se si imposta un valore
     * non supportato (tutti gli oggetti che non implementano __toString
     * ad eccezione di Outputtabile).
     * @throws TemplateChiaveNonTrovataException se si cerca di impostare una
     * chiave segnaposto non esistente
     */
    public function add(string $chiave, $valore) : void {
        /*
         * Tipi supportati:
         * - Tutti i tipi semplici
         * - Tutte le classi che implementano __toString
         * - Tutte le istanze di Outputtabile
         */
        if (!($valore instanceof Outputtabile) &&
            is_object($valore) && !method_exists($valore, "__toString")) {
            throw new TemplateValoreNonSupportatoException(
                "Il valore di tipo \"%s\" non è supportato!\n",
                gettype($valore));
        }

        if (!isset($this->chiaviSegnaposto[$chiave])) {
            throw new TemplateChiaveNonTrovataException("La chiave segnaposto \"%s\" non esiste in questo template!",
                $chiave);
        }

        $this->chiaviSegnaposto[$chiave][]= $valore;
    }


    /**
     * Imposta una certa chiave pulsante.
     * @param string $chiave chiave pulsante da impostare
     * @param bool $valore valore da impostare per la chiave
     * @throws TemplateChiaveNonTrovataException Nel caso in cui $chiave non sia una chiave pulsante del template
     */
    public function setPulsante(string $chiave, bool $valore) : void {
        if (!isset($this->chiaviPulsante[$chiave])) {
            throw new TemplateChiaveNonTrovataException("La chiave pulsante \"%s\" non esiste in questo template!",
                $chiave);
        }

        $this->chiaviPulsante[$chiave] = $valore;
    }


    /**
     * Applica una serie di coppie chiave-valore.
     * Se sono stringhe o istanze di Outputtabile, verranno considerate come
     * chiavi segnaposto, se si tratta di valori booleani, come chiavi
     * pulsante.
     * @param array $coppie le coppie chiave-valore da inserire
     * @throws TemplateValoreNonSupportatoException se si utilizza un valore il cui
     * tipo non è nè Outputtabile, nè string, nè bool
     */
    public function applica(array $coppie, bool $boolAsPulsanti=true) {
        foreach ($coppie as $chiave => $valore) {
            if ($boolAsPulsanti && is_bool($valore)) {
                $this->setPulsante($chiave, $valore);
            } else {
                $this->add($chiave, $valore);
            }
        }
    }
    

    /**
     * Resetta sia le chiavi segnaposto che le chiavi pulsanti.
     */
    public function clear() : void {
        $chiavi = array_keys($this->chiaviSegnaposto);
        foreach ($chiavi as $chiave) {
            $this->chiaviSegnaposto[$chiave] = array();
        }

        $chiavi = array_keys($this->chiaviPulsante);
        foreach ($chiavi as $chiave) {
            $this->chiaviPulsante[$chiave] = false;
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \utils\Outputtabile::output()
     */
    public function output() : string {
        $outputFinale = "";

        $it = new NodoOutputIterator($this->alberoOutput, $this->chiaviPulsante);

        foreach ($it as $nodo) {
            $tipo = $nodo->getTipo();

            if ($tipo === NodoOutput::CONTENUTO) {
                $outputFinale .= $nodo->getValore();
            } else if ($tipo === NodoOutput::CHIAVE_SEGNAPOSTO) {
                $chiave = $nodo->getNome();
                $contenuti =  $this->chiaviSegnaposto[$chiave];
                
                foreach ($contenuti as $elemento) {
                    if ($elemento instanceof Outputtabile) {
                        $outputFinale .= $elemento->output();
                    } else {
                        $outputFinale .= $elemento;
                    }
                }
            }
        }
        
        return $outputFinale;
    }
    
    
    
    
    
    
    /**
     * Effettua l'interpretazione per cercare di creare l'albero di output.
     * @param string $input il contenuto template da interpretare
     * @throws TemplateErroreInterpretazioneException in caso di errori durante l'interpretazione
     */
    private function interpreta(string $input) : void {
        $parser = new TemplateParser($input);

        $risultato = NULL;
        $parsingRiuscito = $parser->parse($risultato);

        if ($parsingRiuscito) {
            $this->calcolaAlbero($risultato);

        } else {
            throw new TemplateErroreInterpretazioneException(implode("\n", $risultato));
        }
    }
    
    
    /**
     * Crea l'albero di output
     * @param array $listaToken lista dei token ottenuti dal parser
     */
    private function calcolaAlbero(array $listaToken) : void {
        $this->alberoOutput = new NodoOutput(NodoOutput::RADICE, NULL);
        $this->chiaviPulsante = array();
        $this->chiaviSegnaposto = array();

        /*
         * Nel caso in cui si incontrino i token CHIAVE_PULSANTE_OPEN,
         * sapremo che i successivi contenuti appartengono ad un nodo figlio
         * del nodo corrente puntato da $puntatore (gioco di parole non voluto).
         * Dovremo tornare al nodo padre, quando si incontra il token CHIAVE_PULSANTE_CLOSE.
         * La pila servirà proprio a permetterci di tornare indietro.
         */
        $pila = new Pila();
        $puntatore = $this->alberoOutput;

        foreach($listaToken as $token) {
            $tipo = $token->getTipo();

            switch ($tipo) {
                case TemplateToken::CONTENUTO:
                    //Sostituisci escape con caratteri giusti
                    $contenuto = str_replace(
                        array("\\\\"), array("\\"),
                        $token->getValore());
                    
                    $nodo = new NodoOutput(NodoOutput::CONTENUTO, NULL,
                        $contenuto);
                    $puntatore->addNodoFiglio($nodo);
                break;
                case TemplateToken::CHIAVE_SEGNAPOSTO:
                    $chiave = substr($token->getValore(), 2);

                    if (!isset($this->chiaviSegnaposto[$chiave])) {
                        $this->chiaviSegnaposto[$chiave] = array();
                    }

                    $nodo = new NodoOutput(NodoOutput::CHIAVE_SEGNAPOSTO, $chiave);
                    $puntatore->addNodoFiglio($nodo);
                break;
                case TemplateToken::CHIAVE_PULSANTE_OPEN:
                    $chiave = substr($token->getValore(), 2, -1);

                    if (!isset($this->chiaviPulsante[$chiave])) {
                        $this->chiaviPulsante[$chiave] = false;
                    }

                    $nodo = new NodoOutput(NodoOutput::CHIAVE_PULSANTE, $chiave);
                    $puntatore->addNodoFiglio($nodo);
                    $pila->push($puntatore);
                    $puntatore = $nodo;
                break;
                case TemplateToken::CHIAVE_PULSANTE_CLOSE:
                    $puntatore = $pila->pop();
                break;
                default:
                    //Mai eseguito
                break;
            }
        }
    }
}
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


namespace controllore;

require_once "LettoreSessione.php";


/**
 * Quest'interfaccia definisce quali sono i metodi che caratterizzano una
 * classe capace di scrivere i dati di una Sessione.
 * @see Sessione
 */
interface ScrittoreSessione extends LettoreSessione {
    /**
     * Imposta l'id dell'utente collegato.
     * @param int $idUtente
     */
    public function setIDUtente(?int $idUtente) : void;
    
    /**
     * Imposta il nome utente
     * @param string $nomeUtente
     */
    public function setNomeUtente(?string $nomeUtente) : void;

    /**
     * Imposta il tipo d'utente
     * @param string $tipoUtente
     */
    public function setTipoUtente(?string $tipoUtente) : void;

    /**
     * Imposta lo stato dell'utente
     * @param string $statoUtente
     */
    public function setStatoUtente(?string $statoUtente) : void;

    /**
     * Imposta il percorso (parziale) all'immagine dell'utente
     * @param string $immagineUtente
     */
    public function setImmagineUtente(?string $immagineUtente) : void;
    
    /**
     * Imposta il valore per indicare se l'utente ha spedito una richiesta
     * di disiscrizione che non è stata ancora accordata
     * @param bool $spedita
     */
    public function setUtenteInDisiscrizione(?bool $inDisiscrizione) : void;


    /**
     * Imposta il valore per indicare se l'utente ha spedito una richiesta
     * di aggiornamento che non è stata ancora accordata
     * @param bool $inAggiornamento
     */
    public function setUtenteInAggiornamento(?bool $inAggiornamento) : void;
    
    
    /**
     * Imposta lo stato dell'operazione
     * @param int $statoOperazione
     */
    public function setStatoOperazione(?int $statoOperazione) : void;


    /**
     * Elimina tutti i dati della sessione
     */
    public function clear() : void;
    
    /*
     * I seguenti sono metodi dedicati alla messaggistica
     * (cioè al meccanismo di restituzione di messaggi d'errore
     * o di conferma) che in questo sistema è leggermente complessa.
     * 
     * La teoria è semplice: ogni schermata deve mostrare dei
     * messaggi (che possono essere d'errore o normali), in più,
     * essi devono essere disponibili anche in caso di refresh
     * della stessa pagina.
     * Nel momento in cui s'avvia una richiesta, tutti i messaggi
     * vengono rimossi.
     * Se la richiesta produce uno o più messaggi, nella schermata di
     * destinazione (cioè quella che raggiungiamo in seguito al
     * successo/fallimento della richiesta), devono potersi vedere
     * (ovviamente se lo si vuole) sia i messaggi prodotti
     * dalla richiesta, sia eventuali messaggi dovuti alla schermata
     * stessa.
     * 
     * Perchè questo si possa fare ho provveduto a considerare
     * per questo problema le seguenti entità.
     * 
     * - messaggi -> lista di messaggi impostabili dai controllori.
     * - messaggiBackup -> copia di backup della lista suddetta, impostabile
     * INDIRETTAMENTE
     * - statoMessaggistica -> lo stato, che consente in varie fasi,
     * di permettere il fenomeno sopra descritto.
     * 
     * FORSE DA ELIMINARE
     * - messaggiUguali -> un flag che indica se messaggi e messaggiBackup
     * sono uguali, serve per evitare problemi di duplicazione dovuti
     * alla copia, in caso la schermata di destinazione non produca
     * alcun messaggio.
     * 
     * 
     * Si assume che tutte e tre le entità vengano impostate come variabili di sessione.
     * Dapprima statoMessaggistica non è impostata.
     * Quando viene richiesta la copia di messaggi in messaggiBackup,
     * statoMessaggistica è impostata ad 's' (i valori sono a titolo
     * d'esempio) e messaggi viene azzerata.
     * Ora è possibile associare una schermata, che imposterà di
     * conseguenza statoMessaggistica allo stato 't'.
     * Se in seguito, viene di nuovo associata una schermata e abbiamo
     * statoMessaggistica nello stato 't', si verificherà se la
     * schermata sia la stessa di quella associata prima, se sì,
     * non accadrà nulla, se no, le due entità e statoMessaggistica
     * verranno cancellati, permettendo così, di rifare il ciclo
     * descritto.
     */
    /**
     * Consente di aggiungere un messaggio alla lista dei messaggi
     * correnti.
     * Se non ci sono messaggi precedenti, si comporta
     * come setMessaggio.
     * @param string $messaggio
     */
    public function addMessaggio(string $messaggio) : void;


    /**
     * Consente di reimpostare il messaggio corrente oppure
     * di azzerarlo (fornendo NULL).
     * @param string $messaggio
     */
    public function setMessaggio(?string $messaggio) : void;


    /**
     * Effettua la copia dei messaggi impostati creando un backup.
     * Poi, i messaggi correnti vengono eliminati.
     * L'entità "statoMessaggistica" verrà impostata ad 's'.<br>
     * Anche questa va utilizzata nella sezione in cui si elaborano
     * richieste.
     */
    public function copiaMessaggisticaPerSchermata() : void;


    /**
     * Consente di associare una schermata alla messaggistica.
     * Se l'entità "statoMessaggistica" vale 's', si farà una banale
     * associazione, che imposterà "statoMessaggistica" a 't'.<br>
     * Se l'entità "statoMessaggistica" vale 't', si verificherà la
     * schermata passata come parametro con quella già associata.<br>
     * Se sono uguali, non succede niente, in caso contrario,
     * si ha l'eliminazione dei messaggi correnti, di quelli di backup
     *  e l'eliminazione di "statoMessaggistica".
     * @param string $schermata
     */
    public function associaSchermataPerMessaggistica(string $schermata) : void;


    /**
     * Cancella tutti i dati inerenti i messaggi.
     * Da utilizzare nella sezione in cui si elaborano richieste.
     */
    public function distruggiMessaggistica() : void;
}
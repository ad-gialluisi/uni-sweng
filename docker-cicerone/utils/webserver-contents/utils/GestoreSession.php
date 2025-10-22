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
 * Generico gestore della variabile globale $_SESSION.
 * <p>Questa classe è protetta contro gli attacchi "session fixation attack".</p>
 * 
 * <p>I file di sessione in PHP sono file che memorizzano dei cookie su server.<br>
 * Ad ognuno di questi file è associato un id che identifica un certo client,
 * tale id, di solito, non cambia.</p>
 * 
 * <p>Il session fixation attack consiste nell'impadronirsi di questo id, e spacciarsi
 * dunque per quel client a cui l'id è stato rubato.</p>
 * 
 * <p>La classe risolve il problema semplicemente rigenerando l'id, ad ogni intervallo
 * di tempo.<br>
 * In breve, se passa una certa quantità di tempo tra un'istanziazione di un GestoreSession
 * e l'altra, si ha la rigenerazione dell'id.</p>
 */
class GestoreSession {
    /**
     * Valori utile per la rigenerazione dell'id di sessione.
     * Impostiamo a 5 minuti.
     */
    private const SESSION_INTERVALLO_REFRESH = 300;
    private const CHIAVE_REFRESH = "REFRESH";


    public function __construct() {
        /*
         * Avvia la sessione solo se non è stata già avviata.
         * In questa maniera non si fa conflitto con eventuali
         * altre chiamate a "session_start".
         */
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }


        /*
         * Qui viene implementata la misura di sicurezza contro
         * il sessione-fixation attack.
         * Ogni 5 minuti viene rigenerato l'id di sessione.
         */
        $currTime = time();
        
        if (isset($_SESSION[self::CHIAVE_REFRESH]) &&
            $_SESSION[self::CHIAVE_REFRESH] - $currTime >= self::SESSION_INTERVALLO_REFRESH) {
            session_regenerate_id(true);
            $_SESSION[self::CHIAVE_REFRESH] = $currTime;
        }

        $_SESSION[self::CHIAVE_REFRESH] = $currTime;
    }


    /**
     * Imposta una coppia chiave-valore
     * @param string $chiave
     * @param mixed $valore
     */
    public function set(string $chiave, $valore) : void {
        if (!$this->isChiaveRefresh($chiave)) {
            $_SESSION[$chiave] = $valore;
        }
    }


    /**
     * Data la chiave come parametro, ottieni il valore corrispondente.
     * NOTA: Se la chiave non è definita (oppure è REFRESH) viene restituito NULL.
     * @param string $chiave
     * @return mixed
     */
    public function get(string $chiave) {
        $valore = NULL;

        if (!$this->isChiaveRefresh($chiave) && $this->esiste($chiave)) {
            $valore = $_SESSION[$chiave];
        }

        return $valore;
    }


    /**
     * Stabilisce se una certa chiave esiste.
     * NOTA: Se la chiave è REFRESH viene restituito false.
     * @param string $chiave
     * @return mixed
     */
    public function esiste(string $chiave) : bool {
        return !$this->isChiaveRefresh($chiave) && isset($_SESSION[$chiave]);
    }


    /**
     * Consente di eliminare il valore associato ad una chiave.
     * @param string $chiave
     */
    public function unset(string $chiave) : void {
        if (!$this->isChiaveRefresh($chiave) && $this->esiste($chiave)) {
            unset($_SESSION[$chiave]);
        }
    }


    /**
     * Pulisce tutti i dati immagazzinati nella sessione
     * (ad eccezione di CHIAVE_REFRESH).
     */
    public function clear() : void {
        $refresh = $_SESSION[self::CHIAVE_REFRESH];
        $_SESSION = array(self::CHIAVE_REFRESH => $refresh);
    }


    /**
     * Stabilisce se la chiave passata è CHIAVE_REFRESH.
     * Serve per evitare che dei buon temponi decidano di "disabilitare" il meccanismo.
     * @param string $chiave
     * @return bool
     */
    private function isChiaveRefresh(string $chiave) : bool {
        return $chiave === self::CHIAVE_REFRESH;
    }


    /**
     * Metodo di test
     */
    private function dump() {
        foreach ($_SESSION as $key => $value) {
            printf("%s=%s<br>", $key, $value);
        }
    }
}

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

require_once "Controllore.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloUtente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";

use modello\ModelloUtente;


/**
 * Macro-controllore che raggruppa tutte le funzionalità comuni utili
 * per gestire gli utenti.
 */
abstract class ControlloreUtente extends Controllore {
    /*
     * Campi comuni ai controllori che gestiscono gli utenti
     */
    /**
     * Campo numero di telefono
     */
    public const CAMPO_TELEFONO = "telefono";
    
    /**
     * Campo residenza
     */
    public const CAMPO_RESIDENZA = "residenza";
    
    /**
     * Campo email
     */
    public const CAMPO_EMAIL = "email";


    /**
     * Costruisce un nuovo ControlloreUtente con un'istanza
     * di ModelloUtente
     * @param ModelloUtente $modello
     */
    public function __construct(?ModelloUtente $modello) {
        parent::__construct($modello);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected abstract function validaParametri(array& $params, string $tipo) : bool;


    /**
     * Effettua la validazione del campo telefono.
     * @param string $telefono
     * @return bool true, se risulta valido, false al contrario
     */
    protected function validaCampoTelefono(string $telefono) : bool {
        /*
         * In base all'articolo:
         * https://www.quora.com/What-is-maximum-and-minimum-length-of-any-mobile-number-across-the-world
         *
         * Il minimo numero da testare è di 4 cifre...
         * Come per le email, faccio solo un banale check, piuttosto che fare un enorme
         * regex.
         */
        $valido = strlen($telefono) <= ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE;
        
        if ($valido) {
            if (!preg_match(ModelloUtente::REGEX_TELEFONO, $telefono)) {
                $this->sessione->addMessaggio("Il numero di telefono non è valido");
            }
        } else {
            $this->sessione->addMessaggio("Il numero di telefono supera i " .
                ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE . " caratteri");
        }

        return $valido;
    }


    /**
     * Questo metodo valida la password fornita.
     * @param string $password
     * @return bool true, se la password è valida, false al contrario
     */
    protected function validaCampoPassword(string $password) : bool {
        $valido = strlen($password) <= ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE;

        if ($valido) {
            if (!preg_match(ModelloUtente::REGEX_PASSWORD, $password)) {
                $this->sessione->addMessaggio("La password inserita non è valida");
                $valido = false;
            }
        } else {
            $this->sessione->addMessaggio("La password inserita supera i " .
                ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE . " caratteri");
        }


        return $valido;
    }


    /**
     * Questo metodo valida i campi password forniti.
     * In caso di errori, verranno impostati sia lo stato che il messaggio dell'interazione.
     * @param string $password
     * @param string $confermaPassword
     * @return bool true, se le password sono valide, false al contrario
     */
    protected function validaCampiPassword(string $password, string $confermaPassword) : bool {
        $valido = strlen($password) <= ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE &&
            strlen($confermaPassword) <= ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE;

        if ($valido) {
            if (preg_match(ModelloUtente::REGEX_PASSWORD, $password)) {
                if ($password !== $confermaPassword) {
                    $this->sessione->addMessaggio("Le password 'standard' e 'conferma' non corrispondono.");
                    $valido = false;
                }
            } else {
                $this->sessione->addMessaggio("La password 'standard', 'conferma' o ambedue non sono valide.<br>" .
                    "Una password deve avere un minimo di 8 caratteri.<br>" .
                    "Sono ammesse lettere maiuscole, minuscole e cifre");
                $valido = false;
           }
        } else {
            $this->sessione->addMessaggio("La password 'standard', 'conferma' o ambedue " .
                "superano i " . ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE . " caratteri");
        }

        return $valido;
    }


    /**
     * Valida il campo email fornito.
     *
     * <p>Qui è necessario aprire una piccola parentesi su questa implementazione:</p>
     * <p>Sebbene sia pratica comune verificare l'email mediante regex, durante
     * le mie ricerche ho constatato che esistono molte regex, e nonostante ciò,
     * esse sono lontane dall'essere perfette.</p>
     * <p>Per questo motivo, ho deciso solo di controllare se l'email contiene
     * il minimo sindacabile affinchè si possa qualificare come "email".<br>
     * Il controllo "vero e proprio" avverrà durante l'invio dell'email.<br>
     * In fondo, se l'email è sbagliata, l'ospite non otterrà mai nulla no?</p>
     *
     * <p>Magari nel futuro si può implementare un meccanismo che rimuova gli utenti
     * non attivati entro un tot di tempo.</p>
     *
     * @link https://davidcel.is/posts/stop-validating-email-addresses-with-regex/
     * @link https://stackoverflow.com/questions/1423195/what-is-the-actual-minimum-length-of-an-email-address-as-defined-by-the-ietf
     * @param string $email
     * @return bool true, se il campo email è valido, false al contrario
     */
    protected function validaCampoEmail(string $email) : bool {
        $valido = strlen($email) <= ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE;

        if ($valido) {
            if (!preg_match(ModelloUtente::REGEX_EMAIL, $email)) {
                $this->sessione->addMessaggio("L'email non è valida");
                $valido = false;
            }
        } else {
            $this->sessione->addMessaggio("L'email supera i " . ModelloUtente::MAX_LUNGHEZZA_CAMPO_TESTUALE . " caratteri");
        }
        
        return $valido;
    }
}
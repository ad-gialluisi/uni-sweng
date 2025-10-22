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


namespace modello;

require_once "Modello.php";


/**
 * Macro-modello che raggruppa tutte le funzionalità comuni utili
 * per gestire gli utenti.
 */
abstract class ModelloUtente extends Modello {
    /*
     * Ulteriori regex utilizzate nei modelli che trattano
     * gli utenti
     */
    /**
     * Regex per i numeri di telefono
     */
    public const REGEX_TELEFONO           = "#^(?:\+|)[0-9]{4,}$#";

    /**
     * Regex per le password
     */
    public const REGEX_PASSWORD           = "#^[A-Za-z0-9]{8,}#";

    /**
     * Regex per le email
     */
    public const REGEX_EMAIL              = "#^[^@]+@[^@]+$#";
}
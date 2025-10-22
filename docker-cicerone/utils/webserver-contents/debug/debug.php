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


    /*
     * Questo è il file che viene chiamato (mediante AJAX) quando si abilita il
     * "debug" mediante l'apposito tasto.
     */
    require_once "DebugSettings.php";
    
    use debug\DebugSettings;

    if (isset($_GET["change"])) {
        DebugSettings::setDebugHeaderLocation(!DebugSettings::isDebugHeaderLocationSet());
    } else {
        echo DebugSettings::isDebugHeaderLocationSet() ? "true" : "false";
    }
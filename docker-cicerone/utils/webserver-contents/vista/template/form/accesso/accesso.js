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


/**
 * Consente di mostrare uno dei tre possibili menù
 * @param parametro un tipo di menu, tra "registrazione", "accesso" e "recupero"
 * @returns
 */
function mostra(parametro) {
    var registrazione = document.getElementById("registrazione");
    var accesso = document.getElementById("accesso");
    var recupero = document.getElementById("recupero");


	if (parametro === "registrazione") {
		registrazione.className = "";
		accesso.className = "hidden";
		recupero.className = "hidden";

	} else if (parametro === "accesso") {
		registrazione.className = "hidden";
		accesso.className = "";
		recupero.className = "hidden";

	} else if (parametro === "recupero") {
		registrazione.className = "hidden";
		accesso.className = "hidden";
		recupero.className = "";
	}
}
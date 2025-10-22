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
 * Consente di aggiungere/togliere i partecipanti dal
 * dare loro il feedback
 */
/**
 * Consente di aggiungere/togliere partecipanti dal form per il rilascio
 * di un feedback organizzatore-partecipante.
 * @param checkboxRef il riferimento al particolare checkbox
 * @param idInputPartecipante l'id dell'input type="hidden" che contiene la lista
 * di partecipanti che otterranno il feedback, una volta inviata la richiesta
 * @returns
 */
function aggiungiRimuoviPartecipante(checkboxRef, idInputPartecipante) {
	var idInput = document.getElementById(idInputPartecipante);

	var valoriInput = undefined;

	if (idInput.value === "") {
		valoriInput = [];
	} else {
		valoriInput = idInput.value.split(",");
	}

	var idxFound = -1;

	for (var i = 0; idxFound === -1 && i < valoriInput.length; i++) {
		var currValore = valoriInput[i];

		if (currValore === checkboxRef.value) {
			idxFound = i;
		}
	}

	if (idxFound !== -1 && !checkboxRef.checked) {
		valoriInput.splice(idxFound, 1);
	} else if (idxFound === -1 && checkboxRef.checked) {
		valoriInput.push(checkboxRef.value);
	}

	idInput.value = valoriInput.join(",");
}
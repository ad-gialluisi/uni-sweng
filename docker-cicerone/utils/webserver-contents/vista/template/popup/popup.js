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
 * Questo array conterrà tutti i popup
 * che hanno il tipo di chiusura "modal"
 * Verrà utilizzato durante l'evento onclick.
 */
var popups = [];


/**
 * Classe Popup
 * @param id Costruisce un'istanza partendo da un id.<br>
 * ATTENZIONE: l'id deve esistere!
 * @returns
 */
function Popup(id) {
	this.popup = document.getElementById(id);
	this.tipoChiusura = this.popup.getAttribute("data-closure-type");

	if (this.tipoChiusura === "modal") {
		popups.push(this);
	}
}


/**
 * Consente l'ottenimento del contenitore, ovvero il div
 * su cui è possibile aggiungere contenuti.
 */
Popup.prototype.getContenitore = function() {
	return this.popup.children[0].children[0];
};


/**
 * Consente l'ottenimento del genitore, ovvero il div
 * su cui bisogna cliccare in caso di chiura del tipo
 * "modal".
 */
Popup.prototype.getGenitore = function() {
	return this.popup.parentNode;
};


/**
 * Mostra il popup
 */
Popup.prototype.mostra = function() {
	this.getGenitore().style.display = "block";
};


/**
 * Nasconde il popup
 */
Popup.prototype.nascondi = function() {
	this.getGenitore().style.display = "none";
};


/**
 * Metodo usato per mostrare/nascondere il popup
 * in base al fatto se sia già nascosto oppure no
 */
Popup.prototype.toggle = function() {
	if (this.isNascosto()) {
		this.mostra();
	} else {
		this.nascondi();
	}
}


/**
 * Stabilisce se il popup è nascosto oppure no
 */
Popup.prototype.isNascosto = function() {
	var genitore = this.getGenitore();

	return  genitore.style.display == "" ||
		genitore.style.display === null ||
		genitore.style.display === undefined ||
		genitore.style.display === "none";
};


/*
 * Questo event listener viene utilizzato per rimanere
 * in ascolto di tutti i popup con chiusura "modal",
 * in quanto consente di stabilire appunto se il loro
 * esterno viene cliccato, e se sì, vengono immediatamente
 * nascosti.
 */
window.addEventListener("click", function(event) {
	for (var i = 0; i < popups.length; i++) {
		var curr = popups[i];
		
		if (!popups[i].isNascosto() &&
			event.target == curr.getGenitore()) {
			curr.nascondi();
		}
	}
});

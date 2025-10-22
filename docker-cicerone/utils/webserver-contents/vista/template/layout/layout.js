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
 ************
 * "Classi" *
 ************
 */
/**
 * Classe utilizzata per mostrare le anteprime delle immagini
 * durante il caricamento.
 * @param maxDimensioneFile massima dimensione dell'immagine in byte
 * @param maxWidth massima larghezza dell'immagine
 * @param maxHeight massima altezza dell'immagine
 * @returns
 */
function GestoreAnteprimaImmagine(maxDimensioneFile=null, maxWidth=null, maxHeight=null) {
	this.maxDimensioneFile = maxDimensioneFile;
	this.maxWidth = maxWidth;
	this.maxHeight = maxHeight;
}


/**
 * Funzione chiamata per mostrare l'anteprima dell'eventuale immagine caricata.
 * @param idInputFile: l'id del input type=file
 * @param idImg: l'id del tag img che mostra l'immagine
 * @param idFlagRipristinoImmagine: l'id del tag input type=hidden che segnala la volontà di ripristinare
 * l'immagine di default
 * @param messaggiId: l'id del tag che conterrà i messaggi d'errore eventuali
 */
GestoreAnteprimaImmagine.prototype.mostraAnteprima = function(idInputFile, idImg,
	idFlagRipristinoImmagine, messaggiId) {
	var evt = document.getElementById(idInputFile);
	var files = evt.files;

	//Così, avrò il riferimento anche sulla chiusura.
	var self = this;
	var img = document.getElementById(idImg);
	var messages = document.getElementById(messaggiId);
	var flagRestore = document.getElementById(idFlagRipristinoImmagine);

	var f = files[0];
	var text = null;

	var reader = new FileReader();
	reader.onload = (function() {
		return function(e) {
			//Elimina eventuali figli presenti nel componente con id messaggiId
			while (messages.firstChild) {
				messages.removeChild(messages.firstChild);
			}

			//Se il MIME inizia con "image"
			if (f.type.startsWith("image/")) {
				if (self.maxDimensioneFile === null || f.size <= self.maxDimensioneFile) {
					var imageLoad = new Image();

					//Questo viene chiamato dopo che l'immagine imageLoad è stata caricata
					imageLoad.onload = function(g) {
						if ((self.maxWidth === null || this.width <= self.maxWidth) &&
							(self.maxHeight === null || this.height <= self.maxHeight)) {
							img.src = e.target.result;
							flagRestore.value = "false";
						} else {
							//Errore, produci messaggio, azzera file inseriti e rimuovi eventuale immagine
							var text = document.createTextNode("Il file \"" + f.name + "\"" +
								" ha dimensioni non valide.");
							messages.appendChild(text);
							messages.appendChild(document.createElement("br"));
							
							var dimMessaggio = "";
							if (self.maxWidth !== null && self.maxHeight) {
								dimMessaggio = "La risoluzione massima richiesta è " + self.maxWidth +
									"x" + self.maxHeight + " pixel.";
							} else if (self.maxWidth !== null) {
								dimMessaggio = "È richiesta una larghezza massima di " + self.maxWidth + "px";
							} else {
								dimMessaggio = "È richiesta un'altezza massima di " + self.maxHeight + "px";
							}

							text = document.createTextNode(dimMessaggio);
							messages.appendChild(text);

							evt.value = ""; //Rimuovi file caricato
							img.src = "";
							flagRestore.value = "false";
						}
					};

					//Questo viene chiamato dopo che l'immagine imageLoad è stata caricata, ma ci sono problemi
					imageLoad.onerror = function(g) {
						text = document.createTextNode("Problema durante il caricamento.");
						messages.appendChild(text);
						messages.appendChild(document.createElement("br"));
						text = document.createTextNode("Accertarsi che si stia fornendo un'immagine.");
						messages.appendChild(text);
						evt.value = ""; //Rimuovi file caricato
						img.src = "";
						flagRestore.value = "false";
					}

					//Provvedi ad effettuare il caricamento dell'immagine
					imageLoad.src = e.target.result;

				} else {
					text = document.createTextNode("Il file \"" + f.name + "\"" +
						" è troppo grande.");
					messages.appendChild(text);
					messages.appendChild(document.createElement("br"));
					text = document.createTextNode("Un file deve avere dimensione massima " +
						"pari a " + self.maxDimensioneFile + " byte!");
					messages.appendChild(text);
					evt.value = ""; //Rimuovi file caricato
					img.src = "";
					flagRestore.value = "false";
				}
			} else {
				text = document.createTextNode("Il file \"" + f.name + "\"" +
						" non è un'immagine.");
				messages.appendChild(text);
				evt.value = ""; //Rimuovi file caricato
				img.src = "";
				flagRestore.value = "false";
			}
		};
    })();

    reader.readAsDataURL(files[0]);
};


/**
 * Funzione chiamata per reimpostare l'anteprima all'immagine di default.
 * @param idInputFile: l'id del input type=file
 * @param idImg: l'id del tag img che mostra l'immagine
 * @param idFlagRipristinoImmagine: l'id del tag input type=hidden che segnala la volontà di ripristinare
 * l'immagine di default
 * @param messaggiId: l'id del tag che conterrà i messaggi d'errore eventuali
 */
GestoreAnteprimaImmagine.prototype.resetAnteprima = function(idInputFile, idImg, messaggiId,
	idFlagRipristinoImmagine, percorsoImmagineDefault) {
	
	var evt = document.getElementById(idInputFile);
	evt.value = "";
	
	var img = document.getElementById(idImg);
	img.src = percorsoImmagineDefault;
	
	var flagRestore = document.getElementById(idFlagRipristinoImmagine);
	flagRestore.value = "true";

	var messages = document.getElementById(messaggiId);
	//Elimina eventuali figli presenti nel componente con id messaggiId
	while (messages.firstChild) {
		messages.removeChild(messages.firstChild);
	}
};



/**
 * Classe utilizzata per creare richieste AJAX generali
 * @param url url della richiesta
 * @param method il metodo della richiesta
 * @returns
 */
function AJAXRequest(url, method) {
	this.url = url;
	this.method = method;
	this.xmlReq = new XMLHttpRequest();
	this.xmlReq.open(method, url);
	this.body = "";
}


/**
 * Imposta il body della richiesta
 * @param bodyParams oggetto con coppie "chiave-valore" che rappresenta i parametri
 * del body di una richiesta
 */
AJAXRequest.prototype.setBody = function(bodyParams) {
	this.body = "";

	var keys = Object.keys(bodyParams);
	
	for (var i = 0; i < keys.length; i++) {
		var key = keys[i];
		var value = bodyParams[key];
		var equalityValue = encodeURIComponent(key) + "=" + encodeURIComponent(value);
		
		this.body += equalityValue;
		
		if (i < keys.length - 1) {
			this.body += "&";
		}
	}
};


/**
 * Imposta il metodo della richiesta
 * @param method metodo della richiesta
 */
AJAXRequest.prototype.setMethod = function(method) {
	this.method = method;
};


/**
 * Imposta un certo header di richiesta
 * @param header header della richiesta
 * @param valore valore dell'header
 */
AJAXRequest.prototype.setRequestHeader = function(header, value) {
	this.xmlReq.setRequestHeader(header, value);
};


/**
 * Inizia la richiesta AJAX
 */
AJAXRequest.prototype.startRequest = function() {
	this.xmlReq.send(this.body);
};


/**
 * Imposta un funzione che verrà chiamata quando l'evento
 * "onload" avrà luogo
 * @param funct funzione da richiamare
 */
AJAXRequest.prototype.setOnLoadHandler = function(funct) {
	this.xmlReq.onload = funct;
};




/*
 **************
 * "Funzioni" *
 **************
 */
/**
 * Consente di mostrare/nascondere elementi in base all'attuale
 * valore di un elemento HTML select.
 * Perchè funzioni è necessario che i "menù" abbiano l'id
 * pari alle singole voci della select.
 * Messo qui per garantire riusabilità.
 */
function mostraMenuTramiteSelect(refSelect) {
	var parametro = refSelect.options[refSelect.selectedIndex].value;

	for (var i = 0; i < refSelect.options.length; i++) {
		var value = refSelect.options[i].value;
		
		var elemento = document.getElementById(value);
		
		if (value === parametro) {
			elemento.className = "";
		} else {
			elemento.className = "hidden";
		}
	}
}



/*
 * Crea un nuovo elemento lista vuoto mediante Javascript.
 * Utilizzato in quei menù che necessitano di creare elementi di
 * una lista da lato client (di solito in seguito a chiamate AJAX).
 */

function ElementoLista() {
	this.contentDiv = document.createElement("div");
	var wrapper1 = document.createElement("div");
	var wrapper2 = document.createElement("div");
	var wrapperElementList = document.createElement("div");
	wrapperElementList.className = "element-list";
	this.wrapperRoot = document.createElement("div");
	
	wrapper1.appendChild(this.contentDiv);
	wrapper2.appendChild(wrapper1);
	wrapperElementList.appendChild(wrapper2);
	this.wrapperRoot.appendChild(wrapperElementList);
}

ElementoLista.prototype.getWrapperRoot = function() {
	return this.wrapperRoot;
}


ElementoLista.prototype.getContentDiv = function() {
	return this.contentDiv;
}

/*
function creaElementoListaVuoto() {
	var contentDiv = document.createElement("div");
	var wrapper1 = document.createElement("div");
	var wrapper2 = document.createElement("div");
	var wrapperElementList = document.createElement("div");
	wrapperElementList.className = "element-list";
	var wrapperRoot = document.createElement("div");

	wrapper1.appendChild(contentDiv);
	wrapper2.appendChild(wrapper1);
	wrapperElementList.appendChild(wrapper2);
	wrapperRoot.appendChild(wrapperElementList);
		
	return [contentDiv, wrapperRoot];
}*/
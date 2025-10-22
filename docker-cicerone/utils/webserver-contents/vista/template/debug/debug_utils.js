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


//Costanti
var SCHEME_HOST = "schost";
var QUERY = "query";
var FRAGMENT = "fragment";
var DEBUG_SESSION = "XDEBUG_SESSION_START";
var DEBUG_SESSION_VALUE = "ECLIPSE_DBGP";
var KEY = "KEY";
var KEY_VALUE = "157270718956123";

var DEBUG_MENU_ID = "debug_menu";


//Elementi di debug_utils.html
var PRESET_LIST = "preset_list";
var PAGE_LIST = "page_list";
var DEBUG_RADIO_GET = "debug_radio_get";
var DEBUG_RADIO_POST = "debug_radio_post";
var PARAMS = "params";
var DEBUG_CHECKBOX = "debug_checkbox";


/*
 * Qui ci saranno i preset e le pagine da utilizzare durante i test.
 * Le variabili globali sono PAGES e PRESETS, sono array di oggetti generici.
 */
\@pages-and-presets


/**
 * Classe DebugSettings
 */
function DebugSettings() {
	this.debugHeaderLocationStatus = \@debug-header-location-status;
	this.debugURLStatus = false;
	this.pagePresetInit = false;
}


/*
 * Inizializza pagine e preset (fatto solo la prima volta)
 */
DebugSettings.prototype.initPresetsAndPages = function() {
	if (!this.pagePresetInit) {
		this.pagePresetInit = true;
		
		var preset_list = document.getElementById(PRESET_LIST);
		var page_list = document.getElementById(PAGE_LIST);

		for (var i = 0; i < PAGES.length; i++) {
			var page = PAGES[i];
			var option = document.createElement("option");
			var text = document.createTextNode(page["label"]);
			option.appendChild(text);
			option.value = page["url"];
			page_list.appendChild(option);
			
			if (page.hasOwnProperty("selected")) {
				page_list.selectedIndex = i + 1;
			}
		}

		for (var i = 0; i < PRESETS.length; i++) {
			var preset = PRESETS[i];
			var option = document.createElement("option");
			var text = document.createTextNode(preset["label"]);
			option.appendChild(text);
			preset_list.appendChild(option);

			if (preset.hasOwnProperty("selected")) {
				preset_list.selectedIndex = i + 1;
				this.cambiaPreset();
			}
		}
	}
};


/**
 * Consente di impostare delle pagine "preset", utili
 * per semi-automatizzare alcuni test.
 */
DebugSettings.prototype.cambiaPreset = function() {
	var preset_list = document.getElementById(PRESET_LIST);
	var debug_checkbox = document.getElementById(DEBUG_CHECKBOX);
	var params_textarea = document.getElementById(PARAMS);
	var page_list = document.getElementById(PAGE_LIST);

	var radiobuttons = {
		"get": document.getElementById(DEBUG_RADIO_GET),
		"post": document.getElementById(DEBUG_RADIO_POST),
	};

	var preset = PRESETS[preset_list.selectedIndex - 1];

	if (!preset.hasOwnProperty("method") || preset["method"] === "get") {
		radiobuttons["get"].checked = true;
		radiobuttons["post"].checked = false;
	} else {
		radiobuttons["get"].checked = false;
		radiobuttons["post"].checked = true;
	}

	if (!preset.hasOwnProperty("debug") || preset["debug"] === false) {
		debug_checkbox.checked = false;
	} else {
		debug_checkbox.checked = true;
	}

	page_list.selectedIndex = preset["page"] + 1;
	params_textarea.value = preset["params"];
};


/**
 * Prende un URL e lo divide in tre parti.
 * SCHEME_HOST: Dove risiedono appunto, insieme, lo SCHEME e l'HOST.
 * QUERY: Dove risiedono i parametri GET
 * FRAGMENT: Dove risiedono i tag (carattere #)
 */
DebugSettings.prototype.URLToComponents = function(url) {
	var urlComponents = {};

	var hashIdx = url.indexOf("#");
	if (hashIdx !== -1) {
		urlComponents[FRAGMENT] = url.substring(hashIdx + 1);
	} else {
		urlComponents[FRAGMENT] = null;
	}

	var questionMarkIdx = url.indexOf("?");
	if (questionMarkIdx !== -1) {
		urlComponents[QUERY] = url.substring(questionMarkIdx + 1,
			hashIdx !== - 1 ? hashIdx : undefined);
		urlComponents[SCHEME_HOST] = url.substring(0, questionMarkIdx);
	} else {
		urlComponents[SCHEME_HOST] = url.substring(0,
			hashIdx !== -1 ? hashIdx : undefined);
	}

	if (urlComponents[QUERY] !== null) {
		urlParams = new URLSearchParams(urlComponents[QUERY]);

		urlComponents[QUERY] = [];
		for (var pair of urlParams.entries()) {
			urlComponents[QUERY][pair[0]] = pair[1];
		}
	}

	return urlComponents;
};


/**
 * Prende un URL diviso in tre parti e lo unisce
 */
DebugSettings.prototype.componentsToURL = function(urlComponents) {
	var query = [];
	if (urlComponents !== null) {
		for (var key in urlComponents[QUERY]) {
			query.push(key + "=" + urlComponents[QUERY][key]);
			}
		query = query.join("&");
	}

	var fullURL = urlComponents[SCHEME_HOST];
	if (query.length > 0) {
		fullURL += "?" + query;
	}
	if (urlComponents[FRAGMENT] !== null) {
		fullURL += "#" + urlComponents[FRAGMENT];
	}
	
	return fullURL;
};


/**
 * Data un URL suddivisa in parti, aggiunge (o rimuove) i
 * parametri GET per abilitare l'uso di XDebug
 */
DebugSettings.prototype.toggleDebugParameters = function(urlComponents) {
	if (this.debugURLStatus) {
		this.addDebugParameters(urlComponents);
	} else {
		this.removeDebugParameters(urlComponents);
	}
};


/**
 * Data un URL suddivisa in parti, aggiunge i parametri GET
 * per abilitare l'uso di XDebug
 */
DebugSettings.prototype.addDebugParameters = function(urlComponents) {
	if (urlComponents[QUERY] !== null) {
		if (!urlComponents[QUERY].hasOwnProperty(DEBUG_SESSION)) {
			urlComponents[QUERY][DEBUG_SESSION] = DEBUG_SESSION_VALUE;
			urlComponents[QUERY][KEY] = KEY_VALUE;
		}
	} else {
		urlComponents[QUERY][DEBUG_SESSION] = DEBUG_SESSION_VALUE;
		urlComponents[QUERY][KEY] = KEY_VALUE;
	}
};


/**
 * Data un URL suddivisa in parti, rimuove i parametri GET
 * che servono ad abilitare l'uso di XDebug
 */
DebugSettings.prototype.removeDebugParameters = function(urlComponents) {
	if (urlComponents[QUERY] !== null) {
		if (urlComponents[QUERY].hasOwnProperty(DEBUG_SESSION)) {
			delete urlComponents[QUERY][DEBUG_SESSION];
			delete urlComponents[QUERY][KEY];
		}
	}
};


/**
 * Abilita/Disabilita il debug per l'istruzione header("location: <url>") da lato PHP
 */
DebugSettings.prototype.toggleDebugHeaderLocationStatus = function() {
	//NOTA: Questa è una chiamata sincrona, di solito,
	//vanno evitate come la peste, in questo caso, so che impiega
	//pochissimo, quindi lo faccio.
	var httpReq = new XMLHttpRequest();
    httpReq.open("GET", "debug/debug.php?change=true", false);
    httpReq.send();
    this.debugHeaderLocationStatus = !this.debugHeaderLocationStatus;

    var status_header = document.getElementById("status_header");
    status_header.innerText = (this.debugHeaderLocationStatus ? "Disabilita" : "Abilita");
};


/**
 * Abilita/Disabilita l'inserimento di parametri per l'uso con XDebug su tutti gli URL
 * della pagina.
 */
DebugSettings.prototype.toggleDebugURLs = function() {
	this.debugURLStatus = !this.debugURLStatus;

	var elementsWithURLs = document.querySelectorAll(
		"a[href]:not([href=\\"javascript:;\\"]), " +
		"input[type=submit][formaction]:not([formaction=\\"javascript:;\\"]), " +
		"form[action]:not([action=\\"javascript:;\\"])");

	for (var idx = 0; idx < elementsWithURLs.length; idx++) {
		var elemento = elementsWithURLs[idx];
		var nomeAttributo = null;

		switch (elemento.nodeName.toLowerCase()) {
			case "a":
				nomeAttributo = "href";
			break;
			case "input":
				nomeAttributo = "formaction";
			break;
			case "form":
				nomeAttributo = "action";
			break;	
		}

		var url = elemento.getAttribute(nomeAttributo);
		var urlComponents = this.URLToComponents(url);
		this.toggleDebugParameters(urlComponents);
		var fullURL = this.componentsToURL(urlComponents);

		elemento.setAttribute(nomeAttributo, fullURL);
	}

    var status_url = document.getElementById("status_url");
    status_url.innerText = (this.debugURLStatus ? "Disabilita" : "Abilita");
};


/**
 * Consente di mostrare il keyCode attuale.
 */
DebugSettings.prototype.mostraKeyCode = function(keyCode) {
	var key_code = document.getElementById("key_code");
	key_code.innerText = keyCode;
};


/**
 * Effettua l'invio della richiesta mediante form.
 */
DebugSettings.prototype.submit = function() {
	//Prendi URL selezionata e suddividila
	var page_list = document.getElementById(PAGE_LIST);
	var url = page_list[page_list.selectedIndex].value;
	var urlComponents = this.URLToComponents(url);

	//Se il debug è abilitato, aggiungi i parametri
	var debug_checkbox = document.getElementById(DEBUG_CHECKBOX);
	if (debug_checkbox.checked) {
		this.addDebugParameters(urlComponents);
	}

	//Cerca i parametri inseriti a mano.
	var params_textarea = document.getElementById(PARAMS);

	//Il form che effettuerà la richiesta
	var debug_form = document.getElementById("debug_form");

	/*
	 * Suddividi i parametri come fossero un URL, e aggiungili
	 * ad uno ad uno come input nascosti del form di debug.
	 */
	var params = this.URLToComponents("?" + params_textarea.value.trim().replace(/\\n/g, "&"));
	for (var i in params[QUERY]) {
		var value = params[QUERY][i];
		var hiddenInput = document.createElement("input");
		hiddenInput.type = "hidden";
		hiddenInput.name = i;
		hiddenInput.value = value;
		debug_form.appendChild(hiddenInput);
	}

	//Infine, stabilisci il tipo di richiesta
	var radiobuttons = {
		"get": document.getElementById(DEBUG_RADIO_GET),
		"post": document.getElementById(DEBUG_RADIO_POST),
	};

	if (radiobuttons["post"].checked) {
		debug_form.method = "post";

	} else {
		debug_form.method = "get";

		/*
		 * Poichè nel caso di metodo "GET" si ha la rimozione
		 * dei parametri già inseriti nell'URL, si provvede
		 * ad aggiungerli come input nascosti, prima di
		 * procedere per la submit.
		 */
		for (var i in urlComponents[QUERY]) {
			var value = urlComponents[QUERY][i];
			var hiddenInput = document.createElement("input");
			hiddenInput.type = "hidden";
			hiddenInput.name = i;
			hiddenInput.value = value;
			debug_form.appendChild(hiddenInput);
		}

		urlComponents[QUERY] = null;
	}

	//Imposta l'azione ed effettua la submit.
	debug_form.action = this.componentsToURL(urlComponents);
	debug_form.submit();
};



/*
 * Impostazione javascript
 */
var debugSettings = new DebugSettings();
var popupDebug = null;

document.addEventListener("keydown", function(e) {
	if (popupDebug === null) {
		popupDebug = new Popup("popup-debug");
	}

	if (e.ctrlKey) {
		switch(e.keyCode) {
			case 68: //D
				e.preventDefault();
				debugSettings.initPresetsAndPages();
				popupDebug.toggle();
			break;
		}
	}

	debugSettings.mostraKeyCode(e.keyCode);
});

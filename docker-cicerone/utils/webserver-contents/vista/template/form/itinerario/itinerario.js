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
 * Funzione principale che consente di effettuare gli invii di richieste
 * di partecipazione/annullamento e anche di accordare/declinare/annullare
 * richieste di partecipazione.
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto la partecipazione/a cui si accorda la partecipazione
 * @param url l'url utile per fare la richiesta
 * @param tipo un id per indicare il tipo di richiesta, cambia il messaggio mostrato
 * @returns
 */
function effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante, url, tipo) {
	var messaggi = document.getElementById("messaggi");

	var ajaxReq = new AJAXRequest(url, "POST");
	ajaxReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

	var body = {
		"\@param-id-itinerario": idItinerario,
		"\@param-id-partecipante": idPartecipante
	};

    ajaxReq.setBody(body);
    ajaxReq.setOnLoadHandler(function () {
    	if (this.readyState === 4 && this.status === 200) {
    		var received = JSON.parse(this.responseText);
    		if (received.hasOwnProperty("error")) {
            	messaggi.innerText = received["error"];

            } else if (received.hasOwnProperty("success")) {
            	var parentNode = buttonRef.parentNode;
            	var p = document.createElement("p");

        		switch (tipo) {
        			case "invioPartecipazione":
        			case "invioAnnullamento":
                		p.className = "no-margin";

                		var textNode = "";

        				if (tipo === "invioPartecipazione") {
                			textNode = "Richiesta di partecipazione";
                		} else if (tipo === "invioAnnullamento") {
                			textNode = "Richiesta d'annullamento";
                		}
        				p.appendChild(document.createTextNode(textNode));
                		p.appendChild(document.createElement("br"));
                		p.appendChild(document.createTextNode("inviata!"));
                		parentNode.removeChild(buttonRef);
                		parentNode.appendChild(p);
        			break;
        			case "accordoRichiesta":
        			case "declinoRichiesta":
        			case "annullamentoRichiesta":
        				//Sì, non è uno scherzo... il problema di non saper fare grafica
        				var refListaElemento = parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;

        				if (tipo === "accordoRichiesta") {
            				var listaAccordate = document.getElementById("accordate");
        					
        					if (listaAccordate.children.length === 1 &&
        						listaAccordate.children[0].tagName === "P") {
        						listaAccordate.removeChild(listaAccordate.children[0]);
        					}
        					listaAccordate.appendChild(refListaElemento);
          					parentNode.removeChild(buttonRef.nextElementSibling);
        					parentNode.removeChild(buttonRef.previousElementSibling);
        					parentNode.removeChild(buttonRef);

        					p.appendChild(document.createTextNode("Richiesta accordata!"));
        					parentNode.appendChild(p);

        				} else if (tipo === "declinoRichiesta" || tipo === "annullamentoRichiesta") {
        					if (tipo === "declinoRichiesta") {
        						var listaAccordande = document.getElementById("accordande");
        						
        						if (listaAccordande.children.length === 1 &&
        							listaAccordande.children[0].tagName === "DIV") {
        							p.appendChild(document.createTextNode("Nessuna richiesta di partecipazione trovata"));
        							listaAccordande.appendChild(p);
        						}
        					} else if (tipo === "annullamentoRichiesta") {
        						var listaAnnullande = document.getElementById("annullande");
        						
        						if (listaAnnullande.children.length === 1 &&
        							listaAnnullande.children[0].tagName === "DIV") {
        							p.appendChild(document.createTextNode("Nessuna richiesta d'annullamento trovata"));
        							listaAnnullande.appendChild(p);
        						}
        					}

                			refListaElemento.parentNode.removeChild(refListaElemento);
                		}
        			break;
        		}
            } else {
            	messaggi.innerText = "Errore di server. Riprovare";
            }
    	} else {
    		messaggi.innerText = "Errore di server. Riprovare";
    	}
    });

    ajaxReq.startRequest();
}


/**
 * Consente l'invio di una richiesta di partecipazione.
 * Metodo di comodità
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto la partecipazione/a cui si accorda la partecipazione
 * @returns
 */
function inviaRichiestaPartecipazione(buttonRef, idItinerario, idPartecipante) {
	effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante,
		"\@url-invio-richiesta-partecipazione", "invioPartecipazione");
}


/**
 * Consente l'invio di una richiesta di partecipazione.
 * Metodo di comodità
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto la partecipazione/a cui si accorda la partecipazione
 * @returns
 */
function inviaRichiestaAnnullamento(buttonRef, idItinerario, idPartecipante) {
	effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante,
		"\@url-invio-richiesta-annullamento", "invioAnnullamento");
}


/**
 * Consente l'invio di una richiesta di partecipazione.
 * Metodo di comodità
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto la partecipazione/a cui si accorda la partecipazione
 * @returns
 */
function accordaRichiestaPartecipazione(buttonRef, idItinerario, idPartecipante) {
	effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante,
		"\@url-accorda-richiesta", "accordoRichiesta");
}


/**
 * Consente il declino di una richiesta di partecipazione.
 * Metodo di comodità
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto la partecipazione/a cui si declina la partecipazione
 * @returns
 */
function declinaRichiestaPartecipazione(buttonRef, idItinerario, idPartecipante) {
	effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante,
		"\@url-declina-richiesta", "declinoRichiesta");
}


/**
 * Consente di annullare una richiesta di partecipazione.
 * Metodo di comodità
 * @param buttonRef elemento HTML che ha consentito l'interazione
 * @param idItinerario l'id dell'itinerario di riferimento
 * @param idPartecipante l'id del partecipante che ha richiesto l'annullamento/a cui si annnulla la partecipazione
 * @returns
 */
function annullaRichiestaPartecipazione(buttonRef, idItinerario, idPartecipante) {
	effettuaOperazionePartecipazione(buttonRef, idItinerario, idPartecipante,
		"\@url-annulla-richiesta", "annullamentoRichiesta");
}




/*
 * Consente di effettuare ricerche, ottenere la risposta ed interagire.
 * Anche qui vanno fatte delle sostituzioni...
 * 
 * Utilizzato in SCHERMATA_RICERCA_ITINERARI
 */
function effettuaRicerca() {
	var page = "\@form-req-ricerca";// + "&XDEBUG_SESSION_START=ECLIPSE_DBGP&KEY=157270718956123";

    /*
     * INIZIO Creazione body richiesta
     */
    var campi = [
    	"\@campo-luogo-contiene", "\@campo-itinerario-contiene",
    	"\@campo-filtro-data", "\@campo-data", "\@campo-ora",
    	"\@campo-popolarita-1", "\@campo-popolarita-2", "\@campo-popolarita-3",
    	"\@campo-popolarita-4", "\@campo-popolarita-5", "\@campo-includi-non-aperti",
    	"\@campo-includi-itinerari-partecipante", "\@campo-includi-itinerari-organizzatore"
    ];

    var body = {};
    for (var i of campi) {
    	if (i !== "") {
        	var campo = document.getElementsByName(i)[0];

        	if (campo.tagName === "INPUT" && campo.type !== "checkbox" ||
        		(campo.type === "checkbox" && campo.checked)) {
        		body[i] = campo.value;

        	} else if (campo.tagName === "SELECT") {
        		body[i] = campo.options[campo.selectedIndex].value;
        	}    		
    	}
    }
    /*
     * FINE Creazione body richiesta
     */

    var messaggi = document.getElementById("messaggi");

    var risultatiRicerca = document.getElementById("risultati-ricerca");

	var ajaxReq = new AJAXRequest(page, "POST");
	ajaxReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    ajaxReq.setBody(body);
    ajaxReq.setOnLoadHandler(function () {
    	if (this.readyState === 4 && this.status === 200) {
    		console.log(this.responseText);

    		var received = JSON.parse(this.responseText);
    		if (received.hasOwnProperty("error")) {
            	messaggi.innerText = received["error"];

            } else if (received.length === 0) {
            	//Rimuovi precedente risultato
            	while (risultatiRicerca.firstChild) {
            		risultatiRicerca.removeChild(risultatiRicerca.firstChild);
            	}
            	
            	var p = document.createElement("p");
            	p.appendChild(document.createTextNode("La ricerca non ha prodotto risultati"));
            	risultatiRicerca.appendChild(p);
            	messaggi.innerText = "La ricerca non ha prodotto risultati";

            } else {
            	//Rimuovi precedente risultato
            	while (risultatiRicerca.firstChild) {
            		risultatiRicerca.removeChild(risultatiRicerca.firstChild);
            	}

            	for (var i of received) {
            		var elementoItinerario = new ElementoItinerario(i);
            		risultatiRicerca.appendChild(elementoItinerario.getWrapperRoot());
            	}
            	
            	messaggi.innerText = "";
            }
    	} else {
    		messaggi.innerText = "Errore di server. Riprovare";
    	}
    });

    ajaxReq.startRequest();
}


function ElementoItinerario(obj) {
	ElementoLista.call(this);
	this.initItinerario(obj);
}

ElementoItinerario.prototype = new ElementoLista();


ElementoItinerario.prototype.initItinerario = function(obj) {
	var urlVisualizzazioneProfilo = "\@url-visualizzazione-profilo";
	var urlCreaFeedbackOP = "\@url-crea-feedback-op";
	var urlCreaFeedbackPO = "\@url-crea-feedback-po";
	var percorsoImmaginiItinerari = "\@percorso-immagini-itinerari";
	var urlVisualizzazioneItinerario = "\@url-visualizzazione-itinerario"
	var urlModificaItinerario = "\@url-modifica-itinerario";

	var contentDiv = this.getContentDiv();
	var datiItinerario = obj["datiItinerario"];
	var ciceroneOrganizzatore = datiItinerario["ciceroneInstance"];
	var valuta = datiItinerario["valutaInstance"];

	//Passi per creare l'elemento HTML
	contentDiv.appendChild(document.createTextNode("Itinerario \\"" + datiItinerario["nome"] + "\\""));
	contentDiv.appendChild(document.createElement("br"));
	contentDiv.appendChild(document.createTextNode("organizzato da "));
	var a = document.createElement("a");
	a.href = urlVisualizzazioneProfilo + ciceroneOrganizzatore["id"];
	a.appendChild(document.createTextNode(ciceroneOrganizzatore["nomeUtente"]));
	contentDiv.appendChild(a);
	contentDiv.appendChild(document.createElement("br"));

	//Creazione elementi di "element-info-side"
	var divInfoSide = document.createElement("div");
	divInfoSide.className = "element-info-side";
	contentDiv.appendChild(divInfoSide);

	var p = document.createElement("p");
	p.className = "no-margin";
	var b = document.createElement("b");
	b.appendChild(document.createTextNode("Breve descrizione"));
	p.appendChild(b);
	p.appendChild(document.createTextNode(":"));
	p.appendChild(document.createElement("br"));
	
	var descrizione = datiItinerario["descrizione"].substring(0, 20) + "...";
	var righeDescrizione = descrizione.split("\\n");

	for (var i = 0; i < righeDescrizione.length; i++) {
		p.appendChild(document.createTextNode(righeDescrizione[i]));
		p.appendChild(document.createElement("br"));
	}	
	divInfoSide.appendChild(p);

	p = document.createElement("p");
	b = document.createElement("b");
	b.appendChild(document.createTextNode("Compenso"));
	p.appendChild(b);
	p.appendChild(document.createTextNode(":"));
	p.appendChild(document.createElement("br"));

	var compenso = datiItinerario["compenso"];
	if (compenso === 0) {
		p.appendChild(document.createTextNode("Gratuito"));
	} else {
		if (valuta["centesimale"]) {
			compenso = compenso / 100.0;
		}
		p.appendChild(document.createTextNode(compenso + " " + valuta["simbolo"]));
	}
	divInfoSide.appendChild(p);

	//Creazione settore specifico per l'eventuale utente collegato
	if (obj.hasOwnProperty("idFruitore") && !obj["isCicerone"]) {
		if (datiItinerario["stato"] === "aperto") {
			if (obj["statoPartecipazione"] === null) {
				a = document.createElement("a");
				a.className = "element-button block";
				a.href = "javascript:;";
				a.onclick = function() {
					inviaRichiestaPartecipazione(this, datiItinerario["id"],  obj["idFruitore"]);
				};
				a.appendChild(document.createTextNode("Invia richiesta partecipazione"));
				divInfoSide.appendChild(a);

			} else if (obj["statoPartecipazione"] === "accordata") {
				a = document.createElement("a");
				a.className = "element-button block";
				a.href = "javascript:;";
				a.onclick = function() {
					inviaRichiestaAnnullamento(this, datiItinerario["id"], obj["idFruitore"]);
				}
				a.appendChild(document.createTextNode("Invia richiesta d'annullamento"));
				divInfoSide.appendChild(a);
				
			} else if (obj["statoPartecipazione"] === "accordanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione in attesa di"));
				p.appendChild(document.createElement("br"));
				p.appendChild(document.createTextNode("essere accordata..."));
				divInfoSide.appendChild(p);
				
			} else if (obj["statoPartecipazione"] === "annullanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione in attesa di"));
				p.appendChild(document.createElement("br"));
				p.appendChild(document.createTextNode("essere annullata..."));
				divInfoSide.appendChild(p);
			}

		} else if (datiItinerario["stato"] === "itinere") {
			if (obj["statoPartecipazione"] === null) {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("L'itinerario è in esecuzione,"));
				p.appendChild(document.createElement("br"));
				p.appendChild(document.createTextNode("non è possibile richiedere"));
				p.appendChild(document.createElement("br"));
				p.appendChild(document.createTextNode("la partecipazione..."));
				divInfoSide.appendChild(p);
			} else if (obj["statoPartecipazione"] === "accordata") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Stai partecipando all'itinerario..."));
				divInfoSide.appendChild(p);
			} else if (obj["statoPartecipazione"] === "accordanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione mai accordata..."));
				divInfoSide.appendChild(p);
			} else if (obj["statoPartecipazione"] === "annullanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione mai annullata..."));
				divInfoSide.appendChild(p);
			}

		} else if (datiItinerario["stato"] === "concluso") {
			if (obj["statoPartecipazione"] === null) {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Non è possibile richiedere"));
				p.appendChild(document.createElement("br"));
				p.appendChild(document.createTextNode("la partecipazione..."));
				divInfoSide.appendChild(p);
			} else if (obj["statoPartecipazione"] === "accordata") {
				if (!obj["feedbackRilasciato"]) {
					a = document.createElement("a");
					a.className = "element-button block";
					a.href = urlCreaFeedbackPO + datiItinerario["id"];
					a.appendChild(document.createTextNode("Rilascia feedback all'organizzatore"));
					divInfoSide.appendChild(a);
				}
			} else if (obj["statoPartecipazione"] === "accordanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione mai accordata..."));
				divInfoSide.appendChild(p);
			} else if (obj["statoPartecipazione"] === "annullanda") {
				p = document.createElement("p");
				p.className = "no-margin";
				p.appendChild(document.createTextNode("Partecipazione mai annullata..."));
				divInfoSide.appendChild(p);
			}

		} else if (datiItinerario["stato"] === "chiuso") {
			p = document.createElement("p");
			p.className = "no-margin";
			p.appendChild(document.createTextNode("Non è possibile richiedere"));
			p.appendChild(document.createElement("br"));
			p.appendChild(document.createTextNode("la partecipazione..."));
			divInfoSide.appendChild(p);
		}
	}
	
	
	//Creazione elementi di "element-button-side"
	var divButtonSide = document.createElement("div");
	divButtonSide.className = "element-button-side";
	contentDiv.appendChild(divButtonSide);


	var img = document.createElement("img");
	img.className = "element-img";
	img.alt = "immagine itinerario";
	img.src = percorsoImmaginiItinerari + "/" + datiItinerario["immagine"];
	divButtonSide.appendChild(img);

	p = document.createElement("p");
	p.className = "no-margin";
	b = document.createElement("b");
	b.appendChild(document.createTextNode("Stato"));
	p.appendChild(b);

	var stato = datiItinerario["stato"];

	switch (stato) {
		case "aperto":
			stato = "Aperto";
		break;
		case "itinere":
			stato = "In Esecuzione";
		break;
		case "concluso":
			stato = "Concluso";
		break;
		case "chiuso":
			stato = "Chiuso";
		break;
	}

	p.appendChild(document.createTextNode(": " + stato));
	divButtonSide.appendChild(p);

	a = document.createElement("a");
	a.className = "element-button block";
	a.href = urlVisualizzazioneItinerario + datiItinerario["id"];
	a.appendChild(document.createTextNode("Visualizza"));
	divButtonSide.appendChild(a);


	if (obj.hasOwnProperty("idFruitore") && obj["isCicerone"]) {
		a = document.createElement("a");
		a.className = "element-button block";
		a.href = urlModificaItinerario + datiItinerario["id"];
		a.appendChild(document.createTextNode("Modifica"));
		divButtonSide.appendChild(a);

		if (!obj["feedbackRilasciato"]) {
			a = document.createElement("a");
			a.className = "element-button block";
			a.href = urlCreaFeedbackOP + datiItinerario["id"];
			a.appendChild(document.createTextNode("Rilascia feedback ai partecipanti"));
			divInfoSide.appendChild(a);
		}
	}
};

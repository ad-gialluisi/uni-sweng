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
 * PAGINE UTILIZZATE DURANTE IL DEBUG:
 * Banali pagine che rappresentano cosa il sistema può fare.
 * Se non si impostano queste, non è possibile creare i preset.
 * 
 * Convenzioni:
 * [SCH] = pagina indicante una schermata
 * [REQ] = pagina indicante una richiesta (detto semplicemente, esegue un'azione)
 * 
 * Sintassi:
 *     label: autoesplicativo
 *     url: autoesplicativo
 *     selected: (se impostato, non importa il valore, il sistema imposta direttamente la voce come
 * selezionata)
 */
var PAGES = [
	/*00*/{"label":                           "[SCH] Pagina di test", "url":                              "test.php?schermata=test"},
	/*01*/{"label":                         "[REQ] Effettua accesso", "url":                        "accesso.php?richiesta=accesso"},
	/*02*/{"label":                           "[REQ] Disconnessione", "url":                 "accesso.php?richiesta=disconnessione", "selected": ""},
	/*03*/{"label":                          "[REQ] Imposta accesso", "url":                "accesso.php?richiesta=impostarecupero"},
	/*04*/{"label":                "[REQ] Recupero (attiva) accesso", "url":                 "accesso.php?richiesta=attivarecupero"},
	/*05*/{"label":                                  "[SCH] Profilo", "url":                        "profilo.php?schermata=profilo"},
	/*06*/{"label":                         "[SCH] Modifica profilo", "url":                     "profilo.php?schermata=modProfilo"},
	/*07*/{"label":                         "[REQ] Modifica profilo", "url":                     "profilo.php?richiesta=modProfilo"},
	/*08*/{"label":                      "[SCH] Modifica anagrafica", "url":                  "profilo.php?schermata=modAnagrafica"},
	/*09*/{"label":                      "[REQ] Modifica anagrafica", "url":                  "profilo.php?richiesta=modAnagrafica"},
	/*10*/{"label":                "[SCH] Richieste amministrazione", "url":     "amministrazione.php?schermata=reqAmministrazione"},
	/*11*/{"label":                  "[SCH] Creazione rich. disiscr", "url":   "amministrazione.php?schermata=creaReqDisiscrizione"},
	/*12*/{"label":            "[SCH] Creazione rich. aggiornamento", "url":   "amministrazione.php?schermata=creaReqAggiornamento"},
	/*13*/{"label":                 "[SCH] Visualizza rich. disiscr", "url":       "amministrazione.php?schermata=reqDisiscrizione"},
	/*14*/{"label":               "[SCH] Visualizza rich. aggiornam", "url":       "amministrazione.php?schermata=reqAggiornamento"},
	/*15*/{"label":             "[REQ] Crea Richiesta aggiornamento", "url":   "amministrazione.php?richiesta=creaReqAggiornamento"},
	/*16*/{"label":             "[REQ] Crea Richiesta disiscrizione", "url":   "amministrazione.php?richiesta=creaReqDisiscrizione"},
	/*17*/{"label":               "[REQ] Trasforma in QuasiCicerone", "url":     "amministrazione.php?richiesta=transQuasiCicerone"},
	/*18*/{"label":                          "[SCH] Crea itinerario", "url":                        "itinerario.php?schermata=crea"},
	/*19*/{"label":                      "[SCH] Modifica itinerario", "url":                   "itinerario.php?schermata=modifica"},
	/*20*/{"label":                        "[SCH] Ricerca itinerari", "url":                    "itinerario.php?schermata=ricerca"},
	/*21*/{"label":                    "[SCH] Visualizza itinerario", "url":                 "itinerario.php?schermata=itinerario"},
	/*22*/{"label":            "[SCH] Visualizza itinerari fruitore", "url":                "itinerario.php?schermata=lsItinerari"},
	/*23*/{"label":           "[SCH] Visualizza ultimi 20 itinerari", "url":                                           "index.php"},
	/*24*/{"label":                          "[REQ] Crea itinerario", "url":                       "itinerario.php?richiesta=crea"},
	/*25*/{"label":                      "[REQ] Modifica itinerario", "url":                   "itinerario.php?richiesta=modifica"},
	/*26*/{"label":                       "[REQ] Rimuovi itinerario", "url":                    "itinerario.php?richiesta=rimuovi"},
	/*27*/{"label":                       "[AJAX] Ricerca itinerari", "url":                    "itinerario.php?richiesta=ricerca"},
	/*28*/{"label":          "[AJAX] Invio richiesta partecipazione", "url":     "itinerario.php?richiesta=inviaReqPartecipazione"},
	/*29*/{"label":            "[AJAX] Invio richiesta annullamento", "url":       "itinerario.php?richiesta=inviaReqAnnullamento"},
	/*30*/{"label":          "[SCH] Lista partecipazioni itinerario", "url":           "itinerario.php?schermata=lsPartecipazioni"},
	/*31*/{"label":        "[AJAX] Accorda richiesta partecipazione", "url":                    "itinerario.php?richiesta=accordo"},
	/*32*/{"label":        "[AJAX] Declina richiesta partecipazione", "url":               "itinerario.php?richiesta=annullamento"},
	/*33*/{"label":        "[AJAX] Annulla richiesta partecipazione", "url":                    "itinerario.php?richiesta=declino"},
	/*34*/{"label":                     "[REQ] Registrazione utente", "url":                 "accesso.php?richiesta=registrazione"},
	/*35*/{"label":                  "[SCH] Lista feedback fruitore", "url":                   "feedback.php?schermata=lsFeedback"},
	/*36*/{"label":                      "[SCH] Visualizza feedback", "url":                     "feedback.php?schermata=feedback"},
	/*37*/{"label": "[SCH] Crea feedback Organizzatore-Partecipante", "url":               "feedback.php?schermata=creaFeedbackOP"},
	/*38*/{"label": "[SCH] Crea feedback Partecipante-Organizzatore", "url":               "feedback.php?schermata=creaFeedbackPO"},
	/*39*/{"label": "[REQ] Crea feedback Organizzatore-Partecipante", "url":               "feedback.php?richiesta=creaFeedbackOP"},
	/*40*/{"label": "[REQ] Crea feedback Partecipante-Organizzatore", "url":               "feedback.php?richiesta=creaFeedbackPO"},
];


/*
 * PRESET UTILIZZATI DURANTE IL DEBUG
 * 
 * Come suggerisce il nome, sono pagine "preparate con dei valori di default".
 * Questo viene fatto per facilitare il testing.
 * 
 * Convenzioni:
 * Stesse di sopra
 * 
 * Sintassi:
 *     label: autoesplicativo
 *     page: un riferimento (numerico) ad una delle pagine di sopra
 *     method: metodo di passaggio dati (se trattasi di richiesta), può essere "get" o "post"
 *     params: una serie di parametri separati da un "\\n" (a capo), verranno impostati (correttamente) nelle richieste GET o POST
 *     debug: se impostato (non importa il valore), abilita di default il debug per la voce
 *     selected: (se impostato, non importa il valore, il sistema imposta direttamente la voce come
 * selezionata)
 * 
 */
var PRESETS = [
	//
	/*00*/{"label":                     "[REQ] Accesso Globetrotter", "page":  1, "method": "post", "params":     "nomeutente=neroneclaudio\\npassword=neroneclaudio"}, 
	/*01*/{"label":                         "[REQ] Accesso Cicerone", "page":  1, "method": "post", "params":               "nomeutente=caligola\\npassword=caligola"}, 
	/*02*/{"label":                   "[REQ] Accesso Amministratore", "page":  1, "method": "post", "params":   "nomeutente=antoniodaniele\\npassword=antoniodaniele"},	
	/*03*/{"label":               "[REQ] Recupero accesso (imposta)", "page":  3, "method": "post", "params":                                  "email=test1@test.com"},
	/*04*/{"label":                "[REQ] Recupero accesso (attiva)", "page":  4, "method": "post", "params":        "password=\\nconfermapassword=\\ncodice=\\nid=1"},
	/*03*/{"label":                     "[SCH] Profilo Globetrotter", "page":  5, "method":  "get", "params": "id=4"},
	/*04*/{"label":                         "[SCH] Profilo Cicerone", "page":  5, "method":  "get", "params": "id=2"},
	/*05*/{"label":                   "[SCH] Profilo Amministratore", "page":  5, "method":  "get", "params": "id=1"},
	/*06*/{"label":                            "[REQ] Salva profilo", "page":  7, "method": "post", "params": "descrizione=test descrizione\\nemail=t%40t\\nvecchiapassword=caligola\\nnuovapassword=\\nconfermanuovapassword="},
	/*07*/{"label":                         "[REQ] Salva anagrafica", "page":  9, "method": "post", "params": "telefono=%2B390804803434\\nresidenza=NessunLuogo"},
	/*08*/{"label":                 "[SCH] Visualizza rich. disiscr", "page": 13, "method":  "get", "params": "id=1"},
	/*09*/{"label":               "[SCH] Visualizza rich. aggiornam", "page": 14, "method":  "get", "params": "id=1"},
	/*10*/{"label":             "[REQ] Crea Richiesta aggiornamento", "page": 15, "method": "post", "params": "nome=all\\ncognome=john\\ndata_nascita=1000-01-01\\nluogo_nascita=jack\\nresidenza=cipeciop\\ntelefono=080286386686\\ncodice_fiscale=CLMPSC00P42F262T"},
	/*11*/{"label":             "[REQ] Crea Richiesta disiscrizione", "page": 16, "method": "post", "params": "descrizione=se%20vedemo%20a%20rubicone%20domani%20mattina%20alle%208asdfasdfasdfasdfasdfasdf"},
	/*12*/{"label":               "[REQ] Trasforma in QuasiCicerone", "page": 17, "method": "post", "params": "id=4"},
	/*13*/{"label":                      "[SCH] Modifica Itinerario", "page": 19, "method": "post", "params": "id=1", "selected": ""},
	/*14*/{"label":                    "[SCH] Visualizza itinerario", "page": 21, "method":  "get", "params": "id=1", "selected": ""},
	/*15*/{"label":            "[SCH] Visualizza itinerari fruitore", "page": 22, "method":  "get", "params": "id=1", "selected": ""},
	/*16*/{"label":                          "[REQ] Crea itinerario", "page": 24, "method": "post", "params": "nome=pincopallo\\ndata=2016-03-08\\nora=10:00:00\\nlingua=italiano\\nluogo=Pisa\\ndescrizione=domo%20arigato%20mr%20roboto\\npopolarit%C3%A0=1\\nvaluta=euro\\ncompenso=30000"},
	/*17*/{"label":                      "[REQ] Modifica itinerario", "page": 25, "method": "post", "params": "id=1"},
	/*18*/{"label":                       "[REQ] Rimuovi itinerario", "page": 26, "method": "post", "params": "id=1"},
	/*19*/{"label":                       "[AJAX] Ricerca itinerari", "page": 27, "method": "post", "selected": "", "debug": true, "params": "luogo-contiene=\\nitinerario-contiene=\\nfiltro-data-ora=\\ndata=\\nora=\\nincludi-non-aperti=1\\nincludi-itinerari-partecipante="},
	/*20*/{"label":          "[AJAX] Invio richiesta partecipazione", "page": 28, "method": "post", "params": "idItinerario=1\\nidPartecipante=4"},
	/*21*/{"label":            "[AJAX] Invio richiesta annullamento", "page": 29, "method": "post", "params": "idItinerario=1\\nidPartecipante=4"},
	/*22*/{"label":          "[SCH] Lista partecipazioni itinerario", "page": 30, "method":  "get", "params": "id=1"},
	/*23*/{"label":        "[AJAX] Accorda richiesta partecipazione", "page": 31, "method": "post", "params": "idItinerario=1\\nidPartecipante=4"},
	/*24*/{"label":        "[AJAX] Declina richiesta partecipazione", "page": 32, "method": "post", "params": "idItinerario=1\\nidPartecipante=3"},
	/*25*/{"label":        "[AJAX] Annulla richiesta partecipazione", "page": 33, "method": "post", "params": "idItinerario=1\\nidPartecipante=3"},
	/*26*/{"label":                     "[REQ] Registrazione utente", "page": 34, "method": "post", "params": "email=asdfasdf@asdfasdf\\nnomeutente=socratesis\\npassword=lollolollolollo\\nconfermapassword=lollolollolollo"},
	/*27*/{"label":                  "[SCH] Lista feedback fruitore", "page": 35, "method":  "get", "params": "id=1"},
	/*28*/{"label":                      "[SCH] Visualizza feedback", "page": 36, "method":  "get", "params": "id=1"},
	/*29*/{"label": "[SCH] Crea feedback Organizzatore-Partecipante", "page": 37, "method":  "get", "params": "id=1"},
	/*30*/{"label": "[SCH] Crea feedback Partecipante-Organizzatore", "page": 38, "method":  "get", "params": "id=1"},
	/*31*/{"label": "[REQ] Crea feedback Organizzatore-Partecipante", "page": 39, "method": "post", "params": "idItinerario=1\\nidPartecipante=4\\ndescrizione=tosto\\nvoto=4"},
	/*32*/{"label": "[REQ] Crea feedback Partecipante-Organizzatore", "page": 40, "method": "post", "params": "idItinerario=1\\ndescrizione=tosto\\nvoto=4"},
];

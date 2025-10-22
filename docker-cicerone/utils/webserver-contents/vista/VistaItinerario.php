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


namespace vista;

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreItinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Partecipazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Valuta.php";
require_once "TriplettaSemplice.php";
require_once "VistaProfilo.php";
require_once "Vista.php";


use modello\entità\Utente;
use modello\entità\Itinerario;
use modello\entità\Partecipazione;
use modello\entità\Valuta;
use controllore\ControlloreItinerario;
use modello\ModelloItinerario;


/**
 * Rappresenta la vista associata al ControlloreItinerario e al ModelloItinerario.
 * 
 * <p>Ha sei schermate, la prima per la creazione di un nuovo itinerario, la seconda
 * per la modifica di un itinerario esistente, la terza per consentire la ricerca
 * di itinerari, la quarta per la visualizzazione dei dettagli di un itinerario,
 * la quinta per la visualizzazione degli itinerari associati ad un fruitore e
 * la sesta per la visualizzazione delle richieste di partecipazione ad un itinerario.</p>
 * 
 * @see \controllore\ControlloreItinerario
 * @see \modello\ModelloItinerario
 */
class VistaItinerario extends Vista {
    protected const PAGINA_VISTA = "itinerario.php";
    
    /*
     * Richieste che questa vista prende in considerazione
     */
    /*
     * Sezione itinerari
     */
    /**
     * Richiesta che consente la creazione di un itinerario.
     */
    private const RICHIESTA_CREAZIONE_ITINERARIO = "crea";

    /**
     * Richiesta che consente la modifica di un itinerario.
     */
    private const RICHIESTA_MODIFICA_ITINERARIO = "modifica";

    /**
     * Richiesta che consente la rimozione di un itinerario.
     */
    private const RICHIESTA_RIMUOVI_ITINERARIO = "rimuovi";
    
    /**
     * Richiesta che consente la ricerca di itinerari.
     */
    private const RICHIESTA_RICERCA_ITINERARI = "ricerca";

    /*
     * Sezione partecipazioni
     */
    private const RICHIESTA_ACCORDO_RICHIESTA_PARTECIPAZIONE = "accordo";
    private const RICHIESTA_ANNULLAMENTO_RICHIESTA_PARTECIPAZIONE = "annullamento";
    private const RICHIESTA_DECLINO_RICHIESTA_PARTECIPAZIONE = "declino";
    private const RICHIESTA_INVIO_RICHIESTA_PARTECIPAZIONE = "inviaReqPartecipazione";
    private const RICHIESTA_INVIO_RICHIESTA_ANNULLAMENTO = "inviaReqAnnullamento";



    /*
     * Le diverse schermate che la vista mostra.
     */
    /*
     * Sezione itinerari
     */
    /**
     * Schermata che consente la creazione di un itinerario.
     */
    private const SCHERMATA_CREAZIONE_ITINERARIO = "crea";

    /**
     * Schermata che consente la modifica di un itinerario.
     */
    private const SCHERMATA_MODIFICA_ITINERARIO = "modifica";
    
    /**
     * Schermata che consente la ricerca di un itinerario.
     */
    private const SCHERMATA_RICERCA_ITINERARI = "ricerca";
    
    /**
     * Schermata che consente la visualizzazione dei dettagli di un itinerario.
     */
    private const SCHERMATA_VISUALIZZAZIONE_ITINERARIO = "itinerario";
    
    /**
     * Schermata che consente la visualizzazione degli itinerari associati
     * ad un fruitore.
     */
    private const SCHERMATA_VISUALIZZAZIONE_ITINERARI_FRUITORE = "lsItinerari";

    /*
     * Sezione partecipazione
     */
    private const SCHERMATA_VISUALIZZAZIONE_RICHIESTE_PARTECIPAZIONE = "lsPartecipazioni";


    /*
     * I diversi "menù" accessibili nella schermata SCHERMATA_VISUALIZZAZIONE_ITINERARI_FRUITORE.
     * In realtà, viene utilizzato un trucco veloce in javascript.
     */
    private const MENU_ITINERARI_PARTECIPANTE = "itinerari-partecipante";
    private const MENU_ITINERARI_ORGANIZZATORE = "itinerari-organizzatore";


    /**
     * È il nome del file immagine di default associabile ai dettagli di un itinerario
     */
    public const FILE_IMMAGINE_DEFAULT = "default.png";


    /**
     * Array contenente le associati tra il valore della popolarità
     * ed un termine che rende l'idea al Cicerone quando andrà a
     * specificare la popolarità di un itinerario.
     */
    private const NOMI_POPOLARITÀ = array(
        1 => "Sconosciuto",
        2 => "Familiare",
        3 => "Conosciuto",
        4 => "Famoso",
        5 => "Celebre"
    );


    /**
     * Crea una VistaItinerario con un ControlloreItinerario sottostante
     */
    public function __construct() {
        parent::__construct(new ControlloreItinerario());
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::isRichiesta()
     */
    public function isRichiesta() : bool {
        return isset($this->getParams[self::GET_RICHIESTA]) &&
            !isset($this->getParams[self::GET_SCHERMATA]);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::elabora()
     */
    public function elabora() : void {
        $richiesta = $this->getParams[self::GET_RICHIESTA];

        /*
         * Distruggi tutta la messaggistica, dato che
         * stiamo facendo una richiesta nuova.
         */
        $this->controllore->distruggiMessaggistica();

        /*
         * Queste variabili vengono utilizzate per semplificare il flusso
         * d'esecuzione delle elaborazioni.
         * Lo scopo è eseguire le elaborazioni correttamente rigettando quelle
         * non valide, sfruttando poche condizioni.
         */
        $richiestaValida = true;
        $paginaRedirect = self::getURLHOME();
        
        $isAjax = false;
        $ajaxData = array();
        $itinerari = NULL;

        $isCiceroneConnesso = $this->controllore->isUtenteCicerone();
        $isIDDefinito = isset($this->postParams[ControlloreItinerario::CAMPO_ID]);
        if ($isIDDefinito) {
            $id = $this->postParams[ControlloreItinerario::CAMPO_ID];
            $isIDDefinito = preg_match(ModelloItinerario::REGEX_ID, $id);
        }

        $isRichiestaCreazione = false;
        $isRichiestaModifica = false;
        $isRichiestaRimozione = false;
        $isRichiestaPartecipazione = false;


        switch ($richiesta) {
            case self::RICHIESTA_CREAZIONE_ITINERARIO:
                $this->controllore->richiediCreazioneItinerario($this->postParams,
                    self::getPercorsoImmaginiItinerari(), self::FILE_IMMAGINE_DEFAULT);
                $isRichiestaCreazione = true;
            break;
            case self::RICHIESTA_MODIFICA_ITINERARIO:
                $this->controllore->richiediModificaItinerario($this->postParams,
                    self::getPercorsoImmaginiItinerari(), self::FILE_IMMAGINE_DEFAULT);
                $isRichiestaModifica = true;
            break;
            case self::RICHIESTA_RIMUOVI_ITINERARIO:
                $this->controllore->richiediRimozioneItinerario($this->getParams, 
                    self::getPercorsoImmaginiItinerari(), self::FILE_IMMAGINE_DEFAULT);
                $isRichiestaRimozione = true;
            break;
            case self::RICHIESTA_RICERCA_ITINERARI:
                $this->controllore->richiediRicercaItinerari($this->postParams, $itinerari);
                $isAjax = true;
            break;
            case self::RICHIESTA_ACCORDO_RICHIESTA_PARTECIPAZIONE:
                $this->controllore->richiediAccordoRichiestaPartecipazione($this->postParams);
                $isAjax = true;
                $isRichiestaPartecipazione = true;
            break;
            case self::RICHIESTA_ANNULLAMENTO_RICHIESTA_PARTECIPAZIONE:
                $this->controllore->richiediAnnullamentoRichiestaPartecipazione($this->postParams);
                $isAjax = true;
                $isRichiestaPartecipazione = true;
            break;
            case self::RICHIESTA_DECLINO_RICHIESTA_PARTECIPAZIONE:
                $this->controllore->richiediDeclinoRichiestaPartecipazione($this->postParams);
                $isAjax = true;
                $isRichiestaPartecipazione = true;
            break;
            case self::RICHIESTA_INVIO_RICHIESTA_PARTECIPAZIONE:
                $this->controllore->richiediInvioRichiestaPartecipazione($this->postParams);
                $isAjax = true;
                $isRichiestaPartecipazione = true;
            break;
            case self::RICHIESTA_INVIO_RICHIESTA_ANNULLAMENTO:
                $this->controllore->richiedInvioRichiestaAnnullamento($this->postParams);
                $isAjax = true;
                $isRichiestaPartecipazione = true;
            break;
            default:
                $richiestaValida = false;
            break;
        }


        if ($richiestaValida) {
            $statoOperazione = $this->controllore->getStatoOperazione();
            $this->controllore->copiaMessaggisticaPerSchermata();

            switch ($statoOperazione) {
                case ControlloreItinerario::STATO_OPERAZIONE_FALLITA:
                    if ($isAjax) {
                        $ajaxData["error"] = $this->controllore->getMessaggi();

                        /*
                         * Evitiamo che i messaggi d'errore si propagano nel sistema
                         * visto che trattasi di AJAX
                         */
                        $this->controllore->distruggiMessaggistica();

                    } else {
                        if ($isRichiestaCreazione && $isCiceroneConnesso) {
                            $paginaRedirect = $this->getSchermata(self::SCHERMATA_CREAZIONE_ITINERARIO);

                        } else if ($isRichiestaModifica && $isCiceroneConnesso && $isIDDefinito) {
                            $paginaRedirect = self::getURLModificaItinerario($id);

                        }
                    }
                break;
                case ControlloreItinerario::STATO_OPERAZIONE_RIUSCITA:
                    if ($isAjax) {
                        if ($isRichiestaPartecipazione) {
                            $ajaxData["success"] = true;
                        } else {
                            foreach ($itinerari as $itinerario) {
                                $ajaxData[]= $itinerario;
                            }
                        }

                        /*
                         * Evitiamo che i messaggi d'errore si propagano nel sistema
                         * visto che trattasi di AJAX
                         */
                        $this->controllore->distruggiMessaggistica();

                    } else {
                        if ($isRichiestaModifica) {
                            $paginaRedirect = self::getURLItinerario($id);
                        } else if ($isRichiestaCreazione || $isRichiestaRimozione) {
                            $paginaRedirect = self::getURLItinerariOrganizzatoreFruitore($this->controllore->getIDUtente());
                        }
                    }
                break;
                default:
                    if ($isAjax) {
                        $ajaxData["error"] = "Richiesta non valida";
                    }
                break;
            }


            if ($isAjax) {
                header("Content-Type: application/json");
                echo json_encode($ajaxData);
            } else {
                $this->mandaA($paginaRedirect);
            }
        } else {
            $this->mandaA($paginaRedirect);
        }
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        $schermata = $schermata = isset($this->getParams[self::GET_SCHERMATA]) ?
            $this->getParams[self::GET_SCHERMATA] : self::SCHERMATA_RICERCA_ITINERARI;


        $isCiceroneConnesso = $this->controllore->isUtenteCicerone();
        $isIDDefinito = isset($this->getParams[ControlloreItinerario::CAMPO_ID]);
        if ($isIDDefinito) {
            $id = $this->getParams[ControlloreItinerario::CAMPO_ID];
            $isIDDefinito = preg_match(ModelloItinerario::REGEX_ID, $id);
        }
        
        $isSchermataValida = true;

        $metodoSchermata = "";


        switch ($schermata) {
            case self::SCHERMATA_CREAZIONE_ITINERARIO:
                $metodoSchermata = "schermataCreazioneItinerario";
                $isSchermataValida = $isCiceroneConnesso;
            break;
            case self::SCHERMATA_MODIFICA_ITINERARIO:
                $metodoSchermata = "schermataModificaItinerario";
                $isSchermataValida = ($isCiceroneConnesso && $isIDDefinito &&
                    $this->controllore->isCiceroneOrganizzatoreDiItinerario(
                        $this->getParams[ControlloreItinerario::CAMPO_ID], 
                        $this->controllore->getIDUtente()
                ));
            break;
            case self::SCHERMATA_RICERCA_ITINERARI:
                $metodoSchermata = "schermataRicercaItinerari";
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_ITINERARIO:
                $metodoSchermata = "schermataVisualizzazioneItinerario";
                $isSchermataValida = $isIDDefinito;
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_ITINERARI_FRUITORE:
                $metodoSchermata = "schermataVisualizzazioneItinerariFruitore";
                $isSchermataValida = $isIDDefinito;
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_RICHIESTE_PARTECIPAZIONE:
                $isSchermataValida = isset($this->getParams[ControlloreItinerario::CAMPO_ID]);
                $metodoSchermata = "schermataVisualizzazioneRichiestePartecipazione";
            break;
            default:
                $isSchermataValida = false;
            break;
        }

        if ($isSchermataValida) {
            /*
             * Associa la schermata ottenuta alla messaggistica, così da permettere
             * di mantenere i messaggi finchè non si lascia la pagina.
             */
            $this->controllore->associaSchermataPerMessaggistica($schermata);
            
            $this->tripletta = new TriplettaSemplice("itinerario", "form/itinerario", true);
            $this->$metodoSchermata();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Crea schermata che consente la creazione di un itinerario
     */
    private function schermataCreazioneItinerario() : void {
        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $percorsoImmagineDefault = self::calcolaPercorsoImmagineItinerario(self::FILE_IMMAGINE_DEFAULT);

        $this->tripletta->applica(array(
            "schermata-crea-itinerario" => true,
            "percorso-immagine" => $percorsoImmagineDefault,
            "form-req-crea-itinerario" => self::getRichiesta(self::RICHIESTA_CREAZIONE_ITINERARIO),
            "campo-nome" => ControlloreItinerario::CAMPO_NOME,
            "campo-data" => ControlloreItinerario::CAMPO_DATA,
            "campo-ora" => ControlloreItinerario::CAMPO_ORA,
            "campo-lingua" => ControlloreItinerario::CAMPO_LINGUA,
            "campo-luogo" => ControlloreItinerario::CAMPO_LUOGO,
            "campo-compenso" => ControlloreItinerario::CAMPO_COMPENSO,
            "campo-popolarita" => ControlloreItinerario::CAMPO_POPOLARITÀ,
            "campo-descrizione" => ControlloreItinerario::CAMPO_DESCRIZIONE,
            "campo-valuta" => ControlloreItinerario::CAMPO_VALUTA,
            "campo-immagine-upload" => ControlloreItinerario::CAMPO_IMMAGINE_UPLOAD,
            "campo-ripristina-immagine" => ControlloreItinerario::CAMPO_RIPRISTINA_IMMAGINE,
            "nome-popolarita-1" => self::NOMI_POPOLARITÀ[1],
            "nome-popolarita-2" => self::NOMI_POPOLARITÀ[2],
            "nome-popolarita-3" => self::NOMI_POPOLARITÀ[3],
            "nome-popolarita-4" => self::NOMI_POPOLARITÀ[4],
            "nome-popolarita-5" => self::NOMI_POPOLARITÀ[5],
        ), TriplettaSemplice::HTML);

        $valute = NULL;
        $this->controllore->richiediValute($valute);

        foreach($valute as $valuta) {
            $nomeValuta = $valuta->getValuta();

            $this->tripletta->add(
                "altre-valute", "<option value=\"$nomeValuta\">$nomeValuta</option>",
                TriplettaSemplice::HTML
            );
        }

        $this->aggiungiGestoreAnteprimaImmagine($percorsoImmagineDefault);
        $this->setTitolo("Creazione di un nuovo itinerario");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la modifica di un itinerario
     */
    private function schermataModificaItinerario() : void {
        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $id = $this->getParams[ControlloreItinerario::CAMPO_ID];

        $itinerario = NULL;
        $this->controllore->richiediItinerario($id, $itinerario);

        $percorsoImmagineDefault = self::calcolaPercorsoImmagineItinerario(self::FILE_IMMAGINE_DEFAULT);

        $dataOra = $itinerario->getData();
        $dataOra = explode(" ", $dataOra);
        $dataOra[1] = substr($dataOra[1], 0, 5);

        $popolarità = $itinerario->getPopolarità();

        $this->tripletta->applica(array(
            "schermata-modifica-itinerario" => true,
            "percorso-immagine" => self::calcolaPercorsoImmagineItinerario($itinerario->getImmagine()),
            "form-req-modifica-itinerario" => self::getRichiesta(self::RICHIESTA_MODIFICA_ITINERARIO),
            "campo-id" => ControlloreItinerario::CAMPO_ID,
            "campo-nome" => ControlloreItinerario::CAMPO_NOME,
            "campo-data" => ControlloreItinerario::CAMPO_DATA,
            "campo-ora" => ControlloreItinerario::CAMPO_ORA,
            "campo-lingua" => ControlloreItinerario::CAMPO_LINGUA,
            "campo-luogo" => ControlloreItinerario::CAMPO_LUOGO,
            "campo-compenso" => ControlloreItinerario::CAMPO_COMPENSO,
            "campo-popolarita" => ControlloreItinerario::CAMPO_POPOLARITÀ,
            "campo-descrizione" => ControlloreItinerario::CAMPO_DESCRIZIONE,
            "campo-valuta" => ControlloreItinerario::CAMPO_VALUTA,
            "campo-immagine-upload" => ControlloreItinerario::CAMPO_IMMAGINE_UPLOAD,
            "campo-ripristina-immagine" => ControlloreItinerario::CAMPO_RIPRISTINA_IMMAGINE,
            "campo-stato" => ControlloreItinerario::CAMPO_STATO,
            "nome-popolarita-1" => self::NOMI_POPOLARITÀ[1],
            "nome-popolarita-2" => self::NOMI_POPOLARITÀ[2],
            "nome-popolarita-3" => self::NOMI_POPOLARITÀ[3],
            "nome-popolarita-4" => self::NOMI_POPOLARITÀ[4],
            "nome-popolarita-5" => self::NOMI_POPOLARITÀ[5],
            "id" => $itinerario->getID(),
            "nome" => $itinerario->getNome(),
            "data" => $dataOra[0],
            "ora" => $dataOra[1],
            "lingua" => $itinerario->getLingua(),
            "luogo" => $itinerario->getLuogo(),
            "compenso" => $itinerario->getCompenso(),
            "selezionato-". $popolarità => "selected='selected'",
            "descrizione" => $itinerario->getDescrizione(),
        ), TriplettaSemplice::HTML);

        switch ($itinerario->getStato()) {
            case Itinerario::STATO_APERTO:
                $this->tripletta->setPulsante("stato-aperto", true, TriplettaSemplice::HTML);
                $this->tripletta->add("nome-stato-itinere", Itinerario::STATO_ITINERE,
                    TriplettaSemplice::HTML);
            break;
            case Itinerario::STATO_ITINERE:
                $this->tripletta->setPulsante("stato-itinere", true, TriplettaSemplice::HTML);
                $this->tripletta->add("nome-stato-concluso", Itinerario::STATO_CONCLUSO,
                    TriplettaSemplice::HTML);
            break;
            case Itinerario::STATO_CONCLUSO:
                $this->tripletta->setPulsante("stato-concluso", true, TriplettaSemplice::HTML);
                $this->tripletta->add("nome-stato-chiuso", Itinerario::STATO_CHIUSO,
                    TriplettaSemplice::HTML);
            break;
            default:
                //Mai eseguito
            break;
        }

        $valute = NULL;
        $this->controllore->richiediValute($valute);

        foreach($valute as $valuta) {
            $nomeValuta = $valuta->getValuta();

            if ($itinerario->getNomeValuta() === $nomeValuta) {
                $option = "<option selected='selected' value=\"$nomeValuta\">$nomeValuta</option>";
            } else {
                $option = "<option value=\"$nomeValuta\">$nomeValuta</option>";
            }

            $this->tripletta->add("altre-valute", $option,
                TriplettaSemplice::HTML
            );
        }


        $this->aggiungiGestoreAnteprimaImmagine($percorsoImmagineDefault);
        $this->setTitolo("Modifica dell'itinerario \"" . $itinerario->getNome() . "\"");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la ricerca di itinerari
     */
    private function schermataRicercaItinerari() : void {
        $this->tripletta->setPulsante("schermata-ricerca-itinerari",
            true, TriplettaSemplice::HTML);

        $isFruitore = $this->controllore->isUtenteFruitore();

        $this->tripletta->applica(array(
            "schermata-ricerca-itinerari" => true,
            "campo-data" => ControlloreItinerario::CAMPO_DATA,
            "campo-ora" => ControlloreItinerario::CAMPO_ORA,
            "campo-popolarita-1" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_1,
            "campo-popolarita-2" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_2,
            "campo-popolarita-3" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_3,
            "campo-popolarita-4" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_4,
            "campo-popolarita-5" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_5,
            "campo-itinerario-contiene" => ControlloreItinerario::CAMPO_ITINERARIO_CONTIENE,
            "campo-luogo-contiene" => ControlloreItinerario::CAMPO_LUOGO_CONTIENE,
            "campo-includi-non-aperti" => ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_NON_APERTI,
            "campo-includi-itinerari-partecipante" => ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE,
            "campo-includi-itinerari-organizzatore" => ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE,
            "campo-filtro-data" => ControlloreItinerario::CAMPO_FILTRO_DATA_ORA,
            "nome-popolarita-1" => self::NOMI_POPOLARITÀ[1],
            "nome-popolarita-2" => self::NOMI_POPOLARITÀ[2],
            "nome-popolarita-3" => self::NOMI_POPOLARITÀ[3],
            "nome-popolarita-4" => self::NOMI_POPOLARITÀ[4],
            "nome-popolarita-5" => self::NOMI_POPOLARITÀ[5],
        ), TriplettaSemplice::HTML);

        if ($isFruitore) {
            $isCicerone = $this->controllore->isUtenteCicerone();

            $this->tripletta->applica(array(
                "param-id-itinerario" => ControlloreItinerario::CAMPO_ID_ITINERARIO,
                "param-id-partecipante" => ControlloreItinerario::CAMPO_ID_PARTECIPANTE,
                "url-invio-richiesta-annullamento" => self::getURLInvioRichiestaAnnullamento(),
                "url-invio-richiesta-partecipazione" => self::getURLInvioRichiestaPartecipazione(),
                "campo-includi-itinerari-partecipante" => ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE,
                "campo-includi-itinerari-organizzatore" => $isCicerone ? ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE : "",
            ), TriplettaSemplice::JAVASCRIPT);

        } else {
            $isCicerone = false;
        }


        $this->tripletta->applica(array(
            "campo-data" => ControlloreItinerario::CAMPO_DATA,
            "campo-ora" => ControlloreItinerario::CAMPO_ORA,
            "campo-popolarita-1" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_1,
            "campo-popolarita-2" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_2,
            "campo-popolarita-3" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_3,
            "campo-popolarita-4" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_4,
            "campo-popolarita-5" => ControlloreItinerario::CAMPO_ITINERARIO_FILTRO_5,
            "campo-itinerario-contiene" => ControlloreItinerario::CAMPO_ITINERARIO_CONTIENE,
            "campo-luogo-contiene" => ControlloreItinerario::CAMPO_LUOGO_CONTIENE,
            "campo-includi-non-aperti" => ControlloreItinerario::CAMPO_INCLUSIONE_ITINERARI_NON_APERTI,
            "campo-filtro-data" => ControlloreItinerario::CAMPO_FILTRO_DATA_ORA,
            "form-req-ricerca" => self::getRichiesta(self::RICHIESTA_RICERCA_ITINERARI),
            "percorso-immagini-itinerari" => self::getPercorsoImmaginiItinerari(),
            "url-visualizzazione-profilo" => VistaProfilo::getURLProfilo(),
            "url-visualizzazione-itinerario" => self::getURLItinerario(),
            "url-modifica-itinerario" => self::getURLModificaItinerario(),
            "url-crea-feedback-op" => VistaFeedback::getURLRilascioFeedbackOP(),
            "url-crea-feedback-po" => VistaFeedback::getURLRilascioFeedbackPO(),
        ), TriplettaSemplice::JAVASCRIPT);

        //Creane  un ElementoLista arbitrario per consentire alla Vista
        //di caricare le risorse associate (JS e CSS)
        $this->associaTripletta(new ElementoLista("elemento_itinerario",
            "form/itinerario"));

        $this->tripletta->setPulsante("is-partecipante",
            $isFruitore && !$isCicerone, TriplettaSemplice::HTML);
        $this->tripletta->setPulsante("is-cicerone",
            $isFruitore && $isCicerone, TriplettaSemplice::HTML);

        $this->setTitolo("Ricerca di itinerari");
    }


    /**
     * Crea schermata che consente la visualizzazione dei dettagli
     * di un itinerario
     */
    private function schermataVisualizzazioneItinerario() : void {
        $id = $this->getParams[ControlloreItinerario::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-visualizzazione-itinerario",
            true, TriplettaSemplice::HTML);


        $itinerario = NULL;
        $this->controllore->richiediItinerario($id, $itinerario);

        if ($itinerario !== NULL) {
            $isOspite = false;
            $isFruitore = false;
            $isCicerone = $this->controllore->isUtenteCicerone();
            $isCiceroneOrganizzatore = $isCicerone &&
                $itinerario->getCicerone()->getID() === $this->controllore->getIDUtente();

            if (!$isCiceroneOrganizzatore && !$isCicerone) {
                $isFruitore = $this->controllore->isUtenteFruitore();
                $isOspite = !$isFruitore;
            } else if (!$isCiceroneOrganizzatore) {
                $isFruitore = true;
            }

            if ($isFruitore) {
                $statoPartecipazione = $this->controllore->getStatoPartecipazioneFruitoreAdItinerario($id,
                    $this->controllore->getIDUtente());
            } else {
                $statoPartecipazione = NULL;
            }

            $statoItinerario = $itinerario->getStato();

            $popolarità = $itinerario->getPopolarità();
            $nomePopolarità = self::NOMI_POPOLARITÀ[$popolarità];

            $feedbackRilasciato = 
                ($isCiceroneOrganizzatore && $this->controllore->isFeedbackOPRilasciato($id)) ||
                ($isFruitore && $this->controllore->isFeedbackPORilasciato($id, $this->controllore->getIDUtente()));

            $this->tripletta->applica(
                array(
                    "itinerario-trovato" => true,
                    "nome-itinerario" => $itinerario->getNome(),
                    "form-scr-profilo" => VistaProfilo::getURLProfilo($itinerario->getIDCicerone()),
                    "nome-utente" => $itinerario->getCicerone()->getNomeUtente(),
                    "percorso-immagine" => self::calcolaPercorsoImmagineItinerario($itinerario->getImmagine()),
                    "lingua" => $itinerario->getLingua(),
                    "luogo" => $itinerario->getLuogo(),
                    "data" => $itinerario->getData(),
                    "compenso" => self::formattaCompenso($itinerario->getCompenso(), $itinerario->getValuta()),
                    "descrizione" => self::newlineToBrTag($itinerario->getDescrizione()),
                    "nome-popolarita" => $nomePopolarità,
                    "stato-aperto" => $statoItinerario === Itinerario::STATO_APERTO,
                    "stato-itinere" => $statoItinerario === Itinerario::STATO_ITINERE,
                    "stato-concluso" => $statoItinerario === Itinerario::STATO_CONCLUSO,
                    "stato-chiuso" => $statoItinerario === Itinerario::STATO_CHIUSO,
                    "is-ospite-fruitore" => $isFruitore || $isOspite,
                    "is-cicerone-organizzatore" => $isCiceroneOrganizzatore,
                    "no-partecipazione" => $isFruitore && $statoPartecipazione === NULL,
                    "is-partecipazione-accordata" => $isFruitore && $statoPartecipazione === Partecipazione::STATO_ACCORDATA,
                    "is-partecipazione-accordanda" => $isFruitore && $statoPartecipazione === Partecipazione::STATO_ACCORDANDA,
                    "is-partecipazione-annullanda" => $isFruitore && $statoPartecipazione === Partecipazione::STATO_ANNULLANDA,
                    "form-scr-modifica-itinerario" => self::getURLModificaItinerario($id),
                    "form-scr-lista-partecipazioni" => self::getURLVisualizzazioneRichiestePartecipazione($id),
                    "form-req-rimuovi-itinerario" => self::getURLRichiestaRimozioneItinerario($id),
                    "form-scr-rilascio-feedback" => ($isCiceroneOrganizzatore ? VistaFeedback::getURLRilascioFeedbackOP($id) : VistaFeedback::getURLRilascioFeedbackPO($id)),
                    "feedback-non-rilasciato" => !$feedbackRilasciato,
                ), TriplettaSemplice::HTML
            );
            $titolo = sprintf("Visualizzazione dell'itinerario \"%s\"", $itinerario->getNome());
 

            if ($isFruitore) {
                $this->tripletta->applica(array(
                    "param-id-itinerario" => ControlloreItinerario::CAMPO_ID_ITINERARIO,
                    "param-id-partecipante" => ControlloreItinerario::CAMPO_ID_PARTECIPANTE,
                    "url-invio-richiesta-annullamento" => self::getURLInvioRichiestaAnnullamento(),
                    "url-invio-richiesta-partecipazione" => self::getURLInvioRichiestaPartecipazione(),
                ), TriplettaSemplice::JAVASCRIPT);
                
                $this->tripletta->applica(array(
                    "id-partecipante" => $this->controllore->getIDUtente(),
                    "id-itinerario" => $id,
                ), TriplettaSemplice::HTML);
            }

            /*
             * Se trattasi di un Cicerone organizzatore, consenti di chiedere
             * se si intende procedere con la rimozione dell'itinerario.
             */
            if ($isCiceroneOrganizzatore) {
                /*
                 * Crea popup per chiedere conferma quandi si intende effettuare
                 * la rimozione dell'itinerario
                 */
                $this->aggiungiPopupConfermaRimozioneItinerario();
            }
        } else {
            $titolo = "Itinerario non trovato!";
            $this->tripletta->setPulsante("itinerario-non-trovato", true,
                TriplettaSemplice::HTML);
        }

        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione degli itinerari
     * associati ad un fruitore (cioè, quelli che a cui partecipa, o
     * in caso di Cicerone, quelli che ha organizzato)
     */
    /*
     * Casistiche di visualizzazione:
     * 
     * GQ = Globetrotter-QuasiCicerone
     * C = Cicerone
     * AO = Amministratore-Ospite
     * 
     * [AO vede QG]
     *     vede: itinerari partecipante accordati di QG
     *     azioni permesse: nessuna
     * 
     * [AO vede C]
     *     vede: itinerari partecipante accordati di C, organizzatore (tutti)
     *     azioni permesse: nessuna
     *     
     * [GQ vede GQ]
     *     vede: itinerari partecipante di QG
     *         - se se stesso: tutti altrimenti solo accordati
     *     azioni permesse: 
     *         - se se stesso: copia dalla query altrimenti verifica per ogni singolo itinerario
     *
     * [GQ vede C]
     *      vede: itinerari partecipante accordati di C, organizzatore (tutti)
     *      azioni permesse:
     *          - verifica partecipazioni per ogni singolo itinerario
     *         
     * [C vede GQ]
     *      vede: itinerari partecipante accordati di GQ
     *      azioni permesse:
     *          - per ogni itinerario, se è di C, consenti la modifica, altrimenti
     *         verifica partecipazioni
     *
     * [C vede C]
     *     vede: itinerari partecipante di C, organizzatore (tutti)
     *         - se se stesso, tutti i partecipanti, altrimenti solo partecipante accordati
     *     azioni permesse:
     *         - se se stesso:
     *             - consenti la modifica degli itinerari orgnizzatore
     *             - copia dalla query le richieste per gli itinerari partecipante
     *         - altrimenti:
     *             - verifica partecipazioni per ogni itinerario organizzatore
     *             - per ogni singolo itinerario partecipante, se è mio, consenti
     *             la modifica, altrimenti verifica partecipazioni
     */
    private function schermataVisualizzazioneItinerariFruitore() : void {
        $id = $this->getParams[ControlloreItinerario::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $this->tripletta->setPulsante("schermata-visualizzazione-itinerari-fruitore",
            true, TriplettaSemplice::HTML);

        $utente = NULL;
        $this->controllore->richiediUtente($id, $utente);


        if ($utente !== NULL && ($tipoUtente = $utente->getTipo()) !== Utente::TIPO_AMMINISTRATORE) {
            $isOspiteAmministratore = (!$this->controllore->isUtenteConnesso() || $this->controllore->isUtenteAmministratore());
            $isFruitore = !$isOspiteAmministratore;

            $isUtenteSeStesso = ($isFruitore && $this->controllore->getIDUtente() === intval($id));

            $this->tripletta->applica(array(
                "fruitore-trovato" => true,
                "is-cicerone" => $tipoUtente === Utente::TIPO_CICERONE,
                "me-stesso" => $tipoUtente === Utente::TIPO_CICERONE && $isUtenteSeStesso,
                "is-fruitore-qualunque" => $tipoUtente !== Utente::TIPO_CICERONE,
                "form-scr-crea-itinerario" => self::getSchermata(self::SCHERMATA_CREAZIONE_ITINERARIO),
                "form-scr-profilo" => VistaProfilo::getURLProfilo($id),
                "nome-utente" => $utente->getNomeUtente(),
            ), TriplettaSemplice::HTML);

            $itinerari = NULL;
            $this->controllore->richiediItinerariFruitore($id, !$isUtenteSeStesso,
                $isUtenteSeStesso, $itinerari);

            $nItinerari = array(
                count($itinerari[0]),
                ($itinerari[1] !== NULL ? count($itinerari[1]) : 0)
            );

            for ($i = 0; $i < 2; $i++) {
                if ($nItinerari[$i] > 0) {
                    foreach ($itinerari[$i] as $item) {
                        if ($i === 0) {
                            $partecipazione = $item;
                            /*
                             * Assumi che lo stato di partecipazione sia
                             * pari a quello recuperato dalla query.
                             * Questo serve SOLO IN CASO abbiamo a che fare
                             * con la visione di noi stessi ($isUtenteSeStesso),
                             * perchè, in tal caso, gli stati di partecipazione, sono
                             * quelli, non c'è bisogno di interrogare il database.
                             */
                            $statoPartecipazione = $partecipazione->getStato();
                            $itinerario = $partecipazione->getItinerario();
                        } else {
                            $itinerario = $item;
                            $statoPartecipazione = NULL;
                        }

                        $statoItinerario = $itinerario->getStato();
                        $cicerone = $itinerario->getCicerone();
                        $triplettaListaElemento = new ElementoLista("elemento_itinerario", "form/itinerario");

                        if ($isFruitore) {
                            if (!$isUtenteSeStesso) {
                                switch ($i) {
                                    case 0:
                                        //SEZIONE ITINERARI PARTECIPANTE
                                        if ($this->controllore->isUtenteCicerone()) {
                                            $isCiceroneOrganizzatore = $itinerario->getCicerone()->getID() === $this->controllore->getIDUtente();
                                            
                                            if (!$isCiceroneOrganizzatore) {
                                                $statoPartecipazione = $this->controllore->getStatoPartecipazioneFruitoreAdItinerario(
                                                    $itinerario->getID(), $this->controllore->getIDUtente());
                                            } else {
                                                $statoPartecipazione = NULL;
                                            }
                                        } else {
                                            $isCiceroneOrganizzatore = false;
                                            $statoPartecipazione = $this->controllore->getStatoPartecipazioneFruitoreAdItinerario(
                                                $itinerario->getID(), $this->controllore->getIDUtente());
                                        }
                                    break;
                                    case 1:
                                        //SEZIONE ITINERARI ORGANIZZATORE
                                        /*
                                         * Se sto elaborando la lista di itinerari creati dal Cicerone,
                                         * poichè non ho dati inerenti la mia partecipazione, devo verificare
                                         * singolarmente se io partecipo ai suoi itinerari
                                         */
                                        $statoPartecipazione = $this->controllore->getStatoPartecipazioneFruitoreAdItinerario(
                                            $itinerario->getID(), $this->controllore->getIDUtente());
                                        $isCiceroneOrganizzatore = false;
                                    break;
                                }
                            } else if ($i === 0) {
                                //SEZIONE ITINERARI PARTECIPANTE
                                $isCiceroneOrganizzatore = false;

                            } else if ($i === 1) {
                                //SEZIONE ITINERARI ORGANIZZATORE
                                $isCiceroneOrganizzatore = true;
                                $statoPartecipazione = NULL;
                            }

                            $feedbackRilasciato = ($isCiceroneOrganizzatore && $this->controllore->isFeedbackOPRilasciato($id)) ||
                                ($isFruitore && $this->controllore->isFeedbackPORilasciato($id, $this->controllore->getIDUtente()));

                            $triplettaListaElemento->applica(array(
                                "is-utente" => !$isCiceroneOrganizzatore,
                                "is-cicerone-organizzatore" => $isCiceroneOrganizzatore,
                                "no-partecipazione" => !$isCiceroneOrganizzatore && $statoPartecipazione === NULL,
                                "partecipazione-accordata" => $statoPartecipazione === Partecipazione::STATO_ACCORDATA,
                                "partecipazione-accordanda" => $statoPartecipazione === Partecipazione::STATO_ACCORDANDA,
                                "partecipazione-annullanda" => $statoPartecipazione === Partecipazione::STATO_ANNULLANDA,
                                "feedback-non-rilasciato" => !$feedbackRilasciato,
                                "form-scr-crea-feedback" => ($isCiceroneOrganizzatore ? VistaFeedback::getURLRilascioFeedbackOP($itinerario->getID()) : VistaFeedback::getURLRilascioFeedbackPO($itinerario->getID())),
                            ));
                        }


                        $triplettaListaElemento->applica(array(
                            "descrizione" => self::newlineToBrTag(substr($itinerario->getDescrizione(), 0, 20) . "..."),
                            "organizzatore-nome-utente" => $cicerone->getNomeUtente(),
                            "form-scr-profilo-organizzatore" => VistaProfilo::getURLProfilo($cicerone->getID()),
                            "form-scr-itinerario" => self::getURLItinerario($itinerario->getID()),
                            "form-scr-modifica-itinerario" => self::getURLModificaItinerario($itinerario->getID()),
                            "nome-itinerario" => $itinerario->getNome(),
                            "percorso-immagine" => self::calcolaPercorsoImmagineItinerario($itinerario->getImmagine()),
                            "luogo" => $itinerario->getLuogo(),
                            "compenso" => self::formattaCompenso($itinerario->getCompenso(), $itinerario->getValuta()),
                            "id-itinerario" => $itinerario->getID(),
                            "id-partecipante" => $isFruitore ? $this->controllore->getIDUtente() : 0,
                            "stato-aperto" => $statoItinerario === Itinerario::STATO_APERTO,
                            "stato-itinere" => $statoItinerario === Itinerario::STATO_ITINERE,
                            "stato-concluso" => $statoItinerario === Itinerario::STATO_CONCLUSO,
                            "stato-chiuso" => $statoItinerario === Itinerario::STATO_CHIUSO
                        ));

                        $this->tripletta->add(($i === 0 ? "itinerari-partecipante" : "itinerari-organizzatore"),
                            $triplettaListaElemento, TriplettaSemplice::HTML);
                        $this->associaTripletta($triplettaListaElemento);
                    }
                } else {
                    $this->tripletta->add(($i === 0 ? "itinerari-partecipante" : "itinerari-organizzatore"),
                        "<p>Nessun itinerario trovato</p>", TriplettaSemplice::HTML);
                }
            }

            //Fa questa cosa solo se trattasi di un fruitore
            if ($isFruitore) {
                $this->tripletta->applica(array(
                    "param-id-itinerario" => ControlloreItinerario::CAMPO_ID_ITINERARIO,
                    "param-id-partecipante" => ControlloreItinerario::CAMPO_ID_PARTECIPANTE,
                    "url-invio-richiesta-annullamento" => self::getURLInvioRichiestaAnnullamento(),
                    "url-invio-richiesta-partecipazione" => self::getURLInvioRichiestaPartecipazione(),
                ), TriplettaSemplice::JAVASCRIPT);
            }

            $titolo = "Lista itinerari di \"" . $utente->getNomeUtente() . "\"";

            //Vai al particolare menù, se richiesto
            if (isset($this->getParams[self::GET_MENU]) && $itinerari[1] !== NULL) {
                $menu = $this->getParams[self::GET_MENU];
                if ($menu === self::MENU_ITINERARI_PARTECIPANTE ||
                    $menu === self::MENU_ITINERARI_ORGANIZZATORE) {

                    $option = ($menu === self::MENU_ITINERARI_PARTECIPANTE) ? 0 : 1;
                        
                    $this->addJSCodice(<<<JS
var selector = document.getElementById("itinerary-selector");
selector.selectedIndex = $option;
mostraMenuTramiteSelect(selector);
JS);
                }
            }
        } else {
           //Se non è stato trovato o trattasi di amministratore, dai errore
           $this->tripletta->setPulsante("fruitore-non-trovato", true, TriplettaSemplice::HTML);
           $titolo = "Itinerari non trovati";
        }


        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione delle richieste di
     * partecipazione ad un itinerario.
     */
    private function schermataVisualizzazioneRichiestePartecipazione() : void {
        $id = $this->getParams[ControlloreItinerario::CAMPO_ID];
        
        $this->tripletta->setPulsante("schermata-lista-richieste-partecipazione", true,
            TriplettaSemplice::HTML);

        $itinerario = NULL;
        $this->controllore->richiediItinerario($id, $itinerario);

        if ($itinerario !== NULL) {
            $isCicerone = ($this->controllore->isUtenteConnesso() &&
                $this->controllore->getTipoUtente() === Utente::TIPO_CICERONE);

            if ($isCicerone) {
                $isCiceroneOrganizzatore = $itinerario->getCicerone()->getID() === $this->controllore->getIDUtente();
            } else {
                $isCiceroneOrganizzatore = false;
            }

            $partecipazioni = NULL;
            $this->controllore->richiediRichiestePartecipazione($itinerario->getID(), !$isCiceroneOrganizzatore, $partecipazioni);


            $this->tripletta->applica(array(
                "itinerario-valido" => $itinerario !== NULL,
                "is-cicerone-organizzatore" => $isCiceroneOrganizzatore,
                "is-utente" => !$isCiceroneOrganizzatore,
                "form-scr-itinerario" => self::getURLItinerario($itinerario->getID()),
                "nome-itinerario" => $itinerario->getNome(),
            ), TriplettaSemplice::HTML);


            $this->tripletta->applica(array(
                "url-accorda-richiesta" => $this->getURLAccordaRichiestaPartecipazione(),
                "url-declina-richiesta" => $this->getURLDeclinaRichiestaPartecipazione(),
                "url-annulla-richiesta" => $this->getURLAnnullaRichiestaPartecipazione(),
                "param-id-itinerario" => ControlloreItinerario::CAMPO_ID_ITINERARIO,
                "param-id-partecipante" => ControlloreItinerario::CAMPO_ID_PARTECIPANTE,
            ), TriplettaSemplice::JAVASCRIPT);


            $nPartecipazioniAccordate = 0;
            $nPartecipazioniAccordande = 0;
            $nPartecipazioniAnnullande = 0;


            foreach ($partecipazioni as $partecipazione) {
                $triplettaListaElemento = new ElementoLista("elemento_partecipazione", "form/itinerario");

                $statoPartecipazione = $partecipazione->getStato();
                $partecipante = $partecipazione->getPartecipante();
                
                $accordandaAccordata = $statoPartecipazione === Partecipazione::STATO_ACCORDANDA ||
                    $statoPartecipazione === Partecipazione::STATO_ACCORDATA;
                
                $statoItinerario = $itinerario->getStato();
                
                $triplettaListaElemento->applica(array(
                    "is-cicerone-organizzatore" => $isCiceroneOrganizzatore,
                    "partecipazione-accordata-accordanda" => $accordandaAccordata,
                    "partecipazione-accordanda" => $statoPartecipazione === Partecipazione::STATO_ACCORDANDA,
                    "partecipazione-annullanda" => $statoPartecipazione === Partecipazione::STATO_ANNULLANDA,
                    "partecipazione-accordata" => $statoPartecipazione === Partecipazione::STATO_ACCORDATA,
                    "nome-utente" => $partecipante->getNomeUtente(),
                    "percorso-immagine" => self::calcolaPercorsoImmagineUtente($partecipante->getImmagine()),
                    "form-scr-utente" => VistaProfilo::getURLProfilo($partecipante->getID()),
                    "form-scr-feedback" => VistaFeedback::getURLFeedbacksFruitore($partecipante->getID()),
                    "id-itinerario" => $itinerario->getID(),
                    "id-partecipante" => $partecipante->getID(),
                    "stato-aperto" => $statoItinerario === Itinerario::STATO_APERTO,
                    "stato-non-aperto" => $statoItinerario !== Itinerario::STATO_APERTO,
                ));

                $chiaveAggiunta = "";

                switch ($partecipazione->getStato()) {
                    case Partecipazione::STATO_ACCORDATA:
                        $chiaveAggiunta = "partecipazioni-accordate";
                        $nPartecipazioniAccordate++;
                    break;
                    case Partecipazione::STATO_ACCORDANDA:
                        $chiaveAggiunta = "richieste-partecipazione";
                        $nPartecipazioniAccordande++;
                    break;
                    case Partecipazione::STATO_ANNULLANDA:
                        $chiaveAggiunta = "richieste-annullamento";
                        $nPartecipazioniAnnullande++;
                    break;
                    default:
                        //mai eseguito
                    break;
                }

                $this->tripletta->add($chiaveAggiunta, $triplettaListaElemento, TriplettaSemplice::HTML);
                $this->associaTripletta($triplettaListaElemento);
            }


            if ($nPartecipazioniAccordate === 0) {
                $this->tripletta->add("partecipazioni-accordate", "<p>Nessuna richiesta accordata trovata</p>", TriplettaSemplice::HTML);
            }

            if ($nPartecipazioniAccordande === 0) {
                $this->tripletta->add("richieste-partecipazione", "<p>Nessuna richiesta di partecipazione trovata</p>", TriplettaSemplice::HTML);
            }

            if ($nPartecipazioniAnnullande === 0) {
                $this->tripletta->add("richieste-annullamento", "<p>Nessuna richiesta d'annullamento trovata</p>", TriplettaSemplice::HTML);
            }

            $titolo = "Lista richieste di partecipazione all'itinerario \"" . $itinerario->getNome() . "\"";

        } else {
            $this->tripletta->setPulsante("itinerario-non-valido", true, TriplettaSemplice::HTML);
            $titolo = "Itinerario non trovato";
        }
        
        $this->setTitolo($titolo);
        $this->mostraErrori();
    }
    

    /**
     * Metodo di servizio per aggiungere i codici necessari affinchè si possa procedere a
     * cambiare l'immagine degli itinerari.
     * @param string $percorsoImmagineDefault percorso all'imagine di default
     */
    private function aggiungiGestoreAnteprimaImmagine(string $percorsoImmagineDefault) : void {
        /*
         * Aggiungi codice di supporto per l'upload di immagini
         */
        $this->tripletta->add("id-input-file", "image-load", TriplettaSemplice::HTML);
        $this->tripletta->add("id-input-restore", "ripristino", TriplettaSemplice::HTML);
        
        //Aggiungi codice Javascript per consentire l'anteprima.
        $this->addJSCodice(
            sprintf("var anteprimaImmagine = new GestoreAnteprimaImmagine(%d, %d, %d);",
                ControlloreItinerario::MAX_DIMENSIONE_IMMAGINE, ControlloreItinerario::MAX_WIDTH_IMMAGINE,
                ControlloreItinerario::MAX_HEIGHT_IMMAGINE));
        $this->tripletta->add("azione-mostra-anteprima",
            "anteprimaImmagine.mostraAnteprima('image-load', 'anteprima', 'ripristino', 'messaggi')",
            TriplettaSemplice::HTML);
        
        //Crea popup per chiedere conferma se effettuare il reset dell'immagine
        $this->addJSCodice(<<<JS
function confermaScelta() {
    anteprimaImmagine.resetAnteprima('image-load', 'anteprima', 'messaggi', 'ripristino', '$percorsoImmagineDefault');
    new Popup('popup-reset-immagine').nascondi();
}
JS);

        $dialog = new TriplettaSemplice("popup_dialog", "layout");
        $dialog->applica(array(
            "testo" => "Vuoi davvero reimpostare l'immagine di default?",
            "testo-pulsante" => "Procedi",
            "azione" => "confermaScelta()"
        ), TriplettaSemplice::HTML);

        $popupReset = new Popup("popup-reset-immagine");
        $popupReset->add($dialog);

        $this->tripletta->add("azione-conferma-reset-anteprima",
            "new Popup('popup-reset-immagine').mostra()",
            TriplettaSemplice::HTML);
        $this->associaTripletta($popupReset);
    }


    /**
     * Metodo di servizio per aggiungere i codici necessari affinchè venga mostrato un Popup
     * quando si richiede la rimozione dell'itinerario.
     */
    private function aggiungiPopupConfermaRimozioneItinerario() : void {
        $this->tripletta->add("id-rimozione-itinerario", "id-rimuovi", TriplettaSemplice::HTML);
        
        $this->addJSCodice(<<<JS
function confermaScelta() {
    new Popup('popup-conferma-rimozione').nascondi();
    var anchor = document.getElementById("id-rimuovi");
    anchor.onclick = null;
    anchor.click();
}
JS);

        $dialog = new TriplettaSemplice("popup_dialog", "layout");
        $dialog->applica(array(
            "testo" => "Vuoi davvero rimuovere l'itinerario?",
            "testo-pulsante" => "Procedi",
            "azione" => "confermaScelta()"
        ), TriplettaSemplice::HTML);

        $popupConferma = new Popup("popup-conferma-rimozione");
        $popupConferma->add($dialog);

        $this->tripletta->add("azione-mostra-popup-conferma",
            "new Popup('popup-conferma-rimozione').mostra(); return false;",
            TriplettaSemplice::HTML);
        $this->associaTripletta($popupConferma);
    }


    /**
     * Metodo utilizzato per "formattare" visivamente il compenso.
     * @param int $compenso il valore del compenso
     * @param Valuta $v la valuta da utilizzare
     * @return string una stringa che rappresenta il compenso "tradotto"
     */
    public static function formattaCompenso(int $compenso, Valuta $v) : string {
        $str = "";
        
        if ($compenso === 0) {
            $str = "Gratuito";
            
        } else {
            if ($v->getCentesimale()) {
                $compenso = $compenso / 100.0;
                $str = sprintf("%.2f %s", $compenso, $v->getSimbolo());
            } else {
                $str = sprintf("%d %s", $compenso, $v->getSimbolo());
            }
        }

        return $str;
    }


    /*
     * Metodi per restituire le URL inerenti gli itinerari
     */
    /**
     * Restituisce l'URL per la visualizzazione di un itinerario, in base all'id passato.
     * @param int $id l'id dell'itinerario da visualizzare, se NULL, restituisce l'url escluso l'id
     * @return string
     */
    public static function getURLItinerario(?int $id = NULL) : string {
        $url = sprintf("%s&%s=", self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_ITINERARIO), ControlloreItinerario::CAMPO_ID);
        
        if ($id !== NULL) {
            $url = sprintf("%s%d", $url, $id);
        }
        return $url;
    }


    /**
     * Restituisce l'URL per la schermata di modifica di un itinerario, in base all'id passato.
     * @param int $id l'id dell'itinerario da visualizzare, se NULL, restituisce l'url escluso l'id
     * @return string
     */
    public static function getURLModificaItinerario(?int $id = NULL) : string {
        $url = sprintf("%s&%s=", self::getSchermata(self::SCHERMATA_MODIFICA_ITINERARIO), ControlloreItinerario::CAMPO_ID);

        if ($id !== NULL) {
            $url = sprintf("%s%d", $url, $id);
        }
        return $url;
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare gli itinerari
     * a cui un fruitore partecipa
     * @param int $idFruitore l'id del fruitore
     * @return string
     */
    public static function getURLItinerariPartecipanteFruitore(int $idFruitore) : string {
        return sprintf("%s&%s=%d", self::getMenu(self::SCHERMATA_VISUALIZZAZIONE_ITINERARI_FRUITORE,
                self::MENU_ITINERARI_PARTECIPANTE), ControlloreItinerario::CAMPO_ID, $idFruitore);
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare gli itinerari
     * organizzati da un fruitore (in altri termini, un Cicerone)
     * @param int $idFruitore l'id del fruitore
     * @return string
     */
    public static function getURLItinerariOrganizzatoreFruitore(int $idFruitore) : string {
        return sprintf("%s&%s=%d", self::getMenu(self::SCHERMATA_VISUALIZZAZIONE_ITINERARI_FRUITORE,
            self::MENU_ITINERARI_ORGANIZZATORE), ControlloreItinerario::CAMPO_ID, $idFruitore);
    }


    /**
     * Restituisce l'URL per la schermata che consente la ricerca di itinerari
     * @return string
     */
    public static function getURLRicercaItinerari() : string {
        return self::getSchermata(self::SCHERMATA_RICERCA_ITINERARI);
    }


    /*
     * Metodi per restituire le URL inerenti le partecipazioni
     */
    /**
     * Restituisce l'URL per l'invio di una richiesta di partecipazione
     * @return string
     */
    public static function getURLInvioRichiestaPartecipazione() : string {
        return self::getRichiesta(self::RICHIESTA_INVIO_RICHIESTA_PARTECIPAZIONE);
    }


    /**
     * Restituisce l'URL per l'invio di una richiesta di annullamento
     * @return string
     */
    public static function getURLInvioRichiestaAnnullamento() : string {
        return self::getRichiesta(self::RICHIESTA_INVIO_RICHIESTA_ANNULLAMENTO);
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare le richieste
     * di partecipazione ad un itinerario
     * @param id id dell'itinerario
     * @return string
     */
    public static function getURLVisualizzazioneRichiestePartecipazione(int $id) : string {
        return sprintf("%s&%s=%d", self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_RICHIESTE_PARTECIPAZIONE), ControlloreItinerario::CAMPO_ID, $id);
    }


    /*
     * Altri metodi di servizio
     */
    /**
     * Restituisce l'URL per la richiesta di rimozione di un itinerario.
     * @return string
     */
    private static function getURLRichiestaRimozioneItinerario(int $id) : string {
        return sprintf("%s&%s=%d", self::getRichiesta(self::RICHIESTA_RIMUOVI_ITINERARIO),
            ControlloreItinerario::CAMPO_ID, $id);
    }
    
    
    
    /**
     * Restituisce l'URL per l'accordo di una richiesta di partecipazione
     * @return string
     */
    private static function getURLAccordaRichiestaPartecipazione() : string {
        return self::getRichiesta(self::RICHIESTA_ACCORDO_RICHIESTA_PARTECIPAZIONE);
    }


    /**
     * Restituisce l'URL per il declino di una richiesta di partecipazione
     * @return string
     */
    private static function getURLDeclinaRichiestaPartecipazione() : string {
        return self::getRichiesta(self::RICHIESTA_DECLINO_RICHIESTA_PARTECIPAZIONE);
    }


    /**
     * Restituisce l'URL per l'annullamento di una richiesta di partecipazione
     * @return string
     */
    private static function getURLAnnullaRichiestaPartecipazione() : string {
        return self::getRichiesta(self::RICHIESTA_ANNULLAMENTO_RICHIESTA_PARTECIPAZIONE);
    }
}

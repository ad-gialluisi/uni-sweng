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



require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Utente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreAmministrazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloAmministrazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/debug/DebugSettings.php";
require_once "ElementoLista.php";
require_once "TriplettaSemplice.php";
require_once "VistaProfilo.php";
require_once "Vista.php";


use controllore\ControlloreAmministrazione;
use modello\ModelloAmministrazione;


/**
 * Rappresenta la vista associata al ControlloreAmministrazione e al ModelloAmministrazione.
 * 
 * <p>Ha cinque schermate, la prima per la visualizzazione delle richieste d'amministrazione
 * da parte di un fruitore, la seconda e la terza per la visualizzazione, rispettivamente
 * di una richiesta d'aggiornamento e disiscrizione, la quarta e la quinta per la creazione
 * rispettivamente, di una richiesta d'aggiornamento e disiscrizione.</p>
 * 
 * @see \controllore\ControlloreAmministrazione
 * @see \modello\ModelloAmministrazione
 */
class VistaAmministrazione extends Vista {
    protected const PAGINA_VISTA = "amministrazione.php";

    /*
     * Richieste che questa vista prende in considerazione
     */
    /**
     * Richiesta che consente la creazione di una richiesta di disiscrizione
     * (che poi verrà accordata da un amministratore).
     */
    private const RICHIESTA_CREAZIONE_RICHIESTA_DISISCRIZIONE = "creaReqDisiscrizione";

    /**
     * Richiesta che consente la creazione di una richiesta d'aggiornamento
     * (che poi verrà accordata da un amministratore).
     */
    private const RICHIESTA_CREAZIONE_RICHIESTA_AGGIORNAMENTO = "creaReqAggiornamento";

    /**
     * Richiesta che consente la disiscrizione di un fruitore, come risposta
     * ad una richiesta di disiscrizione.
     */
    private const RICHIESTA_DISISCRIZIONE_FRUITORE = "disiscriviFruitore";

    /**
     * Richiesta che consente la trasformazione a QuasiCicerone di un fruitore,
     * come risposta ad una richiesta di aggiornamento.
     */
    private const RICHIESTA_TRASFORMAZIONE_QUASICICERONE = "transQuasiCicerone";

    /**
     * Richiesta che consente la transizione a Cicerone da parte di un QuasiCicerone.
     */
    private const RICHIESTA_TRANSIZIONE_CICERONE = "transCicerone";



    /*
     * Le diverse schermate che la vista mostra.
     */
    /**
     * Schermata che mostra le richieste d'amministrazione.
     */
    private const SCHERMATA_RICHIESTE_AMMINISTRAZIONE = "reqAmministrazione";
    
    /**
     * Schermata che mostra i dettaglia di una richiesta d'aggiornamento.
     */
    private const SCHERMATA_VISUALIZZAZIONE_RICHIESTA_AGGIORNAMENTO = "reqAggiornamento";

    /**
     * Schermata che mostra i dettagli di una richiesta di disiscrizione.
     */
    private const SCHERMATA_VISUALIZZAZIONE_RICHIESTA_DISISCRIZIONE = "reqDisiscrizione";

    /**
     * Schermata che consente la creazione di una richiesta di disiscrizione.
     */
    private const SCHERMATA_CREAZIONE_RICHIESTA_DISISCRIZIONE = "creaReqDisiscrizione";

    /**
     * Schermata che consente la creazione di una richiesta d'aggiornamento.
     */
    private const SCHERMATA_CREAZIONE_RICHIESTA_AGGIORNAMENTO = "creaReqAggiornamento";



    
    
    /**
     * Crea una VistaAmministrazione con un ControlloreAmministrazione sottostante.
     */
    public function __construct() {
        parent::__construct(new ControlloreAmministrazione());
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

        $richiestaValida = true;
        $isUtenteFruitore = $this->controllore->isUtenteFruitore();
        $isUtenteGlobetrotter = $this->controllore->isUtenteGlobetrotter();
        $isUtenteQuasiCicerone = $this->controllore->isUtenteQuasiCicerone();
        $isUtenteAmministratore = $this->controllore->isUtenteAmministratore();
        $isUtenteSenzaRichiesteAmministrazione = !$this->controllore->isUtenteInDisiscrizione() &&
            !$this->controllore->isUtenteInAggiornamento();

        $idUtente = $this->controllore->getIDUtente();


        switch ($richiesta) {
            case self::RICHIESTA_DISISCRIZIONE_FRUITORE:
                $this->controllore->richiediDisiscrizioneFruitore($this->postParams,
                    self::getPercorsoImmaginiUtenti(), VistaProfilo::FILE_IMMAGINE_DEFAULT);
                $isDisiscrizione = true;
            break;
            case self::RICHIESTA_TRASFORMAZIONE_QUASICICERONE:
                $this->controllore->richiediTrasformazioneInQuasiCicerone($this->postParams);
                $isTrasformazioneQuasiCicerone = true;
            break;
            case self::RICHIESTA_CREAZIONE_RICHIESTA_DISISCRIZIONE:
                $this->controllore->richiediInvioRichiestaDisiscrizione($this->postParams);
                $isCreazioneRichiestaDisiscrizione = true;
            break;
            case self::RICHIESTA_CREAZIONE_RICHIESTA_AGGIORNAMENTO:
                $this->controllore->richiediInvioRichiestaAggiornamento($this->postParams);
                $isCreazioneRichiestaAggiornamento = true;
            break;
            case self::RICHIESTA_TRANSIZIONE_CICERONE:
                $this->controllore->richiediTransizioneACicerone();
                $isTransizioneCicerone = true;
            break;
            default:
                $richiestaValida = false;
            break;
        }

        $paginaRedirect = self::getURLHOME();

        if ($richiestaValida) {
            $statoOperazione = $this->controllore->getStatoOperazione();

            /*
             * Ora copia la messaggistica ottenuta, così, nell'eventuale schermata
             * di destinazione, è possibile aggiungere ulteriori messaggi.
             */
            $this->controllore->copiaMessaggisticaPerSchermata();


            switch ($statoOperazione) {
                case ControlloreAmministrazione::STATO_OPERAZIONE_FALLITA:
                    if (($isDisiscrizione || $isTrasformazioneQuasiCicerone) && $isUtenteAmministratore) {
                        $paginaRedirect = $this->getSchermata(self::SCHERMATA_RICHIESTE_AMMINISTRAZIONE);

                    } else if ($isCreazioneRichiestaDisiscrizione && $isUtenteFruitore && $isUtenteSenzaRichiesteAmministrazione) {
                        $paginaRedirect = $this->getSchermata(self::SCHERMATA_CREAZIONE_RICHIESTA_DISISCRIZIONE);

                    } else if ($isCreazioneRichiestaAggiornamento && $isUtenteGlobetrotter && $isUtenteSenzaRichiesteAmministrazione) {
                        $paginaRedirect = $this->getSchermata(self::SCHERMATA_CREAZIONE_RICHIESTA_AGGIORNAMENTO);
                        
                    } else if ($isTransizioneCicerone && $isUtenteQuasiCicerone) {
                        $paginaRedirect = VistaProfilo::getURLProfilo($idUtente);
                    }
                break;
                case ControlloreAmministrazione::STATO_OPERAZIONE_RIUSCITA:
                    if (($isDisiscrizione || $isTrasformazioneQuasiCicerone) && $isUtenteAmministratore) {
                        $paginaRedirect = $this->getSchermata(self::SCHERMATA_RICHIESTE_AMMINISTRAZIONE);

                    } else if (($isCreazioneRichiestaDisiscrizione && $isUtenteFruitore && $isUtenteSenzaRichiesteAmministrazione) ||
                        ($isCreazioneRichiestaAggiornamento && $isUtenteGlobetrotter && $isUtenteSenzaRichiesteAmministrazione) ||
                        ($isTransizioneCicerone && $isUtenteQuasiCicerone)) {
                        $paginaRedirect = VistaProfilo::getURLProfilo($idUtente);
                    }
                break;
                default:
                    //Mai eseguito
                break;
            }
        }

        $this->mandaA($paginaRedirect);
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        $schermata = isset($this->getParams[self::GET_SCHERMATA]) ?
            $this->getParams[self::GET_SCHERMATA] : self::SCHERMATA_RICHIESTE_AMMINISTRAZIONE;


        $isUtenteAmministratore = $this->controllore->isUtenteAmministratore();
        $isUtenteFruitore = $this->controllore->isUtenteFruitore();
        $isUtenteGlobetrotter = $this->controllore->isUtenteGlobetrotter();
        
        $isIDDefinito = isset($this->getParams[ControlloreAmministrazione::CAMPO_ID]);
        if ($isIDDefinito) {
            $id = $this->getParams[ControlloreAmministrazione::CAMPO_ID];
            $isIDDefinito = preg_match(ModelloAmministrazione::REGEX_ID, $id);
        }
        
        $isSchermataValida = true;
        $metodoSchermata = "";

        switch ($schermata) {
            case self::SCHERMATA_CREAZIONE_RICHIESTA_DISISCRIZIONE:
                $metodoSchermata = "schermataCreazioneRichiestaDisiscrizione";
                $isSchermataValida = $isUtenteFruitore;
            break;
            case self::SCHERMATA_CREAZIONE_RICHIESTA_AGGIORNAMENTO:
                $metodoSchermata = "schermataCreazioneRichiestaAggiornamento";
                $isSchermataValida = $isUtenteGlobetrotter;
            break;
            case self::SCHERMATA_RICHIESTE_AMMINISTRAZIONE:
                $metodoSchermata = "schermataRichiesteAmministrazione";
                $isSchermataValida = $isUtenteAmministratore;
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_RICHIESTA_AGGIORNAMENTO:
                $metodoSchermata = "schermataVisualizzazioneRichiestaAggiornamento";
                $isSchermataValida = $isUtenteAmministratore && $isIDDefinito;
            break;
            case self::SCHERMATA_VISUALIZZAZIONE_RICHIESTA_DISISCRIZIONE:
                $metodoSchermata = "schermataVisualizzazioneRichiestaDisiscrizione";
                $isSchermataValida = $isUtenteAmministratore && $isIDDefinito;
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
            $this->tripletta = new TriplettaSemplice("amministrazione", "form/amministrazione");
            $this->$metodoSchermata();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Crea schermata che mostra le richieste d'amministrazione.
     */
    private function schermataRichiesteAmministrazione() : void {
        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $richieste = NULL;
        $this->controllore->richiediRichiesteAmministrazione($richieste);

        $this->tripletta->setPulsante("schermata-richieste-amministrazione", true,
            TriplettaSemplice::HTML);

        //Aggiunta delle richieste d'aggiornamento
        $nRichiesteAggiornamento = $richieste->getNRichiesteAggiornamento();
        
        if ($nRichiesteAggiornamento > 0) {
            for ($i = 0; $i < $nRichiesteAggiornamento; $i++) {
                $richiesta = $richieste->getRichiestaAggiornamento($i);
                $anagrafica = $richiesta->getAnagrafica();
                $fruitore = $anagrafica->getCicerone();

                $triplettaListaElemento = new ElementoLista("elemento_richiesta", "form/amministrazione");
                $triplettaListaElemento->applica(array(
                    "richiesta-aggiornamento" => true,
                    "nome-utente" => $fruitore->getNomeUtente(),
                    "form-scr-profilo" => VistaProfilo::getURLProfilo($fruitore->getID()),
                    "form-scr-visualizza-richiesta" => self::getURLVisualizzazioneRichiestaAggiornamento($richiesta->getID())
                ));

                $this->tripletta->add("richieste-aggiornamento", $triplettaListaElemento,
                    TriplettaSemplice::HTML);

                $this->associaTripletta($triplettaListaElemento);
            }
        } else {
            $this->tripletta->add("richieste-aggiornamento", 
                "<p>Nessuna richiesta d'aggiornamento trovata.</p>",
                TriplettaSemplice::HTML);
        }


        //Aggiunta delle richieste di disicrizione
        $nRichiesteDisiscrizione = $richieste->getNRichiesteDisiscrizione();
        
        if ($nRichiesteDisiscrizione > 0) {
            for ($i = 0; $i < $nRichiesteDisiscrizione; $i++) {
                $richiesta = $richieste->getRichiestaDisiscrizione($i);
                $fruitore = $richiesta->getFruitore();

                $triplettaListaElemento = new ElementoLista("elemento_richiesta", "form/amministrazione");
                $triplettaListaElemento->applica(array(
                    "richiesta-disiscrizione" => true,
                    "nome-utente" => $fruitore->getNomeUtente(),
                    "form-scr-profilo" => VistaProfilo::getURLProfilo($fruitore->getID()),
                    "form-scr-visualizza-richiesta" => self::getURLVisualizzazioneRichiestaDisiscrizione($richiesta->getID())
                ));

                $this->tripletta->add("richieste-disiscrizione", $triplettaListaElemento,
                    TriplettaSemplice::HTML);

                $this->associaTripletta($triplettaListaElemento);
            }
        } else {
            $this->tripletta->add("richieste-disiscrizione",
                "<p>Nessuna richiesta di disiscrizione trovata.</p>",
                TriplettaSemplice::HTML);
        }

        $this->setTitolo("Richieste d'amministrazione");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la creazione della richiesta di disiscrizione.
     */
    private function schermataCreazioneRichiestaDisiscrizione() : void {
        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();
        
        $this->tripletta->applica(array(
            "schermata-creazione-richiesta-disiscrizione" => true,
            "form-req-invio-disiscrizione" => self::getRichiesta(self::RICHIESTA_CREAZIONE_RICHIESTA_DISISCRIZIONE),
            "campo-descrizione" => ControlloreAmministrazione::CAMPO_DESCRIZIONE
        ), TriplettaSemplice::HTML);

        $this->setTitolo("Creazione della richiesta di disiscrizione");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la creazione della richiesta d'aggiornamento.
     */
    private function schermataCreazioneRichiestaAggiornamento() : void {
        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();
        
        $this->tripletta->applica(array(
            "schermata-creazione-richiesta-aggiornamento" => true,
            "form-req-invio-aggiornamento" => self::getRichiesta(self::RICHIESTA_CREAZIONE_RICHIESTA_AGGIORNAMENTO),
            "campo-nome" => ControlloreAmministrazione::CAMPO_NOME,
            "campo-cognome" => ControlloreAmministrazione::CAMPO_COGNOME,
            "campo-data-nascita" => ControlloreAmministrazione::CAMPO_DATA_NASCITA,
            "campo-luogo-nascita" => ControlloreAmministrazione::CAMPO_LUOGO_NASCITA,
            "campo-residenza" => ControlloreAmministrazione::CAMPO_RESIDENZA,
            "campo-telefono" => ControlloreAmministrazione::CAMPO_TELEFONO,
            "campo-codice-fiscale" => ControlloreAmministrazione::CAMPO_CODICE_FISCALE,
        ), TriplettaSemplice::HTML);

        $this->setTitolo("Creazione della richiesta d'aggiornamento");
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione di una richiesta di disiscrizione.
     */
    private function schermataVisualizzazioneRichiestaDisiscrizione() : void {
        $id = $this->getParams[ControlloreAmministrazione::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $richiesta = NULL;
        $this->controllore->richiediRichiestaDisiscrizione($id, $richiesta);

        $this->tripletta->setPulsante("schermata-visualizzazione-richiesta-disiscrizione",
            true, TriplettaSemplice::HTML);

        if ($richiesta !== NULL) {
            //Richiesta trovata
            $fruitore = $richiesta->getFruitore();
            $idFruitore = $fruitore->getID();
            
            $this->tripletta->applica(array(
                "richiesta-trovata" => true,
                "descrizione-disiscrizione" => $richiesta->getDescrizione(),
                "form-req-disiscrizione" => self::getRichiesta(self::RICHIESTA_DISISCRIZIONE_FRUITORE),
                "form-scr-profilo-fruitore" => VistaProfilo::getURLProfilo($idFruitore),
                "nome-utente" => $fruitore->getNomeUtente(),
                "campo-id-fruitore" => ControlloreAmministrazione::CAMPO_ID,
                "valore-id-fruitore" => $idFruitore,
            ), TriplettaSemplice::HTML);

            $titolo = "Richiesta di disiscrizione #" . $id;

        } else {
            $this->tripletta->setPulsante("richiesta-non-trovata", true,
                TriplettaSemplice::HTML);
            $titolo = "Richiesta di disiscrizione non trovata!";
        }



        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Crea schermata che consente la visualizzazione di una richiesta d'aggiornamento.
     */
    private function schermataVisualizzazioneRichiestaAggiornamento() : void {
        $id = $this->getParams[ControlloreAmministrazione::CAMPO_ID];

        //Azzera messaggi creati da questa schermata
        $this->controllore->resetMessaggi();

        $richiesta = NULL;
        $this->controllore->richiediRichiestaAggiornamento($id, $richiesta);

        $this->tripletta->setPulsante("schermata-visualizzazione-richiesta-aggiornamento",
            true, TriplettaSemplice::HTML);
        
        if ($richiesta !== NULL) {
            //Richiesta trovata
            $anagrafica = $richiesta->getAnagrafica();
            $globetrotter = $anagrafica->getCicerone();
            $id = $richiesta->getID();
            $idGlobetrotter = $globetrotter->getID();

            $this->tripletta->applica(array(
                "richiesta-trovata" => true,
                "form-req-transquasicicerone" => $this->getRichiesta(self::RICHIESTA_TRASFORMAZIONE_QUASICICERONE),
                "form-scr-profilo-globetrotter" => VistaProfilo::getURLProfilo($idGlobetrotter),
                "nome-utente" => $globetrotter->getNomeUtente(),
                "nome" => $anagrafica->getNome(),
                "cognome" => $anagrafica->getCognome(),
                "data-nascita" => $anagrafica->getDataNascita(),
                "luogo-nascita" => $anagrafica->getLuogoNascita(),
                "residenza" => $anagrafica->getResidenza(),
                "telefono" => $anagrafica->getTelefono(),
                "codice-fiscale" => $anagrafica->getCodiceFiscale(),
                "campo-id-globetrotter" => ControlloreAmministrazione::CAMPO_ID,
                "valore-id-globetrotter" => $idGlobetrotter
            ), TriplettaSemplice::HTML);
        
            $titolo = "Richiesta di aggiornamento #" . $id;
            
        } else {
            $this->tripletta->setPulsante("richiesta-non-trovata", true, TriplettaSemplice::HTML);
            $titolo = "Richiesta di aggiornamento non trovata!";
        }


        $this->setTitolo($titolo);
        $this->mostraErrori();
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare le richieste d'amministrazione.
     * @return string
     */
    public static function getURLRichiesteAmministrazione() : string {
        return self::getSchermata(self::SCHERMATA_RICHIESTE_AMMINISTRAZIONE);
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare una particolare richiesta di disiscrizione.
     * @param int $id l'id della richiesta di disiscrizione
     * @return string
     */
    public static function getURLVisualizzazioneRichiestaDisiscrizione(int $id) : string {
        return sprintf("%s&%s=%d", self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_RICHIESTA_DISISCRIZIONE), ControlloreAmministrazione::CAMPO_ID, $id);
    }


    /**
     * Restituisce l'URL per la schermata che consente di visualizzare una particolare richiesta di aggiornamento.
     * @param int $id l'id della richiesta di aggiornamento
     * @return string
     */
    public static function getURLVisualizzazioneRichiestaAggiornamento(int $id) : string {
        return sprintf("%s&%s=%d", self::getSchermata(self::SCHERMATA_VISUALIZZAZIONE_RICHIESTA_AGGIORNAMENTO), ControlloreAmministrazione::CAMPO_ID, $id);
    }


    /**
     * Restituisce l'URL per la schermata che consente di creare una richiesta di disiscrizione.
     * @return string
     */
    public static function getURLCreazioneRichiestaDisiscrizione() : string {
        return self::getSchermata(self::SCHERMATA_CREAZIONE_RICHIESTA_DISISCRIZIONE);
    }


    /**
     * Restituisce l'URL per la schermata che consente di creare una richiesta di aggiornamento.
     * @return string
     */
    public static function getURLCreazioneRichiestaAggiornamento() : string {
        return self::getSchermata(self::SCHERMATA_CREAZIONE_RICHIESTA_AGGIORNAMENTO);
    }


    /**
     * Restituisce l'URL per la richiesta che consente di effettuare la transizione a Cicerone.
     * @return string
     */
    public static function getURLTransizioneACicerone() : string {
        return self::getRichiesta(self::RICHIESTA_TRANSIZIONE_CICERONE);
    }
}

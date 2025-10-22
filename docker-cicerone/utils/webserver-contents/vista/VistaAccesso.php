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

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreAccesso.php";
require_once "TriplettaSemplice.php";
require_once "Vista.php";


use controllore\ControlloreAccesso;

/**
 * Rappresenta la vista associata al ControlloreAccesso e al ModelloUtente.
 * 
 * <p>Ha due schermate, una d'accesso standard e l'altra per la reimpostazione
 * della password in caso di recupero dell'accesso.</p>
 * 
 * @see \controllore\ControlloreAccesso
 * @see \modello\ModelloUtente
 */
class VistaAccesso extends Vista {
    protected const PAGINA_VISTA = "accesso.php";

    /*
     * Richieste che questa vista prende in considerazione
     */
    /**
     * Richiesta fatta quando viene chiesto di accedere
     * @var string
     */
    private const RICHIESTA_ACCESSO = "accesso";
    
    /**
     * Richiesta fatta quando viene chiesto di registrare un nuovo utente
     * @var string
     */
    private const RICHIESTA_REGISTRAZIONE = "registrazione";
    
    /**
     * Richiesta fatta quando viene chiesto di procedere al recupero
     * dell'accesso dopo aver inserito l'indirizzo email di appartenenza
     * (cambia lo stato dell'utente associato all'email da "attivato" a "recupero").
     * @var string
     */
    private const RICHIESTA_IMPOSTA_RECUPERO = "impostaRecupero";
    
    /**
     * Disconnessione
     * @var string
     */
    private const RICHIESTA_DISCONNESSIONE = "disconnessione";
    
    /**
     * Richiesta fatta quando si procede all'attivazione di un utente
     * registrato (mediante link diretto da email).
     * @var string
     */
    private const RICHIESTA_ATTIVAZIONE = "attivazione";

    /**
     * Richiesta fatta quando si procede all'attivazione di un utente
     * di cui è necessario recuperare l'accesso, dopo aver reimpostato
     * la password in SCHERMATA_REIMPOSTAZIONE).
     * @var string
     */
    private const RICHIESTA_ATTIVA_RECUPERO = "attivaRecupero";

    /*
     * I diversi "menù" accessibili nella schermata SCHERMATA_ACCESSO.
     * In realtà, viene utilizzato un trucco veloce in javascript.
     */
    private const MENU_ACCESSO = "accesso";
    private const MENU_REGISTRAZIONE = "registrazione";
    private const MENU_RECUPERO = "recupero";


    /**
     * Schermata principale
     */
    private const SCHERMATA_ACCESSO = "accesso";


    /**
     * L'unica schermata alternativa alla SCHERMATA_ACCESSO.
     * Questa schermata viene utilizzata quando si accede al link ottenuto via email
     * quando viene richiesto il recupero dell'accesso.<br>
     * Consente la reimpostazione della password.
     */
    private const SCHERMATA_REIMPOSTAZIONE = "reimpostazione";


    /**
     * Crea una VistaAccesso con un ControlloreAccesso sottostante
     */
    public function __construct() {
        parent::__construct(new ControlloreAccesso());
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::isRichiesta()
     */
    public function isRichiesta() : bool {
        /*
         * Si ha una richiesta se è presente il solo parametro GET_RICHIESTA
         * tra i parametri GET
         */
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
        $menuFallimento = NULL;

        /*
         * Distruggi tutta la messaggistica, dato che
         * stiamo facendo una richiesta nuova.
         */
        $this->controllore->distruggiMessaggistica();
        $isUtenteConnesso = $this->controllore->isUtenteConnesso();
        $richiestaValida = true;

        switch ($richiesta) {
            case self::RICHIESTA_ACCESSO:
                $this->controllore->richiediAccesso($this->postParams);
                $menuFallimento = self::MENU_ACCESSO;
            break;
            case self::RICHIESTA_REGISTRAZIONE:
                $this->controllore->richiediRegistrazione($this->postParams,
                    self::creaURLPerAttivazioneViaMail(), VistaProfilo::FILE_IMMAGINE_DEFAULT);
                $menuFallimento = self::MENU_REGISTRAZIONE;
            break;
            case self::RICHIESTA_IMPOSTA_RECUPERO:
                $this->controllore->richiediRecuperoAccesso($this->postParams,
                    self::creaURLPerAttivazioneViaMail(true));
                $menuFallimento = self::MENU_RECUPERO;
            break;
            case self::RICHIESTA_ATTIVAZIONE:
            case self::RICHIESTA_ATTIVA_RECUPERO:
                /*
                 * Poichè l'attivazione di un utente con stato "inserito" si ha per
                 * "link diretto", è necessario usare i parametri GET.
                 * Se al contrario, trattasi dell'attivazione di un utente con
                 * stato "recupero", è necessario ricorrere ai parametri POST, visto
                 * che i dati vengono mandati mediante form in SCHERMATA_REIMPOSTAZINE.
                 */
                $this->controllore->richiediAttivazioneUtente(
                    ($richiesta === self::RICHIESTA_ATTIVAZIONE) ? $this->getParams : $this->postParams);
            break;
            case self::RICHIESTA_DISCONNESSIONE:
                $this->controllore->richiediDisconnessione();
            break;
            default:
                $richiestaValida = false;
                //Mai eseguito
            break;
        }

        $paginaRedirect = self::getURLHOME();

        if ($richiestaValida) {
            $this->controllore->copiaMessaggisticaPerSchermata();
            
            $paginaRedirect = self::getURLHOME();
            $statoOperazione = $this->controllore->getStatoOperazione();
            
            switch ($statoOperazione) {
                case ControlloreAccesso::STATO_OPERAZIONE_FALLITA:
                    if (!$isUtenteConnesso) {
                        $paginaRedirect = $this->getMenu(self::SCHERMATA_ACCESSO, $menuFallimento);
                    }
                break;
                case ControlloreAccesso::STATO_OPERAZIONE_RIUSCITA:
                    $paginaRedirect = $this->getMenu(self::SCHERMATA_ACCESSO, self::MENU_ACCESSO);
                break;
                    /*
                     * Questo errore serve in caso si fallisca durante la reimpostazione della password
                     * durante un recupero dell'accesso.
                     * Infatti, se avessi fatto restituire STATO_OPERAZIONE_FALLITA, avrei dovuto
                     * ancora una volta, controllare i parametri inviati via post, e stabilire
                     * se CAMPO_ID e CAMPO_CODICE_ATTIVAZIONE fossero inseriti.
                     * Poichè non sappiamo se sono definiti, attendiamo che, in caso di
                     * errore, il controllore ci restituisca questo errore, così,
                     * non siamo costretti a fare ciò.
                     */
                case ControlloreAccesso::STATO_ERRORE_REIMPOSTAZIONE_PASSWORD:
                    /*
                     * Sappiamo che CAMPO_ID e CAMPO_CODICE_ATTIVAZIONE sono definiti, procedi
                     * pure.
                     */
                    $paginaRedirect = $this->getAttivazione($this->postParams[ControlloreAccesso::CAMPO_ID],
                        $this->postParams[ControlloreAccesso::CAMPO_CODICE_ATTIVAZIONE], true);
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
            $this->getParams[self::GET_SCHERMATA] : self::SCHERMATA_ACCESSO;
        
        $isUtenteConnesso = $this->controllore->isUtenteConnesso();
        $isSchermataValida = true;

        switch ($schermata) {
            case self::SCHERMATA_REIMPOSTAZIONE:
                $isSchermataValida = isset($this->getParams[ControlloreAccesso::CAMPO_ID]) &&
                    isset($this->getParams[ControlloreAccesso::CAMPO_CODICE_ATTIVAZIONE]) &&
                    !$isUtenteConnesso;
                $metodoSchermata = "schermataReimpostazionePassword";
            break;
            case self::SCHERMATA_ACCESSO:
                $isSchermataValida = !$isUtenteConnesso;
                $metodoSchermata = "schermataAccesso";
            break;
            default:
                $isSchermataValida = false;
            break;
        }

        if ($isSchermataValida) {
            $this->tripletta = new TriplettaSemplice("accesso", "form/accesso");
            
            /*
             * Associa la schermata ottenuta alla messaggistica, così da permettere
             * di mantenere i messaggi finchè non si lascia la pagina.
             */
            $this->controllore->associaSchermataPerMessaggistica($schermata);
            $this->$metodoSchermata();
        } else {
            $this->mandaA(self::getURLHOME());
        }
    }


    /**
     * Crea schermata d'accesso
     */
    private function schermataAccesso() : void {
        $this->tripletta->applica(array(
            "schermata-accesso"       => true,
            "form-req-accesso"        => self::getRichiesta(self::RICHIESTA_ACCESSO),
            "form-req-registrazione"  => self::getRichiesta(self::RICHIESTA_REGISTRAZIONE),
            "form-req-recupero"       => self::getRichiesta(self::RICHIESTA_IMPOSTA_RECUPERO),
            "campo-nomeutente"        => ControlloreAccesso::CAMPO_NOME_UTENTE,
            "campo-password"          => ControlloreAccesso::CAMPO_PASSWORD,
            "campo-conferma-password" => ControlloreAccesso::CAMPO_CONFERMA_PASSWORD,
            "campo-email"             => ControlloreAccesso::CAMPO_EMAIL 
        ), TriplettaSemplice::HTML);


        //Vai al particolare menù, se richiesto
        if (isset($this->getParams[self::GET_MENU])) {
            $menu = $this->getParams[self::GET_MENU];
            if ($menu === self::MENU_ACCESSO || $menu === self::MENU_RECUPERO ||
                $menu === self::MENU_REGISTRAZIONE) {
                $this->addJSCodice("mostra('$menu');");
            }
        }

        $this->setTitolo("Accesso");
        $this->mostraErrori();
    }


    /**
     * Crea schermata di reimpostazione password
     */
    private function schermataReimpostazionePassword() : void {
        $codiceAttivazione = $this->getParams[ControlloreAccesso::CAMPO_CODICE_ATTIVAZIONE];
        $id = $this->getParams[ControlloreAccesso::CAMPO_ID];

        $this->tripletta->applica(array(
            "schermata-reimpostazione" => true,
            "form-req-reimposta"       => self::getRichiesta(self::RICHIESTA_ATTIVA_RECUPERO),
            "campo-password"           => ControlloreAccesso::CAMPO_PASSWORD,
            "campo-conferma-password"  => ControlloreAccesso::CAMPO_CONFERMA_PASSWORD,
            "campo-codice-attivazione" => ControlloreAccesso::CAMPO_CODICE_ATTIVAZIONE,
            "campo-id"                 => ControlloreAccesso::CAMPO_ID,
            "codice-attivazione"       => $codiceAttivazione,
            "id"                       => $id
        ), TriplettaSemplice::HTML);

        $this->setTitolo("Reimposta password");
        $this->mostraErrori();
    }


    /**
     * Crea un URL da passare al controllore cosìcchè si sappia qual'è la pagina
     * da caricare per effettuare l'attivazione/recupero.
     * 
     * È importante notare che l'URL viene passato per disaccoppiare le informazioni
     * inerenti il sito stesso, in maniera tale che controllore e modello non debbano
     * conoscere qual'è il sito effettivo.
     * 
     * @param bool $recupero <p>Se impostato su vero, genererà un URL per effettuare
     * il recupero (ovvero, rimandare alla schermata di reimpostazione password).<br>
     * In caso contrario, genererà un URL per effettuare l'attivazione.</p>
     * @return string l'URL che verrà utilizzato.
     */
    private static function creaURLPerAttivazioneViaMail(bool $recupero=false) : string {
        return sprintf("%s://%s:%s/%s", $_SERVER["REQUEST_SCHEME"], $_SERVER["SERVER_NAME"],
            $_SERVER["SERVER_PORT"], self::getAttivazione("%s", "%s", $recupero));
    }


    /**
     * Crea un url parziale per gli indirizzi di attivazione.
     * @param string $id
     * @param string $codiceAttivazione
     * @param bool $recupero <p>Se impostato su vero, genererà un URL per effettuare
     * il recupero (ovvero, rimandare alla schermata di reimpostazione password).<br>
     * In caso contrario, genererà un URL per effettuare l'attivazione.</p>
     * @return string
     */
    private static function getAttivazione(string $id, string $codiceAttivazione, bool $recupero) : string {
        $URL_ATTIVAZIONE = "%s?%s=%s&%s=%s&%s=%s";

        if ($recupero) {
            //L'URL rimanderà ad una pagina che consentirà di reimpostare la password
            $tipoForwarding = self::GET_SCHERMATA;
            $forwarding = self::SCHERMATA_REIMPOSTAZIONE;
        } else {
            //L'URL rimanderà alla pagina di attivazione diretta
            $tipoForwarding = self::GET_RICHIESTA;
            $forwarding = self::RICHIESTA_ATTIVAZIONE;
        }

        return sprintf($URL_ATTIVAZIONE, self::PAGINA_VISTA, $tipoForwarding, $forwarding,
            ControlloreAccesso::CAMPO_ID, $id, ControlloreAccesso::CAMPO_CODICE_ATTIVAZIONE,
            $codiceAttivazione);
    }


    /*
     * Usati per l'esterno
     */
    /**
     * Restituisce l'URL per giungere al menù d'accesso nella SCHERMATA_ACCESSO
     * @return string
     */
    public static function getURLMenuAccesso() : string {
        return self::getMenu(self::SCHERMATA_ACCESSO, self::MENU_ACCESSO);
    }


    /**
     * Restituisce l'URL per giungere al menù di registrazione nella SCHERMATA_ACCESSO
     * @return string
     */
    public static function getURLMenuRegistrazione() : string {
        return self::getMenu(self::SCHERMATA_ACCESSO, self::MENU_REGISTRAZIONE);
    }


    /**
     * Restituisce l'URL per giungere al menù di recupero nella SCHERMATA_ACCESSO
     * @return string
     */
    public static function getURLMenuRecupero() : string {
        return self::getMenu(self::SCHERMATA_ACCESSO, self::MENU_RECUPERO);
    }


    /**
     * Restituisce l'URL per effettuare la disconessione
     * @return string
     */
    public static function getURLRichiestaDisconnessione() : string {
        return self::getRichiesta(self::RICHIESTA_DISCONNESSIONE);
    }
}
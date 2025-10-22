<?php

namespace vista;

require_once $_SERVER["DOCUMENT_ROOT"] . "/controllore/ControlloreNotifica.php";
require_once "TriplettaSemplice.php";
require_once "Vista.php";


use controllore\ControlloreNotifica;


/**
 * Classe vista di test
 */
class VistaNotifica extends Vista {
    protected const PAGINA_VISTA = "notifica.php";


    /*
     * Le diverse schermata che la vista mostra.
     */
    /**
     * Schermata di test.
     */
    private const SCHERMATA_VISUALIZZAZIONE_NOTIFICHE = "notifiche";

    private const SCHERMATA_MODIFICA_PREFERENZE_NOTIFICA = "modPrefNotifiche";


    /*
     * Richieste che questa vista prende in considerazione
     */
    /**
     * Richiesta fatta quando nella schermata SCHERMATA_MODIFICA_PROFILO
     * si chiede l'invio dei cambiamenti.
     */
    private const RICHIESTA_NOTIFICHE = "lastnotifs";

    private const RICHIESTA_RIMOZIONE_NOTIFICA = "rimuovi";
    
    private const RICHIESTA_MODIFICA_PREFERENZE_NOTIFICA = "modPrefNotifiche";
    
    
    private $separateNotifications;


    /**
     * Crea una VistaNotifica con un ControlloreNotifica sottostante
     */
    public function __construct(bool $separateNotifications=false) {
        parent::__construct(new ControlloreNotifica());
        $this->separateNotifications = $separateNotifications;
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


        if ($this->controllore->isUtenteConnesso()) {
            switch ($richiesta) {
                case self::RICHIESTA_ULTIME_NOTIFICHE:
                    $notifiche = NULL;
                    $this->controllore->richiediNotifiche($this->getParams, $notifiche);
                    if ($notifiche !== NULL) {
                        foreach ($notifiche as $idx => $notifica) {
                            $notifiche[$idx] = $notifica->toArray();
                        }
                    }
                    header("Content-Type: application/json");
                    echo json_encode($notifiche);
                    exit();
                break;
                default:
                    //Mai eseguito
                break;
            }
        }
    }
    
    
    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \vista\Vista::disegna()
     */
    public function disegna() : void {
        if ($this->separateNotifications) {
            $this->getParams[self::GET_SCHERMATA] = self::SCHERMATA_NOTIFICHE;
        }

        $schermata = $this->getParams[self::GET_SCHERMATA];

        if ($schermata === self::SCHERMATA_VISUALIZZAZIONE_NOTIFICHE) {
            $this->tripletta = new TriplettaSemplice("notifica", "form/notifica");
            $this->schermataVisualizzazioneNotifiche();
        }
    }


    /**
     * Schermata di test.
     * C'è altro da dire?
     */
    private function schermataVisualizzazioneNotifiche() : void {
        $this->tripletta->setPulsante("schermata-notifiche", true, TriplettaSemplice::HTML);
        $this->tripletta->setPulsante("ultime-notifiche", $this->separateNotifications, TriplettaSemplice::HTML);

        $this->setTitolo("Tutte le notifiche");
    }
}

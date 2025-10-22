<?php

namespace controllore;

require_once "Controllore.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloNotifica.php";

use modello\ModelloNotifica;


/**
 * Controllore di Test, nient'altro da segnalare
 */
class ControlloreNotifica extends Controllore {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva  elaborazione.
     */

    /*
     * Tipi di validazione
     */

    
    /*
     * Messaggi che appaiono in più zone
     */

    /**
     * Crea un nuovo ControlloreAccesso con un ModelloUtente sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloNotifica());
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected function validaParametri(array& $params, string $tipo) : bool {
        return false;
    }


    public function richiediNotifiche(array $params, ?array& $notifiche) : void {
        session_write_close(); //da mandare da un'altra parte
        $this->modello->getNotifiche($params["idUtente"], $params["dataCreazione"], $notifiche);
    }
}
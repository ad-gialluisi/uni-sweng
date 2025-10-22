<?php

namespace modello;

require_once "CiceroneDatabase.php";
require_once "PreferenzeNotifica.php";


/**
 * Rappresenta un modello di questo sistema.
 * <p>Ogni classe che rappresenta un modello specifico deve essere figlia
 * di questa classe.<br>
 * Un qualunque modello in questo sistema si occupa esclusivamente di:</p>
 * <ul><li>Eseguire i casi d'uso</li>
 * <li>Manipolare e ottenere i dati dal database Cicerone.</li>
 * <li>Comunicare con il corrispondente Controllore.<br>
 * La comunicazione avviene mediante codici di stato (cioè che indicano
 * come è andata una certa operazione).<br>
 * I controllori otterrano dei codici che poi tramuteranno in messaggi
 * significativi o in dati da restituire alle viste.</li></ul>
 */
abstract class Modello {
    /**
     * Questo stato è utilizzato ogniqualvolta non si vuole segnalare qualcosa.
     * È da notare che a seguito di questo stato, è possibile che l'operazione
     * sia andata a buon fine, però può anche essere l'opposto.
     * @var int
     */
    public const STATO_NO_SEGNALAZIONE = 0;
    
    public const STATO_PREFERENZE_NOTIFICA_NON_VALIDE = 99;
    
    
    //Gestore della connessione al DB
    protected $ciceroneDatabase;


    public function __construct() {
        $this->ciceroneDatabase = new CiceroneDatabase();
    }
    
    
    /**
     * Consente di inviare notifiche
     * @param int $idUtente
     * @param string $descrizione
     * @param string $link
     * @return int
     */
    public function inviaNotifica(int $idUtente, string $descrizione, string $link) : int {
        $this->ciceroneDatabase->apri();

        $this->ciceroneDatabase->manipola("insert into Notifica id_utente, descrizione, link, data_creazione values (?, ?, ?, ?, now(3))",
            $idUtente, $descrizione, $link);

        $this->ciceroneDatabase->chiudi();
    }


    /**
     * Consente di ottenere le particolari preferenze di notifica
     * di un utente.
     * @param int $idUtente
     * @param PreferenzeNotifica $preferenze
     * @return int
     */
    public function getPreferenzeNotifica(int $idUtente, ?PreferenzeNotifica& $preferenze) : int {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select * from PreferenzeNotifica where id = ?",
            $idUtente);

        if (count($righe) !== 1) {
            $codiceStato = self::STATO_PREFERENZE_NOTIFICA_NON_VALIDE;
        } else {
            $preferenze = PreferenzeNotifica::daArray($righe[0]);
            $codiceStato = self::STATO_NO_SEGNALAZIONE;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
}
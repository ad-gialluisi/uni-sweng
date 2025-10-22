<?php

namespace modello;

require_once "Modello.php";
require_once "Utente.php";
require_once "Notifica.php";
require_once "Anagrafica.php";

use modello\entità\Notifica;


/**
 * Questo è il modello che tratta alcune informazioni che riguardano le notifiche.
 * <p>In particolare, esegue i casi d'uso inerenti:</p>
 * <ul><li>Rimozione di una notifica</li>
 * <li>Modifica delle preferenze di notifica</li>
 * <li>Impostazione di una notifica come letta</li></ul>
 */
class ModelloNotifica extends Modello {
    /*
     * Queste costanti servono ad indicare gli stati che un operazione
     * raggiunge in seguito al successo/fallimento/evoluzione della stessa.
     * Fornirò commenti solo agli stati che possono risultare "ambigui" ad una
     * prima lettura.
     */
    /**
     * Stato raggiunto quando viene rimossa una notifica
     */
    public const STATO_RIMOZIONE_NOTIFICA_RIUSCITA = 2;

    /**
     * Stato raggiunto quando vengono modificate le preferenze di notifica
     */
    public const STATO_MODIFICA_PREFERENZE_NOTIFICA_RIUSCITA = 3;

    /**
     * Stato raggiunto quando una notifica viene impostata come letta.
     */
    public const STATO_IMPOSTAZIONE_NOTIFICA_LETTA_RIUSCITA = 4;


    /**
     * Consente di restituire le notifiche non appena sono presenti nel Database.
     * Questo metodo deve essere chiamato da una chiamata AJAX affinchè funzioni correttamente.
     * @param string $idUtente
     * @param string $dataCreazione
     * @param array $notifiche
     * @return int
     */
    public function getNotifiche(string $idUtente, string $dataCreazione, ?array& $notifiche) : int {
        $this->ciceroneDatabase->apri();

        //Attendi 5 minuti prima di chiudere la connessione.
        $maxSecondi = 5 * 60;
        $notificheTrovate = false;
        $secondi = 0;

        while (!$notificheTrovate) {
            $righe = $this->ciceroneDatabase->query("select * from Notifica where id_utente = ? and data_creazione > ?",
                $idUtente, $dataCreazione);
            $nRighe = count($righe);

            if ($nRighe > 0) {
                $notifiche = array();
                foreach ($righe as $riga) {
                    $notifiche[]= Notifica::daArray($riga);
                }
                $notificheTrovate = true;

            } else {
                /*
                 * Se non ci sono stati nuovi risultati, attendi
                 */
                //Attendi 2 secondi
                sleep(2);
                $secondi += 2;

                if ($secondi === $maxSecondi) {
                    break;
                }
            }
        }

        $this->ciceroneDatabase->chiudi();

        return 0;
    }
    
    
    /**
     * Esegue il caso d'uso inerente la rimozione di una notifica.
     * @param string $id
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RIMOZIONE_NOTIFICA_RIUSCITA
     */
    public function rimuoviNotifica(string $id) : int {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select 1 from Notifica where id = ?", $id);
        $nRighe = count($righe);
        
        $codiceStato = self::STATO_NO_SEGNALAZIONE;
        
        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola("delete from Notifica where id = ?", $id);
            $codiceStato = self::STATO_RIMOZIONE_NOTIFICA_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la modifica delle preferenze di notifica.
     * @param string $idUtente
     * @param bool $partecipazioneItinerario
     * @param bool $annullamentoItinerario
     * @param bool $declinoItinerario
     * @param bool $ricezioneFeedback
     * @param bool $viaMail
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_MODIFICA_PREFERENZE_NOTIFICA_RIUSCITA
     */
    public function modificaPreferenzeNotifica(string $idUtente, bool $partecipazioneItinerario,
        bool $annullamentoItinerario, bool $declinoItinerario, bool $ricezioneFeedback, bool $viaMail) : int {

        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select 1 from PreferenzeNotifica where id_utente = ?", $idUtente);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;
        
        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola(
                "update PreferenzeNotifica set partecipazione_itinerario = ?, annullamento_itinerario = ?, " .
                "declino_itinerario = ?, ricezione_feedback = ?, via_mail = ? where id_utente = ?",
                $partecipazioneItinerario, $annullamentoItinerario, $declinoItinerario, $ricezioneFeedback,
                $viaMail, $idUtente
            );
            $codiceStato = self::STATO_MODIFICA_PREFERENZE_NOTIFICA_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();
        
        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'impostazione di una notifica come letta
     * @param string $id
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_IMPOSTAZIONE_NOTIFICA_LETTA_RIUSCITA
     */
    public function impostaNotificaLetta(string $id) : int {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("update Notifica set letta = true where id = ?", $id);
        $nRighe = count($righe);
        
        $codiceStato = self::STATO_NO_SEGNALAZIONE;
        
        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola("delete from Notifica where id = ?", $id);
            $codiceStato = self::STATO_IMPOSTAZIONE_NOTIFICA_LETTA_RIUSCITA;
        }
        
        $this->ciceroneDatabase->chiudi();
        
        return $codiceStato;
    }
}

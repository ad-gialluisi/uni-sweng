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


namespace modello;

require_once "ModelloImpostazioniItinerario.php";
require_once "entità/Feedback.php";
require_once "entità/Utente.php";
require_once "entità/Itinerario.php";
require_once "entità/Partecipazione.php";

use modello\entità\Feedback;
use modello\entità\Utente;
use modello\entità\Itinerario;
use modello\entità\Partecipazione;


/**
 * Questo è il modello che tratta le informazioni che riguardano i feedback.
 * <p>In particolare, esegue i casi d'uso inerenti:</p>
 * <ul><li>Il rilascio dei feedback partecipante-organizzatore</li>
 * <li>Il rilascio dei feedback organizzatore-partecipante.</li></ul>
 */
class ModelloFeedback extends ModelloImpostazioniItinerario {
    /*
     * Queste costanti servono ad indicare gli stati che un operazione
     * raggiunge in seguito al successo/fallimento/evoluzione della stessa.
     * Fornirò commenti solo agli stati che possono risultare "ambigui" ad una
     * prima lettura.
     */
    /**
     * Stato raggiunto quando i feedback sono stati trovati.
     */
    public const STATO_FEEDBACK_TROVATI = 5;
    
    /**
     * Stato raggiunto quando un feedback è già esistente.
     */
    public const STATO_FEEDBACK_ESISTENTE = 6;
    
    /**
     * Stato raggiunto quando viene rilasciato un feedback
     */
    public const STATO_RILASCIO_FEEDBACK_RIUSCITO = 7;
    
    /**
     * Stato raggiunto quando il feedback richiesto viene trovato
     */
    public const STATO_FEEDBACK_TROVATO = 8;
    
    /**
     * Stato raggiunto quando il feedback richiesto NON viene trovato
     */
    public const STATO_FEEDBACK_NON_TROVATO = 9;

    /*
     * Costanti utili a definire le regex per i tipi di dato passati.
     */
    /**
     * Regex per il voto di un Feedback
     */
    public const REGEX_VOTO = "#[0-5]#";


    /**
     * Stabilisce se esiste un certo Feedback
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @param string $tipo tipo di feedback
     * @return bool restituisce true, se il feedback ricercato esiste, false altrimenti
     */
    public function esisteFeedback(int $idItinerario, int $idPartecipante, string $tipo) : bool {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Feedback where id_itinerario = ? " .
            "and id_partecipante = ? and tipo = ?", $idItinerario, $idPartecipante,
            $tipo);

        $this->ciceroneDatabase->chiudi();
        
        return count($righe) === 1;
    }


    /**
     * Restiuisce lo stato del particolare itinerario
     * @param int $idItinerario id dell'itinerario
     * @return string|NULL lo stato dell'itinerario o NULL se l'itinerario non esiste
     */
    public function getStatoItinerario(int $idItinerario) : ?string {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select stato from Itinerario where id = ?",
            $idItinerario);
        
        $this->ciceroneDatabase->chiudi();
        
        return count($righe) === 1 ? $righe[0]["stato"] : NULL;
    }


    /**
     * Restiuisce tutti i partecipanti ad un certo itinerario
     * @param int $idItinerario id dell'itinerario
     * @param array $partecipanti risultato dell'operazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getPartecipantiAdItinerario(int $idItinerario, ?array& $partecipanti) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Utente.id, Utente.nome_utente, Utente.immagine " .
            "from Utente, Partecipazione where Utente.id = Partecipazione.id_partecipante and " .
            "Partecipazione.id_itinerario = ? and Partecipazione.stato = ?",
            $idItinerario, Partecipazione::STATO_ACCORDATA);

        $partecipanti = array();

        foreach ($righe as $riga) {
            $partecipanti[]= Utente::daArray($riga);
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_NO_SEGNALAZIONE;
    }


    /**
     * Restituisce un certo Feedback dato l'id
     * @param int $id id del feedback
     * @param Feedback $feedback
     * @return int lo stato dell'operazione, che può essere: STATO_FEEDBACK_NON_TROVATO, STATO_FEEDBACK_TROVATO
     */
    public function getFeedback(int $id, ?Feedback& $feedback): int {
        $this->ciceroneDatabase->apri();

        $colonneUtente = array("id", "nome_utente");
        $colonneItineario = array("id", "nome", "id_cicerone");
        $colonneFeedback = array("id", "id_itinerario", "id_partecipante", "voto", "descrizione",
            "tipo");

        $query = self::creaQueryConColonneRiconoscibili(
            "select %s from Feedback, Itinerario, Utente where " .
            "Itinerario.id = Feedback.id_itinerario and " .
            "Utente.id = Feedback.id_partecipante and " .
            "Feedback.id = ?",
            array(Itinerario::NOME_TABELLA, Feedback::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneItineario, $colonneFeedback, $colonneUtente));

        $righe = $this->ciceroneDatabase->query($query, $id);
        $nRighe = count($righe);

        $codiceStato = self::STATO_FEEDBACK_NON_TROVATO;
        $feedback = NULL;

        if ($nRighe === 1) {
            $feedback = Feedback::daArray($righe[0]);
            $codiceStato = self::STATO_FEEDBACK_TROVATO;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Restituisce tutti i feedback di un determinato fruitore
     * @param int $idFruitore id del fruitore
     * @param array $feedbacks risultato dell'operazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getFeedbacksFruitore(int $idFruitore, ?array& $feedbacks): int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select tipo from Utente where id = ? and " .
            "tipo != ?", $idFruitore, Utente::TIPO_AMMINISTRATORE);
        $nRighe = count($righe);


        $feedbacks = NULL;
        if ($nRighe === 1) {
            $feedbacks = array();
            
            $isCicerone = $righe[0]["tipo"] === Utente::TIPO_CICERONE;

            $colonneUtente = array("id", "nome_utente");
            $colonneItineario = array("id", "nome");
            $colonneFeedback = array("id", "id_itinerario", "id_partecipante", "voto", "descrizione", "tipo");

            if ($isCicerone) {
                //Preleva anche feedback di tipo partecipante-organizzatore
                $query = self::creaQueryConColonneRiconoscibili(
                    "select %s from Feedback, Itinerario, Utente where " .
                    "Itinerario.id = Feedback.id_itinerario and " .
                    "Utente.id = Feedback.id_partecipante and Feedback.tipo = ? and " .
                    "Itinerario.id_cicerone = ?",
                    array(Itinerario::NOME_TABELLA, Feedback::NOME_TABELLA, Utente::NOME_TABELLA),
                    array($colonneItineario, $colonneFeedback, $colonneUtente));

                $righe = $this->ciceroneDatabase->query($query, Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE,
                    $idFruitore);

                $feedbacks[1] = array();
                foreach ($righe as $riga) {
                    $feedbacks[1][]= Feedback::daArray($riga);
                }
            } else {
                $feedbacks[1] = NULL;
            }


            //Preleva feedback di tipo organizzatore-partecipante
            $query = self::creaQueryConColonneRiconoscibili(
                "select %s from Feedback, Itinerario, Utente where " .
                "Itinerario.id = Feedback.id_itinerario and " .
                "Utente.id = Itinerario.id_cicerone and Feedback.tipo = ? and " .
                "Feedback.id_partecipante = ?",
                array(Itinerario::NOME_TABELLA, Feedback::NOME_TABELLA, Utente::NOME_TABELLA),
                array($colonneItineario, $colonneFeedback, $colonneUtente));
            
            $righe = $this->ciceroneDatabase->query($query, Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE,
                $idFruitore);

            $feedbacks[0] = array();
            foreach ($righe as $riga) {
                $feedbacks[0][]= Feedback::daArray($riga);
            }
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_NO_SEGNALAZIONE;
    }


    /**
     * Esegue il caso d'uso inerente il rilascio di un feedback partecipante-organizzatore.
     * @param int $idItinerario l'id dell'itinerario
     * @param int $idPartecipante l'id del partecipante
     * @param string $descrizione
     * @param int $voto
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RILASCIO_FEEDBACK_RIUSCITO
     */
    public function rilasciaFeedbackPartecipanteOrganizzatore(int $idItinerario, int $idPartecipante, string $descrizione, int $voto): int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Itinerario, Partecipazione " .
            "where Itinerario.id = Partecipazione.id_itinerario and Itinerario.id = ? and " .
            "Partecipazione.id_partecipante = ? and Partecipazione.stato = ? and " .
            "Itinerario.stato = ?", $idItinerario, $idPartecipante,
            Partecipazione::STATO_ACCORDATA, Itinerario::STATO_CONCLUSO);
        $nRighe = count($righe);
        
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $righe = $this->ciceroneDatabase->query("select 1 from Feedback where id_itinerario = ? and " .
                "id_partecipante = ? and tipo = ?", $idItinerario, $idPartecipante,
                Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE);
            $nRighe = count($righe);


            if ($nRighe === 0) {
                $this->ciceroneDatabase->manipola("insert into Feedback (id_itinerario, id_partecipante, " .
                    "descrizione, voto, tipo) values (?, ?, ?, ?, ?)", $idItinerario,
                    $idPartecipante, $descrizione, $voto,
                    Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE);
                $codiceStato = self::STATO_RILASCIO_FEEDBACK_RIUSCITO;
            }
        }
            
        $this->ciceroneDatabase->chiudi();
            
        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente il rilascio di un feedback organizzatore-partecipante.
     * @param int $idItinerario l'id dell'itinerario
     * @param int $idCicerone l'id del Cicerone organizzatore
     * @param int $idPartecipante l'id del partecipante
     * @param string $descrizione
     * @param int $voto
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RILASCIO_FEEDBACK_RIUSCITO
     */
    public function rilasciaFeedbackOrganizzatorePartecipante(int $idItinerario, int $idCicerone, array $idPartecipanti, string $descrizione, int $voto): int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Itinerario where id = ? " .
            "and id_cicerone = ? and stato = ?",
            $idItinerario, $idCicerone, Itinerario::STATO_CONCLUSO);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $nPartecipanti = count($idPartecipanti);
            $queryPart = array_fill(0, $nPartecipanti, "id_partecipante = ?");

            $query = sprintf("select 1 from Partecipazione where id_itinerario = ? and " .
                "stato = ? and (%s)", implode(" or ", $queryPart));

            $righe = call_user_func_array(array($this->ciceroneDatabase, "query"),
                array_merge(array($query, $idItinerario, Partecipazione::STATO_ACCORDATA), $idPartecipanti));

            $sonoPartecipanti = (count($righe) === $nPartecipanti);

            $query = sprintf("select 1 from Feedback where id_itinerario = ? and " .
                "tipo = ? and (%s)", implode(" or ", $queryPart));
            
            $righe = call_user_func_array(array($this->ciceroneDatabase, "query"),
                array_merge(array($query, $idItinerario, Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE), $idPartecipanti));

            $feedbackAssenti = (count($righe) === 0);

            if ($sonoPartecipanti && $feedbackAssenti) {
                $this->ciceroneDatabase->iniziaTransazione();
                
                foreach ($idPartecipanti as $idPartecipante) {
                    $this->ciceroneDatabase->manipola("insert into Feedback (id_itinerario, " .
                        "id_partecipante, descrizione, voto, tipo) values (?, ?, ?, ?, ?)",
                        $idItinerario, $idPartecipante, $descrizione, $voto,
                        Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE);
                }
                
                $this->ciceroneDatabase->commit();
                $codiceStato = self::STATO_RILASCIO_FEEDBACK_RIUSCITO;
            }
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
}

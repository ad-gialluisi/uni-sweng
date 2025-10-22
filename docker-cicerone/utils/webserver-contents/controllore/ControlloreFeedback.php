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


namespace controllore;

require_once "ControlloreImpostazioniItinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloFeedback.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Feedback.php";

use modello\ModelloFeedback;
use modello\entità\Feedback;


/**
 * Rappresenta il controllore associato alla VistaFeedback e al ModelloFeedback.
 * 
 * @see \vista\VistaFeedback
 * @see \modello\ModelloFeedback
 */
class ControlloreFeedback extends ControlloreImpostazioniItinerario {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva elaborazione.
     */
    public const CAMPO_VOTO = "voto";


    /*
     * Tipi di validazione
     */
    private const VALIDAZIONE_PO = "partecipante-organizzatore";
    private const VALIDAZIONE_OP = "organizzatore-partecipante";


    /**
     * Crea un nuovo ControlloreFeedback con un ModelloFeedback sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloFeedback());
    }


    /**
     * Esegue l'elaborazione necessaria per stabilire se un certo Feedback esiste.
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @param string $tipo tipo di feedback
     * @return bool true, se il feedback esiste, false altrimenti
     */
    public function esisteFeedback(int $idItinerario, int $idPartecipante, string $tipo) : bool {
        return $this->modello->esisteFeedback($idItinerario, $idPartecipante, $tipo);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire lo stato di un itinerario.
     * @param int $idItinerario l'id dell'itinerario
     * @return string|NULL lo stato dell'itinerario o NULL se l'itinerario non esiste
     */
    public function richiediStatoItinerario(int $idItinerario) : ?string {
        return $this->modello->getStatoItinerario($idItinerario);
    }
    

    /**
     * Esegue l'elaborazione necessaria per reperire i dati di un particolare Feedback.
     * @param int $id l'id del feedback
     * @param Feedback $feedback il risultato del prelievo
     */
    public function richiediFeedback(int $id, ?Feedback& $feedback) : void {
        $this->modello->getFeedback($id, $feedback);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati dei partecipanti ad un certo ad un itienrario.
     * @param int $idItinerario l'id dell'itinerario
     * @param array $partecipanti il risultato del prelievo
     */
    public function richiediPartecipantiAdItinerario(int $idItinerario, ?array& $partecipanti) : void {
        $this->modello->getPartecipantiAdItinerario($idItinerario, $partecipanti);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i feedback associati ad un fruitore.
     * @param int $idFruitore l'id del fruitore
     * @param array $feedbacks il risultato del prelievo
     */
    public function richiediFeedbacksFruitore(int $idFruitore, ?array& $feedbacks) : void {
        $this->modello->getFeedbacksFruitore($idFruitore, $feedbacks);
    }


    /**
     * Esegue l'elaborazione necessaria per il rilascio di un feedback partecipante-organizzatore.
     * @param array $params i parametri della richiesta
     */
    public function richiediRilascioFeedbackPO(array $params) : void {
        $this->richiediRilascioFeedback($params, false);
    }


    /**
     * Esegue l'elaborazione necessaria per il rilascio di un feedback organizzatore-partecipante.
     * @param array $params i parametri della richiesta
     */
    public function richiediRilascioFeedbackOP(array $params) : void {
        $this->richiediRilascioFeedback($params, true);
    }


    /**
     * Metodo di servizio che elabora effettivamente il rilascio del feedback
     * @param array $params i parametri della richiesta
     * @param bool $isOP se true, trattasi di un feedback organizzatore-partecipante,
     * se false di feedback partecipante-organizzatore
     */
    private function richiediRilascioFeedback(array $params, bool $isOP) {
        $validazioneRiuscita = $this->validaParametri($params, ($isOP ? self::VALIDAZIONE_OP : self::VALIDAZIONE_PO));
        if ($validazioneRiuscita) {
            $descrizione = $params[self::CAMPO_DESCRIZIONE];
            $voto = $params[self::CAMPO_VOTO];
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            
            if ($isOP) {
                $idPartecipanti = explode(",", $params[self::CAMPO_ID_PARTECIPANTE]);
                $codiceStato = $this->modello->rilasciaFeedbackOrganizzatorePartecipante(
                    $idItinerario, $this->getIDUtente(), $idPartecipanti, $descrizione, $voto
                );
            } else {
                $codiceStato = $this->modello->rilasciaFeedbackPartecipanteOrganizzatore(
                    $idItinerario, $this->getIDUtente(), $descrizione, $voto
                );
            }

            if ($codiceStato === ModelloFeedback::STATO_RILASCIO_FEEDBACK_RIUSCITO) {
                $messaggio = "Feedback rilasciato con successo";
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Metodo ereditato
     * {@inheritDoc}
     * @see \controllore\Controllore::validaParametri()
     */
    protected function validaParametri(array& $params, string $tipo) : bool {        
        if ($tipo === self::VALIDAZIONE_PO) {
            $isFruitore = $this->isUtenteFruitore();

            if ($isFruitore) {
                $valido = $this->validaParametriRilascioFeedbackPO($params);
            } else {
                $valido = false;
            }
        } else if ($tipo === self::VALIDAZIONE_OP) {
            $isCicerone = $this->isUtenteCicerone();
            
            if ($isCicerone) {
                $valido = $this->validaParametriRilascioFeedbackOP($params);
            } else {
                $valido = false;
            }
        }

        if (!$valido) {
            $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
        }

        return $valido;
    }


    /**
     * Valida i parametri per il rilascio del feedback organizzatore-partecipante.
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriRilascioFeedbackOP(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_DESCRIZIONE, self::CAMPO_ID_PARTECIPANTE,
            self::CAMPO_ID_ITINERARIO, self::CAMPO_VOTO);
        
        if ($valido) {
            $valido = $this->validaCampoSemplice($params[self::CAMPO_DESCRIZIONE], "descrizione", 5);

            if ($valido) {
                $idPartecipanti = explode(",", $params[self::CAMPO_ID_PARTECIPANTE]);

                foreach ($idPartecipanti as $idPartecipante) {
                    $valido = preg_match(ModelloFeedback::REGEX_ID, $idPartecipante);
                    
                    if (!$valido) {
                        $this->sessione->addMessaggio("Partecipanti non validi");
                        break;
                    }
                }
                
                if ($valido) {
                    $validazioni = array(
                        self::CAMPO_ID_ITINERARIO => array(
                            "regex" => ModelloFeedback::REGEX_ID,
                            "nomeCampo" => "Il campo itinerario"),
                        self::CAMPO_VOTO => array(
                            "regex" => ModelloFeedback::REGEX_VOTO,
                            "nomeCampo" => "Il campo voto"),
                    );
                    
                    foreach ($validazioni as $campo => $validazione) {
                        $valido = preg_match($validazione["regex"], $params[$campo]);
                        
                        if (!$valido) {
                            $this->sessione->addMessaggio($validazione["nomeCampo"] . " è malformato");
                            break;
                        }
                    }
                }
            }
        }

        return $valido;
    }


    /**
     * Valida i parametri per il rilascio del feedback partecipante-organizzatore.
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriRilascioFeedbackPO(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_DESCRIZIONE,
            self::CAMPO_ID_ITINERARIO, self::CAMPO_VOTO);

        if ($valido) {
            $valido = $this->validaCampoSemplice($params[self::CAMPO_DESCRIZIONE], "descrizione", 5);

            if ($valido) {
                $validazioni = array(
                    self::CAMPO_ID_ITINERARIO => array(
                        "regex" => ModelloFeedback::REGEX_ID,
                        "nomeCampo" => "Il campo itinerario"),
                    self::CAMPO_VOTO => array(
                        "regex" => ModelloFeedback::REGEX_VOTO,
                        "nomeCampo" => "Il campo voto"),
                    );
                    
                foreach ($validazioni as $campo => $validazione) {
                    $valido = preg_match($validazione["regex"], $params[$campo]);
                        
                    if (!$valido) {
                        $this->sessione->addMessaggio($validazione["nomeCampo"] . " è malformato");
                        break;
                    }
                }
            }
        }

        return $valido;
    }
}
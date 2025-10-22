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
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloItinerario.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/Itinerario.php";

use modello\ModelloItinerario;
use modello\entità\Itinerario;


/**
 * Rappresenta il controllore associato alla VistaItinerario e al ModelloItinerario.
 * 
 * @see \vista\VistaItinerario
 * @see \modello\ModelloItinerario
 */
class ControlloreItinerario extends ControlloreImpostazioniItinerario {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva elaborazione.
     */
    /*
     * Campi inerenti gli itinerari
     */
    public const CAMPO_NOME = "nome";
    public const CAMPO_DATA = "data"; //Usato anche nella ricerca
    public const CAMPO_ORA = "ora"; //Usato anche nella ricerca
    public const CAMPO_LINGUA = "lingua";
    public const CAMPO_LUOGO = "luogo";
    public const CAMPO_POPOLARITÀ = "popolarità"; //Usato anche nella ricerca
    public const CAMPO_VALUTA = "valuta";
    public const CAMPO_COMPENSO = "compenso";
    public const CAMPO_STATO = "stato";


    //Campi inerenti la ricerca di itinerari
    public const CAMPO_LUOGO_CONTIENE = "luogo-contiene";
    public const CAMPO_ITINERARIO_CONTIENE = "itinerario-contiene";
    public const CAMPO_FILTRO_DATA_ORA = "filtro-data-ora";
    public const CAMPO_ITINERARIO_FILTRO_1 = "filtro-popolarità-1";
    public const CAMPO_ITINERARIO_FILTRO_2 = "filtro-popolarità-2";
    public const CAMPO_ITINERARIO_FILTRO_3 = "filtro-popolarità-3";
    public const CAMPO_ITINERARIO_FILTRO_4 = "filtro-popolarità-4";
    public const CAMPO_ITINERARIO_FILTRO_5 = "filtro-popolarità-5";
    public const CAMPO_INCLUSIONE_ITINERARI_NON_APERTI = "includi-non-aperti";
    public const CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE = "includi-itinerari-partecipante";
    public const CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE = "includi-itinerari-organizzatore";


    /*
     * Tipi di validazione
     */
    /*
     * Per gli itinerari
     */
    private const VALIDAZIONE_CREAZIONE_ITINERARIO = "creazione";
    private const VALIDAZIONE_MODIFICA_ITINERARIO = "modifica";
    private const VALIDAZIONE_RIMOZIONE_ITINERARIO = "rimozione";
    private const VALIDAZIONE_RICERCA_ITINERARIO = "ricerca";

    /*
     * Per le partecipazioni
     */
    private const VALIDAZIONE_INVIO_RICHIESTE = "invio";
    private const VALIDAZIONE_ACCORDO_RICHIESTE = "accordo";


    /**
     * Dimensione massima consentita per un'immagine di itinerario in byte
     */
    public const MAX_DIMENSIONE_IMMAGINE = 500000;


    /**
     * Larghezza massima per un'immagine di itinerario in pixel
     */
    public const MAX_WIDTH_IMMAGINE = 1920;


    /**
     * Altezza massima per un'immagine di itinerario in pixel
     */
    public const MAX_HEIGHT_IMMAGINE = 1080;



    /**
     * Crea un nuovo ControlloreItinerario con un ModelloItinerario sottostante
     */
    public function __construct() {
        parent::__construct(new ModelloItinerario());
    }


    /*
     * Metodi di servizio vari
     */
    /**
     * Esegue l'elaborazione necessaria per reperire i dati degli ultimi itinerari
     * creati in ordine di tempo.
     * @param array $itinerari il risultato del prelievo
     */
    public function richiediUltimiItinerari(?array& $itinerari) : void {
        $this->modello->getUltimiItinerari($itinerari);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati delle valute memorizzate.
     * @param array valute il risultato del prelievo
     */
    public function richiediValute(?array& $valute) : void {
        $this->modello->getValute($valute);
    }


    /*
     * Metodi di servizio per la ricerca di itinerari
     */
    /**
     * Esegue l'elaborazione necessaria per stabilire se è stato rilasciato
     * un certo Feedback partecipante-organizzatore.
     * @param int $idItinerario l'id dell'itinerario
     * @param int $idPartecipante l'id del partecipante
     * @return bool true, se il feedback è stato rilasciato, false altrimenti
     */
    public function isFeedbackPORilasciato(int $idItinerario, int $idPartecipante) : bool {
        return $this->modello->isFeedbackPORilasciato($idItinerario, $idPartecipante);
    }


    /**
     * Esegue l'elaborazione necessaria per stabilire se è stato rilasciato
     * un certo Feedback organizzatore-partecipante.
     * @param int $idItinerario l'id dell'itinerario
     * @return bool true, se il feedback è stato rilasciato, false altrimenti
     */
    public function isFeedbackOPRilasciato(int $idItinerario) : bool {
        return $this->modello->isFeedbackOPRilasciato($idItinerario);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati degli itinerari associati
     * ad un fruitore.
     * @param int $idFruitore id del fruitore
     * @param bool $consideraSoloPartecipazioniAccordate se true, preleva solo gli Itinerari in
     * cui il fruitore è sicuramente un partecipante
     * @param bool $ordinaPerStatoPartecipazione se true, durante il prelievo, li ordine per stato
     * di partecipazione
     * @param array $itinerari il risultato del prelievo
     */
    public function richiediItinerariFruitore(int $idFruitore, bool $consideraSoloPartecipazioniAccordate, bool $ordinaPerStatoPartecipazione, ?array& $itinerari) : void {
        $this->modello->getItinerariFruitore($idFruitore, $consideraSoloPartecipazioniAccordate, $ordinaPerStatoPartecipazione, $itinerari);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati delle richieste di partecipazione
     * ad un itinerario.
     * @param int $idItinerario id dell'itinerario
     * @param bool $consideraSoloPartecipazioniAccordate se true, preleva solo le partecipazioni accordate.
     * @param array $partecipazioni il risultato del prelievo
     */
    public function richiediRichiestePartecipazione(int $idItinerario, bool $consideraSoloPartecipazioniAccordate, ?array& $partecipazioni) : void {
        $this->modello->getRichiestePartecipazione($idItinerario, $consideraSoloPartecipazioniAccordate, $partecipazioni);
    }


    /*
     * Metodi inerenti gli itinerari
     */
    /**
     * Esegue l'elaborazione necessaria per la creazione di un itinerario.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini un percorso alla cartella che contiene le immagini degli itinerari
     * @param string $immagineDefault il percorso all'immagine di default per gli itinerari
     */
    public function richiediCreazioneItinerario(array $params, string $percorsoImmagini, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_CREAZIONE_ITINERARIO);

        if ($validazioneRiuscita) {
            $validazioneRiuscita = $this->validaImmagineUpload($params, $percorsoImmagini);

            if ($validazioneRiuscita) {
                $idCicerone = $this->sessione->getIDUtente();

                $nome = $params[self::CAMPO_NOME];
                $dataOra = sprintf("%s %s", $params[self::CAMPO_DATA], $params[self::CAMPO_ORA]);
                $descrizione = $params[self::CAMPO_DESCRIZIONE];
                $lingua = $params[self::CAMPO_LINGUA];
                $luogo = $params[self::CAMPO_LUOGO];
                $popolarità = $params[self::CAMPO_POPOLARITÀ];
                $valuta = $params[self::CAMPO_VALUTA];
                $compenso = $params[self::CAMPO_COMPENSO];
                $nuovaImmagineItinerario = $params[self::CAMPO_IMMAGINE_UPLOAD];

                if ($nuovaImmagineItinerario === NULL) {
                    $nuovaImmagineItinerario = $immagineDefault;
                }

                $codiceStato = $this->modello->creaItinerario($idCicerone, $nome, $dataOra,
                    $descrizione, $nuovaImmagineItinerario, $lingua, $luogo,
                    $popolarità, $valuta, $compenso
                );

                switch ($codiceStato) {
                    case ModelloItinerario::STATO_CREAZIONE_ITINERARIO_RIUSCITA:
                        $messaggio = "L'itinerario è stato creato con successo!";
                        $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                    break;
                    case ModelloItinerario::STATO_VALUTA_NON_VALIDA:
                        $messaggio = "La valuta non è valida";
                        $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                    break;
                    default:
                        $messaggio = NULL;
                        $codiceStato = self::STATO_NO_SEGNALAZIONE;
                    break;
                }

                $this->setInfoOperazione($codiceStato, $messaggio);
            } else {
                $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
            }
        }
    }


    /**
     * Esegue l'elaborazione necessaria per la modifica di un itinerario.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini un percorso alla cartella che contiene le immagini degli itinerari
     * @param string $immagineDefault il percorso all'immagine di default per gli itinerari
     */
    public function richiediModificaItinerario(array $params, string $percorsoImmagini, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_MODIFICA_ITINERARIO);

        if ($validazioneRiuscita) {
            $validazioneRiuscita = $this->validaImmagineUpload($params, $percorsoImmagini);

            if ($validazioneRiuscita) {
                $idCicerone = $this->sessione->getIDUtente();
                $idItinerario = $params[self::CAMPO_ID];
                $nome = $params[self::CAMPO_NOME];
                $dataOra = sprintf("%s %s", $params[self::CAMPO_DATA], $params[self::CAMPO_ORA]);
                $descrizione = $params[self::CAMPO_DESCRIZIONE];
                $lingua = $params[self::CAMPO_LINGUA];
                $luogo = $params[self::CAMPO_LUOGO];
                $popolarità = $params[self::CAMPO_POPOLARITÀ];
                $valuta = $params[self::CAMPO_VALUTA];
                $compenso = $params[self::CAMPO_COMPENSO];
                $stato = $params[self::CAMPO_STATO];
                $nuovaImmagineItinerario = $params[self::CAMPO_IMMAGINE_UPLOAD];
                $ripristinaImmagineItinerario = $params[self::CAMPO_RIPRISTINA_IMMAGINE];

                if ($nuovaImmagineItinerario === NULL && $ripristinaImmagineItinerario) {
                    $nuovaImmagineItinerario = $immagineDefault;
                }

                $vecchiaImmagine = NULL;
                $codiceStato = $this->modello->modificaItinerario($idCicerone,
                    $idItinerario, $nome, $dataOra, $descrizione, $nuovaImmagineItinerario,
                    $vecchiaImmagine, $lingua, $luogo, $popolarità, $valuta, $compenso,
                    $stato
                );

                switch ($codiceStato) {
                    case ModelloItinerario::STATO_MODIFICA_ITINERARIO_RIUSCITA:
                        $messaggio = "L'itinerario è stato modificato con successo!";
                        $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                        
                        if ($nuovaImmagineItinerario !== NULL &&
                            $vecchiaImmagine !== $immagineDefault) {
                            $this->cancellaImmagine($percorsoImmagini, $vecchiaImmagine);
                        }
                    break;
                    case ModelloItinerario::STATO_VALUTA_NON_VALIDA:
                        $messaggio = "La valuta non è valida";
                        $codiceStato = self::STATO_OPERAZIONE_FALLITA;
                        break;
                    default:
                        $messaggio = NULL;
                        $codiceStato = self::STATO_NO_SEGNALAZIONE;
                    break;
                }

                $this->setInfoOperazione($codiceStato, $messaggio);
            } else {
                $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
            }
        }
    }


    /**
     * Esegue l'elaborazione necessaria per la rimozione di un itinerario.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini un percorso alla cartella che contiene le immagini degli itinerari
     * @param string $immagineDefault il percorso all'immagine di default per gli itinerari
     */
    public function richiediRimozioneItinerario(array $params, string $percorsoImmagini, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_RIMOZIONE_ITINERARIO);

        if ($validazioneRiuscita) {
            $idCicerone = $this->getIDUtente();
            $idItinerario = $params[self::CAMPO_ID];

            $immagineItinerario = NULL;
            $codiceStato = $this->modello->rimuoviItinerario($idCicerone,
                $idItinerario, $immagineItinerario);

            switch ($codiceStato) {
                case ModelloItinerario::STATO_RIMOZIONE_ITINERARIO_RIUSCITA:
                    $messaggio = sprintf("L'itinerario è stato rimosso con successo!");
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;

                    if ($immagineItinerario !== $immagineDefault) {
                        $this->cancellaImmagine($percorsoImmagini, $immagineItinerario);
                    }
                break;
                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per la ricerca degli itinerari.
     * @param array $params i parametri della richiesta
     * @param array $itinerari il risultato della ricerca
     */
    public function richiediRicercaItinerari(array $params, ?array& $itinerari) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_RICERCA_ITINERARIO);
        if ($validazioneRiuscita) {
            $luogoContiene = $params[self::CAMPO_LUOGO_CONTIENE];
            $itinerarioContiene = $params[self::CAMPO_ITINERARIO_CONTIENE];
            $filtroDataOra = $params[self::CAMPO_FILTRO_DATA_ORA];

            if ($filtroDataOra !== NULL) {
                $filtroDataOra = array(
                    $filtroDataOra,
                    sprintf("%s %s", $params[self::CAMPO_DATA], $params[self::CAMPO_ORA])
                );
            }

            $filtroPopolarità = $params[self::CAMPO_POPOLARITÀ];

            if ($this->isUtenteFruitore()) {
                $id = $this->getIDUtente();
            } else {
                $id = NULL;
            }

            $includiItinerariNonAperti = $params[self::CAMPO_INCLUSIONE_ITINERARI_NON_APERTI];
            $includiItinerariPartecipante = $params[self::CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE];
            $includiItinerariOrganizzatore = $params[self::CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE];

            $codiceStato = $this->modello->ricercaItinerari($itinerarioContiene, $luogoContiene,
                $filtroPopolarità, $filtroDataOra, $id, $includiItinerariNonAperti, $includiItinerariPartecipante,
                $includiItinerariOrganizzatore, $itinerari);

            switch ($codiceStato) {
                case ModelloItinerario::STATO_RICERCA_ITINERARI_RIUSCITA:
                    $messaggio = "Lista di itinerari recuperata";
                    $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                break;
                default:
                    $messaggio = NULL;
                    $codiceStato = self::STATO_NO_SEGNALAZIONE;
                break;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /*
     * Metodi inerenti le richieste di partecipazione
     */
    /**
     * Esegue l'elaborazione necessaria per effettuare l'accordo di una
     * richiesta di partecipazione.
     * @param array $params i parametri della richiesta
     */
    public function richiediAccordoRichiestaPartecipazione(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ACCORDO_RICHIESTE);
        if ($validazioneRiuscita) {
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            $idPartecipante = $params[self::CAMPO_ID_PARTECIPANTE];
            
            $codiceStato = $this->modello->accordaRichiestaPartecipazione($this->getIDUtente(),
                $idItinerario, $idPartecipante);

            if ($codiceStato === ModelloItinerario::STATO_PARTECIPAZIONE_ACCORDATA) {
                $messaggio = "Richiesta di partecipazione accordata con successo";
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }
            
            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per effettuare l'annullamento di una
     * richiesta di partecipazione.
     * @param array $params i parametri della richiesta
     */
    public function richiediAnnullamentoRichiestaPartecipazione(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ACCORDO_RICHIESTE);
        if ($validazioneRiuscita) {
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            $idPartecipante = $params[self::CAMPO_ID_PARTECIPANTE];
            
            $codiceStato = $this->modello->annullaRichiestaPartecipazione($this->getIDUtente(),
                $idItinerario, $idPartecipante);

            if ($codiceStato === ModelloItinerario::STATO_PARTECIPAZIONE_ANNULLATA) {
                $messaggio = "Richiesta di partecipazione annullata con successo";
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per effettuare il declino di una
     * richiesta di partecipazione.
     * @param array $params i parametri della richiesta
     */
    public function richiediDeclinoRichiestaPartecipazione(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_ACCORDO_RICHIESTE);
        if ($validazioneRiuscita) {
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            $idPartecipante = $params[self::CAMPO_ID_PARTECIPANTE];
            
            $codiceStato = $this->modello->declinaRichiestaPartecipazione($this->getIDUtente(),
                $idItinerario, $idPartecipante);

            if ($codiceStato === ModelloItinerario::STATO_PARTECIPAZIONE_DECLINATA) {
                $messaggio = "Richiesta di partecipazione declinata con successo";
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }
            
            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per effettuare l'invio di una
     * richiesta di partecipazione.
     * @param array $params i parametri della richiesta
     */
    public function richiediInvioRichiestaPartecipazione(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_INVIO_RICHIESTE);
        if ($validazioneRiuscita) {
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            $idPartecipante = $params[self::CAMPO_ID_PARTECIPANTE];

            $codiceStato = $this->modello->inviaRichiestaPartecipazione($idItinerario, $idPartecipante);

            if ($codiceStato === ModelloItinerario::STATO_RICHIESTA_PARTECIPAZIONE_INVIATA) {
                $messaggio = "Richiesta di partecipazione inviata con successo";
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }
            
            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per effettuare l'invio di una
     * richiesta di annullamento.
     * @param array $params i parametri della richiesta
     */
    public function richiedInvioRichiestaAnnullamento(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_INVIO_RICHIESTE);
        if ($validazioneRiuscita) {
            $idItinerario = $params[self::CAMPO_ID_ITINERARIO];
            $idPartecipante = $params[self::CAMPO_ID_PARTECIPANTE];

            $codiceStato = $this->modello->inviaRichiestaAnnullamento($idItinerario, $idPartecipante);

            if ($codiceStato === ModelloItinerario::STATO_RICHIESTA_ANNULLAMENTO_INVIATA) {
                $messaggio = "Richiesta di annullamento inviata con successo";
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
        if ($tipo === self::VALIDAZIONE_CREAZIONE_ITINERARIO || $tipo === self::VALIDAZIONE_MODIFICA_ITINERARIO ||
            $tipo === self::VALIDAZIONE_RIMOZIONE_ITINERARIO) {

            $ciceronePresente = $this->isUtenteCicerone();

            if ($ciceronePresente) {
                $valido = $this->validaParametriGestioneItinerario($params, $tipo);
            } else {
                $valido = false;
            }
        } else if ($tipo === self::VALIDAZIONE_RICERCA_ITINERARIO) {
            $valido = $this->validaParametriRicercaItinerario($params);
 
        } else if ($tipo === self::VALIDAZIONE_INVIO_RICHIESTE) {
            $valido = $this->validaParametriInvioRichieste($params);
            
        } else if ($tipo === self::VALIDAZIONE_ACCORDO_RICHIESTE) {
            $valido = $this->validaParametriAccordoRichieste($params);
        }

        if (!$valido) {
            $this->sessione->setStatoOperazione(self::STATO_OPERAZIONE_FALLITA);
        }

        return $valido;
    }


    /*
     * Validazioni inerenti gli itinerari
     */
    /**
     * Valida i parametri per la creazione/modifica/rimozione di un itinerario.
     * @param array $params i parametri della richiesta
     * @param string $tipo il tipo di validazione
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriGestioneItinerario(array $params, string $tipo) : bool {
        $campiDisponibili = array();

        if ($tipo === self::VALIDAZIONE_CREAZIONE_ITINERARIO ||
            $tipo === self::VALIDAZIONE_MODIFICA_ITINERARIO) {
            array_push($campiDisponibili, self::CAMPO_NOME, self::CAMPO_DATA, self::CAMPO_ORA,
                self::CAMPO_DESCRIZIONE, self::CAMPO_LINGUA, self::CAMPO_LUOGO,
                self::CAMPO_POPOLARITÀ, self::CAMPO_VALUTA, self::CAMPO_COMPENSO
            );
        }

        if ($tipo === self::VALIDAZIONE_MODIFICA_ITINERARIO ||
            $tipo === self::VALIDAZIONE_RIMOZIONE_ITINERARIO) {
            $campiDisponibili[]= self::CAMPO_ID;
        }

        $valido = call_user_func_array(array($this, "isImpostato"),
            array_merge(array($params), $campiDisponibili));


        if ($valido && $tipo === self::VALIDAZIONE_MODIFICA_ITINERARIO) {
            if (isset($params[self::CAMPO_STATO])) {
                $stato = $params[self::CAMPO_STATO];
                
                $valido = ($stato === Itinerario::STATO_APERTO || $stato === Itinerario::STATO_ITINERE ||
                    $stato === Itinerario::STATO_CHIUSO || $stato === Itinerario::STATO_CONCLUSO);

                if (!$valido) {
                    $this->sessione->addMessaggio("Il campo stato è malformato!");
                }
            } else {
                $params[self::CAMPO_STATO] = NULL;
            }
        }

        
        if ($valido && $tipo === self::VALIDAZIONE_CREAZIONE_ITINERARIO ||
            $tipo === self::VALIDAZIONE_MODIFICA_ITINERARIO) {
            $nomiCampi = array(
                self::CAMPO_NOME => "nome",
                self::CAMPO_LINGUA => "lingua",
                self::CAMPO_LUOGO => "luogo",
                self::CAMPO_DESCRIZIONE => "descrizione"
            );

            foreach($params as $chiave => $valore) {
                $needMessage = true;
                
                switch($chiave) {
                    case self::CAMPO_NOME:
                    case self::CAMPO_LINGUA:
                    case self::CAMPO_LUOGO:
                    case self::CAMPO_DESCRIZIONE:
                        $nomeCampo = $nomiCampi[$chiave];
                        $valido = $this->validaCampoSemplice($valore, $nomeCampo, ($chiave === self::CAMPO_DESCRIZIONE ? 0 : 3));
                        $needMessage = false;
                    break;
                    case self::CAMPO_DATA:
                        $valido = $this->validaCampoData($valore);
                        $needMessage = false;
                    break;
                    case self::CAMPO_ID:                        
                        $valido = preg_match(ModelloItinerario::REGEX_ID,
                            $valore);
                        $nomeCampo = "Il campo ID";
                    break;
                    case self::CAMPO_COMPENSO:
                        $valido = preg_match(ModelloItinerario::REGEX_ID,
                            $valore);
                        $nomeCampo = "Il campo compenso";
                    break;
                    case self::CAMPO_POPOLARITÀ:
                        $valido = preg_match(ModelloItinerario::REGEX_POPOLARITÀ,
                            $valore);
                        $nomeCampo = "Il campo popolarità";
                    break;
                    case self::CAMPO_ORA:
                        $valido = preg_match(ModelloItinerario::REGEX_ORA,
                            $valore);
                        $nomeCampo = "Il campo ora";
                    break;
                    default:
                        $valido = true;
                        //Ignora altri
                    break;
                }

                if (!$valido) {
                    if ($needMessage) {
                        $this->sessione->addMessaggio($nomeCampo . " è malformato");
                    }
                    break;
                }
            }
        } else if ($valido && $tipo === self::VALIDAZIONE_RIMOZIONE_ITINERARIO) {
            $valido = preg_match(ModelloItinerario::REGEX_ID,
                $params[self::CAMPO_ID]);
        }


       return $valido;
    }


    /**
     * Valida i parametri per la ricerca di itinerari.
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriRicercaItinerario(array& $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_ITINERARIO_CONTIENE,
            self::CAMPO_LUOGO_CONTIENE, self::CAMPO_FILTRO_DATA_ORA, self::CAMPO_DATA,
            self::CAMPO_ORA
        );


        if ($valido) {
            foreach ($params as $chiave => $valore) {
                switch ($chiave) {
                    case self::CAMPO_FILTRO_DATA_ORA:
                        $valido = preg_match(ModelloItinerario::REGEX_FILTRO_DATA_ORA,
                            $valore);
                        $nomeCampo = "Il campo filtro data e ora";
                    break;
                    default:
                        //Ignora altri
                        $valido = true;
                    break;
                }
 
               if (!$valido) {
                    $this->sessione->addMessaggio($nomeCampo . " è malformato");
                    break;
                }
            }
        }

        /*
         * Verifica le impostazioni sulla data
         */
        if ($valido && ($params[self::CAMPO_FILTRO_DATA_ORA] !== "")) {
            $valido = $this->validaCampoData($params[self::CAMPO_DATA]);

            if ($valido) {
                $valido = preg_match(ModelloItinerario::REGEX_ORA, $params[self::CAMPO_ORA]);
                if (!$valido) {
                    $this->sessione->addMessaggio("Il campo ora è malformato");
                }
            }
        } else {
            //Imposta cosicchè si ignori il controllo della data
            $params[self::CAMPO_FILTRO_DATA_ORA] = NULL;
            $params[self::CAMPO_DATA] = NULL;
            $params[self::CAMPO_ORA] = NULL;
        }


        if ($valido) {
            $params[self::CAMPO_POPOLARITÀ] = NULL;

            $filtroPopolaritàItinerario = array(
                self::CAMPO_ITINERARIO_FILTRO_1, self::CAMPO_ITINERARIO_FILTRO_2,
                self::CAMPO_ITINERARIO_FILTRO_3, self::CAMPO_ITINERARIO_FILTRO_4,
                self::CAMPO_ITINERARIO_FILTRO_5
            );

            foreach ($filtroPopolaritàItinerario as $idx => $filtro) {
                if (isset($params[$filtro])) {
                    if ($params[self::CAMPO_POPOLARITÀ] === NULL) {
                        $params[self::CAMPO_POPOLARITÀ] = array();
                    }

                    $params[self::CAMPO_POPOLARITÀ][]= ($idx + 1);
                    unset($params[$filtro]);
                }
            }
        }

        $params[self::CAMPO_INCLUSIONE_ITINERARI_NON_APERTI] =
            isset($params[self::CAMPO_INCLUSIONE_ITINERARI_NON_APERTI]);


        if ($valido) {
            /*
             * Verifica validità filtri itinerari.
             */
            $filtroItinerariPartecipante = isset($params[self::CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE]);
            $filtroItinerariOrganizzatore = isset($params[self::CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE]);

            if ($filtroItinerariPartecipante || $filtroItinerariOrganizzatore) {
                if ($this->isUtenteFruitore()) {
                    if (!$this->isUtenteCicerone() && $filtroItinerariOrganizzatore) {
                        $valido = false;
                        $this->sessione->addMessaggio("Richiesta non valida");
                    }
                } else {
                    $valido = false;
                    $this->sessione->addMessaggio("Richiesta non valida");
                }
            }

            $params[self::CAMPO_INCLUSIONE_ITINERARI_PARTECIPANTE] = $filtroItinerariPartecipante;
            $params[self::CAMPO_INCLUSIONE_ITINERARI_ORGANIZZATORE] = $filtroItinerariOrganizzatore;
        }


        return $valido;
    }


    /*
     * Validazioni inerenti le richieste
     */
    /**
     * Valida i parametri per l'invio di richieste (partecipazione e annullamento).
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriInvioRichieste(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_ID_ITINERARIO,
            self::CAMPO_ID_PARTECIPANTE);
            
        if ($valido) {
            $valido = preg_match(ModelloItinerario::REGEX_ID, $params[self::CAMPO_ID_ITINERARIO]) &&
                preg_match(ModelloItinerario::REGEX_ID, $params[self::CAMPO_ID_PARTECIPANTE]);

            if ($valido && $this->isUtenteFruitore()) {
                $valido = ($this->getIDUtente() === intval($params[self::CAMPO_ID_PARTECIPANTE]));
            }
        }

        return $valido;
    }


    /**
     * Valida i parametri per l'accordo/declino/annullamento di richieste di partecipazione e annullamento.
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriAccordoRichieste(array $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_ID_ITINERARIO,
            self::CAMPO_ID_PARTECIPANTE);

        if ($valido) {
            $valido = preg_match(ModelloItinerario::REGEX_ID, $params[self::CAMPO_ID_ITINERARIO]) &&
                preg_match(ModelloItinerario::REGEX_ID, $params[self::CAMPO_ID_PARTECIPANTE]);
        }

        if ($valido) {
            $valido = $this->isUtenteCicerone();
        }

        return $valido;
    }
}
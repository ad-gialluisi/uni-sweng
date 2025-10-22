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

require_once "ControlloreUtente.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/ModelloAmministrazione.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/modello/entità/RichiesteAmministrazione.php";


use modello\ModelloAmministrazione;
use modello\entità\Utente;
use modello\entità\RichiesteAmministrazione;
use modello\entità\RichiestaAggiornamento;
use modello\entità\RichiestaDisiscrizione;


/**
 * Rappresenta il controllore associato alla VistaAmministrazione e al ModelloAmministrazione.
 * 
 * @see \vista\VistaAmministrazione
 * @see \modello\ModelloAmministrazione
 */
class ControlloreAmministrazione extends ControlloreUtente {
    /*
     * Questi sono i campi che questo controllore è in grado di validare e di
     * utilizzare per la successiva elaborazione.
     */
    public const CAMPO_NOME = "nome";
    public const CAMPO_COGNOME = "cognome";
    public const CAMPO_DATA_NASCITA = "data_nascita";
    public const CAMPO_LUOGO_NASCITA = "luogo_nascita";
    public const CAMPO_CODICE_FISCALE = "codice_fiscale";

    
    /*
     * Tipi di validazione
     */
    private const VALIDAZIONE_INVIO_RICHIESTA_DISISCRIZIONE = "invio_disiscrizione";
    private const VALIDAZIONE_INVIO_RICHIESTA_AGGIORNAMENTO = "invio_aggiornamento";
    private const VALIDAZIONE_DISISCRIZIONE_FRUITORE = "disiscrizione_Fruitore";
    private const VALIDAZIONE_TRASFORMAZIONE_QUASICICERONE = "trasformazione_quasicicerone";


    /**
     * Crea un nuovo ControlloreAmministrazione con un ModelloAmministrazione sottostante.
     */
    public function __construct() {
        parent::__construct(new ModelloAmministrazione());
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati di una richiesta d'aggiornamento.
     * @param int $id L'id dell'utente da reperire
     * @param RichiestaAggiornamento $richiesta un'istanza che conterrà alla fine dell'operazione,
     * i dati della richiesta cercato, se esiste.
     */
    public function richiediRichiestaAggiornamento(int $id, ?RichiestaAggiornamento& $richiesta) : void {
        $this->modello->getRichiestaAggiornamento($id, $richiesta);
    }


    /**
     * Esegue l'elaborazione necessaria per reperire i dati di una richiesta di disiscrizione.
     * @param int $id L'id dell'utente da reperire
     * @param RichiestaDisiscrizione $richiesta un'istanza che conterrà alla fine dell'operazione,
     * i dati della richiesta cercato, se esiste.
     */
    public function richiediRichiestaDisiscrizione(int $id, ?RichiestaDisiscrizione& $richiesta) : void {
        $this->modello->getRichiestaDisiscrizione($id, $richiesta);
    }


    /**
     * Esegue l'elaborazione necessaria al reperimento delle richieste d'amministrazione
     * memorizzate.
     * @param RichiesteAmministrazione $richieste
     */
    public function richiediRichiesteAmministrazione(?RichiesteAmministrazione& $richieste) : void {
        $this->modello->getRichiesteAmministrazione($richieste);
    }


    /**
     * Esegue l'elaborazione necessaria per la disiscrizione di un certo fruitore.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini un percorso alla cartella che contiene le
     * immagini di profilo degli utenti
     * @param string $immagineDefault il percorso all'immagine di default per gli utenti
     */
    public function richiediDisiscrizioneFruitore(array $params, string $percorsoImmagini, string $immagineDefault) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_DISISCRIZIONE_FRUITORE);

        if ($validazioneRiuscita) {
            $id = $params[self::CAMPO_ID];

            $immagineFruitore = NULL;
            $codiceStato = $this->modello->disiscriviFruitore($id, $immagineFruitore);

            if ($codiceStato === ModelloAmministrazione::STATO_DISISCRIZIONE_RIUSCITA) {
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                $messaggio = "Disiscrizione del fruitore avvenuta con successo!";

                if ($immagineFruitore !== $immagineDefault) {
                    $this->cancellaImmagine($percorsoImmagini, $immagineFruitore);
                }
            } else {
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
                $messaggio = NULL;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per effettuare la transizione a Cicerone
     * essendo l'utente un QuasiCicerone.
     */
    public function richiediTransizioneACicerone() : void {
        if ($this->isUtenteQuasiCicerone()) {
            $codiceStato = $this->modello->effettuaTransizioneACicerone($this->getIDUtente());

            if ($codiceStato === ModelloAmministrazione::STATO_TRANSIZIONE_CICERONE_RIUSCITA) {
                $this->sessione->setTipoUtente(Utente::TIPO_CICERONE);
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                $messaggio = "Transizione a Cicerone riuscita!";
            } else {
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
                $messaggio = NULL;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per inviare una richiesta d'aggiornamento.
     * @param array $params i parametri della richiesta
     */
    public function richiediInvioRichiestaAggiornamento(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_INVIO_RICHIESTA_AGGIORNAMENTO);

        if ($validazioneRiuscita) {
            $id = $this->getIDUtente();
            $nome = $params[self::CAMPO_NOME];
            $cognome = $params[self::CAMPO_COGNOME];
            $dataNascita = $params[self::CAMPO_DATA_NASCITA];
            $luogoNascita = $params[self::CAMPO_LUOGO_NASCITA];
            $residenza = $params[self::CAMPO_RESIDENZA];
            $telefono = $params[self::CAMPO_TELEFONO];
            $codiceFiscale = $params[self::CAMPO_CODICE_FISCALE];
            
            $codiceStato = $this->modello->inviaRichiestaAggiornamento($id, $nome, $cognome, $dataNascita,
                $luogoNascita, $residenza, $telefono, $codiceFiscale);

            if ($codiceStato === ModelloAmministrazione::STATO_RICHIESTA_AGGIORNAMENTO_INVIATA) {
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                $messaggio = "Richiesta d'aggiornamento inviata con successo!";
            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per inviare una richiesta di disiscrizione.
     * @param array $params i parametri della richiesta
     */
    public function richiediInvioRichiestaDisiscrizione(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_INVIO_RICHIESTA_DISISCRIZIONE);

        if ($validazioneRiuscita) {
            $id = $this->getIDUtente();
            $descrizione = $params[self::CAMPO_DESCRIZIONE];
            $codiceStato = $this->modello->inviaRichiestaDisiscrizione($id, $descrizione);
            
            if ($codiceStato === ModelloAmministrazione::STATO_RICHIESTA_DISISCRIZIONE_INVIATA) {
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                $messaggio = "Richiesta di disiscrizione inviata con successo!";

            } else {
                $messaggio = NULL;
                $codiceStato = self::STATO_NO_SEGNALAZIONE;
            }

            $this->setInfoOperazione($codiceStato, $messaggio);
        }
    }


    /**
     * Esegue l'elaborazione necessaria per tramutare un Globetrotter in QuasiCicerone.
     * @param array $params i parametri della richiesta
     */
    public function richiediTrasformazioneInQuasiCicerone(array $params) : void {
        $validazioneRiuscita = $this->validaParametri($params, self::VALIDAZIONE_TRASFORMAZIONE_QUASICICERONE);
        
        if ($validazioneRiuscita) {
            $id = $params[self::CAMPO_ID];
            $codiceStato = $this->modello->trasformaInQuasiCicerone($id);

            if ($codiceStato === ModelloAmministrazione::STATO_TRASFORMAZIONE_QUASICICERONE_RIUSCITA) {
                $codiceStato = self::STATO_OPERAZIONE_RIUSCITA;
                $messaggio = "Trasformazione del Globetrotter in QuasiCicerone riuscita!";

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
        if ($tipo === self::VALIDAZIONE_INVIO_RICHIESTA_DISISCRIZIONE) {
            $isFruitore = $this->isUtenteFruitore();

            if ($isFruitore) {
                $valido = $this->validaParametriInvioRichiestaDisiscrizione($params);
            } else {
                $valido = false;
            }
            
        } else if ($tipo === self::VALIDAZIONE_INVIO_RICHIESTA_AGGIORNAMENTO) {
            $isGlobetrotter = $this->isUtenteGlobetrotter();

            if ($isGlobetrotter) {
                $valido = $this->validaParametriInvioRichiestaAggiornamento($params);
            } else {
                $valido = false;
            }

        } else if ($tipo === self::VALIDAZIONE_DISISCRIZIONE_FRUITORE ||
            $tipo === self::VALIDAZIONE_TRASFORMAZIONE_QUASICICERONE) {

            $isAmministratore = $this->isUtenteAmministratore();

            if ($isAmministratore) {
                $valido = $this->isImpostato($params, self::CAMPO_ID) &&
                    preg_match(ModelloAmministrazione::REGEX_ID, $params[self::CAMPO_ID]);
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
     * Valida i parametri per l'invio della richiesta di disiscrizione.
     * @param array $params i parametri della richiesta
     * @param string $percorsoImmagini il percorso alla cartella contenente le immagini di
     * profilo degli utenti.
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriInvioRichiestaDisiscrizione(array& $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_DESCRIZIONE);
        
        if ($valido) {
            $descrizione = $params[self::CAMPO_DESCRIZIONE];

            $valido = $this->validaCampoSemplice($descrizione, "descrizione", 10);
        }

        return $valido;
    }


    /**
     * Valida i parametri per l'invio della richiesta di aggiornamento.
     * @param array $params i parametri della richiesta
     * @return bool true, se i parametri sono validi, false se non lo sono.
     */
    private function validaParametriInvioRichiestaAggiornamento(array& $params) : bool {
        $valido = $this->isImpostato($params, self::CAMPO_NOME, self::CAMPO_COGNOME,
            self::CAMPO_DATA_NASCITA, self::CAMPO_LUOGO_NASCITA,
            self::CAMPO_RESIDENZA, self::CAMPO_TELEFONO,
            self::CAMPO_CODICE_FISCALE);

        if ($valido) {
            $campiTesto = array(
                self::CAMPO_NOME => "nome",
                self::CAMPO_COGNOME => "cognome",
                self::CAMPO_LUOGO_NASCITA => "luogo di nascita",
                self::CAMPO_RESIDENZA => "residenza",
            );
            
            foreach ($campiTesto as $chiave => $nomeCampo) {
                $campo = $params[$chiave];
                $valido = $this->validaCampoSemplice($campo, $nomeCampo, 3);

                if (!$valido) {
                    break;
                }
            }
            
            if ($valido) {
                $validazioni = array(
                    "validaCampoTelefono" => array($params[self::CAMPO_TELEFONO]),
                    "validaCampoCodiceFiscale" => array($params[self::CAMPO_CODICE_FISCALE]),
                    "validaCampoData" => array($params[self::CAMPO_DATA_NASCITA])
                );
            
                foreach ($validazioni as $metodo => $operandi) {
                    $valido = call_user_func_array(array($this, $metodo), $operandi);

                    if (!$valido) {
                        break;
                    }
                }
            }
        }


        return $valido;
    }


    /**
     * Effettua la validazione del campo codice fiscale.
     * @param string $codiceFiscale
     * @return bool true, se risulta valido, false, al contrario
     */
    private function validaCampoCodiceFiscale(string $codiceFiscale) : bool {
        /*
         * Basato sulla "versione generica" della regex proposta in quest'articolo:
         * http://blog.marketto.it/2016/01/regex-validazione-codice-fiscale-con-omocodia/
         *
         */
        $valido = preg_match(ModelloAmministrazione::REGEX_CODICE_FISCALE, $codiceFiscale);

        if (!$valido) {
            $this->sessione->addMessaggio("Il codice fiscale inserito non è valido");
        }

        return $valido;
    }
}
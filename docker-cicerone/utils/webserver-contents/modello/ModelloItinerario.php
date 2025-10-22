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


require_once "entità/Itinerario.php";
require_once "entità/Valuta.php";
require_once "entità/Utente.php";
require_once "entità/Feedback.php";


use modello\entità\Itinerario;
use modello\entità\Utente;
use modello\entità\Valuta;
use modello\entità\Partecipazione;
use modello\entità\Feedback;


/**
 * Questo è il modello che tratta le informazioni che riguardano gli itinerari.
 * <p>In particolare, esegue i casi d'uso inerenti:</p>
 * <ul><li>La creazione/modifica/rimozione dell'itinerario.</li>
 * <li>La ricerca di itinerari</li>
 * <li>Accordo/declino/annullamento di richieste di partecipazione</li>
 * <li>Invio di richieste di partecipazione/annullamento</li>
 * </ul>
 */
class ModelloItinerario extends ModelloImpostazioniItinerario {
    /*
     * Queste costanti servono ad indicare gli stati che un operazione
     * raggiunge in seguito al successo/fallimento/evoluzione della stessa.
     * Fornirò commenti solo agli stati che possono risultare "ambigui" ad una
     * prima lettura.
     */
    /*
     * Costanti inerenti gli itinerari
     */
    /**
     * Stato raggiunto quando viene creato un nuovo itinerario
     */
    public const STATO_CREAZIONE_ITINERARIO_RIUSCITA = 5;
    
    /**
     * Stato raggiunto quando viene modificato un itinerario
     */
    public const STATO_MODIFICA_ITINERARIO_RIUSCITA = 6;

    /**
     * Stato raggiunto quando viene rimosso un itinerario
     */
    public const STATO_RIMOZIONE_ITINERARIO_RIUSCITA = 7;

    /**
     * Stato raggiunto quando la ricerca di itinerari ha successo
     */
    public const STATO_RICERCA_ITINERARI_RIUSCITA = 8;

    /**
     * Stato raggiunto quando la valuta utilizzata per definire
     * il compenso non risulta valida
     */
    public const STATO_VALUTA_NON_VALIDA = 9;

    /**
     * Stato raggiunto quando il fruitore richiesto viene trovato
     */
    public const STATO_FRUITORE_TROVATO = 10;

    /**
     * Stato raggiunto quando il fruitore richiesto NON viene trovato
     */
    public const STATO_FRUITORE_NON_TROVATO = 11;

    
    /**
     * Regex per l'orario
     */
    public const REGEX_ORA                = "#(?:[0-1][0-9]|2[0-3])\:(?:[0-5][0-9])#";
    
    /**
     * Regex per i valori di popolarità di un itinerario
     */
    public const REGEX_POPOLARITÀ         = "#[1-5]#";
    
    /**
     * Regex per le operazioni di comparazione di data e ora
     */
    public const REGEX_FILTRO_DATA_ORA    = "#(?:<|>|!=|=|<=|>=|)#";
    
    
    
    /*
     * Costanti inerenti le partecipazioni
     */
    /**
     * Stato raggiunto quando viene accordata una richiesta di partecipazione.
     */
    public const STATO_PARTECIPAZIONE_ACCORDATA = 12;
    
    /**
     * Stato raggiunto quando viene amnnullata una richiesta di partecipazione.
     */
    public const STATO_PARTECIPAZIONE_ANNULLATA = 13;
    
    /**
     * Stato raggiunto quando viene declinata una richiesta di partecipazione.
     */
    public const STATO_PARTECIPAZIONE_DECLINATA = 14;
    
    /**
     * Stato raggiunto quando viene inviata una richiesta di partecipazione.
     */
    public const STATO_RICHIESTA_PARTECIPAZIONE_INVIATA = 15;
    
    /**
     * Stato raggiunto quando viene inviata una richiesta di annullamento.
     */
    public const STATO_RICHIESTA_ANNULLAMENTO_INVIATA = 16;
    


    /*
     * Metodi di servizio per gli itinerari
     */
    /**
     * Restiuisce le valute presenti nel DB
     * @param array $valute il risultato dell'operazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getValute(?array& $valute) : int {
        $this->ciceroneDatabase->apri();
        
        $righe = $this->ciceroneDatabase->query("select * from Valuta");
        
        $valute = array();
        foreach ($righe as $riga) {
            $valute[]= Valuta::daArray($riga);
        }
        
        $this->ciceroneDatabase->chiudi();
        
        return self::STATO_NO_SEGNALAZIONE;
    }


    /**
     * Restiuisce gli ultimi venti itinerari creati in ordine decrescente di tempo
     * @param array $itinerari il risultato dell'operazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getUltimiItinerari(?array& $itinerari) : int {
        $this->ciceroneDatabase->apri();
        
        $colonneUtente = array("id", "nome_utente");
        $colonneValuta = array("valuta", "centesimale", "simbolo");
        $colonneItineario = array("id", "nome", "id_cicerone", "data", "descrizione",
            "immagine", "lingua", "luogo", "popolarità", "valuta", "compenso", "stato"
        );
        
        $query = self::creaQueryConColonneRiconoscibili(
            "select %s from Itinerario, Utente, Valuta where " .
            "id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta " .
            "order by Itinerario.id DESC limit 20",
            array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneItineario, $colonneValuta, $colonneUtente)
            );
        
        $righe = $this->ciceroneDatabase->query($query);
        
        $itinerari = array();
        foreach ($righe as $riga) {
            $itinerari[]= Itinerario::daArray($riga);
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_NO_SEGNALAZIONE;
    }


    /*
     * Metodi inerenti gli itinerari
     */
    /**
     * Esegue il caso d'uso inerente la creazione di un nuovo itinerario
     * @param int $idCicerone id del Cicerone organizzatore
     * @param string $data
     * @param string $descrizione
     * @param string $luogo
     * @param int $popolarità
     * @param string $valuta
     * @param int $compenso
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE, STATO_VALUTA_NON_VALIDA,
     * STATO_CREAZIONE_ITINERARIO_RIUSCITA
     */
    public function creaItinerario(int $idCicerone, string $nome, string $dataOra, string $descrizione, ?string $immagine, string $lingua, string $luogo,
        int $popolarità, string $valuta, int $compenso) : int {
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Valuta where valuta = ?", 
            $valuta);
        $nRighe = count($righe);

        if ($nRighe === 0) {
            $codiceStato = self::STATO_VALUTA_NON_VALIDA;

        } else {
            $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ? and " .
                "tipo = ?", $idCicerone, Utente::TIPO_CICERONE);
            $nRighe = count($righe);

            if ($nRighe === 1) {
                $campi = array("id_cicerone", "nome", "data", "descrizione", "lingua", "luogo",
                    "popolarità", "valuta", "compenso", "stato"
                );
                $valori = array($idCicerone, $nome, $dataOra, $descrizione, $lingua,
                    $luogo, $popolarità, $valuta, $compenso, Itinerario::STATO_APERTO
                );

                if ($immagine !== NULL) {
                    $campi[]= "immagine";
                    $valori[]= $immagine;
                }

                $query = sprintf("insert into Itinerario (%s) values (%s)",
                    implode(", ", $campi), implode(", ", array_fill(0, count($campi), "?"))
                );

                call_user_func_array(array($this->ciceroneDatabase, "manipola"),
                    array_merge(array($query), $valori));
                $codiceStato = self::STATO_CREAZIONE_ITINERARIO_RIUSCITA;
            }
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la modifica di un itinerario
     * @param int $idCicerone id del Cicerone organizzatore
     * @param int $id id dell'itinerario
     * @param string $data
     * @param string $descrizione
     * @param string $luogo
     * @param int $popolarità
     * @param string $valuta
     * @param int $compenso
     * @param string $stato
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE, STATO_VALUTA_NON_VALIDA
     * STATO_MODIFICA_ITINERARIO_RIUSCITA
     */
    public function modificaItinerario(int $idCicerone, int $id, string $nome, string $dataOra, string $descrizione, ?string $immagine, ?string& $vecchiaImmagine, string $lingua,
        string $luogo, int $popolarità, string $valuta, int $compenso, ?string $stato) : int {
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Valuta where valuta = ?",
            $valuta);
        $nRighe = count($righe);

        if ($nRighe === 0) {
            $codiceStato = self::STATO_VALUTA_NON_VALIDA;

        } else {
            $righe = $this->ciceroneDatabase->query("select immagine from Itinerario where id = ? and " .
                "id_cicerone = ?", $id, $idCicerone);
            $nRighe = count($righe);

            if ($nRighe === 1) {
                //Salva e porta all'esterno
                $vecchiaImmagine = $righe[0]["immagine"];

                $campi = array("id_cicerone", "nome", "data", "descrizione", "lingua", "luogo",
                    "popolarità", "valuta", "compenso"
                );
                $valori = array($idCicerone, $nome, $dataOra, $descrizione, $lingua,
                    $luogo, $popolarità, $valuta, $compenso
                );

                if ($immagine !== NULL) {
                    $campi[]= "immagine";
                    $valori[]= $immagine;
                }

                if ($stato !== NULL) {
                    $campi[]= "stato";
                    $valori[]= $stato;
                }

                $queryPart = array();
                foreach ($campi as $campo) {
                    $queryPart[] = sprintf("%s = ?", $campo);
                }

                $query = sprintf("update Itinerario set %s where id = ?",
                    implode(", ", $queryPart));

                call_user_func_array(array($this->ciceroneDatabase, "manipola"),
                    array_merge(array($query), $valori, array($id)));
                $codiceStato = self::STATO_MODIFICA_ITINERARIO_RIUSCITA;
            }
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la rimozione di un itinerario
     * @param int $idCicerone id del Cicerone organizzatore
     * @param int $id id dell'itinerario
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RIMOZIONE_ITINERARIO_RIUSCITA
     */
    public function rimuoviItinerario(int $idCicerone, int $id, ?string& $immagine) : int {
        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select immagine from Itinerario where id = ? and " .
            "id_cicerone = ?", $id, $idCicerone);
        $nRighe = count($righe);

        if ($nRighe === 1) {
            $immagine = $righe[0]["immagine"];
            $this->ciceroneDatabase->manipola("delete from Itinerario where id = ?", $id);
            $codiceStato = self::STATO_RIMOZIONE_ITINERARIO_RIUSCITA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente la ricerca degli itinerari
     * @param string $itinerarioContiene cosa deve contenere nome o descrizione dell'itinerario
     * @param string $luogoContiene cosa deve contenere il luogo dell'itinerario
     * @param array $popolarità le impostazioni di popolarità
     * @param array $dataOra le impostazioni di data e ora
     * @param int $idFruitore l'id del fruitore, se collegato
     * @param bool $includiItinerariNonAperti stabilisce se includere itinerari il cui stato NON È Itinerario::STATO_APERTO
     * @param bool $includiItinerariPartecipante stabilisce se includere itinerari a cui il fruitore già partecipa
     * @param bool $includiItinerariOrganizzatore stabilisce se includere itinerari organizzati dal fruitore (Cicerone organizzatore)
     * @param array $itinerari risultato dell'operazione
     * @return int lo stato dell'operazione STATO_RICERCA_ITINERARI_RIUSCITA
     */
    public function ricercaItinerari(string $itinerarioContiene, string $luogoContiene, ?array $popolarità, ?array $dataOra, ?int $idFruitore, bool $includiItinerariNonAperti,
        bool $includiItinerariPartecipante, bool $includiItinerariOrganizzatore, ?array& $itinerari) : int {

        $colonneUtente = array("id", "nome_utente");
        $colonneValuta = array("valuta", "centesimale", "simbolo");
        $colonneItineario = array("id", "nome", "descrizione",
            "luogo", "compenso", "stato", "immagine"
        );

        $parametri = array();
        if ($idFruitore !== NULL) {
            $query = "select %s, " .
                "case when Itinerario.id_cicerone = ? then 1 else NULL end as \"isCicerone\", " .
                "Partecipazione.stato as \"statoPartecipazione\" " .
                "from Itinerario left outer join Partecipazione on Partecipazione.id_itinerario = Itinerario.id " .
                "and Partecipazione.id_partecipante = ? " . 
                "inner join Valuta on Valuta.valuta = Itinerario.valuta " .
                "inner join Utente on Itinerario.id_cicerone = Utente.id " . 
                "where";

            if ($includiItinerariPartecipante && $includiItinerariOrganizzatore) {
                //Non devo togliere niente
                $query .= " (1 = 1)";

            } else if ($includiItinerariPartecipante) {
                /*
                 * Per includere gli itinerari a cui partecipo (più gli altri)
                 * devo ESCLUDERE quelli organizzati da me.
                 */
                $query .= " (Itinerario.id_cicerone != ?)";
                $parametri[]= $idFruitore;

            } else if ($includiItinerariOrganizzatore) {
                /*
                 * Per includere gli itinerari organizzati da me (più gli altri)
                 * devo ESCLUDERE quelli a cui partecipo
                 */
                $query .= " (Partecipazione.stato is null)";

            } else {
                /*
                 * Per tenere solo gli itinerari che nulla hanno a che fare
                 * con me, devo escludere sia quelli organizzati che quelli
                 * a cui partecipo
                 */
                $query .= " (Itinerario.id_cicerone != ? and Partecipazione.stato is null)";
                //$needsGroupBy = false;
                $parametri[]= $idFruitore;
            }

            $parametri[]= $idFruitore;
            $parametri[]= $idFruitore;
        } else {
            $query = "select %s from Itinerario, Utente, Valuta where " .
                "id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta";
        }

        if (!$includiItinerariNonAperti) {
            $query .= " and (Itinerario.stato = ?)";
            $parametri[]= Itinerario::STATO_APERTO;
        }

        if ($itinerarioContiene !== "") {
            $query .= " and (Itinerario.nome like ? or Itinerario.descrizione like ?)";
            $parametri[]= "%" . $itinerarioContiene . "%";
            $parametri[]= "%" . $itinerarioContiene . "%";
        }

        if ($luogoContiene !== "") {
            $query .= " and (Itinerario.luogo like ?)";
            $parametri[]= "%" . $luogoContiene . "%";
        }

        if ($popolarità !== NULL) {
            $queryPart = array_fill(0, count($popolarità), "Itinerario.popolarità = ?");
            $query .= sprintf(" and (%s)", implode(" or ", $queryPart));

            foreach ($popolarità as $singola) {
                $parametri[]= $singola;
            }
        }

        if ($dataOra !== NULL) {
            $query .= sprintf(" and (Itinerario.data %s ?)", $dataOra[0]);
            $parametri[]= $dataOra[1];
        }

        $query .= " order by Itinerario.id DESC";

        $this->ciceroneDatabase->apri();

        $finalQuery = self::creaQueryConColonneRiconoscibili($query,
            array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA),
            array($colonneItineario, $colonneValuta, $colonneUtente)
        );

        $righe = call_user_func_array(array($this->ciceroneDatabase, "query"),
            array_merge(array($finalQuery), $parametri)
        );

        $itinerari = array();

        foreach ($righe as $riga) {
            $currItinerario = Itinerario::daArray($riga);

            $datiItinerario = array();
            if ($idFruitore !== NULL) {
                $datiItinerario["isCicerone"] = ($riga["isCicerone"] !== NULL);

                if ($datiItinerario["isCicerone"]) {
                    $datiItinerario["feedbackRilasciato"] = $this->checkFeedbackOP($currItinerario->getID());
                } else {
                    $datiItinerario["feedbackRilasciato"] = $this->checkFeedbackPO($currItinerario->getID(), $idFruitore);
                }

                $datiItinerario["statoPartecipazione"] = $riga["statoPartecipazione"];
                $datiItinerario["idFruitore"] = $idFruitore;
            }
            $datiItinerario["datiItinerario"] = $currItinerario;

            $itinerari[]= $datiItinerario;
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_RICERCA_ITINERARI_RIUSCITA;
    }


    /**
     * Restiuisce tutti gli itinerari associati ad un fruitore
     * @param int $idFruitore id del fruitore
     * @param bool $consideraSoloPartecipazioniAccordate restituisce soltanto gli itinerari a cui il fruitore effettivamente partecipa
     * @param bool $ordinaPerStatoPartecipazione ordina gli itinerari in base allo stato di partecipazione del fruitore 
     * @param array $itinerari il risultato dell'operazione
     * @return int lo stato dell'operazione, che può essere: STATO_FRUITORE_TROVATO,
     * STATO_FRUITORE_NON_TROVATO
     */
    public function getItinerariFruitore(int $idFruitore, bool $consideraSoloPartecipazioniAccordate, bool $ordinaPerStatoPartecipazione, ?array& $itinerari) : int {
        $this->ciceroneDatabase->apri();
        
        $colonneUtente = array("id", "nome_utente");
        $colonneValuta = array("valuta", "centesimale", "simbolo");
        $colonneItineario = array("id", "nome", "id_cicerone", "data", "descrizione",
            "immagine", "lingua", "luogo", "popolarità", "valuta", "compenso", "stato"
        );
        $colonnePartecipazione = array("stato");

        $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ? and " .
            "tipo != ?", $idFruitore, Utente::TIPO_AMMINISTRATORE);
        $nRighe = count($righe);

        $itinerari = NULL;
        
        if ($nRighe === 1) {
            $righe = $this->ciceroneDatabase->query("select 1 from Utente where id = ? and tipo = ?", $idFruitore, Utente::TIPO_CICERONE);
            $nRighe = count($righe);

            $itinerari = array(
                array(),
                NULL
            );

            if ($nRighe === 1) {
                $itinerari[1] = array();

                /*
                 * Trattasi di un Cicerone, aggiungi itinerari organizzati
                 */
                $query = self::creaQueryConColonneRiconoscibili(
                    "select %s from Itinerario, Utente, Valuta where " .
                    "id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta " .
                    "and Utente.id = ? order by Itinerario.id DESC",
                    array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA),
                    array($colonneItineario, $colonneValuta, $colonneUtente));

                $righe = $this->ciceroneDatabase->query($query, $idFruitore);

                foreach ($righe as $riga) {
                    $itinerari[1][]= Itinerario::daArray($riga);
                }
            }

            if ($consideraSoloPartecipazioniAccordate) {
                $query = self::creaQueryConColonneRiconoscibili(
                    "select %s from Itinerario, Utente, Valuta, Partecipazione where " .
                    "Itinerario.id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta " .
                    "and Partecipazione.id_itinerario = Itinerario.id and Partecipazione.id_partecipante = ? " .
                    "and Partecipazione.stato = ? order by Itinerario.id DESC",
                    array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA, Partecipazione::NOME_TABELLA),
                    array($colonneItineario, $colonneValuta, $colonneUtente, $colonnePartecipazione)
                 );

                 $righe = $this->ciceroneDatabase->query($query, $idFruitore, Partecipazione::STATO_ACCORDATA);

            } else {
                $query = self::creaQueryConColonneRiconoscibili(
                    "select %s from Itinerario, Utente, Valuta, Partecipazione where " .
                    "Itinerario.id_cicerone = Utente.id and Valuta.valuta = Itinerario.valuta " .
                    "and Partecipazione.id_itinerario = Itinerario.id and Partecipazione.id_partecipante = ? ",
                    array(Itinerario::NOME_TABELLA, Valuta::NOME_TABELLA, Utente::NOME_TABELLA, Partecipazione::NOME_TABELLA),
                    array($colonneItineario, $colonneValuta, $colonneUtente, $colonnePartecipazione)
                );

                if ($ordinaPerStatoPartecipazione) {
                    $query .= " order by Partecipazione.stato ASC, Itinerario.id DESC";
                } else {
                    $query .= "order by Itinerario.id DESC";
                }

                $righe = $this->ciceroneDatabase->query($query, $idFruitore);
            }

            foreach ($righe as $riga) {
                $itinerari[0][] = Partecipazione::daArray($riga);
            }

            $codiceStato = self::STATO_FRUITORE_TROVATO;
        } else {
            $codiceStato = self::STATO_FRUITORE_NON_TROVATO;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /*
     * Metodi di servizio per le richieste di partecipazione
     */    
    /**
     * Metodo di servizio per stabilire se un certo feedback partecipante-organizzatore
     * sia stato rilasciato
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @return bool true, se il feedback è stato rilasciato, false altrimenti
     */
    public function isFeedbackPORilasciato(int $idItinerario, int $idPartecipante) : bool {
        $this->ciceroneDatabase->apri();

        $PORilasciato = $this->checkFeedbackPO($idItinerario, $idPartecipante);

        $this->ciceroneDatabase->chiudi();

        return $PORilasciato;
    }


    /**
     * Metodo di servizio per stabilire se sono stati
     * rilasciati feedback organizzatore-partecipante a tutti i partecipanti
     * di un itinerario
     * @param int $idItinerario id dell'itinerario
     * @return bool true, se i feedback sono stati rilasciati tutti, false altrimenti
     */
    public function isFeedbackOPRilasciato(int $idItinerario) : bool {
        $this->ciceroneDatabase->apri();

        $OPRilasciato = $this->checkFeedbackOP($idItinerario);

        $this->ciceroneDatabase->chiudi();

        return $OPRilasciato;
    }


    /**
     * Metodo di servizio "del metodo di servizio" per stabilire se sono stati
     * rilasciati feedback organizzatore-partecipante a tutti i partecipanti
     * di un itinerario
     * @param int $idItinerario id dell'itinerario
     * @return bool true, se i feedback sono stati rilasciati tutti, false altrimenti
     */
    private function checkFeedbackOP(int $idItinerario) : bool {
        $righe = $this->ciceroneDatabase->query("select count(id) as conteggio from Partecipazione where id_itinerario = ? ", $idItinerario);
        $conteggioPartecipazioni = $righe[0]["conteggio"];

        $righe = $this->ciceroneDatabase->query("select count(id) as conteggio from Feedback where id_itinerario = ? and tipo = ?", $idItinerario, Feedback::TIPO_ORGANIZZATORE_PARTECIPANTE);
        $conteggioFeedback = $righe[0]["conteggio"];

        return $conteggioPartecipazioni === $conteggioFeedback;
    }


    /**
     * Metodo di servizio "del metodo di servizio" per stabilire se un certo feedback partecipante-organizzatore
     * sia stato rilasciato
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @return bool true, se il feedback è stato rilasciato, false altrimenti
     */
    private function checkFeedbackPO(int $idItinerario, int $idPartecipante) : bool {
        $righe = $this->ciceroneDatabase->query("select 1 from Feedback where id_itinerario = ? and id_partecipante = ? and tipo = ?",
            $idItinerario, $idPartecipante, Feedback::TIPO_PARTECIPANTE_ORGANIZZATORE);

        return count($righe) === 1;
    }
    
    
    /*
     * Metodi inerenti le richieste di partecipazione
     */
    /**
     * Restituisce tutte le richieste di partecipazione inerenti un itinerario.
     * @param int $idItinerario id dell'itinerario
     * @param bool $consideraSoloAccordate restituisce solo le richieste di partecipazione accordate
     * @param array $partecipazioni il risultato dell'operazione
     * @return int lo stato dell'operazione STATO_NO_SEGNALAZIONE
     */
    public function getRichiestePartecipazione(int $idItinerario, bool $consideraSoloAccordate, ?array& $partecipazioni) : int {
        $this->ciceroneDatabase->apri();
        $colonneUtente = array("id", "nome_utente", "immagine");
        $colonnePartecipazione = array("stato");

        if ($consideraSoloAccordate) {
            $query = self::creaQueryConColonneRiconoscibili(
                "select %s from Partecipazione, Utente where " .
                "id_partecipante = Utente.id and Partecipazione.id_itinerario = ? " .
                "and Partecipazione.stato = ? order by Partecipazione.stato ASC, " .
                "Partecipazione.id_itinerario DESC",
                array(Partecipazione::NOME_TABELLA, Utente::NOME_TABELLA),
                array($colonnePartecipazione, $colonneUtente));

            $righe = $this->ciceroneDatabase->query($query, $idItinerario, Partecipazione::STATO_ACCORDATA);

        } else {
            $query = self::creaQueryConColonneRiconoscibili(
                "select %s from Partecipazione, Utente where " .
                "id_partecipante = Utente.id and Partecipazione.id_itinerario = ? " .
                "order by Partecipazione.id_itinerario DESC",
                array(Partecipazione::NOME_TABELLA, Utente::NOME_TABELLA),
                array($colonnePartecipazione, $colonneUtente));

            $righe = $this->ciceroneDatabase->query($query, $idItinerario);
        }

        $partecipazioni = array();
        foreach ($righe as $rigaPartecipazione) {
            $partecipazioni[]= Partecipazione::daArray($rigaPartecipazione);
        }

        $this->ciceroneDatabase->chiudi();

        return self::STATO_NO_SEGNALAZIONE;
    }
    
    
    /**
     * Esegue il caso d'uso inerente l'accordo di una richiesta di partecipazione.
     * @param int $idCicerone id del Cicerone organizzatore dell'itinerario
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante che ha inviato la richiesta
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_PARTECIPAZIONE_ACCORDATA
     */
    public function accordaRichiestaPartecipazione(int $idCicerone, int $idItinerario, int $idPartecipante) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Partecipazione.id as id from " .
            "Partecipazione, Itinerario where Itinerario.id = Partecipazione.id_itinerario " .
            "and Itinerario.id = ? and Itinerario.id_cicerone = ? and " .
            "Partecipazione.id_partecipante = ? and Partecipazione.stato = ? and " . 
            "Itinerario.stato = ?", $idItinerario, $idCicerone, $idPartecipante,
            Partecipazione::STATO_ACCORDANDA, Itinerario::STATO_APERTO);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $id = $righe[0]["id"];

            $this->ciceroneDatabase->manipola("update Partecipazione set stato = ? where id = ?",
                Partecipazione::STATO_ACCORDATA, $id);

            $codiceStato = self::STATO_PARTECIPAZIONE_ACCORDATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'annullamento di una richiesta di partecipazione.
     * @param int $idCicerone id del Cicerone organizzatore dell'itinerario
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante che ha inviato la richiesta
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_PARTECIPAZIONE_ANNULLATA
     */
    public function annullaRichiestaPartecipazione(int $idCicerone, int $idItinerario, int $idPartecipante) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Partecipazione.id as id from " .
            "Partecipazione, Itinerario where Itinerario.id = Partecipazione.id_itinerario " .
            "and Itinerario.id = ? and Itinerario.id_cicerone = ? and " .
            "Partecipazione.id_partecipante = ? and Partecipazione.stato = ? and " .
            "Itinerario.stato = ?", $idItinerario, $idCicerone, $idPartecipante,
            Partecipazione::STATO_ANNULLANDA, Itinerario::STATO_APERTO);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $id = $righe[0]["id"];
            $this->ciceroneDatabase->manipola("delete from  Partecipazione where id = ?", $id);
            $codiceStato = self::STATO_PARTECIPAZIONE_ANNULLATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'annullamento di una richiesta di partecipazione.
     * @param int $idCicerone id del Cicerone organizzatore dell'itinerario
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante che ha inviato la richiesta
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_PARTECIPAZIONE_DECLINATA
     */
    public function declinaRichiestaPartecipazione(int $idCicerone, int $idItinerario, int $idPartecipante) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Partecipazione.id as id from " .
            "Partecipazione, Itinerario where Itinerario.id = Partecipazione.id_itinerario " .
            "and Itinerario.id = ? and Itinerario.id_cicerone = ? and " .
            "Partecipazione.id_partecipante = ? and Partecipazione.stato = ? and " .
            "Itinerario.stato = ?", $idItinerario, $idCicerone, $idPartecipante,
            Partecipazione::STATO_ACCORDANDA, Itinerario::STATO_APERTO);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $id = $righe[0]["id"];
            $this->ciceroneDatabase->manipola("delete from Partecipazione where id = ?", $id);
            $codiceStato = self::STATO_PARTECIPAZIONE_DECLINATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'invio di una richiesta di partecipazione.
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RICHIESTA_PARTECIPAZIONE_INVIATA
     */
    public function inviaRichiestaPartecipazione(int $idItinerario, int $idPartecipante) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select 1 from Itinerario where id_cicerone != ? " .
            "and id = ? and stato = ?", $idPartecipante, $idItinerario, Itinerario::STATO_APERTO);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $righe = $this->ciceroneDatabase->query("select 1 from Partecipazione where " .
                "id_itinerario = ? and id_partecipante = ?", $idItinerario, $idPartecipante);
            $nRighe = count($righe);

            if ($nRighe === 0) {
                $this->ciceroneDatabase->manipola("insert into Partecipazione (id_itinerario, id_partecipante, stato) values (?, ?, ?)",
                    $idItinerario, $idPartecipante, Partecipazione::STATO_ACCORDANDA);
                $codiceStato = self::STATO_RICHIESTA_PARTECIPAZIONE_INVIATA;
            }
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }


    /**
     * Esegue il caso d'uso inerente l'invio della richiesta d'annullamento.
     * @param int $idItinerario id dell'itinerario
     * @param int $idPartecipante id del partecipante
     * @return int lo stato dell'operazione, che può essere: STATO_NO_SEGNALAZIONE,
     * STATO_RICHIESTA_ANNULLAMENTO_INVIATA
     */
    public function inviaRichiestaAnnullamento(int $idItinerario, int $idPartecipante) : int {
        $this->ciceroneDatabase->apri();

        $righe = $this->ciceroneDatabase->query("select Partecipazione.id from Partecipazione, " .
            "Itinerario where Itinerario.id = Partecipazione.id_itinerario and " .
            "Partecipazione.id_itinerario = ? and Partecipazione.id_partecipante = ? and " .
            "Itinerario.stato = ? and Partecipazione.stato = ?", $idItinerario, $idPartecipante,
            Itinerario::STATO_APERTO, Partecipazione::STATO_ACCORDATA);
        $nRighe = count($righe);

        $codiceStato = self::STATO_NO_SEGNALAZIONE;

        if ($nRighe === 1) {
            $this->ciceroneDatabase->manipola("update Partecipazione set stato = ? " .
                "where id = ?", Partecipazione::STATO_ANNULLANDA, $righe[0]["id"]);
            $codiceStato = self::STATO_RICHIESTA_ANNULLAMENTO_INVIATA;
        }

        $this->ciceroneDatabase->chiudi();

        return $codiceStato;
    }
}

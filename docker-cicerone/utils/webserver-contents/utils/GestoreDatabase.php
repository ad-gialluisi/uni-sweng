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


namespace utils;

use PDO;
use PDOException;
use PDOStatement;

require_once "CustomException.php";


/**
 * È l'eccezione sollevata in caso di problemi di connessione col database
 */
class ConnessioneDBException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}

/**
 * È l'eccezione sollevata in caso di problemi relativi ai comandi richiesti
 */
class ComandoSQLException extends CustomException {
    public function __construct($formato, ...$args) {
        call_user_func_array(array($this, "parent::__construct"), array_merge(array($formato), $args));
    }
}

/**
 * Classe generica per la comunicazione con il DBMS MySQL.
 * È facilmente migliorabile per consentire l'interfacciamento con altri DBMS.
 */
class GestoreDatabase {
    /**
     * Riferimento all'oggetto PDO usato per effettuare operazioni
     * @var PDO
     */
    private $dbRef;


    /**
     * Protocollo usato per la connessione al database
     * @var string
     */
    private $protocollo;

    
    /**
     * Credenziali usate
     * @var array
     */
    private $credenziali;

    
    /**
     * Tipo di DMBS utilizzato
     * @var string
     */
    private $dbms;


    /*
     * Alcune costanti utili per evitare ripetizioni
     */
    private const CREDENZIALI_USERNAME = "username";
    private const CREDENZIALI_PASSWORD = "password";

    /*
     * Tipi di DBMS previsti (possono essere aggiunti degli altri)
     */
    public const DBMS_MYSQL = "mysql";
    public const DBMS_SQLITE = "sqlite";
    

    /*
     * Messaggi di errore, idem con patate.
     */
    private const MESSAGGIO_DATABASE_NON_APERTO = "Database non aperto!";
    private const MESSAGGIO_FALLIMENTO_APERTURA_DATABASE = "Fallimento nell'apertura del database.\n%s";
    private const MESSAGGIO_PARAMETRI_NON_VALIDI = "Trovati parametri non validi!";
    private const MESSAGGIO_NO_TRANSAZIONI = "Nessuna transazione in corso!";
    private const MESSAGGIO_TRANSAZIONE_IN_CORSO = "Transazione in corso!";
    private const MESSAGGIO_ERRORE_PREPARAZIONE_STATEMENT = "Errore durante la preparazione dello statement!\n%s";
    private const MESSAGGIO_ERRORE_ABBINAMENTO_PARAMETRI = "Errore durante l'abbinamento dei parametri!\n%s";
    private const MESSAGGIO_ERRORE_MANIPOLAZIONE = "Errore durante l'esecuzione della manipolazione!\n%s";
    private const MESSAGGIO_ERRORE_QUERY = "Errore durante l'esecuzione della query!\n%s";
    private const MESSAGGIO_ERRORE_COMANDO = "Errore durante l'esecuzione del comando!\n%s";


    /**
     * Costruttore che consente di creare un'istanza di GestoreDatabase.
     * È importante notare che questa classe è nata per essere estesa e fornire
     * supporto ad altri DBMS, in base ai driver PDO installati.
     */
    public function __construct(string $dbms, ?string $host, string $dbname, ?string $username, ?string $password) {
        $this->dbms = $dbms;

        if ($dbms === self::DBMS_MYSQL) {
            $this->protocollo = sprintf("mysql:host=%s;dbname=%s", $host, $dbname);
            
            $this->credenziali = array(
                self::CREDENZIALI_USERNAME => $username,
                self::CREDENZIALI_PASSWORD => $password,
            );
        } else if ($dbms === self::DBMS_SQLITE) {
            $this->protocollo = sprintf("sqlite:%s", $dbname);
        }

        $this->dbRef = NULL;
    }


    /**
     * Apre la connessione al database
     */
    public function apri() : void {
        try {
            if ($this->dbms === self::DBMS_MYSQL) {
                $this->dbRef = new PDO($this->protocollo,
                    $this->credenziali[self::CREDENZIALI_USERNAME],
                    $this->credenziali[self::CREDENZIALI_PASSWORD]);
            } else if ($this->dbms === self::DBMS_SQLITE) {
                $this->dbRef = new PDO($this->protocollo);
            }

            $this->dbRef->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $excp) {
            $this->dbRef = NULL;
            throw new ConnessioneDBException(self::MESSAGGIO_FALLIMENTO_APERTURA_DATABASE,
                $excp->getMessage());
        }
    }

    
    /**
     * Chiude la connessione al database
     */
    public function chiudi() : void {
        if ($this->dbRef == NULL) {
            throw new ConnessioneDBException(self::MESSAGGIO_DATABASE_NON_APERTO);
        }

        $this->dbRef = NULL;
    }


    /**
     * Crea uno statement partendo da un array.
     * NOTA: l'array deve essere di tipo numerico (indici numerici) e deve possedere solo stringhe1
     * @param array $parameters Il primo parametro deve essere la frase di preparazione
     * @throws ConnessioneDBException In caso di non apertura del database.
     * @throws ComandoSQLException In caso di errori durante l'uso o creazione degli statement.
     * @return PDOStatement
     */
    private function makeStatement(array $parameters) : PDOStatement {
        if ($this->dbRef == NULL) {
            throw new ConnessioneDBException(self::MESSAGGIO_DATABASE_NON_APERTO);
        }

        foreach ($parameters as $idx => $value) {
            if (($idx === 0 && !is_string($value)) ||
                (is_object($value) && !method_exists($value, "__toString"))) {
                throw new ComandoSQLException(self::MESSAGGIO_PARAMETRI_NON_VALIDI);
            }

            if  ($idx === 0) {
                try {
                    $stmt = $this->dbRef->prepare($value);
                } catch (PDOException $excp) {
                    throw new ComandoSQLException(
                        self::MESSAGGIO_ERRORE_PREPARAZIONE_STATEMENT,
                        $excp->getMessage());
                }
            } else {
                try {
                    $stmt->bindValue($idx, $value);
                } catch (PDOException $excp) {
                    throw new ComandoSQLException(
                        self::MESSAGGIO_ERRORE_ABBINAMENTO_PARAMETRI,
                        $excp->getMessage());
                }
            }
        }

        return $stmt;
    }


    /**
     * Effettua una manipolazione (comandi UPDATE e INSERT)
     * NOTA: l'array deve essere di tipo numerico (indici numerici) e deve possedere solo stringhe
     * @param string[] ...$parameters Il primo parametro deve essere la frase di preparazione
     * @throws ComandoSQLException In caso di problemi con l'esecuzione della manipolazione
     * @return int il numero di righe su cui la manipolazione ha avuto effetto.
     */
    public function manipola(...$parameters) : int {
        $stmt = $this->makeStatement($parameters); 

        try {
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->errorInfo();
                throw new ComandoSQLException($error[2]);
            }
        } catch (PDOException $excp) {
            throw new ComandoSQLException(
                self::MESSAGGIO_ERRORE_MANIPOLAZIONE, $excp->getMessage());
        }

        return $stmt->rowCount();
    }


    /**
     * Effettua una query (comando SELECT)
     * NOTA: l'array deve essere di tipo numerico (indici numerici) e deve possedere solo stringhe
     * @param string[] ...$parameters Il primo parametro deve essere la frase di preparazione
     * @throws ComandoSQLException In caso di problemi con l'esecuzione della query.
     * @return array il risultato della query (array di righe di database)
     */
    public function query(...$parameters) : array {
        $stmt = $this->makeStatement($parameters);

        try {
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->errorInfo();
                throw new ComandoSQLException($error[2]);
            }
        } catch (PDOException $excp) {
            throw new ComandoSQLException(
                self::MESSAGGIO_ERRORE_QUERY, $excp->getMessage());
        }

        /*
         * Restituisce tutte le righe del risultato, trasformando ogni riga
         * in un array associativo in cui la chiave è il campo del
         * DB e il valore, appunto, il valore associato al camapo.
         */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Effettua un comando (ad esempio, impostazione della codifica del testo)
     * @param string[] ...$parameters Il primo parametro deve essere la frase di preparazione
     * @throws ComandoSQLException in caso di problemi con l'esecuzione del comando.
     */
    public function comando(...$parameters) : void {
        $stmt = $this->makeStatement($parameters);

        try {
            $result = $stmt->execute();
            if (!$result) {
                $error = $stmt->errorInfo();
                throw new ComandoSQLException($error[2]);
            }
        } catch (PDOException $excp) {
            throw new ComandoSQLException(
                self::MESSAGGIO_ERRORE_COMANDO, $excp->getMessage());
        }
    }


    /**
     * Disabilita la modalità autocommit e fa partire una transazione.
     * @throws ConnessioneDBException In caso di non apertura del database.
     * @throws ComandoSQLException In caso il DB sia già in una transazione.
     */
    public function iniziaTransazione() : void {
        if ($this->dbRef == NULL) {
            throw new ConnessioneDBException(self::MESSAGGIO_DATABASE_NON_APERTO);
        }
        if ($this->dbRef->inTransaction()) {
            throw new ComandoSQLException(self::MESSAGGIO_TRANSAZIONE_IN_CORSO);
        }

        $this->dbRef->beginTransaction();
    }


    /**
     * Effettua il commit dei comandi dettati dalla transazione.
     * NOTA: se il comando va a buon fine, verrà ripristinata la modalità autocommit,
     * in altri termini, non si starà più all'interno di una transazione.
     * @throws ComandoSQLException In caso il DB NON sia in una transazione.
     * @return bool true, se il commit è riuscito, false al contrario.
     */
    public function commit() : bool {
        if (!$this->dbRef->inTransaction()) {
            throw new ComandoSQLException(self::MESSAGGIO_NO_TRANSAZIONI);
        }

        return $this->dbRef->commit();
    }


    /**
     * Effettua il rollback dei comandi dettati dalla transazione.
     * NOTA: se il comando va a buon fine, verrà ripristinata la modalità autocommit,
     * in altri termini, non si starà più all'interno di una transazione.
     * @throws ComandoSQLException In caso il DB NON sia in una transazione.
     * @return bool true, se il rollback è riuscito, false al contrario.
     */
    public function rollback() : bool {
        if (!$this->dbRef->inTransaction()) {
            throw new ComandoSQLException(self::MESSAGGIO_NO_TRANSAZIONI);
        }

        return $this->dbRef->rollBack();
    }


    /**
     * Ottiene l'ultimo id creato per via di una clausola INSERT nel DB.
     * @return string l'ultimo id creato
     */
    public function getLastInsertID() : string {
        return $this->dbRef->lastInsertId();
    }
}

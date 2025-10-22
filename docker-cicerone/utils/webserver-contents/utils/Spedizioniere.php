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


require_once "PHPMailer/src/PHPMailer.php";
require_once "PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;


/**
 * Classe d'utilità il cui unico scopo è quello di spedire email.
 * Sfrutta una parte della libreria PHPMailer.
 */
class Spedizioniere {
    /**
     * Ond'evitare di spedire email inutilmente, si utilizza questo valore per
     * "scrivere l'email" su un file di testo ad hoc, usato per testare.
     * Se serve la vera funzionalità, impostare a "false".
     */
    private const DEBUG = true;


    public static function invia($destinatario, $nomeDestinatario, $oggetto,
        $corpoHTML, $corpoTesto) : bool {

        if (!self::DEBUG) {
            /*
             * Modifica informazioni qui se necessario
             */
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->CharSet = "UTF-8";

            $mail->Username = self::getUsername();
            $mail->Password = self::getPassword();

            $mail->isHTML(true);
            $mail->setFrom('webmaster@cicero.com', 'Sistema Cicerone');
            $mail->addReplyTo('webmaster@cicero.com', 'Sistema Cicerone');
            $mail->addAddress($destinatario, $nomeDestinatario);
            $mail->Subject = $oggetto;
            $mail->Body = $corpoHTML;
            $mail->AltBody = $corpoTesto;

            $sent = $mail->send();

        } else {
            //Per debugging, scrivi su file di testo
            $f = fopen($_SERVER["DOCUMENT_ROOT"] . "/debug/rwdir/mail.txt", "w");
            fwrite($f,
                sprintf("Mittente: webmaster@cicero.com\nDestinatario: %s\n" .
                    "Oggetto: %s\nCorpo:\n%s", $destinatario, $oggetto, $corpoHTML));
            fclose($f);
            $sent = true;
        }

        return $sent;
    }
    
    
    //Inserire username e password qui
    private static function getUsername() {
        return "";
    }
    
    private static function getPassword() {
        return "";
    }
}

# Progetto "Cicerone": Esame di Ingegneria del software

## Italiano

La piattaforma Cicerone, ovvero, il sistema software sviluppato per il caso di studio di Ingegneria del Software.



### Progetto Docker

Grazie a Docker, è possibile eseguire la piattaforma senza preoccuparsi di installare dipendenze, pacchetti ed altro.\
Assicurarsi di aver installato Docker correttamente, quindi, fare quanto segue.\
Dapprima, eseguire la clonazione del repository:

```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-cicerone
```

Quindi eseguire il comando `make`.\
Il Makefile supporta i seguenti target:

- *stop*: Ferma il container attualmente in esecuzione;
- *rm*: Rimuove l'immagine Docker;
- *build*: Costruisce l'immagine Docker;
- *run*: Esegue un container della suddetta immagine;
- *all*: Esegue tutti i target prima citati nell'ordine: *stop*, *rm*, *build* e *run*;


Una volta in esecuzione, è sufficiente usare un browser e collegarsi all'indirizzo [http://localhost](http://localhost).


#### Perchè "Cicerone"?

La consegna prevedeva una piattaforma di viaggi low-cost e non convenzionali, con due tipi principali di utenti: il Globetrotter (colui che intende partecipare ai viaggi) e il Cicerone (colui che li organizza).

Nell'ambito dei viaggi, il Globetrotter (letteralmente "colui che cammina per il globo") indica una persona che ama viaggiare in ogni dove, ed è aperto a culture nuove (fonte: [https://dictionary.cambridge.org/it/dizionario/inglese/globetrotter](https://dictionary.cambridge.org/it/dizionario/inglese/globetrotter)).\
Il Cicerone invece, è una guida che accompagna i viaggiatori, al fine di arricchire il loro bagaglio culturale, in quanto esperti dell'itinerario prescelto.

L'uso del termine, come intuibile, deriva dal celebre oratore romano, Marco Tullio Cicerone (Marcus Tullius Cicero in latino); Conosciuto per la sua cultura e abilità dialettica, egli era e considerato, una "guida" da chiunque volesse apprendere il mestiere di oratore.\
In un contesto più ampio, il "Cicerone" indica una qualunque persona, in grado di guidarne altre, rispetto ad uno o più argomenti di sua competenza (fonte [https://www.localiperpensare.it/significato-cicerone-sinonimi-contrari-frasi-ed-esempi/](https://www.localiperpensare.it/significato-cicerone-sinonimi-contrari-frasi-ed-esempi/)).

Il nome "piattaforma Cicerone", è stato scelto per banale pigrizia da parte del sottoscritto nell'individuare un nome adeguato.

La piattaforma viene fornita con dati di test, utili per testare le sue funzionalità **immediatamente**.\
È possibile fare login, usando uno dei seguenti utenti, riportati per nome utente, password e tipo di utente:

- antoniodaniele, antoniodaniele, Amministratore;
- caligola, caligola, Cicerone;
- giuliocesare, giuliocesare, QuasiCicerone;
- neroneclaudio, neroneclaudio, Globetrotter;


Ho aggiunto come utenti — oltre al sottoscritto — altri personaggi di epoca romana, come Caligola, Nerone e Giulio Cesare.\
La scelta è dovuta al "doppio significato" di "Cicerone", in riferimento alla guida e al personaggio storico.



### Copyright

Questo software è distribuito sotto licenza GPLv3, e fa uso della libreria PHPMailer 6.1.1, distribuita sotto licenza LGPLv2.1+.



### Istruzioni installazione piattaforma (ex manuale)

Questa sezione è strata estratta e leggermente rielaborata dal manuale che fu consegnato ai tempi.\
Per usi standard, è sufficiente adoperare il progetto Docker indicato precedentemente: Questa rielaborazione viene riportata per completezza.

Innanzitutto, installare i seguenti:

- Webserver Apache httpd v2.4 o superiore;
- PHP v7.3 o superiore con i moduli PDO (con driver MySQL), SSL, EXIF e JSON;
- Un server SMTP esterno per la funzionalità di invio mail. Ai tempi, ho utilizzato un account GMail con un'opportuna password per app;
- XAMPP v7.3.13 o superiore, se su Windows;


Ora occorre abilitare alcune opzioni nel file di configurazione (su Debian `/etc/php/<versione-php>/apache2 php.ini`).\
Dapprima abilitare i moduli prima citati:

```
extension=pdo.so
extension=pdo_mysql.so
extension=openssl.so
extension=mbstring.so
extension=exif.so
extension=json.so
```

Quindi l'upload dei file:

```
file_uploads = On
```

Infine, è consigliabile visualizzare i messaggi d'errore direttamente su schermo, in caso di problemi:

```
display_errors = On
display_startup_errors = On
log_errors = On
html_errors = On
```

Per MariaDB, occorre garantire che esista, da qualche parte tra i file di configurazione, un file con il seguente contenuto:

```
[server]
character-set-server = "utf8mb4"
```

Se non ci dovesse essere, è possibile aggiungere tale sezione in un file apposito, oppure nel file di default (`/etc/mysql/my.cnf` su Debian).\
Per confermare il corretto funzonamento, accedere al client a riga di comando e digitare `status`; Si dovrebbe ottenere un risultato del genere:

```
..omesso..
Server characterset: utf8mb4
Db characterset:     utf8mb4
Client characterset: utf8
Conn. characterset:  utf8
..omesso..
```

Occorre che tutti i valori riportati siano di tipo UTF-8, i primi due sono garantiti dal file di configurazione menzionato, gli altri due dipendono dal locale del sistema.\
Su Debian, è possibile riconfigurare i locales con `dpkg-reconfigure locales`, oppure — come nel progetto Docker — impostando la variabile d'ambiente `LANG` ad un valore appropriato.

Per cambiare il percorso dove piazzare i file della piattaforma (su GNU/Linux, il default è `/var/www/html`), occorre impostare un nuovo `VirtualHost`.\
Su Debian, bisogna porre il file di configurazione in `/etc/apache2/sites-available`. Qui segue un esempio di `VirtualHost`:

```
<VirtualHost *:80>
    ServerName <HOSTNAME_CICERONE>
    ServerAdmin webmaster@localhost
    DocumentRoot "<PERCORSO_ASSOLUTO_PIATTAFORMA>"
    <Directory "<PERCORSO_ASSOLUTO_PIATTAFORMA>">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

dove:

- `<PERCORSO_ASSOLUTO_PIATTAFORMA>` è un percorso assoluto, a scelta;
- `<HOSTNAME_CICERONE>` è un hostname a scelta, utilizzato per collegarsi alla piattaforma;

Una volta scelto l'hostname, occorre inserire la riga `127.0.0.1 <HOSTNAME_CICERONE>` nel file hosts (`/etc/hosts` su GNU/Linux, `%SystemRoot%/System32/driver/etc/hosts` su Windows).

Una volta memorizzato il `VirtualHost`, occorre abilitarlo (`a2ensite <nome-file-configurazione>` su Debian).\
Si noti che in alcune installazioni, occorre inserire esplicitamente delle direttive "Include" nel file di configurazione principale, affinchè i file secondari vengano letti.

Spostare i contenuti della cartella `utils/webserver-contents` in `<PERCORSO_ASSOLUTO_PIATTAFORMA>`.\
Su GNU/Linux, assicurarsi di spostare i file nascosti, e di impostare i permessi e proprietari corretti (su Debian, l'utente usato dal webserver è "www-data").

Avviare il servizio del webserver, quindi il servizio del DBMS.\
A questo punto, aprire il file `inizializza.sh`/`inizializza.bat` e verificare la correttezza delle informazioni inerenti il DBMS.\
Commentare/scommentare secondo necessità, quindi eseguire lo script come super-utente.

Se si intende usare la funzionalità di spedizione mail, assicurarsi che il servizio SMTP non sia bloccato da antivirus e/o firewall.\
Aprire eventualmente il file `utils/Spedizioniere.php` e modificare i dettagli di collegamento.



### Modalità DEBUG

Questo sistema include un paio di modalità DEBUG, sparse in diversi file:

- `vista/Vista.php`:\
Proprietà const `DEBUG`; Se abilitata, permette di usare CTRL-D per invocare un dialog di Debug, usato durante lo sviluppo della piattaforma.\
Tale menù consente, tra le cose, di impostare la modalità DEBUG di XDebug, anche nei file di elaborazione delle richieste.\
Di default è impostata su `true`;

- `utils/Spedizioniere.php`:\
Proprietà const `DEBUG`; Se abilitata, le mail non vengono inviate mediante SMTP, ma scritte su un file (sovrascrivendosi di volta in volta) nella cartella `debug/rwdir`.\
Usato per debug e per evitare invii accidentali.\
Di default è impostata su `true`;

- `debug/DebugSettings.php`:\
Proprietà const `XDEBUG_SESSION_START_VALUE` e `XDEBUG_KEY_VALUE`; I valori usati dal protocollo XDebug. Variano da IDE ad IDE, e possono essere personalizzati.\
I valori di default sono impostati, rispettivamente, su `ECLIPSE_DBGP` e `157270718956123`;

- `vista/Layout.php`:\
Proprietà const `DEBUG`; Se abilitata, per ogni pagina che si visita, viene eseguito un dump della stessa su un file (sovrascrivendosi di volta in volta) nella cartella `debug/rwdir`.\
Questa modalità è stata usata per fare debug del linguaggio Template.\
Di default è impostata su `false`;



### Note

- Il codice era ospitato su un repository Subversion, su server universitario. Ai tempi, ignoravo di poter eseguire un dump del repository, di conseguenza, ho solo la versione finale della piattaforma;

- La libreria PHPMailer è stata integrata per eseguire l'invio di mail, tramite server SMTP esterni. La versione usata è oggi obsoleta.\
Una copia di tale versione è fornita con questo progetto in formato zip, ma è ancora possibile scaricarla da [https://github.com/PHPMailer/PHPMailer/tree/v6.1.1](https://github.com/PHPMailer/PHPMailer/tree/v6.1.1);

- Se si imposta la proprietà `DEBUG` di `utils/Spedizioniere.php` su `false`, occorre configurare correttamente lo Spedizionere, prima di poter inviare mail;

- Ho dovuto correggere una chiamata `implode` in `vista/Vista.php`. Incredibilmente, era l'unica chiamata IN TUTTO IL PROGETTO, ad usare la sintassi legacy rimossa da PHP 8.0: `implode(array, string)`;


## English

The Cicerone platform, that is, the software system developed for the case study of Software Engineering.



### Docker project

Thanks to Docker, it is possible to run the platform, without worrying about dependencies, packages and other things.\
Be sure to install Docker correctly, then do as follows.\
First, clone this repository:


```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-cicerone
```


Then, run the command `make`.\
The Makefile supports the following targets:

- *stop*: It stops the currently running container;
- *rm*: It removes the Docker image;
- *build*: It builds the Docker image;
- *run*: It runs a container based on the aforementioned image;
- *all*: It runs all the previous targets in the order: *stop*, *rm*, *build* and *run*;


Once the container is running, connect to [http://localhost](http://localhost) by using a browser.


#### Why "Cicerone"?

The assignment expected a low-cost and non-conventional travel platform, with two main types of users: The Globetrotter (the one that participates in trips) and the Cicerone (the one that organizes them).

In the context of travel, the Globetrotter indicates a person that loves to travel, and is open to new cultures (reference: [https://dictionary.cambridge.org/it/dizionario/inglese/globetrotter](https://dictionary.cambridge.org/it/dizionario/inglese/globetrotter)).\
In the Italian language, "Cicerone" is used to indicate a tour guide, that — being expert in the chosen itinerary — accompanies travellers to enrich their cultural experience.

The usage of the term, comes from the famous Roman orator, Marcus Tullius Cicero; He was known for his culture and dialectical abilities, to the point, he was considered a "guide", for anyone who wanted to become an orator.\
In a broader context, "Cicerone" indicates any person capable of guiding others, in respect to one or more subjects, he/she masters (reference [https://www.merriam-webster.com/dictionary/cicerone](https://www.merriam-webster.com/dictionary/cicerone)).

The name "Cicerone platform", was chosen because of my laziness in choosing a better name.

The platform is provided with test data, useful to **immediately** test its functionalities.\
It is possible to login, by using one of the following users, reported by username, password and type of user (with translation):

- antoniodaniele, antoniodaniele, Amministratore (Administrator);
- caligola, caligola, Cicerone (Tour guide);
- giuliocesare, giuliocesare, QuasiCicerone (Almost a tour guide);
- neroneclaudio, neroneclaudio, Globetrotter;


I added as users — other than the undersigned — various figures from ancient Rome, such as Caligola (Caligula), Nerone (Nero) and Giulio Cesare (Julius Caesar).\
This choice was made because of the "double meaning" of "Cicerone", referring to both, the tour guide and the historical figure.



### Copyright

This software is distributed under the GPLv3 license, and makes use of the PHPMailer 6.1.1 library, distributed under the LGPLv2.1+ license.



### Platform install instructions (former manual)

This section was extracted and slightly reworked from the manual, which belonged to the assignment from that time.\
For standard usages, the Docker project mentioned earlier is enough: This rework is reported for completeness.

First of all, install the following:

- Apache httpd webserver v2.4 or higher;
- PHP v7.3 or higher with the modules PDO (with the MySQL driver), SSL, EXIF and JSON;
- An external SMTP server for the mail sending functionality. At the time, I used a GMail account with a proper per-app password;
- XAMPP v7.3.13 or higher, if on Windows;


Now it's time to enable some options in the configuration file (`/etc/php/<php-version>/apache2/php.ini` on Debian).\
First, enable the above-mentioned modules:

```
extension=pdo.so
extension=pdo_mysql.so
extension=openssl.so
extension=mbstring.so
extension=exif.so
extension=json.so
```

Then, the file upload option:

```
file_uploads = On
```

Finally, it's a good idea — in case of problems — to show error messages directly on-screen:

```
display_errors = On
display_startup_errors = On
log_errors = On
html_errors = On
```

In relation to MariaDB, there must exist — among the configuration files — a file with the following content:

```
[server]
character-set-server = "utf8mb4"
```

If it doesn't exist, the above content must added to a specific file, or to the default one (`/etc/mysql/my.cnf` on Debian).\
To confirm everything works, use the command-line client and type `status`; It should produce something like this:

```
..omitted..
Server characterset: utf8mb4
Db characterset:     utf8mb4
Client characterset: utf8
Conn. characterset:  utf8
..omitted..
```

All the reported values must be of UTF-8 type, the first two are guaranteed by the mentioned config file, the other two depend on the system locale.\
On Debian, the locales can reconfigured by using `dpkg-reconfigure locales`, or — as in the Docker project — by setting the environment variable `LANG` to a proper value.

In order to change where to place the platform's files (default is `/var/www/html` on GNU/Linux), a new `VirtualHost` is necessary.\
On Debian, the configuration file must be placed in `/etc/apache2/sites-available`. Here follows an example of `VirtualHost`:

```
<VirtualHost *:80>
    ServerName <CICERONE_HOSTNAME>
    ServerAdmin webmaster@localhost
    DocumentRoot "<PLATFORM_ABSOLUTE_PATH>"
    <Directory "<PLATFORM_ABSOLUTE_PATH>">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

where:

- `<PLATFORM_ABSOLUTE_PATH>` is a chosen absolute path;
- `<CICERONE_HOSTNAME>` is a chosen hostname, which will be used to connect to the platform;

When the hostname is ready, insert the line `127.0.0.1 <CICERONE_HOSTNAME>` in the hosts file (`/etc/hosts` on GNU/Linux, `%SystemRoot%/System32/driver/etc/hosts` on Windows).

After storing the `VirtualHost`, enable it (`a2ensite <configuration-filename>` on Debian).\
Please note that some installations, require the use of explicit "Include" directives in the main config file, in order for the secondary files to be read.

Move the contents of the directory `utils/webserver-contents` in `<PLATFORM_ABSOLUTE_PATH>`.\
On GNU/Linux, be sure to move hidden files, to set the correct permissions and the owners (on Debian, the user of the webserver is "www-data").

Start the webserver service, then the DBMS one.\
At this point, open the `inizializza.sh`/`inizializza.bat` file and check if the DBMS information is correct.\
Coment/uncomment as needed, and then, run the script as super-user.

To use the mail sending functionality, be sure the SMTP service is not blocked by antiviruses and/or firewalls.\
If needed, open the file `utils/Spedizioniere.php` and edit the connection details.



### DEBUG Mode

This system includes some DEBUG modes, across different files:

- `vista/Vista.php`:\
Const property `DEBUG`; If enabled, it allows to use CTRL-D to invoke a Debug dialog, which was used during the platform's development.\
The menu provides the ability — among other things — to set the XDebug DEBUG mode in requests' processing files.\
By default, it is set to `true`;

- `utils/Spedizioniere.php`:\
Const property `DEBUG`; If enabled, mails are not sent through SMTP, but written on a file (overwriting it each time) in the `debug/rwdir` directory.\
This was used for debugging and to prevent accidental sending of mails.\
By default, it is set to `true`;

- `debug/DebugSettings.php`:\
Const properties `XDEBUG_SESSION_START_VALUE` and `XDEBUG_KEY_VALUE`; Values used by the XDebug protocol. These change from IDE to IDE and can be customized.\
Default values are set, respectively, to `ECLIPSE_DBGP` and `157270718956123`;

- `vista/Layout.php`:\
Const property `DEBUG`; If enabled, each time a page is visited, a dump of it is written on a file (overwriting it each time) in the `debug/rwdir` directory.\
This mode was used to debug the Template language.\
By default, it is set to `false`;



### Notes

- The code was hosted on a Subversion repository, on a university server. At the time, I didn't know I could make a dump of the repository, as a consequence, I only own the final version of the platform;

- The PHPMailer library has been integrated in order to send mails, by using external SMTP servers. This version of the library is now considered obsolete.\
A copy of it is provided with this project in the zip format, but it can be still downloaded from [https://github.com/PHPMailer/PHPMailer/tree/v6.1.1](https://github.com/PHPMailer/PHPMailer/tree/v6.1.1);

- If the `DEBUG` property of `utils/Spedizioniere.php` is set to `false`, the Spedizioniere (Literally "Shipping agent", here intended as "Mail dispatcher"), must be properly configured, before it can be used to send mails;

- I had to fix a call to `implode` in `vista/Vista.php`. Unbelivably, this was the only call IN THE WHOLE PROJECT, to use the legacy syntax removed from PHP 8.0: `implode(array, string)`;

- To the international audience: Code and comments are written in Italian. When I developed this project, I didn't consider the possibility to publish it on the Internet.\
Sorry about that;
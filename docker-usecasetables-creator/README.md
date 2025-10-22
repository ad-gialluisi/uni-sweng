# Ingegneria del software: UseCaseTablesCreator

## Italiano

Progetto docker contenente una versione migliorata del tool **UseCaseTablesCreator**, nato dalla necessità di semplificarmi la costruzione dei casi d'uso (scenario principale) e i vari scenari alternativi ad essi associati, durante la stesura dei report.\
Fare manualmente tutte le tabelle avrebbe portato ad importanti perdite di tempo, nel momento in cui si fossero riscontrati errori.\
La soluzione adottata è stata quella di memorizzare i contenuti delle tabelle in file XML e quindi applicare un foglio di stile (XSLT) al momento opportuno.\
Ottenuto l'output in HTML, sarebbe stato usato Pandoc per la conversione in file .docx, così da semplificare l'operazione di copia-incolla sul documento Writer in lavorazione.

Per questa pubblicazione è stato deciso di migliorare il tool, sia graficamente, sia nello "stile di codifica", lasciando però intatte la funzionalità originali.\
Il software venne scritto in fretta e furia, in quanto ad esclusivo uso e consumo di me medesimo.\
Tra i difetti che ho corretto:


- Poca modularizzazione:
    - Tutto il codice Javascript era posto in un unico file;
    - Dei pochi fogli di stile presenti, la maggior parte era inserita all'interno dei file HTML, mediante tag &lt;style&gt;;
    - In generale, il codice PHP non era ben modularizzato, con diverse ripetizioni e magic constants;

- Gli shortcut erano tutti hardcoded e non facilmente configurabili;
- Codice e commenti erano scritti a volte in italiano, a volte in inglese;
- Gli shortcut "Make a new file", "Focus on file selector", "Toggle preview table language" e "Show shorcuts" non esistevano;
- Attualmente, la prima entry del selettore dei file, permette di crearne di nuovi, trasformando il pulsante "Edit" in "Make new". In origine, esisteva sia tale entry che un pulsante apposito per la creazione di nuovi file, rendendo totalmente inutile la suddetta entry;
- La disposizione degli elementi non era molto logica, rendendo l'UI poco intuitiva;
- Attualmente, l'editor è invisibile, a meno che non si crei un nuovo file, o se ne carichi uno esistente. In origine, esso era sempre visibile;
- Gli shortcut, venivano mostrati direttamente sulla pagina, non c'era il pulsante per mostrarli su un alert;
- Il testo della pagina e i campi delle tabelle di anteprima erano SOLO in italiano. Di conseguenza, non c'era il checkbox per forzare le tabelle in italiano, nè tantomeno gli script `make_tables_it.sh` e `make_tables_en.sh`;
- Gli script `show_docx_tables.sh` e `copy_xmls_to_tmp.sh` sono stati creati appositamente per questo progetto Docker;


- La disposizione e l'aspetto delle etichette e dei campi dell'editor erano molto approssimative:
   - Etichette e campi erano separati da uno o più tag &lt;br&gt;, senza alcun allineamento specifico;
   - Le &lt;textarea&gt; erano totalmente ridimensionabili, rendendole, in alcuni casi, difficili da usare;
   - I campi testuali non avevano colore di sfondo, rendendo difficile la distinzione tra campi abilitati e disabilitati;



### Progetto Docker

Grazie a Docker, è possibile eseguire questo tool senza preoccuparsi di dipendenze e configurazioni.\
Assicurarsi di aver installato Docker correttamente, quindi, fare quanto segue.\
Dapprima, eseguire la clonazione del repository:

```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-usecasetables-creator
```

Quindi eseguire il comando `make`.\
Il Makefile supporta i seguenti target:

- *stop*: Ferma il container attualmente in esecuzione;
- *rm*: Rimuove l'immagine Docker;
- *build*: Costruisce l'immagine Docker;
- *run*: Esegue un container della suddetta immagine;
- *all*: Esegue tutti i target prima citati nell'ordine: *stop*, *rm*, *build* e *run*;



Una volta in esecuzione, è sufficiente usare un browser e collegarsi all'indirizzo [http://localhost](http://localhost).\
Si noti che il tool viene fornito con tutti gli scenari che ho scritto per la piattaforma Cicerone.

La finestra di terminale avrà una sessione bash interattiva sulla cartella `cmdline-scripts`; Da qui, sarà possibile invocare diversi script (discussi in seguito).


### Utilizzo del tool

Rispetto al tool originale, questa versione è di gran lunga più semplice da utilizzare, in ogni caso, di seguito vengono offerte delle indicazioni.

Piccolo promemoria: Un caso d'uso rappresenta una funzionalità da realizzare, esso definisce chi è coinvolto nell'attività (attori), le condizioni (pre e post) che devono valere ed uno o più scenari, ovvero, l'insieme dei passi da seguire, per portare a compimento il caso d'uso.\
Si noti che ogni caso d'uso ha ALMENO un attore ed uno scenario.\
In caso di molteplici scenari, ce ne sarà uno principale, e 0 o più alternativi.

Nel tool, gli scenari si differenziano tra "caso d'uso" e "scenario alternativo", il primo corrisponde allo scenario principale ed i file corrispondenti, hanno un nome che inizia per "CU", al contrario, gli scenari alternativi hanno un nome file che inizia per "ALT".


#### Come creare un nuovo scenario

Assicurarsi di scegliere la prima entry del file selector, quindi cliccare su "Make new".\
Questo aprirà l'editor con i vari campi vuoti, pronti per essere compilati. Occorre salvare la prima volta, per garantire la memorizzazione: scrivere uno specifico ID, quindi cliccare su "Save".


#### Come salvare uno scenario

Una volta caricato/creato un nuovo scenario, è possibile modificare i vari campi.\
Una volta concluse le modifiche, cliccare su "Save" per memorizzarle all'interno del file XML.\
I file XML vengono salvati sul webserver stesso, nella cartella `xmls`; Se occorre, è bene eseguire una copia di tale cartella, sfruttando lo script `copy_xmls_to_tmp.sh`.

NOTA: Ricordare di SALVARE SEMPRE, questo tool non esegue salvataggi automatici e non dà indicazioni rispetto al fatto se un file ha subito modifiche dall'ultima volta che è stato salvato.


#### Come caricare uno scenario esistente

Nel file selector, scegliere lo scenario di interesse, quindi cliccare su "Edit".\
Questo aprirà l'editor con i vari campi compilati, in base al preciso scenario scelto.


#### Come mostrare l'anteprima di uno scenario

Come per il caricamento, scegliere lo scenario di interesse, quindi cliccare su "Preview", questo NON CARICHERÀ lo scenario sull'editor.\
Cliccando sul checkbox "Italian table", i campi delle tabelle saranno scritti in italiano (di default, sono scritti in inglese).


#### Come duplicare scenari

Per evitare di codificare — ai tempi — complesse routine di duplicazione, si è optato per un semplicissimo meccanismo di copia-incolla.\
Cliccando su "Copy fields (No ID)" è possibile memorizzare tutti i campi correnti, ad eccezione dell'ID; Fatto ciò, è sufficiente creare un nuovo scenario/caricarne uno esistente, e cliccare su "Paste fields (No ID)"; Questo comporterà la sovrascrittura di tutti i campi, ad eccezione dell'ID.


#### I campi di uno scenario

Qui segue una descrizione dei singoli campi testuali di uno scenario:

- *ID*: Identifica lo scenario specifico;
- *NAME*: Nome del caso d'uso/scenario;
- *DESCRIPTION*: Descrive lo scenario a grandi linee;
- *PRIMARY ACTORS*: Elenco degli attori che possono avviare lo scenario;
- *SECUNDARY ACTORS*: Elenco degli attori che sono indirettamente influenzati dallo scenario;
- *PRECONDITIONS*: Condizioni che devono valere perchè lo scenario possa avere inizio;
- *EXECUTION STEP*: Usato solo negli scenari alternativi (ALT), indica in quale passo dello scenario principale, ha inizio lo scenario alternativo (posto che le precondizioni di quest'ultimo siano soddisfatte);
- *SEQUENCE*: Elenca la sequenza di azioni da compiere;
- *POSTCONDITIONS*: Condizioni che devono valere a scenario concluso;
- *ALT. SEQUENCE*: Usato solo negli scenari principali (CU), indica la lista degli scenari alternativi correlati.

Si noti che TUTTI i campi qui descritti sono solo "convenzioni", nel senso che, non viene eseguito, a livello di codice, alcun controllo che faccia rispettare particolari formati.\
Ad esempio, in questo esame, è stata usata la convenzione che le singole cifre indicano scenari principali, mentre le cifre separate da punti quelli alternativi. La prima cifra di questi ultimi è l'ID dello scenario principale cui appartengono.\
Nonostante ciò però, a livello di codice, l'ID è trattato come una stringa.


### Utilizzo della riga di comando

Come già detto, la finestra di terminale, avrà una sessione bash interattiva sulla cartella `cmdline-scripts`.\
Gli script possono essere usati per esportare dati dal progetto Docker, o come supporto al lavoro.\
Qui di seguito, vengono descritti i diversi script disponibili:

- `make_tables_it.sh` e `make_tables_en.sh`: Si usano per generare i file `output.html` e `output.docx` contenenti — in formato tabellare — tutti gli scenari, sulla cartella `/tmp` del sistema;
- `show_docx_tables.sh`: Si usa per visualizzare su LibreOffice Writer (fornito con l'ambiente Docker) il documento `output.docx`;
- `copy_xmls_to_tmp.sh`: Si usa per ottenere una copia di tutti gli XML degli scenari, sulla cartella `/tmp` del sistema;


I seguenti script esistono, ma ne è sconsigliato l'uso:

- `make_tables.sh` e `make_tables.php`: Sono gli script che generano, effettivamente i file `output.html` ed `output.docx`. `makes_tables_it.sh` e `make_tables_en.sh` sfruttano `make_tables.sh` che a sua volta sfrutta `make_tables.php`;

- `clipboard_helper.sh`: Semplice script utilizzato durante la stesura degli scenari, per aggiungere stili alle parole (corsivo, sottolineato, grassetto, eccetera), fa uso dell'utility `xclip`.\
Per replicare questo tipo di funzionalità, si suggerisce l'uso di software di clipboard management, come l'ottimo [CopyQ](https://hluk.github.io/CopyQ/), che consente di memorizzare e ricaricare testi su clipboard all'occorrenza.\
Perchè creare questo script quindi? Banalmente, perchè ai tempi, ignoravo le potenzialità dei clipboard manager;


### Copyright

UseCaseTablesCreator e gli script di supporto sono distribuiti sotto licenza MIT.


## English

Docker project containing an improved version of the **UseCaseTablesCreator** tool, created out of necessity to simplify the making of use cases (main scenarios) and the alternative scenarios associated to them, during the reports' writing.\
Making all of the tables manually would've brought important waste of time, in case of errors.\
The adopted solution was to store the contents of the tables in XML files, and then, apply a style sheet (XSLT) at the right moment.\
After getting the HTML output, Pandoc would've been used to convert it into a .docx file, in order to simplify the copy-paste operation in the current working Writer document.

For this publication, it was decided to improve the tool, both graphically and in its "coding style", keeping all the original functionality intact.\
The software was written in a hurry, as it was intended solely for my own use.\
Among the flaws I fixed:


- Little to no modularization:
    - All the Javascript code was put into a single file;
    - Most of the stylesheets were put inside the HTML files, within &lt;style&gt; tags;
    - In general, the PHP code lacked modularization, there were lots of repetitions and magic constants;

- The shortcuts were all hardcoded and not easily configurable;
- Code and comments were sometimes written in Italian, and sometimes in English;
- The shortcuts "Make a new file", "Focus on file selector", "Toggle preview table language" and "Show shorcuts" didn't exist;
- Currently, the first entry of the file selector gives the ability to make new files, by transforming the "Edit" button into "Make new". Originally, both the entry and an actual "make a new file" button existed, making the mentioned entry useless;
- The elements' disposition was not very logical, making the UI not very intuitive;
- Currently, the editor is invisible, unless a new file is created, or an existent one is loaded. Originally, it was always visible;
- The shortcuts, were directly reported on the page, there was no button to show them on an alert;
- The page's text and the preview tables' fields were ONLY in Italian. Consequently, there was no checkbox to force tables in Italian, neither the scripts `make_tables_it.sh` and `make_tables_en.sh`;
- The scripts `show_docx_tables.sh` and `copy_xmls_to_tmp.sh` were created specifically for this Docker project;

- The disposition, and the appearence of the editor's labels and fields, were very superficial:
    - Labels and fields were separated by one or more &lt;br&gt; tags, with no alignment;
    - The &lt;textarea&gt;s were totally resizable, making them difficult to use, in some cases;
    - The textual fields didn't have any background color, making it difficult to distinguish, between enabled ones and disabled ones;




### Docker project

Thanks to Docker, it is possible to run this tool, without worrying about dependencies and configurations.\
Be sure to install Docker correctly, then, do as follows.\
First, clone this repository:

```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-usecasetables-creator
```

Then, run the command `make`.\
The Makefile supports the following targets:

- *stop*: It stops the currently running container;
- *rm*: It removes the Docker image;
- *build*: It builds the Docker image;
- *run*: It runs a container out of the above-mentioned image;
- *all*: It runs all the above-mentioned targets in the order: *stop*, *rm*, *build* and *run*;



Once up and running, use a browser to connect to the address [http://localhost](http://localhost).\
Please note that the tool comes with all the scenarios I made for the Cicerone platform.

The terminal window will have an interactive bash session in the directory `cmdline-scripts`; From here, it is possible to run different scripts (discussed later).


### Usage of the tool

In respect to the original tool, this version is way easier to use, in any case, here follows some indications.

Little reminder: An use case represents a functionality to be implemented, it defines who is involved in the activity (actors), the conditions (pre and post) that must hold true and one or more scenarios, that is, the set of steps to perform, to bring the use case to completion.\
Please note that every use case has AT LEAST one actor and one scenario.\
In case of multiple scenarios, there will be a main one and 0 or more alternative ones.

In this tool, the scenarios are categorized as "use case" and "alternative scenario", the first corresponds to the main scenario and its files, have a name which starts with "CU", on the other hand, the alternative scenarios, have a file name which starts with "ALT".


#### How to make a new scenario

Be sure to choose the first entry of the file selector, then, click on "Make new".\
This will open the editor with all the fields, ready to be filled. Save it once in order to store it: write a specific ID, then click on "Save".


#### How to save a scenario

Once a new scenario is created/an existing one is loaded, it is possible to edit the different fields.\
Once modifications are concluded, click on "Save", to store them on a XML file.\
The files are saved on the webserver itself, in the `xmls` directory; If needed, perform a copy of said directory, by using the `copy_xmls_to_tmp.sh` script.

NB: Remember to ALWAYS SAVE, this tool does not perform auto-saves, neither it indicates if a file was modified since its last save.


#### How to load an existing scenario

In the file selector, choose a scenario of interest, then click on "Edit".\
This will open the editor, with all the filled fields, according to the chosen scenario.


#### How to preview a scenario

As for the loading, choose a scenario of interest, then click on "Preview", this WON'T LOAD the scenario on the editor.\
By clicking on the "Italian table" checkbox, the tables' fields will be written in Italian (by default, they'll be written in English).


#### How to duplicate scenarios

In order to avoid — at the time — the coding of complex duplication routines, it was opted for a very simple copy-paste mechanism.\
By clicking on "Copy fields (No ID)" it is possible to store the current fields, except for the ID, into memory; After that, just make a new scenario/load an existing one, and click on "Paste fields (No ID)"; This will overwrite all the fields, except for the ID.


#### The fields of a scenario

Here follows a description of the single textual fields belonging to a scenario:

- *ID*: It identifies the specific scenario;
- *NAME*: Name of the use case/scenario;
- *DESCRIPTION*: It describes the scenario in broad terms;
- *PRIMARY ACTORS*: List of actors that might start the scenario;
- *SECUNDARY ACTORS*: List of actors that are indirectly affected by the scenario;
- *PRECONDITIONS*: Conditions that must hold true for the scenario to start;
- *EXECUTION STEP*: It is used only in alternative scenarios (ALT), it indicates in which particular step of the main scenario, this scenario may take place (assuming its preconditions hold true, of course);
- *SEQUENCE*: The sequence of actions to perform;
- *POSTCONDITIONS*: Conditions that must hold true when the scenario is concluded;
- *ALT. SEQUENCE*: It is used only in main scenarios (CU), it indicates the list of the related alternatives scenarios;

Please note that ALL the fields here described are just "conventions", in the sense that, there's no code check that enforces the usage of particular formats.\
For example, in this exam, the established convention is that single digits indicate main scenarios and dotted-separated digits indicate alternative ones. Their first digit represents the related main scenario's ID.\
Despite that though, the ID is treated as string, at code level.


### Usage of the command line

As already stated, the terminal window, will have an interactive bash session in the directory `cmdline-scripts`.\
The scripts may be used to export data from the Docker project or, as support during the work.\
Here follows, a description of the available scripts:

- `make_tables_it.sh` and `make_tables_en.sh`: These are used to generate the files `output.html` and `output.docx` containing — in tabular form — all the scenarios, on the system directory `/tmp`;
- `show_docx_tables.sh`: It is used to show the document `output.docx` on  LibreOffice Writer (provided by this Docker environment);
- `copy_xmls_to_tmp.sh`: It is used to get a copy of all the scenarios' XML files, on the system directory `/tmp`;


The following scripts exist, but it's best not to use them:

- `make_tables.sh` and `make_tables.php`: These scripts actually generate the `output.html` and `output.docx` files. `makes_tables_it.sh` and `make_tables_en.sh` makes use of `make_tables.sh` which in turn, makes use of `make_tables.php`;

- `clipboard_helper.sh`: Simple script used during the writing of scenarios, to add styles to words (italic, underline, bold, etcetera), it uses the `xclip` utility.\
To replicate this functionality, it is recommended to use clipboard management software, such as the great [CopyQ](https://hluk.github.io/CopyQ/), which gives the ability to store and reload texts on clipboard, when needed.\
Why make this script then? Simply put, at the time, I didn't know the potentiality of clipboard maangers;


### Copyright

UseCaseTablesCreator and its support scripts are distributed under the MIT license.
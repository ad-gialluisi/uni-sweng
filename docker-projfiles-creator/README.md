# Ingegneria del software: projfiles-creator

## Italiano

Progetto docker basato su Debian 12 "Bookworm", che installa — dai repository ufficiali — i software MuPDF, Firefox, XTerm, Pandoc, Make, Dia, PcManFM, GPicView, Geany e LibreOffice Writer, nonchè, le dipendenze necessarie ad eseguire i seguenti software, forniti appositamente:

- [Wkhtmltox 0.12.6.1-3 (con qt pachato)](https://github.com/wkhtmltopdf/packaging/releases/tag/0.12.6.1-3), distribuito sotto licenza GPLv3 ([licenses/GPLv3.txt](licenses/GPLv3.txt)).\
Insieme a Pandoc, è stato usato per generare vari file di documentazione, partendo da alcuni sorgenti in Markdown;

- [PlantUML 1.2025.7](https://github.com/plantuml/plantuml/releases/tag/v1.2025.7), distribuito sotto licenza GPLv3 ([licenses/GPLv3.txt](licenses/GPLv3.txt)).\
Usato per trasformare in immagini gli script dei diagrammi di sequenza, in quanto più semplici da realizzare, rispetto all'uso di VisualParadigm/Dia.\
Ai tempi, venne usata una versione più vecchia, ma non son state trovate differenze significative;

- [Pencil 3.1.1](https://pencil.evolus.vn/Downloads.html), distribuito sotto licenza GPLv2 ([licenses/GPLv2.txt](licenses/GPLv2.txt)).\
Usato per costruire le mockup delle diverse pagine. Ai tempi è stata usata la versione 3.1.0, ma non ci sono differenze significative;


### Progetto Docker

Grazie a Docker, è possibile controllare i materiali prodotti, senza dover installare appositamente i singoli programmi.\
Assicurarsi di aver installato Docker correttamente, quindi, fare quanto segue.\
Dapprima, eseguire la clonazione del repository:

```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-projfiles-creator
```

Quindi eseguire il comando `make`.\
Il Makefile supporta i seguenti target:

- *stop*: Ferma il container attualmente in esecuzione;
- *rm*: Rimuove l'immagine Docker;
- *build*: Costruisce l'immagine Docker;
- *run*: Esegue un container della suddetta immagine;
- *all*: Esegue tutti i target prima citati nell'ordine: *stop*, *rm*, *build* e *run*;



Una volta avviato il container, verrà aperta un'istanza di PcManFM ed una sessione bash.\
Usare PcManFM per consultare i materiali montati sulla cartella `/root/materials`, mediante doppio-click o tasto destro seguito dall'applicazione specifica; Eccezione a questa regola sono i file di Pencil e PlantUML: in questo caso, occorre andare in `/root`, eseguire il file .desktop corrispondente, quindi dal software stesso, andare ai file di interesse ed aprirli.\
I file apribili con doppio click sono:

- Tutti i file pdf;
- Tutti i file odt;
- Tutti i file di immagini;
- Tutti i file dia;
- I file html/txt/markdown;


Infine, è possibile eseguire il comando `make` nelle cartelle in cui è presente un Makefile. Molti di essi generano documenti in pdf (tramite pandoc + wkhtmltopdf).\
L'ideale per questa situazione è usare l'opzione `Tools->Open Current Folder in Terminal` di PcManFM, quindi digitare `make` in XTerm.


### Note

- Si noti che è possibile rimuovere/sovrascrivere file dei materiali accidentalmente/volutamente, durante l'uso di questo progetto Docker.\
Se dovesse succedere e si intende ripristinare, uscire dall'ambiente Docker ed usare il comando:

    ```
./restore-materials.sh
```


- L'unico file di progetto non apribile con questo ambiente è quello VisualParadigm;



## English

Docker project based upon Debian 12 "Bookworm", installing — from official repositories — MuPDF, Firefox, XTerm, Pandoc, Make, Dia, PcManFM, GPicView, Geany and LibreOffice Writer.\
It also installs all the necessary dependencies to run the following softwares, provided specifically for this:

- [Wkhtmltox 0.12.6.1-3 (with patched-qt)](https://github.com/wkhtmltopdf/packaging/releases/tag/0.12.6.1-3), distributed under GPLv3 license ([licenses/GPLv3.txt](licenses/GPLv3.txt)).\
This, together with Pandoc, was used to generate various documentation files, starting from some Markdown sources;

- [PlantUML 1.2025.7](https://github.com/plantuml/plantuml/releases/tag/v1.2025.7), distributed under GPLv3 license ([licenses/GPLv3.txt](licenses/GPLv3.txt)).\
This was used to make images out of sequence diagrams scripts, as they were easier to make, in respect to using VisualParadigm/Dia.\
At the time, an older version was used, but no significant differences were found;

- [Pencil 3.1.1](https://pencil.evolus.vn/Downloads.html), distributed under GPLv2 license ([licenses/GPLv2.txt](licenses/GPLv2.txt)).\
This was used to make the different pages' mockups. At the time, the 3.1.0 version was used, but there are no significant differences;


### Docker project

Thanks to Docker, it is possible to check the produced materials, without specifically install the single programs.\
Be sure to install Docker correctly, then, do as the following.\
First, clone this repository:

```
git clone https://github.com/ad-gialluisi/uni-sweng
cd uni-sweng/docker-projfiles-creator
```

Then, run the command `make`.\
The Makefile supports the following targets:

- *stop*: It stops the currently running container;
- *rm*: It removes the Docker image;
- *build*: It builds the Docker image;
- *run*: It runs a container out of the above-mentioned image;
- *all*: It runs all the above-mentioned targets in the order: *stop*, *rm*, *build* and *run*;


Once the container is up, an instance of PcManFM and a bash session will be running.\
Use PcManFM to consult the materials mounted on the directory `/root/materials`, by double-clicking or right-clicking followed by the specific application; Exception to this rule are the files for Pencil and PlantUML: in this case, go to `/root`, run the corresponding .desktop file, and finally, from the software itself, go to the files of interest and open them.\
The files that can be opened by double-clicking are:

- All pdf files;
- All odt files;
- All image files;
- All dia files;
- All html/txt/markdown files;


Finally, it is possible to run the command `make` in directories where a Makefile is present. Most of them generate pdf documents (by using pandoc + wkhtmltopdf).\
The best course of action for this, is to use PcManFM's `Tools->Open Current Folder in Terminal` option and in XTerm, type `make`.


### Notes

- Please note that it is possible to remove/overwrite material files accidentally/voluntarily when using this Docker project.\
If that happens and there's the need to restore everything, exit from the Docker environment and use the command:

    ```
./restore-materials.sh
```


- The only project file that can't be opened with this environment, is the VisualParadigm one;


- To the international audience: Most of the documentation (`Materiali` directory) is written in Italian. When I made it, I didn't consider the possibility to publish it on the Internet.\
Sorry about that;
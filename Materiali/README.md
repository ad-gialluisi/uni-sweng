# Progetto "Cicerone": Esame di Ingegneria del software

## Italiano

### Cartella "Materiali"

Questa cartella contiene tutti i file di progetto creati/sviluppati durante l'esame.

Tra i contenuti ci sono:


- La documentazione finale (cartella `Documentazione_finale`): l'insieme dei file che componevano la consegna, ovvero:
    - `Product_Backlog.odt`: Come da terminologia SCRUM, l'insieme delle caratteristiche/requisiti previsti per il progetto, a lungo termine;
    - I singoli `Sprint_Report`: ovvero, i report delle singole sessioni di lavoro. Oltre ai file di progetto (.odt, LibreOffice Writer), vengono anche distribuiti — separatamente — i diagrammi e i documenti esportati come pdf;

- Dump dei dati del Redmine "universitario" (cartella `Dump_RedmineUnivInstall`): Pagine HTML contenenti informazioni sul progetto svolto. L'esame prevedeva l'uso di Subversion e Redmine, ambedue ospitati su server dell'università. Non avendo accesso diretto a questi server, ho sfruttato l'accoppiata Firefox + script di GreaseMonkey per fare webscraping dei dati e generare queste pagine.\
Ho utilizzato tali dump durante la stesura dei report, in quanto i server avevano la brutta abitudine di diventare irrangiungibili;


- I file di progetto (cartella `Files_Progettuali`): Contiene tutti i file utilizzati per creare la consegna. Tra questi:
    - Assets: Alcuni file grafici;
    - Diagrammi: Tutti i file progettuali dei diagrammi UML.
        - Il file di progetto VisualParadigm (usato nell'esame);
        - Tutti i diagrammi di sequenza creati con PlantUML (usati nell'esame);
        - Tutti i diagrammi creati con Dia, per singolo Sprint (creati appositamente per questa pubblicazione);
        - Il diagramma delle dipendenze dei dati, creato con Dia (usato nell'esame);
    - Lavorazione_notifiche: Codice di gestione delle notifiche mai concluso, con alcuni diagrammi di sequenza (PlantUML);
    - LingFormale_Template: Progetto Dia e specifica del "linguaggio Template". Il templating system adoperato in questa piattaforma;
    - Manuale_utilizzo: Creato per la consegna di allora, contiene molte imprecisioni;
    - Mockup_UI: Progetto Pencil ed immagini esportate che rappresentano le pagine previste;



### Note

- È possibile consultare in maniera semplice questi file — ad eccezione del progetto Visual Paradigm — sfruttando il progetto docker `projfiles-creator`;

- Durante il ciclo di sviluppo, sono state create diverse versioni degli sprint report e del product backlog.\
L'insieme di questi file non è stato preservato.




## English

### "Materiali" directory

This directory contains all the project files made/developed during the exam.

Among the contents, there is:


- The final documentation (`Documentazione_finale` directory): that is, the set of assignment materials, that is:
    - `Product_Backlog.odt`: As per the SCRUM terminology, the set of expected characteristics/requirements for the project, on the long-term;
    - The single `Sprint_Report`s: that is, all the reports regarding the single work sessions. In addition to project files (.odt, LibreOffice Writer), both diagrams and the resulting pdf files are also distributed, separately;


- Data dump of the "university" Redmine (`Dump_RedmineUnivInstall` directory): These are HTML pages containing information on the project. The exam required using Subversion and Redmine, both hosted on university servers. Since I didn't have any access to these, I used Firefox + GreaseMonkey script in order to webscrape, and finally, generate the pages.\
I've used these dumps during the writing of the reports, as servers tended to become unreachable;


- The project files (`Files_Progettuali` directory): It contains all the files used for making the assignment materials. Among these:
    - Assets: Some graphical files;
    - Diagrams: All the UML diagrams project files.
        - The VisualParadigm project file (used in the exam);
        - All the PlantUML sequence diagrams (used in the exam);
        - All the diagrams made with Dia, per single sprint (made specifically for this publication);
        - The dependency diagram for the data, made with Dia (used in the exam);
    - Lavorazione_notifiche: Notification management code that was never completed, together with some sequence diagrams (PlantUML);
    - LingFormale_Template: Dia project and specifications for the "Template language". The templating system used in the platform;
    - Manuale_utilizzo: This is the manual created as part of the assignment materials, it contains many mistakes;
    - Mockup_UI: Pencil project (.egpz) and exported images representing the expected user pages;



### Notes

- To consult these files — except for the Visual Paradigm project — use the `projfiles-creator` Docker Project;

- During the development cycle, different versions of the sprint reports and the product backlog were made, but they weren't preserved.

- To the international audience: Most of the documentation is written in Italian. When I made it, I didn't consider the possibility to publish it on the Internet.\
Sorry about that;
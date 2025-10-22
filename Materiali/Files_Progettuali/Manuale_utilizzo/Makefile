all:
	pandoc -s -f markdown -t html -H header.html INSTALL -o INSTALL.html -M title="Manuale d'installazione del Sistema Cicerone"
	wkhtmltopdf --enable-internal-links --enable-external-links INSTALL.html INSTALL.pdf

all:
	pandoc -s -f markdown -t html -H header.html specifica.md -o output.html -M title="Linguaggio Template"
	wkhtmltopdf --enable-local-file-access --enable-internal-links --enable-external-links output.html linguaggio_template.pdf 2> /dev/null

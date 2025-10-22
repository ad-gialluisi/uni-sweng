#!/bin/bash

# Copyright (c) 2025 Antonio Daniele Gialluisi

# This file is part of "UseCaseTableCreator"

# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:

# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.


english_mode=""
if [ "$#" -eq 1 -a "$1" = "-eng" ]
then
    english_mode=0
fi

lang_mode=-ita
[ "$english_mode" ] && lang_mode=-eng

php make_tables.php "$lang_mode" > /shared/output.html
pandoc -f html -t docx /shared/output.html -o /shared/output.docx

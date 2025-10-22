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


echo "Use"
echo "a for \"<b><u>SOMETHING</u></b>\""
echo "b for \"<i><u>SOMETHING</u></i>\""
echo "c for \"<i><b>SOMETHING</b></i>\""
echo "u for \"<u>SOMETHING</u>\""
echo "i for \"<i>SOMETHING</i>\""
echo "b for \"<b>SOMETHING</b>\""


while true; do
    read -rsn1 input
    if [ "$input" = "a" ]
    then
        echo -n "<b><u>SOMETHING</u></b>" | xclip -selection clipboard -i
    elif [ "$input" = "b" ]
    then
        echo -n "<i><u>SOMETHING</u></i>" | xclip -selection clipboard -i
    elif [ "$input" = "c" ]
    then
        echo -n "<i><b>SOMETHING</b></i>" | xclip -selection clipboard -i
    elif [ "$input" = "u" ]
    then
        echo -n "<u>SOMETHING</u>" | xclip -selection clipboard -i
    elif [ "$input" = "i" ]
    then
        echo -n "<i>SOMETHING</i>" | xclip -selection clipboard -i
    elif [ "$input" = "b" ]
    then
        echo -n "<b>SOMETHING</b>" | xclip -selection clipboard -i
    fi
done



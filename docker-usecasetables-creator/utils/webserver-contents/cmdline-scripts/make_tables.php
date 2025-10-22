#!/usr/bin/php
<?php
    // Copyright (c) 2025 Antonio Daniele Gialluisi

    // This file is part of "UseCaseTableCreator"

    // Permission is hereby granted, free of charge, to any person obtaining a copy
    // of this software and associated documentation files (the "Software"), to deal
    // in the Software without restriction, including without limitation the rights
    // to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    // copies of the Software, and to permit persons to whom the Software is
    // furnished to do so, subject to the following conditions:

    // The above copyright notice and this permission notice shall be included in all
    // copies or substantial portions of the Software.

    require_once "../utils.php";

    $english_mode = true;
    if ($argc === 2) {
        if ($argv[1] === "-ita") {
            $english_mode = false;
        }
    }



    $files = get_files();

    $html_data = "";

    foreach ($files as $id => $file) {
        foreach ($file as $type) {
            $html_data .= xml_to_html_table($type . "_" . $id, $english_mode);
            $html_data .= "\n<br>\n<br>\n<br>\n<br>";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html"/>
        <meta charset="utf-8"/>
        <meta content='width=device-width,initial-scale=1.0' name='viewport'/>
        <title>TOTAL DUMP</title>
        <link href="../single-table-style.css" rel="stylesheet"/>
        <style>
            td {
                text-align: center
            }
        </style>
    </head>
    <body>
        <?php echo $html_data; ?>
    </body>
</html>

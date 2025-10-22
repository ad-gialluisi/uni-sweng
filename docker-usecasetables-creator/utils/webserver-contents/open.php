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

    require_once "utils.php";
    $page = $_GET["page"];
    $lang = $_GET["lang"];

    //var_dump($_GET);

    //header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html"/>
        <meta charset="utf-8"/>
        <meta content='width=device-width,initial-scale=1.0' name='viewport'/>
        <title><?php echo $page;?></title>
        <link href="styles/single-table-style.css" rel="stylesheet"/>
    </head>
    <body>
        <?php echo xml_to_html_table($page, $lang !== "ita"); ?>
    </body>
</html>

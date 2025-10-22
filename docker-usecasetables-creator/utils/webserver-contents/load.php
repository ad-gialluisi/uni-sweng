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

    $page = $_GET["page"];

    //var_dump($_GET);

    $xml = simplexml_load_file("xmls/" . $page . ".xml");

    preg_match("#(CU|ALT)_((?:\d+\.)*\d+)#", $page, $result);
    $type = $result[1];
    $id = $result[2];

    //var_dump($result);

    //echo "type is \"$type\"\n";
    //echo "id is \"$id\"\n";
    //echo "page is \"$page\"\n";

    $xml_array = array(
        "id" => (string)$xml->id,
        "name" => (string)$xml->name,
        "description" => (string)$xml->description,
        "primary_actors" => (string)$xml->{"primary-actors"},
        "secondary_actors" => (string)$xml->{"secondary-actors"},
        "preconditions" => (string)$xml->preconditions,
        "sequence" => (string)$xml->sequence,
        "postconditions" => (string)$xml->postconditions
    );

    if ($type === "CU") {
        $xml_array["alternative_sequences"] = (string)$xml->{"alternative-sequences"};
    } else if ($type === "ALT") {
        $xml_array["execution_step"] = (string)$xml->{"execution-step"};
    }

    $xml_array["scenario_type"] = $type;
    $xml_array["scenario_id"] = $id;

    header("Content-Type: application/json");
    echo json_encode($xml_array);

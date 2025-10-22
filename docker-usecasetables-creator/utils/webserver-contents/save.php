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



    //debug();

    function make_xml($filename) {
        $xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";

        $fields = array(
            "id", "name", "description", "primary_actors", "secondary_actors",
            "preconditions", "execution_step", "sequence", "postconditions",
            "alternative_sequences"
        );

        $selected_scentype = $_POST["selected-scentype"];

        $xml_data .= "<$selected_scentype>\n";

        foreach ($fields as $key => $value) {
            // Ignore specific scenario-type keys (alternative sequences does not exist for ALT, nor does execution_step for CU)
            if (($selected_scentype === "ALT" && $value === "alternative_sequences") ||
                ($selected_scentype === "CU" && $value === "execution_step")) {
                continue;
            }
            $value = str_replace("_", "-", $value);
            $xml_data .= "<$value><![CDATA[${_POST[$value]}]]></$value>\n";
        }

        $xml_data .= "</$selected_scentype>\n";

        $f = fopen(sprintf("xmls/%s", $filename), "w");
        fwrite($f, $xml_data);
        fclose($f);
    }


    function signal_file_exists($filename) {
        header("Content-Type: application/json");
        echo json_encode(array("filename" => $filename,
            "status" => "file_exists"
        ));
    }


    function signal_success($filename) {
        header("Content-Type: application/json");
        echo json_encode(array("filename" => $filename,
            "status" => "success"
        ));
    }


    function signal_success_removal($old_filename, $filename) {
        header("Content-Type: application/json");
        echo json_encode(array(
            "oldfilename" => $old_filename,
            "filename" => $filename,
            "status" => "success"
        ));
    }


    function debug() {
        echo var_dump($_POST);
        exit();
    }




    $selected_scentype = $_POST["selected-scentype"];
    $scenario_type = $_POST["scenario-type"];
    $scenario_id = $_POST["scenario-id"];
    $id = $_POST["id"];


    if ($scenario_type === "") {
        //New scenario
        $filename = sprintf("%s_%s.xml", $selected_scentype, $id);

        if (file_exists("xmls/$filename")) {
            signal_file_exists($filename);
        } else {
            make_xml($filename);
            signal_success($filename);
        }

    } else {
        //Scenario already existing
        if (($selected_scentype === $scenario_type) && ($id === $scenario_id)) {
            $filename = sprintf("%s_%s.xml", $selected_scentype, $id);
            make_xml($filename);
            signal_success($filename);

        } else {
            // Scenario id/type was changed
            $old_filename = sprintf("%s_%s.xml", $scenario_type, $scenario_id);
            $filename = sprintf("%s_%s.xml", $selected_scentype, $id);

            if (file_exists("xmls/$filename")) {
                signal_file_exists($filename);
            } else {
                unlink("xmls/$old_filename");
                make_xml($filename);
                signal_success_removal($old_filename, $filename);
            }
        }
    }

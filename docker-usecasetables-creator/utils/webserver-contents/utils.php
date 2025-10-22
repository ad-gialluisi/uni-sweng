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



    function get_files() {
        $files = array();
        $dir = opendir(__DIR__ . "/xmls");

        while (($file = readdir($dir)) != null) {
            if (preg_match("#(CU|ALT)_((?:\d+\.)*\d+).xml#", $file, $result)) {
                $type = $result[1];
                $id = $result[2];

                if (!isset($files[$id])) {
                    $files[$id] = array($type);
                } else {
                    $files[$id][]= $type;
                }
            }
        }

        // First sort by keys, in increasing order (1, 1.1, 2, 2.1, etc...)
        ksort($files);

        // Then, sort by value by rearranging the keys in decreasing order (from ALT, CU to CU, ALT...)
        foreach ($files as $key => $arr) {
            rsort($files[$key]);
        }

        return $files;
    }


    function xml_to_html_table($xmlfile, $english=true) {
        preg_match("#(CU|ALT)_((?:\d+\.)*\d+)#", $xmlfile, $result);

        $type = $result[1];
        $id = $result[2];

        $xmlfile = __DIR__ . "/xmls/$xmlfile.xml";
        if ($english) {
            $xslfile = __DIR__ . "/styles/table-style-en.xsl";
        } else {
            $xslfile = __DIR__ . "/styles/table-style.xsl";
        }

        //QUICKFIX, done to avoid wasting time with XSLT (why? I can't remember)
        $xml = simplexml_load_file($xmlfile);

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

        $xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
        $xml_data .= "<$type>\n";

        foreach ($xml_array as $key => $value) {
            $key = str_replace("_", "-", $key);

            if ($key !== "id" && $key !== "name" && $value !== "") {
                //echo ($value . "<br>");

                $sentences = explode("\n", $value);
                if (count($sentences) > 0) {
                    //$value = str_replace("\n", "</br>", $value);

                    foreach ($sentences as $idx => $sentence) {
                        $sentences[$idx] = "<p>$sentence</p>";
                    }
                    $value = implode("", $sentences);
                }
            }

            $xml_array[$key] = $value;
            //$xml_array[$key] = str_replace("\n<br>", "<br>", $value);
            $xml_data .= "<$key><![CDATA[${xml_array[$key]}]]></$key>\n";
        }

        $xml_data .= "</$type>";

        //var_dump($xml_data);

        $dom = new DOMDocument();
        $dom->loadXML($xml_data);
        $xslt = new XSLTProcessor();

        $xsl = new DOMDocument();
        $xsl->load($xslfile);
        $xslt->importStylesheet($xsl);

        return $xslt->transformToXML($dom);
    }

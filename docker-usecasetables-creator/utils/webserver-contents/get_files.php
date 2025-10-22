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

    $files = get_files();

    // Properly sort the files
    $files_sorted = array();
    foreach($files as $id => $types) {
        foreach($types as $type) {
            $files_sorted[]= array($type, $id, sprintf("%s_%s", $type, $id));
        }
    }

    header("Content-Type: application/json");
    echo json_encode($files_sorted);

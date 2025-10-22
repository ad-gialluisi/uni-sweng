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

function make_selector_files() {
    global $files;

    foreach($files as $key => $arr) {
        foreach($arr as $value) {
            $filename = $value . "_" . $key;
            printf("<option value=\"%s\">%s</option>", $filename, $filename);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta content='width=device-width,initial-scale=1.0' name='viewport'/>
    <title>UseCaseTablesCreator</title>
    <link href="styles/index-style.css" rel="stylesheet">
    <script src="js/globals.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/button-actions.js"></script>
    <script src="js/shortcuts.js"></script>
    <script src="js/entrypoint.js"></script>
</head>
<body>
    <h1>UseCaseTablesCreator</h1>

    <div id="control-panel">
        <h3>Control panel</h3>

        <button class="reminder-btn" onclick="show_scuts_reminder()">Show shortcuts</button>
        <button class="hidden" onclick="debug()">Debug button</button>

        <div class="field">
            <span class="label">FILE SELECTOR:</span>
            <select id="file-selector">
                <option value='__none__'></option>
                <?php make_selector_files(); ?>
            </select>
            <button id="btn-edit" onclick="load_file()">Make new</button>
        </div>

        <div class="actions-panel2">
         <button onclick="preview_file()">Preview</button> <input type="checkbox" id="lang-checkbox"/> <label for="lang-checkbox">Italian table</label>
        </div>
    </div>

    <div class="hidden" id="fields-editor">
        <h3>Editing: <span id="title">Unsaved scenario</span></h3>

        <div class="actions-panel"><button onclick="save_file()">Save</button> <button id="btn-copy" onclick="copy_fields()">Copy fields (No ID)</button> <button id="btn-paste" onclick="paste_fields()">Paste fields (no ID)</button></div>

        <input id="scenario-type" type="hidden" name="scenario-type" value=""/>
        <input id="scenario-id" type="hidden" name="scenario-id" value=""/>

        <div class="field"><span class="label">TYPE:</span>
        <select id="scenario-type-selector" onchange="change_scenario_type()">
            <option value="CU">Use case</option>
            <option value="ALT">Alternative scenario</option>
        </select></div>
        <div class="field"><span class="label">ID:</span> <input id="id-edit" type="text" name="id" value=""/></div>
        <div class="field"><span class="label">NAME:</span> <input id="name-edit" type="text" name="name" value=""/></div>
        <div class="field"><span class="label">DESCRIPTION:</span>
        <textarea id="description-edit" name="description"></textarea></div>
        <div class="field"><span class="label">PRIMARY ACTORS:</span> <input id="primary-actors-edit" type="text" name="primary-actors" value=""/></div>
        <div class="field"><span class="label">SECONDARY ACTORS:</span> <input id="secondary-actors-edit" type="text" name="secondary-actors" value=""/></div>
        <div class="field"><span class="label">PRECONDITIONS:</span> <textarea id="preconditions-edit" name="preconditions"></textarea></div>
        <div class="field"><span class="label">EXECUTION STEP:</span> <input id="execution-step-edit" type="text" name="execution-step"  disabled="disabled" value=""/></div>
        <div class="field"><span class="label">SEQUENCE:</span> <textarea id="sequence-edit" name="sequence"></textarea></div>
        <div class="field"><span class="label">POSTCONDITIONS:</span> <textarea id="postconditions-edit" name="postconditions"></textarea></div>
        <div class="field"><span class="label">ALT. SEQUENCE.:</span> <input id="alternative-sequences-edit" type="text" name="alternative-sequences" value=""/></div>
    </div>
    </body>
</html>

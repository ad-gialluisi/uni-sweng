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



/*
 * Button-related actions
 */
function load_file() {
    if (fields_editor_visible && !confirm("The latest non-saved modifications will be lost.\nProceed?")) {
        return;
    }

    // Remove initial hidden
    fields_editor.className = "";
    fields_editor_visible = true;

    var selected_file = get_selected_file();
    if (selected_file === null) {
        hidden_references["scenario_id"].value = "";
        hidden_references["scenario_type"].value = "";

        title.innerText = "Unsaved scenario";
        clear_fields();

    } else {
        perform_xml_load(selected_file);
    }
}


function preview_file() {
    var options = file_selector.options;
    var selected = file_selector.selectedIndex;

    if (selected === 0) {
        window.alert("Select a saved scenario!");
        return;
    }

    var page = options[selected].value;
    var lang = get_preview_selected_lang();

    window.open("open.php?page=" + page + "&lang=" + lang, "_blank");
}


function copy_fields() {
    for (var key in field_references) {
        if (key !== "id") {
            fields_copy[key] = field_references[key].value;
        }
    }
}


function paste_fields() {
    if (confirm("All the current fields (except the ID) will be overwritten. Proceed?")) {
        for (var key in fields_copy) {
            field_references[key].value = fields_copy[key];
        }
    }
}


function change_scenario_type() {
    var options = scenario_type_selector.options;
    var selected = scenario_type_selector.selectedIndex;

    if (options[selected].value === "CU") {
        field_references["execution_step"].disabled = true;
        field_references["alternative_sequences"].disabled = null;

    } else if (options[selected].value === "ALT") {
        field_references["execution_step"].disabled = null;
        field_references["alternative_sequences"].disabled = true;
    }
}


function save_file() {
    if (!fields_editor_visible) {
        window.alert("Load an existing scenario or make a new one!");
        return;
    }

    if (scentype_or_id_have_changed()) {
        if (!confirm("ID or scenario type was modified, this means that the resultant file will be different from the current one.\nProceed?")) {
            return;
        }
    }

    if (field_references["id"].value.trim() === "") {
        window.alert("Insert a valid Scenario ID!");
        return;
    }

    perform_xml_save(make_request_body_from_fields());
}




function newfile_scut() {
    file_selector.selectedIndex = 0;
    load_file();
}

function focus_fileselector_scut() {
    file_selector.focus();
    file_selector.click();
}

function toggle_lang_checkbox() {
    lang_checkbox.checked = !lang_checkbox.checked;
}
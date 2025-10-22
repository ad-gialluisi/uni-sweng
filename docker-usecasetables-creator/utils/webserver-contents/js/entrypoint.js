// Copyright (c) 2025 Antonio Daniele Gialluisi

// This file is part of "UseCaseTableCreator"

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all



/*
 * Entrypoint
 */
window.onload = function() {
    /*
     * Collect all the references to the elements of the page
     */
    for (var i = 0; i < FIELD_NAMES.length; i++) {
        var field = FIELD_NAMES[i];
        field_references[field] = document.getElementById(
            field.replace("_", "-") + "-edit");
    }

    for (var i = 0; i < HIDDEN_FIELD_NAMES.length; i++) {
        var hidden = HIDDEN_FIELD_NAMES[i];
        hidden_references[hidden] = document.getElementById(
            hidden.replace("_", "-"));
    }

    file_selector = document.getElementById("file-selector");
    file_selector.addEventListener("change", update_btn_edit_label);

    scenario_type_selector = document.getElementById("scenario-type-selector");
    title = document.getElementById("title");
    btn_edit = document.getElementById("btn-edit");
    update_btn_edit_label();
    lang_checkbox = document.getElementById("lang-checkbox");
    fields_editor = document.getElementById("fields-editor");
    fields_editor_visible = is_fieldseditor_visible();

    clear_fields();

    add_shortcuts_listener();
};
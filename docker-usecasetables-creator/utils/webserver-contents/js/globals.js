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
 * Constants
 */
var FIELD_NAMES = ["id", "name", "description", "primary_actors", "secondary_actors",
        "preconditions", "execution_step", "sequence", "postconditions",
        "alternative_sequences"
];
var HIDDEN_FIELD_NAMES = ["scenario_type", "scenario_id"];


/*
 * Shortcut related
 */
// Some keys
var A_KEY = 65;
var B_KEY = 66;
var C_KEY = 67;
var D_KEY = 68;
var E_KEY = 69;
var F_KEY = 70;
var G_KEY = 71;
var H_KEY = 72;
var I_KEY = 73;
var J_KEY = 74;
var K_KEY = 75;
var L_KEY = 76;
var M_KEY = 77;
var N_KEY = 78;
var O_KEY = 79;
var P_KEY = 80;
var Q_KEY = 81;
var R_KEY = 82;
var S_KEY = 83;
var T_KEY = 84;
var U_KEY = 85;
var V_KEY = 86;
var W_KEY = 87;
var X_KEY = 88;
var Y_KEY = 89;
var Z_KEY = 90;



var field_references = {};
var hidden_references = {};
var fields_copy = {}; // This is used to perform the copy-paste of fields
                      // Basically, a cheap way to make a "duplicate" function

var title = null;
var file_selector = null;
var scenario_type_selector = null;
var btn_edit = null;
var fields_editor = null;
var fields_editor_visible = false;


// Use this when testing
function debug() {
    console.log("WIP");
}



function update_btn_edit_label() {
    if (file_selector.selectedIndex === 0) {
        btn_edit.textContent = "Make new";
    } else {
        btn_edit.textContent = "Edit";
    }
}

function get_preview_selected_lang() {
    return lang_checkbox.checked ? "ita" : "eng";
}
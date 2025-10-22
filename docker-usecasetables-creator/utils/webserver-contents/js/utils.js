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
 * Utilities
 */
function clear_fields() {
    for (var key in field_references) {
        field_references[key].value = "";
    }

    for (var key in hidden_references) {
        hidden_references[key].value = "";
    }
}


function load_ineditor(scenario_type, scenario_id) {
    // Indica che ora, il file si sta modificando (in caso non sia mai stato salvato)
    hidden_references["scenario_type"].value = scenario_type;
    hidden_references["scenario_id"].value = scenario_id;

    if (scenario_type === "CU") {
        scenario_type_selector.selectedIndex = 0;
    } else {
        scenario_type_selector.selectedIndex = 1;
    }
    change_scenario_type();

    title.innerText = scenario_type + "_" + scenario_id;
}


function scentype_or_id_have_changed() {
    var selected_scentype = get_selected_scentype();

    if (hidden_references["scenario_type"].value !== "") {
        return (hidden_references["scenario_type"].value !== selected_scentype ||
            hidden_references["scenario_id"].value !== field_references["id"].value.trim());
    }

    return false;
}


function make_request_body_from_fields() {
    var selected_scentype = get_selected_scentype();

    var req_body = "";
    for (var key in field_references) {
        // Ignore specific scenario-type keys (alternative sequences does not exist for ALT, nor does execution_step for CU)
        if ((selected_scentype === "ALT" && key === "alternative_sequences") ||
            (selected_scentype === "CU" && key === "execution_step")) {
            continue;
        }

        // Add key
        req_body += field_references[key].name + "=" + field_references[key].value.trim() + "&";
    }

    //Add hidden fields
    for (var key in hidden_references) {
        req_body += hidden_references[key].name + "=" + hidden_references[key].value + "&";
    }

    // Finally, add the current selected scentype
    req_body += "selected-scentype=" + selected_scentype;

    return req_body;
}


function get_selected_scentype() {
    var options = scenario_type_selector.options;
    var selected = scenario_type_selector.selectedIndex;
    var selected_scentype = options[selected].value;
    return selected_scentype;
}


function get_selected_file() {
    var options = file_selector.options;
    var selected = file_selector.selectedIndex;
    return (selected === 0) ? null :  options[selected].value;
}


function is_fieldseditor_visible() {
    return window.getComputedStyle(fields_editor, null).display !== "none";
}


function make_ajax(req_type, page_url, callback, sync=false, body=undefined, init=undefined) {
    var xmhttp_req = new XMLHttpRequest();
    xmhttp_req.open(req_type, page_url, !sync);
    if (init !== undefined) {
        init(xmhttp_req);
    }
    xmhttp_req.send(body);

    if (sync) {
        // If sync call directly, onload gets ignored
        callback(xmhttp_req);
    } else {
        xmhttp_req.onload = function() {
            callback(xmhttp_req);
        };
    }
}




function add_shortcuts_listener() {
    document.addEventListener("keydown", function(e) {
        e.preventDefault();
        for (var j in SHORTCUTS) {
            var data = SHORTCUTS[j];
            if (data.scut_funct(e)) {
                e.preventDefault();
                data.oper_funct();
            }
        }
    });
}
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
 * AJAX requests
 */
function perform_xml_selector_update() {
    make_ajax("GET", "get_files.php", function(ajax) {
        var received_file_list = JSON.parse(ajax.responseText);

        while (file_selector.children.length > 1) {
            file_selector.removeChild(file_selector.lastElementChild);
        }
        file_selector.selectedIndex = 0;

        for (var i = 0; i < received_file_list.length; i++) {
            var curr_data = received_file_list[i];
            var type = curr_data[0];
            var id = curr_data[1];
            var filename = curr_data[2];


            var option = document.createElement("option");
            option.appendChild(document.createTextNode(filename));
            option.value = filename;
            file_selector.appendChild(option);

            if (type === hidden_references["scenario_type"].value &&
                id == hidden_references["scenario_id"].value) {
                file_selector.selectedIndex = i + 1;
                update_btn_edit_label();
                break;
            }
        }
    }, true);
}


function perform_xml_load(filename) {
    make_ajax("GET", "load.php?page=" + filename, function(ajax) {
        if (ajax.readyState === 4 && ajax.status === 200) {
            clear_fields();

            var received_file_data = JSON.parse(ajax.responseText);
            for (var key in received_file_data) {
                var value = received_file_data[key];
                if (key === "scenario_id" || key === "scenario_type") {
                    hidden_references[key].value = value;
                } else {
                    field_references[key].value = value;
                }
            }

            load_ineditor(hidden_references["scenario_type"].value, hidden_references["scenario_id"].value);

        } else {
            window.alert("Error with the server at perform_xml_load.");
        }
    });
}


function perform_xml_save(body) {
    make_ajax("POST", "save.php", function(ajax) {
        if (ajax.readyState === 4 && ajax.status === 200) {
            // After the save succeded, listen to the returned status
            // and show the information
            var received = JSON.parse(ajax.responseText);

            if (received["status"] === "file_exists") {
                window.alert("File " + received["filename"] + " exists already!");

            } else {
                if (received["oldfilename"] != null) {
                    window.alert("File " + received["oldfilename"] + " was transformed into " + received["filename"] + " successfully!");
                } else {
                    window.alert("File " + received["filename"] + " was successfully saved!");
                }

                var scenario_type = get_selected_scentype();
                var scenario_id = field_references["id"].value;

                load_ineditor(scenario_type, scenario_id);

                perform_xml_selector_update();
            }

        } else {
            window.alert("Error with the server at perform_xml_save");
        }
    },
    true, body, function(ajax) {
        ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    });
}

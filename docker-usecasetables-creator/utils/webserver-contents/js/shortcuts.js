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



// Shortcut database
var SHORTCUTS = {};


function add_shortcut(id, scut_funct, oper_funct, scut_desc, desc) {
    SHORTCUTS[id] = {
        "scut_funct": scut_funct,
        "oper_funct": oper_funct,
        "scut_desc": scut_desc,
        "desc": desc,
    };
}

function show_scuts_reminder() {
    var message = "";
    for (var i in SHORTCUTS) {
        scut = SHORTCUTS[i];
        message += scut.scut_desc + ": " + scut.desc + "\n";
    }
    window.alert(message);
}



add_shortcut("preview_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === W_KEY; },
    preview_file, "CTRL-ALT-W", "Preview selected Scenario"
);
add_shortcut("load_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === O_KEY; },
    load_file, "CTRL-ALT-O", "Load selected Scenario"
);
add_shortcut("copy_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === C_KEY; },
    copy_fields, "CTRL-ALT-C", "Copy fields (no ID)"
);
add_shortcut("paste_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === V_KEY; },
    paste_fields, "CTRL-ALT-V", "Paste fields (no ID)"
);
add_shortcut("save_scut",
    function(e) { return e.ctrlKey && e.keyCode === S_KEY; },
    preview_file, "CTRL-S", "Save currently editing Scenario"
);
add_shortcut("newfile_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === N_KEY; },
    newfile_scut, "CTRL-ALT-N", "Make new file"
);
add_shortcut("focusfilesel_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === F_KEY; },
    focus_fileselector_scut, "CTRL-ALT-F", "Focus on file selector"
);
add_shortcut("togglelang_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === I_KEY; },
    toggle_lang_checkbox, "CTRL-ALT-I", "Toggle preview table language"
);
add_shortcut("showreminder_scut",
    function(e) { return e.ctrlKey && e.altKey && e.keyCode === J_KEY; },
    show_scuts_reminder, "CTRL-ALT-J", "Show shortcuts"
);




function add_shortcuts_listener() {
    document.addEventListener("keydown", function(e) {
        for (var j in SHORTCUTS) {
            var data = SHORTCUTS[j];
            if (data.scut_funct(e)) {
                e.preventDefault();
                data.oper_funct();
            }
        }
    });
}
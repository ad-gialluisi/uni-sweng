"use strict";

function showIssue(num) {
    var div = document.getElementById(num);

    if (div.className === "hidden") {
        div.className = "";
    } else {
        div.className = "hidden";
    }
}

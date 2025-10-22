function GestoreNotifiche() {
	this.contenitoriNotifiche = document.getElementsByClassName("notification-container");

	this.soloUltimeNotifiche = true;
	for (var i = 0; i < this.contenitoriNotifiche.length; i++) {
		var currContenitore = this.contenitoriNotifiche[i];
		if (!currContenitore.hasOwnProperty("data-last-only")) {
			this.soloUltimeNotifiche = false;
			break;
		}
	}

	//this.maxNotificationLastOnly = 10
	
	this.lastTimeStamp = 0;
}


GestoreNotifiche.prototype.richiediNotifiche = function() {
	var URLLastOnly = "notifica.php?richiesta=lastnotifs&idUtente=1&dataCreazione=0";
	var URL = "notifica.php?richiesta=notifs&timestamp=%s";

	var self = this;
	
    var httpReq = new XMLHttpRequest();
    httpReq.open("GET", "notifica.php?richiesta=lastnotifs&idUtente=1&dataCreazione=" + this.lastTimeStamp, true);
    httpReq.send();
    httpReq.onload = function() {
    	console.log("THIS WORKS");
        console.log(httpReq.responseText);

        var received = JSON.parse(httpReq.responseText);
        
		var nReceived = received.length;
		self.lastTimeStamp = encodeURI(received[nReceived - 1]["data_creazione"]);
		console.log(self.lastTimeStamp);

        for (var i = 0; i < self.contenitoriNotifiche.length; i++) {
    		var currContenitore = self.contenitoriNotifiche[i];

    		for (var e = 0; e < nReceived; e++) {
    			var div = document.createElement("div");
    			var text = document.createTextNode(received[e]["descrizione"]);
    			div.appendChild(text);
    			currContenitore.appendChild(div);
    		}
    	}
        
        httpReq.open("GET", "notifica.php?richiesta=lastnotifs&idUtente=1&dataCreazione=" + self.lastTimeStamp, true);
        httpReq.send();
    };
};


GestoreNotifiche.prototype.addNotifica = function(notifica) {
	//Aggiunge la notifica nell'interfaccia
};


var gestoreNotifiche = new GestoreNotifiche();
gestoreNotifiche.richiediNotifiche();
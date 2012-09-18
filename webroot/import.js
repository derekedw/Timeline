var proto = new SlowStartProto();

function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object
    // files is a FileList of File objects. List some properties.
    for (var i = 0; i < files.length; i++) {
    	proto.send(files[i]);
    }
}

function onLoad() {
	document.getElementById('files').addEventListener('change', handleFileSelect, false);
}

function createRequest() {
	try {
		request = new XMLHttpRequest();
	} catch (tryMS) {
		try {
			request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (otherMS) {
			try {
				request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (failed) {
				request = null;
			}
		}
	}
	return request;
}

SlowStartProto.prototype.getHeader = function() {
	var oOutput = document.implementation.createDocument(null, "Timeline", null);
	var oEle = oOutput.createComment("Reading " + this.file.name);
	oEle = oOutput.appendChild(oEle);
	return oOutput;
};

SlowStartProto.prototype.print = function(text) {
	// output text to the console
	var console = document.getElementById("console");
	var value = console.value;
	console.value = value + text;
};

SlowStartProto.prototype.dispatch = function(data) {
	var oOutput = this.getHeader();
	var oHT = oOutput.getElementsByTagName("Timeline");
	var oData = oOutput.createElement("Data");
	oData = oHT[0].appendChild(oData);
	oData.appendChild(oOutput.createCDATASection(data.join("\n")));
	
	// Create a request
	req = createRequest();
	req.onreadystatechange = function() {
		// Check the outcome of the response
		if (this.readyState == 4) {
			if (this.status == 200) {
				proto.print(this.responseText + "\n");
				proto.print("[" + this.statusText + "] " + "\n");
			} else {
				proto.print("[" + this.statusText + "] " + "\n");
			}
			proto.schedule();
		} 
	}; 
	this.schedule(req, oOutput);	
};

SlowStartProto.prototype.schedule = function(req, oOutput) {
	if (req && oOutput) {
		this.queue.push({'request': req, 'data': oOutput});
	}
	strURL = window.location.href;
	for (var i = 0; i < this.slots.length; i++) {
		if (this.slots[i] == null || this.slots[i].request.readyState == 4) {
			this.slots[i] = this.queue.shift();
			if (this.slots[i] != null) {
				this.print("[  ] " + " sending to " + strURL + "\n");
				this.slots[i].request.open("POST", strURL, true);
				this.slots[i].request.send(this.slots[i].data);
			}
		} 
	}
};
		
SlowStartProto.prototype.getStart = function() {
	return this.start;
};

SlowStartProto.prototype.getEnd = function() {
	return this.end;
};

SlowStartProto.prototype.send = function(file) {
	this.file = file;
    this.reader = new FileReader();
    this.reader.onloadend = handleChunkLoaded;
    var chunk = this.file.slice(this.getStart(), this.getEnd());
    this.reader.readAsBinaryString(chunk);    
};

SlowStartProto.prototype.post = function(text) {
	var done;
	if (text == '')
		done = true;
	else 
		done = false;
	
	// update counters
	this.start = this.end;
	this.end = this.end + this.interval;
	
	// Split text into lines
	var lines = text.split("\n");
	
	// Splice the last line of the buffer back together with its remainder at lines[0]
	if (! done) {
		if (this.buf != null) {
			var last = this.buf.pop() + lines.shift();
			// this.print(last);

			// Add the line and lines together with the buffer
			this.buf.push(last);
			while(lines.length > 0)
				this.buf.push(lines.shift());
		} else {
			this.buf = lines;
		}
	}
	if (this.buf.length >= this.minRows || done) {
		var newbuf = this.buf.pop();
		this.dispatch(this.buf, window.location.href);
		this.buf = new Array();
		this.buf[0] = newbuf;
	}
};

function handleChunkLoaded(evt) {
    if (evt.target.readyState == FileReader.DONE) {
    	proto.post(evt.target.result);
    	if (evt.target.result != '') {
	    	var chunk = proto.file.slice(proto.getStart(), proto.getEnd());
	        evt.target.readAsBinaryString(chunk);
    	}
    }
}

function SlowStartProto() {
	// Set the initial start and end byte count at 1400, which should 
	// fit into a single TCP packet.
	this.start = 0;
	this.end = 4096;
	this.interval = 4096;
	this.minRows = 1000;
	this.file = null;
	this.reader = null;
	this.buf = null;
	this.queue = new Array();
	this.slots = new Array(2);
}
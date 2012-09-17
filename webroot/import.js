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
	this.oOutput = document.implementation.createDocument(null, "Timeline", null);
	var oEle = this.oOutput.createComment("Reading " + this.file.name);
	oEle = this.oOutput.appendChild(oEle);
};

SlowStartProto.prototype.print = function(text) {
	// output text to the console
	var console = document.getElementById("console");
	var value = console.value;
	console.value = value + text;
};

SlowStartProto.prototype.dispatch = function(data) {
	strURL = window.location.href;
	if (this.oOutput == null)
		this.getHeader();
	var oHT = this.oOutput.getElementsByTagName("Timeline");
	var oData = this.oOutput.createElement("Data");
	oData = oHT[0].appendChild(oData);
	
	oData.appendChild(this.oOutput.createCDATASection(data.join("\n")));

	this.print("[  ] " + " sending to " + strURL + "\n");
  		
	// Create a request
	req = createRequest();
	/* req.onreadystatechange = function() {
		// Check the outcome of the response
		if (req.readyState == 4) {
			if (req.status == 200) {
				proto.print(req.responseText + "\n");
				proto.print("[" + req.statusText + "] " + "\n");
			} else {
				proto.print("[" + req.statusText + "] " + "\n");
			}
			proto.oOutput.removeElement(oData);
		} 
	};*/ 
	req.open("POST", strURL, false);
	req.send(this.oOutput);	
	proto.print(req.responseText + "\n");
	proto.print("[" + req.statusText + "] " + "\n");
	oHT[0].removeChild(oData);
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
	this.end = 8192;
	this.interval = 8192;
	this.minRows = 1000;
	this.file = null;
	this.reader = null;
	this.buf = null;
	this.oOutput = null;
}
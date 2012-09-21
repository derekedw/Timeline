var proto = new SlowStartProto();

/**
 * Event handler for the selection of a File 
 * @param evt
 */
function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object
    // files is a FileList of File objects. List some properties.
    for (var i = 0; i < files.length; i++) {
    	proto.send(files[i]);
    }
}

/**
 * onLoad handler for the import page
 */
function onLoad() {
	var files = document.getElementById('files');
	if (files)
		files.addEventListener('change', handleFileSelect, false);
}

function redirectToIndex(){
	window.location = "index.php";
}

/**
 * Utility method for creating an XMLHttpRequest
 * @returns the XHR
 */
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

/**
 * Creates the XML wrapper document
 * @returns the XML DOM document
 */
SlowStartProto.prototype.getHeader = function() {
	var oOutput = document.implementation.createDocument(null, "Timeline", null);
	var oEle = oOutput.createComment("Reading " + this.file.name);
	oEle = oOutput.appendChild(oEle);
	return oOutput;
};

/**
 * Prints the the HTTP response output to the textarea with 'console' as its ID
 * @param text the HTTP responseText
 */
SlowStartProto.prototype.print = function(text) {
	// output text to the console
	var console = document.getElementById("console");
	var value = console.value;
	console.value = value + text;
};

/**
 * Creates an XMLHttpRequest and wraps a number of lines of the input file in 
 * an XML envelope. 
 * @param data
 */
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

/**
 * Queues and/or opens an XmlHttpRequest and uses it to send data to the server
 * @param req the XmlHttpRequest
 * @param oOutput the file data, wrapped in an XML envelope
 */
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

/**
 * 
 * @returns {Number} the starting offset of the next file chunk
 */
SlowStartProto.prototype.getStart = function() {
	return this.start;
};

/**
 * 
 * @returns {Number} the ending offset of the next file chunk 
 */
SlowStartProto.prototype.getEnd = function() {
	return this.end;
};

/**
 * Sets up a file to be read in chunks and sent to the server.
 * 
 * Ancient browsers, like the Firefox 3.6 (really?!) that's on the current SIFT
 * workstation don't support loading the file 
 * in chunks, so we set up a 500 millisecond timer to load the whole file into RAM and
 * split it. 
 * @param file
 */
SlowStartProto.prototype.send = function(file) {
	this.file = file;
    this.reader = new FileReader();
    try {
	    this.reader.onloadend = handleChunkLoaded;
	    var chunk = this.file.slice(this.getStart(), this.getEnd());
	    this.reader.readAsBinaryString(chunk);
    } catch(e) {
    	proto.reader.onloadend = function(evt) {
    		if (evt.target.readyState == FileReader.DONE) {
    			// Loading the file this way seems to cause deadlock in the 
    			// database when there are multiple slots for concurrent 
    			// XMLHttpRequests
    			proto.slots = new Array(1);
    			var lines = evt.target.result.split("\n");
    			while(lines.length > 0) {
    				var subset = new Array();
    				var i = 0;
    				while (i < proto.minRows && lines.length > 0) {
    					subset.push(lines.shift());
    					i++;
    				}
    				if (lines.length > 0) {
	    				var last = lines.shift();
	    				subset.push(last.substring(0,20));
	    				lines.unshift(last.substring(20));
    				}
    				proto.post(subset.join("\n"), window.location.href);
    			}
				proto.post('', window.location.href);
    		}
    	};
    	proto.reader.readAsText(file);
    }
};

/**
 * Adds a fixed-length file chunk to a buffer.  If the buffer is larger than a 
 * certain number of text lines, all of the buffer, except the last (probably 
 * partial) line of text is sent 
 * @param text - text from the file chunk
 */
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

/**
 * Callback function that handles the loading of a file chunk.
 * @see SlowStartProto.getStart() SlowStartProto.getEnd() SlowStartProto.post() 
 * File.slice(); FileReader.readAsBinaryString()
 */
function handleChunkLoaded(evt) {
	// Having multiple slots for concurrent XMLHttpRequests seems to work 
	// when loading the file this way 
	if (proto.slots == null) {
		proto.slots = new Array(2);
	}
    if (evt.target.readyState == FileReader.DONE) {
    	proto.post(evt.target.result);
    	if (evt.target.result != '') {
	    	var chunk = proto.file.slice(proto.getStart(), proto.getEnd());
	        evt.target.readAsBinaryString(chunk);
    	}
    }
}

/**
 * The idea was of a protocol that would scale the import to MySQL speed as 
 * fast as possible, in the same way as the slow start protocol did in TCP.
 * @returns {SlowStartProto}
 */
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
	this.slots = null;
};
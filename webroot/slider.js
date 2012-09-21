
function loadImages() {
	this.imgLeft=new Image(3, 16);
	this.imgLeft.src="slider/sliderL.jpg";
	this.imgRight=new Image(3, 16);
	this.imgRight.src="slider/sliderR.jpg";
	this.imgSlider=new Image(2, 16);
	this.imgSlider.src="slider/slider.jpg";
	this.imgBlank=new Image(1, 16);
	this.imgBlank.src="slider/1x1.gif";
	this.container.width = this.width + 2;
	this.container.height = this.imgLeft.height + 2;
}

function setStatus(percent) {
	if (this.displayed==false) {
		this.container.appendChild(this.imgLeft);
		this.container.appendChild(this.imgSlider);
		this.container.appendChild(this.imgRight);
		this.container.appendChild(this.imgBlank);
		this.displayed=true;
	}
	if (percent > (6*100/this.width)) {
		// display left, right, center, blank images
		this.imgLeft.width=3;
		this.imgRight.width=3;
		this.imgSlider.width=(percent*this.width/100)-6;
		this.imgBlank.width=(100-percent)*this.width/100;
	} else {
		this.imgLeft.width=0;
		this.imgSlider.width=0;
		this.imgRight.width=0;
		this.imgBlank.width=this.width;
	}
}

function slider(outer) {
	// properties
	this.outer=outer;
	this.container=document.createElement("div");
	this.outer.appendChild(this.container);
	this.container.style.display = "inline"; 
	this.container.style.border = "1px solid black"; 
	this.container.style.padding = "0px"; 
	this.container.style.margin = "5px";
	this.width=200;
	this.displayed=false;
	// methods
	this.loadImages=loadImages;
	this.setStatus=setStatus;
}
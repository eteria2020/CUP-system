function gauge(name) {
    var me = this;
	//canvas initialization
	var canvas = document.getElementById(name);
    var ctx = canvas.getContext("2d");
    var percent;
	//dimensions
	var W = canvas.width;
	var H = canvas.height;
	//Variables

    var value=0;
	var new_value = 0;
	var difference = 0;
	var color = "#b2c831"; //green looks better to me
	var bgcolor = "#222";
	var text='';
	var animation_loop=null;


	this.init = function() {
		//Clear the canvas everytime a chart is drawn
		ctx.clearRect(0, 0, W, H);

		//Background 360 degree arc
		ctx.beginPath();
		ctx.strokeStyle = bgcolor;
		ctx.lineWidth = 20;
		ctx.arc(W/2, H/2, 60, 0, Math.PI*2, false); //you can see the arc now
		ctx.stroke();

		//gauge will be a simple arc
		//Angle in radians = angle in degrees * PI / 180
		var radians = Math.round(3.6*value) * Math.PI / 180;
		ctx.beginPath();
		ctx.strokeStyle = color;
		ctx.lineWidth = 20;
		//The arc starts from the rightmost end. If we deduct 90 degrees from the angles
		//the arc will start from the topmost end
		ctx.arc(W/2,H/2, 60, 0 - 90*Math.PI/180, radians - 90*Math.PI/180, false);
		//you can see the arc now
		ctx.stroke();

		//Lets add the text
		ctx.fillStyle = color;
		ctx.font = "30px open sans";
		text = value + "%";
		//Lets center the text
		//deducting half of text width from position x
		var text_width = ctx.measureText(text).width;
		//adding manual value to position y since the height of the text cannot
		//be measured easily. There are hacks but we will keep it manual for now.
		ctx.fillText(text, W/2 - text_width/2, H/2 + 10);


	}
	
	this.draw = function(value)	{
		//Cancel any movement animation if a new chart is requested
		if(animation_loop != null ) clearInterval(animation_loop);

        value = Math.round(value);
		//random degree from 0 to 360
		new_value = value;
		difference = new_value - value;
		//This will animate the gauge to new positions
		//The animation will take 1 second
		//time for each frame is 1sec / difference in degrees
		animation_loop = setInterval(this.animate_to, 10000/difference);
	}

	//function to make the chart move to new degrees
	this.animate_to = function () {
		//clear animation loop if degrees reaches to new_degrees
		if(value == new_value) {
			clearInterval(animation_loop);
            animation_loop=null;
        } else {
    		if(value < new_value)
    			value++;
    		else
    			value--;
        }

        me.init();

	}
	
	//Lets add some animation for fun
	this.draw(50);


}
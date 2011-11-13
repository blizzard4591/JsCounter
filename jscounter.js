var delay = 0;
var clickStack = [];
var trys = 0;
var drawDelayCounter = 0;
var starttime = 0;
var events = [];
var length = 90;	// Minutes
var gruppe = "";


// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

/**
 *
 * @access public
 * @return void
 **/
function writeToLog(logId, message){
	if (typeof logId != "undefined") {
		currentTime = new Date();
		$('<div class="logEntry"></div>').text(padNumber(currentTime.getHours(),2) + ':' + padNumber(currentTime.getMinutes(),2) + ':' + padNumber(currentTime.getSeconds(),2) + ' - ' + message).appendTo($(logId));
		$(logId).scrollTop($(logId).height());
	}
}

/**
 *
 * @access public
 * @return void
 **/
function starteZaehlen(){
	events = [];
	starttime = +new Date(); // Meh.
	var event = {timestamp: starttime, eventType: 1};
	clickStack.push(event);
	var tempDate = new Date(starttime);
	$('#startpunkt').text("gestartet am " + padNumber(tempDate.getDate(),2) + "." + padNumber(tempDate.getMonth()+1, 2) + "." + tempDate.getFullYear() + " um " + padNumber(tempDate.getHours()+1,2) + ":" + padNumber(tempDate.getMinutes(), 2) + ":" + padNumber(tempDate.getSeconds(),2));

	var dauer = $('#selectionDauer').val();
	gruppe = $('#inputGruppe').val();

	length = dauer;
	showButton();
}

/**
 *
 * @access public
 * @return void
 **/
function syncTime(callback){
	var timestampNow = Math.round(+new Date()/1000);
	writeToLog('#statusLog', "Syncing Time, current time is " + timestampNow + " and delay is " + delay);

	$.getJSON('jscounter.php?ajax=1&sync=' + timestampNow, function(data) {
		var items = [];
		timestamp = 0;
		sync      = 0;
		//writeToLog('#statusLog', "Sync returned with " + data);
		$.each(data, function(key, val) {
	  		items.push('<li id="' + key + '">' + val + '</li>');
	  		if (key == "timestamp") {
	  			timestamp = val;
	  		} else if (key == "sync") {
	  			sync = val;
	  		}
		});

		if ((timestamp != 0) && (sync != 0)) {
			//writeToLog('#statusLog', "Ajax returned " + timestamp + "(timestamp), and " + sync + " (sync)");
			delay = Math.round((timestamp - sync) / 2);
			writeToLog('#statusLog', "Delay is " + delay);
		} else {
			// Meh.
		}

		if (callback && typeof(callback) === "function") {
			// execute the callback, passing parameters as necessary
			callback();
		}

	});
}

/**
 *
 * @access public
 * @return void
 **/
function showButton(){
	$('<input/>', {
		'type': 'button',
		'value': 'Counter++',
		'onclick': 'counterDoClick()'
	}).appendTo('body');

	setInterval("sendEvents()", 2000);
}

/**
 *
 * @access public
 * @return void
 **/
function counterDoClick(){
	var timestampNow = Math.round(+new Date()/1000);
	var event = {timestamp: timestampNow, eventType: 2};
	clickStack.push(event);
	events.push(+new Date());

	writeToLog('#statusLog', "Counted click on " + timestampNow);

	if ($("input[name='showGraph']:checked").val() == "showGraph") {
		drawChart();
	} else {
		$('#chartContainer').html("");
	}
}

/**
 *
 * @access public
 * @return void
 **/
function sendEvents(){
	// Called every 2 seconds, checks for events to send and goes to work.
	var size = clickStack.length;
	//writeToLog('#statusLog', "Checking Events, " + size + " Event(s) pending, currently " + trys + " pending trys.");
	if (size > 0) {
		if (trys == 0) {
			// Assign handlers immediately after making the request,
			// and remember the jqxhr object for this request
			trys++;
			//alert("jscounter.php?ajax=1&gruppe="+gruppe+"&event=" + clickStack[trys - 1].timestamp + "&type=" + clickStack[trys - 1].eventType);
			var jqxhr = $.getJSON("jscounter.php?ajax=1&gruppe="+gruppe+"&event=" + clickStack[trys - 1].timestamp + "&type=" + clickStack[trys - 1].eventType, function(data) {
					//alert("success, time is " + data.time + " and status is " + data.status);
					for (var i = 0; i < clickStack.length; i++) {
						if (clickStack[i].timestamp == data.time) {
							clickStack.remove(i);
							break;
						}
					}
					trys--;
				})
				//.success(function() { alert("second success"); })
				.error(function() { trys--; //alert("Failed.");
				//.complete(function() { alert("complete");
			});

			// perform other work here ...

			// Set another completion function for the request above
			//jqxhr.complete(function(){ alert("second complete"); });
		}
	}

	if (drawDelayCounter == 10) {
		if ($("input[name='showGraph']:checked").val() == "showGraph") {
			drawChart();
		} else {
			$('#chartContainer').html("");
		}
		drawDelayCounter = 0;
	} else {
		drawDelayCounter++;
	}
}

function padNumber(number, padTo) {
	var result = String(number);
	while (result.length < padTo) {
		result = "0" + result;
	}
	return result;
}

/**
 *
 * @access public
 * @return void
 **/
function drawChart(){
	var intervall = 30; // Seconds
	var timestampNow = +new Date();

	var points = [];
	for (var i = starttime; i < (starttime + length * 60 * 1000); i += (intervall * 1000)) {
		if (i > timestampNow) {
			points.push(null);
		} else {
			var count = 0;
			for (var j = 0; j < events.length; j++) {
				if (events[j] <= i) {
					count++;
				} else {
					break;
				}
			}
			points.push(count);
		}
	}

	writeToLog('#statusLog', "Drawing Chart with " + points.length + " Points.");

	chart1 = new Highcharts.Chart({
		chart: {
			renderTo: 'chartContainer',
			zoomType: 'x',
			spacingRight: 20
		},
		title: {
			text: 'Number of Events along a Timeline'
		},
		xAxis: {
			type: 'datetime',
			maxZoom: 60,
			title: {
				text:null
			}
		},
		yAxis: {
			title: {
				text: 'Number of Events (#)'
			},
			startOnTick: false,
         	showFirstLabel: false
		},
		tooltip: {
        	shared: true
		},
		legend: {
     		enabled: false
		},
		plotOptions: {
			area: {
				fillColor: {
					linearGradient: [0, 0, 0, 300],
					stops: [
						[0, Highcharts.getOptions().colors[0]],
						[1, 'rgba(2,0,0,0)']
					]
				},
				lineWidth: 1,
				marker: {
					enabled: false,
					states: {
						hover: {
			  				enabled: true,
			  				radius: 5
						}
					}
				},
				shadow: false,
				states: {
					hover: {
						lineWidth: 1
					}
				}
			}
		},
		series: [{
         type: 'area',
         name: '# of "Ja" since the beginning',
         pointInterval: intervall * 1000,
         pointStart: starttime,
         data: points
 		}]
		});
}
//https://www.dyclassroom.com/chartjs/chartjs-how-to-draw-line-graph-using-data-from-mysql-table-and-php



$(document).on("keydown", "form", function(event) { 
    return event.key != "Enter";
});

function showChart(CHANNEL_ID, SUBCHANNEL, READ_KEY, TYPE) {


console.log(CHANNEL_ID);
console.log(SUBCHANNEL);



//Retrieve Input from HTML Elements

var _CHANNEL_NAME = "CHANNEL_NAME_" + CHANNEL_ID;
var _DIV = "#DIV_SUBCHANNEL_" + CHANNEL_ID + "_" + SUBCHANNEL;
var _DIV2 = "#DIV_" + CHANNEL_ID + "_" + SUBCHANNEL;
var _CANVAS = "#CANVAS_" + CHANNEL_ID + "_" + SUBCHANNEL;
var _CANVAS_ = "CANVAS_" + CHANNEL_ID + "_" + SUBCHANNEL;
var _COUNT = "COUNT_" + CHANNEL_ID + "_" + SUBCHANNEL;
var _CONTROL = "#chart-control-" + CHANNEL_ID + "_" + SUBCHANNEL;



var COUNT = document.getElementById(_COUNT).value;

if(COUNT == "undefined" || COUNT == null || COUNT == 0){
	var COUNT = 10;
	document.getElementById(_COUNT).value = 10;
}
else{}

var COUNT = parseInt(COUNT);

//Show Graph DIV

/*
if(TYPE == 'SHOW'){
$(_DIV).toggle();
//$(_CONTROL).toggle();
}
*/

//Workaround to remove existing Chart instance before updating existing one


if (TYPE == 'UPDATE'){
	$('iframe.chartjs-hidden-iframe').remove();
	$(_CANVAS).remove();
	$(_DIV2).append('<canvas id="' + _CANVAS_ + '"></canvas>');
}


//Prepare request Header

var TIMESTAMP = parseInt(Date.now() / 1000);
var timeFormat = 'YYYY/MM/DD HH:mm:ss';

var URL = "../API/"+CHANNEL_ID+"/"+SUBCHANNEL+"/"+COUNT;

var ENDPOINT = 
	{
		channelname: CHANNEL_ID,
		subchannel: SUBCHANNEL,
		count: COUNT,
		timestamp: TIMESTAMP
	}
;

ENDPOINT_JSON = JSON.stringify(ENDPOINT);

console.log(ENDPOINT_JSON);
console.log(URL);

var REQUEST_HASH_INPUT = ENDPOINT_JSON;

console.log(REQUEST_HASH_INPUT);

var hash = sha256.hmac(READ_KEY, REQUEST_HASH_INPUT);



//Request Data from Server

	$.ajax({
		url : URL,
		headers: {
		'x-auth-type': 'Signature',
		'x-auth-alg': 'HS256',
		'x-auth-hash': hash,
		'x-auth-timestamp': TIMESTAMP,
		},
		type : "GET",

		success : function(data, textStatus, xhr){
			console.log(data);
			
			//Get response Headers
			
			var RESPONSE_HASH = xhr.getResponseHeader('x-auth-hash');
			console.log("Response Hash: " + RESPONSE_HASH);
			
			var RESPONSE_LABEL = xhr.getResponseHeader('x-'+SUBCHANNEL+'-label');
			console.log("Response Label: " + RESPONSE_HASH);
			
			var RESPONSE_TIMESTAMP = parseInt(xhr.getResponseHeader('x-auth-timestamp'));
			console.log("Response Timestamp: " + RESPONSE_TIMESTAMP);
			
			var RESPONSE_ENDPOINT = 
				{
					channelname: CHANNEL_ID,
					subchannel: SUBCHANNEL,
					count: COUNT,
					timestamp: RESPONSE_TIMESTAMP
				}
			;			
			
			RESPONSE_ENDPOINT_JSON = JSON.stringify(RESPONSE_ENDPOINT);

			//Check whether Server response is signed correctly ---------------------------------------------------------
			
			var HASH_INPUT = RESPONSE_ENDPOINT_JSON + "." + JSON.stringify(data);
			console.log("Hash Input: " + HASH_INPUT);
			
			var hash = sha256.hmac(READ_KEY, HASH_INPUT);
			
			console.log("Calculated Hash: " + hash);
			
			deltaTime = Math.abs(RESPONSE_TIMESTAMP - (Date.now() / 1000));
			
			console.log(deltaTime);

			if (hash != RESPONSE_HASH){
			throw new Error("Calculated HASH does not match Server HASH");
			}
			else if (deltaTime > 120){
			throw new Error("Timeout Server / Client");
			}
			else{}
			


			//----------------------------------------------------------------------------------------------------------
									
			data = JSON.parse(data);			

			var id = [];
			var VAR0_DATA = [];
			var DATE_DATA = [];

			//Push response JSON into Graph Object, only read defined Sub-Channel Data
			for(var i in data) {
				VAR0_DATA.push(data[i][SUBCHANNEL]);
				DATE_DATA.push(data[i].date);
			}
	
			//Generate Graph properties

			var chartdata = {
				labels: DATE_DATA,
				datasets: [
					{
						label: RESPONSE_LABEL,
						fill: false,
						lineTension: 0.05,
						backgroundColor: "rgba(59, 89, 152, 0.75)",
						borderColor: "rgba(59, 89, 152, 1)",
						pointHoverBackgroundColor: "rgba(59, 89, 152, 1)",
						pointHoverBorderColor: "rgba(59, 89, 152, 1)",
						data: VAR0_DATA
					},

				]
			};

			if(LineGraph != null){
				LineGraph.destroy();
				console.log("Chart destroyed");
			}
			
			var ctx = $(_CANVAS);
			
			var LineGraph = new Chart(ctx, {
				type: 'line',
				data: chartdata,
				options: {
					responsive: true,
					maintainAspectRatio: false,
					legend: {
						display: true,
						position: 'bottom',
					},
					animation: {
						duration: 0,
					},
					scales: {
						xAxes: [{
							type: 'time',
							time: {
								parser: timeFormat,
								// round: 'day'
								tooltipFormat: 'll HH:mm:ss',
								displayFormats: {
									millisecond: 'HH:mm:ss.SSS',
									second: 'HH:mm:ss',
									minute: 'HH:mm',
									hour: 'HH'
								},
							},
							scaleLabel: {
								display: true,
								labelString: 'Date'
							}
						}],
						yAxes: [{
							scaleLabel: {
								display: true,
								labelString: 'Value'
							}
						}]
					},
					title: {
						display: true,
						//text: 'Legend Position: '
					}
				}
			});

		},
		error : function(data) {

		}
	});


};
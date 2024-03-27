//Hook up the tweet display

$(document).ready(function() {
						   
	$(".countdown").countdown({
				date: "04 April 2024 14:30:00",
				format: "on"
			},
			
			function() {
				// callback function
			});

});	
$('#feed_input_textarea').autosize();

getLocation();
//Automatic Refreshses?
/*
window.setInterval(function(){
  //refresh_feed();
}, 5000);*/

$(document).on('click', '.feed_items', function (e) {
    if(!$(e.target).hasClass('like_buttons') )
   {
       var feedID = parseInt($(this).attr('id').substr(10)); 
       //alert(feedID);
       $(this).children(".comment_view_links")[0].click();
       
   }else{
       
       var itemname = $(this).attr('id');
    	var id = itemname.substr(10);
    	alert("Spreading... "+id);
    	
    	$.ajax({
    		url: '/index.php/feed/spread_feed/'+id,
    		context: this,
    		success: function(data){
    			alert("Successfully Spread!");
    			$(this).children('.like_buttons').hide();
    			$(this).append('<span class="afterlike_messages">The word has been spread</span>');
    			
    			var prev_likecount = $(this).children(".likecount_spans").children(".blueify").html();
    			$(this).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
    			
    			$('.my_lists').children('#feed_item_'+id).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
    			$('.my_lists').children('#feed_item_'+id).children(".like_buttons").hide();
    			$('.my_lists').children('#feed_item_'+id).append('<span class="afterlike_messages">The word has been spread</span>');

    			/*
    			var prev_likecount = $(this).children('.like_buttons').siblings(".likecount_spans").children(".blueify").html();
    			$(this).children('.like_buttons').siblings(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
    			$(this).append('<span class="afterlike_messages">The word has been spread</span>');
    			
    			var prev_spreadcount = parseInt($('#my_spreads_open').children('b').html().substr(1,2))+1;
    			$('#my_spreads_open').children('b').html("("+prev_spreadcount+")");
    			
    			$('#feed_main_div').children('#feed_item_'+id).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
    			$('#feed_main_div').children('#feed_item_'+id).hide();
    			$('#feed_main_div').children('#feed_item_'+id).append('<span class="afterlike_messages">The word has been spread</span>');
    			
    			$('.my_lists').children('#feed_item_'+id).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
    			$('.my_lists').children('#feed_item_'+id).hide();
    			$('.my_lists').children('#feed_item_'+id).append('<span class="afterlike_messages">The word has been spread</span>');
    			*/
    		}
    	});
   }
});

$(".comment_view_links").fancybox({
        'type' : 'ajax',
        helpers : {
            overlay : {
                    css : {
                        'background' : 'rgba(0, 0, 0, 0.7)'
                    }
            }
        },
        ajax: {
        complete: function(jqXHR, textStatus) { //runs after fancybox successfully loaded everything
                $('.comment_inputs').focus();
            }
        }
});

$(document).on('keypress','.comment_inputs',function(e){
    if(e.which==13){
        var mess = $(this).val();
        //alert(mess);
        var feedID = parseInt($(this).parent().siblings('.feed_items').attr('id').substr(10)); 
        $.ajax({
    		url: '/index.php/feed/add_comment/'+feedID,
    		type: 'POST',
    		context: this,
    		data: 'message='+mess,
    		success: function(data){
    		    $(this).val('');
    			$('.comment_mains').html(data);
    		
	    	}   
	    });
    }
});

$('#feed_submit_button').click(function(){
	var message = $('#feed_input_textarea').val();
	
	if(!message.trim()){	//is empty or whitespace
		var n = noty({text: 'You have to spread something. With words.',type:'warning',timeout:2000,layout:'topRight'});
	}else{
		var latitude = $('#input_lat_hidden').val();
		var longitude = $('#input_long_hidden').val();
		var location = $('#location').html();
		
		$.ajax({
			url: '/index.php/feed/add_feed/',
			type: 'POST',
			data: {latitude: latitude, longitude: longitude, message: message, location: location},
			success: function(data){
				//alert("Success!");
				var n = noty({text: 'You spread a new word',type:'success',timeout:2000,layout:'topRight'});
				refresh_feed();
				var prev_spreadcount = parseInt($('#my_feeds_open').children('b').html().substr(1,2))+1;
			    $('#my_feeds_open').children('b').html("("+prev_spreadcount+")");
				//refresh_feed();
			}
		});
	}
	$('#feed_input_textarea').val('');
	$('#feed_input_textarea').focus();
});

$('.tooltip').tooltipster({contentAsHTML:true,maxWidth:400,content: 'For every <b>10</b> likes you get on a word, your informant level goes up by <b>1</b>. The higher your informant level is, the more likely your words will be heard by others.',theme:'tooltipster-light'});


$("#feed_input_textarea").focus(function(){
	$("hr").css('margin-top','-16px');
	$("#user_level").show();
}).blur(function(){
	$("hr").css('margin-top','20px');
	$("#user_level").hide();
});

var rtvar = 0;
$(".my_open_toggles").click(function(e){
	if(rtvar==0){
		$(this).siblings(".my_lists").show();
		$(this).children("img").rotate({animateTo:90,duration:700});
		rtvar = 1;
	}else{
		$(this).siblings(".my_lists").hide();
		$(this).children("img").rotate({animateTo:360,duration:700});
		rtvar = 0;
	}
});

$.fn.extend( {
	limiter: function(limit, elem) {
		$(this).on("keyup focus", function() {
			setCount(this, elem);
		});
		function setCount(src, elem) {
			var chars = src.value.length;
			if (chars > limit) {
				src.value = src.value.substr(0, limit);
				chars = limit;
			}
			elem.html( limit - chars );
		}
		setCount($(this)[0], elem);
	}
});

var elem = $("#char_counter");
$('#feed_input_textarea').limiter(500,elem);
$("abbr.time_spans").timeago();	
/*
$('#refresh_button').click(function(){
	//show loading gif
	var opts = {
	  lines: 13, // The number of lines to draw
	  length: 20, // The length of each line
	  width: 10, // The line thickness
	  radius: 30, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  direction: 1, // 1: clockwise, -1: counterclockwise
	  color: '#000', // #rgb or #rrggbb or array of colors
	  speed: 1, // Rounds per second
	  trail: 60, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '50%', // Top position relative to parent
	  left: '50%' // Left position relative to parent
	};
	var target = document.getElementById('loading_image');
	var spinner = new Spinner(opts).spin(target);
	
	//alert("sd");
	var latitude = $('#input_lat_hidden').val();
	var longitude = $('#input_long_hidden').val();


	$.ajax({
		url: '/index.php/feed/refresh_feed/',
		type: 'POST',
		data: {latitude: latitude, longitude: longitude},
		success: function(data){
			//alert("Successfully Refreshed!");
			//Hide loading gif
			spinner.stop();
			$('#feed_main_div').html(data);
		}
	});
	
});
*/
/*
$(document).on('click', '.like_buttons', function (e) {
	//e.preventDefault();
	
	var itemname = $(this).parent().attr('id');
	var id = itemname.substr(10);
	alert("Spreading... "+id);
	
	$.ajax({
		url: '/index.php/feed/spread_feed/'+id,
		context: this,
		success: function(data){
			//alert("Successfully Spread!");
			$(this).hide();
			var prev_likecount = $(this).siblings(".likecount_spans").children(".blueify").html();
			$(this).siblings(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
			$(this).parent().append('<span class="afterlike_messages">The word has been spread</span>');
			
			var prev_spreadcount = parseInt($('#my_spreads_open').children('b').html().substr(1,2))+1;
			$('#my_spreads_open').children('b').html("("+prev_spreadcount+")");
			
			$('#feed_main_div').children('#feed_item_'+id).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
			$('#feed_main_div').children('#feed_item_'+id).hide();
			$('#feed_main_div').children('#feed_item_'+id).append('<span class="afterlike_messages">The word has been spread</span>');
			
			$('.my_lists').children('#feed_item_'+id).children(".likecount_spans").children(".blueify").html(parseInt(prev_likecount)+1);
			$('.my_lists').children('#feed_item_'+id).hide();
			$('.my_lists').children('#feed_item_'+id).append('<span class="afterlike_messages">The word has been spread</span>');
		}
	});
});
*/

function refresh_feed(){
	//show loading gif
	var opts = {
	  lines: 13, // The number of lines to draw
	  length: 20, // The length of each line
	  width: 10, // The line thickness
	  radius: 30, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  direction: 1, // 1: clockwise, -1: counterclockwise
	  color: '#000', // #rgb or #rrggbb or array of colors
	  speed: 1, // Rounds per second
	  trail: 60, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '50%', // Top position relative to parent
	  left: '50%' // Left position relative to parent
	};
	var target = document.getElementById('loading_image');
	var spinner = new Spinner(opts).spin(target);
	
	//alert("sd");
	var latitude = $('#input_lat_hidden').val();
	var longitude = $('#input_long_hidden').val();


	$.ajax({
		url: '/index.php/feed/refresh_feed/',
		type: 'POST',
		data: {latitude: latitude, longitude: longitude},
		success: function(data){
			//alert("Successfully Refreshed!");
			//Hide loading gif
			spinner.stop();
			$('#feed_main_div').html(data);
		}
	});
}

function getLocation(){
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showMap, showError);
	} else {
		alert("Geolocation is not supported by this browser.");
	    
	}
	
}
function showMap(position) {
	//alert("sds");
	$('#input_lat_hidden').val(position.coords.latitude);
	$('#input_long_hidden').val(position.coords.longitude);
	
	var cityname = getCityName(position.coords.latitude,position.coords.longitude,function(cityname){
		$('#feed_city_information').html('The word around <b><span id="location">'+cityname+'</span></b> is...'+'<button onclick="refresh_feed()" id="refresh_button"><i class="fa fa-refresh"></i> Refresh</button>');
	});
	
	
	$.ajax({
		url: '/index.php/feed/add_ip/',
		type: 'POST',
		data: {latitude: position.coords.latitude, longitude: position.coords.longitude},
		context: this,
		success: function(data){
			//alert(data);
		}
	});
}

function showError(error) {
	switch(error.code) {
		case error.PERMISSION_DENIED:
			alert("User denied the request for Geolocation.");
			break;
		case error.POSITION_UNAVAILABLE:
			alert("Location information is unavailable.");
			break;
		case error.TIMEOUT:
			alert("The request to get user location timed out.");
			break;
		case error.UNKNOWN_ERROR:
			alert("An unknown error occurred.");
			break;
	}
}

function getCityName(lat, lng, callback) {
	geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
	var city;
    geocoder.geocode({'location': latlng}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (results[1]) {
         //formatted address
         //alert(results[0].formatted_address)
        //find country name
            for (var i=0; i<results[0].address_components.length; i++) {
			//alert(results[0].address_components[i].long_name);
            for (var b=0;b<results[0].address_components[i].types.length;b++) {
				//alert(results[0].address_components[i].types[b]);
            //there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
                if (results[0].address_components[i].types[b] == "sublocality") {
                    //this is the object you are looking for
                    city= results[0].address_components[i];
					//alert(city.long_name);
					callback(city.long_name);
                    break;
                }
            }
        }
        //city data
        


        } else {
          alert("No results found");
        }
      } else {
        alert("Geocoder failed due to: " + status);
      }
    });
}
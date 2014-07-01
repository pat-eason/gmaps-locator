var infowindow;
function initialize(){
	console.log(gmaps_locator_data.shortcode);

	//set default map options
  var mapOptions = {
		center: new google.maps.LatLng(40.0171852,-97.240944),
		zoom: 4,
		disableDefaultUI: true
	};

	//init map
  var map = new google.maps.Map(document.getElementById("gmaps-locator"),mapOptions);

	//location forEach loop
	i = 0;
	var infowindow = new google.maps.InfoWindow({
      content: ''
  });

	gmaps_locator_data.locations.forEach(function(location){
		var loc = JSON.parse(location.coordinates);

		//gimme a marker!
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(loc.lat,loc.lng),
			map: map,
			title:location.title
		});

		google.maps.event.addListener(marker, 'click', function() {
        infowindow.close();
        infowindow.setContent(contentString);
        infowindow.open(map,marker);
    });

    var contentString = '<div id="GMAP_LOCATOR_content">'+
      '<div id="GMAPS_LOCATOR_siteNotice">'+
      '</div>'+
      '<h1 id="GMAPS_LOCATOR_heading" class="GMAPS_LOCATOR_heading">'+ location.title + '</h1>'+
      '<div id="GMAPS_LOCATOR_body">'+ location.infowindow +
      '</div>'+
      '</div>';

		i++;
	});

	//geolocation
	if(gmaps_locator_data.shortcode.geolocate == true && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var pos = new google.maps.LatLng(position.coords.latitude,
                                       position.coords.longitude);
      map.setCenter(pos);
			map.setZoom(12);
    }, function() {
      handleNoGeolocation(true);
    });
  }
}
google.maps.event.addDomListener(window, 'load', initialize);

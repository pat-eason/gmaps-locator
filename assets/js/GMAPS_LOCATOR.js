var infowindow;
function initialize(){
	//get markers by map bounds
	function getMarkersBound(){
		var cont = document.getElementById('gmaps-locator-radius');
		cont.innerHTML = '<h3><span id="gmaps-locator-radius-count"></span> locations found</h3>';
		var count=0;
		for (var i=0; i<markers.length; i++){
				if( map.getBounds().contains(markers[i].getPosition()) ){
						markerData = '<div class="gmaps-locator-radius-entry">'+
													'<div class="gutter">'+
													'<h5>'+markers[i].title+'</h5>'+
													'<div class="gmaps-locator-radius-entry-content">'+
													markers[i].infowindow+
													'</div>'+
													'</div>'+
													'</div>';
						cont.innerHTML = cont.innerHTML + markerData;
						count++;
				}
		}
		var counthead = document.getElementById('gmaps-locator-radius-count');
		counthead.innerHTML = count;
	}

	//set default map options
  var mapOptions = {
		center: new google.maps.LatLng(parseFloat(gmaps_locator_data.gmaps_latitude),parseFloat(gmaps_locator_data.gmaps_longitude)),
		zoom: parseInt(gmaps_locator_data.zoom_level),
		zoomControl: true,
		scaleControl: false
	};

	//init map
  var map = new google.maps.Map(document.getElementById("gmaps-locator"),mapOptions);

	//instantiate infowindow
	i = 0;
	var infowindow = new google.maps.InfoWindow({
      content: ''
  });

	//location forEach loop
	var markers = [];
	gmaps_locator_data.locations.forEach(function(location){
		var loc = JSON.parse(location.coordinates);

		//gimme a marker!
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(loc.lat,loc.lng),
			map: map,
			animation: google.maps.Animation.DROP,
			title:location.title,
			infowindow:location.infowindow,
		});
		markers.push(marker);

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
      var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
      map.setCenter(pos);
			map.setZoom(12);
			getMarkersBound();
    }, function() {
      handleNoGeolocation(true);
    });
  }

	//places API search
  var input = /** @type {HTMLInputElement} */(document.getElementById('pac-input'));
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
  var searchBox = new google.maps.places.SearchBox(
  /** @type {HTMLInputElement} */(input));
	google.maps.event.addListener(searchBox, 'places_changed', function() {
    var places = searchBox.getPlaces();
		for (var i = 0, place; place = places[i]; i++) {
			if (place.geometry.viewport) {
	      map.fitBounds(place.geometry.viewport);
	    } else {
	      map.setCenter(place.geometry.location);
	      map.setZoom(17);
				getMarkersBound();
	    }
		}
	});
	google.maps.event.addListener(map, 'bounds_changed', function() {
    var bounds = map.getBounds();
    searchBox.setBounds(bounds);
  });

	//drag and zoom events
	google.maps.event.addListener(map, 'dragend', function(){
		var bounds = map.getBounds();
		getMarkersBound();
	});

	google.maps.event.addListener(map, 'zoom_changed', function() {
		var bounds = map.getBounds();
		getMarkersBound();
	});


}
google.maps.event.addDomListener(window, 'load', initialize);

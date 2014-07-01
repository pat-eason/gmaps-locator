function initialize() {
	GMAPS_LOCATOR_data.latitude = parseFloat(GMAPS_LOCATOR_data.latitude);
	GMAPS_LOCATOR_data.longitude = parseFloat(GMAPS_LOCATOR_data.longitude);
	GMAPS_LOCATOR_data.zoom = parseInt(GMAPS_LOCATOR_data.zoom);
	console.log(GMAPS_LOCATOR_data);
  var mapOptions = {
    center: new google.maps.LatLng(GMAPS_LOCATOR_data.latitude, GMAPS_LOCATOR_data.longitude),
    zoom: GMAPS_LOCATOR_data.zoom,
		disableDefaultUI: true
  };
  var map = new google.maps.Map(document.getElementById("gmaps_locator_map"), mapOptions);
	google.maps.event.addListener(map, 'dragend', function(){
		var pos = map.getCenter();
		var elem = document.getElementById("gmaps_latitude");
		elem.value = pos.k;
		var elem = document.getElementById("gmaps_longitude");
		elem.value = pos.A;
	});

	google.maps.event.addListener(map, 'zoom_changed', function() {
		var zoomLevel = map.getZoom();
		var elem = document.getElementById("zoom_level");
		elem.value = zoomLevel;
	});

}
google.maps.event.addDomListener(window, 'load', initialize);

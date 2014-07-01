function initialize() {
	console.log(gmaps_locator_data.locations);
	//set default map options
  var mapOptions = {
    center: new google.maps.LatLng(40.0171852,-97.240944),
    zoom: 4,
		disableDefaultUI: true
  };

	//init map
  var map = new google.maps.Map(document.getElementById("gmaps-locator"),mapOptions);

	//add markers
	var marker = new google.maps.Marker({
    position: new google.maps.LatLng(40.0171852,-97.240944),
    map: map,
    title:"Hello World!"
	});

	//infowindows
	var infowindow = new google.maps.InfoWindow({
      content: 'Hello!'
  });

	//click events for markers/infowindows
	google.maps.event.addListener(marker, 'click', function() {
    infowindow.open(map,marker);
  });


}
google.maps.event.addDomListener(window, 'load', initialize);
console.log('gmaps locator loaded');

var infowindow;
function initialize() {
	console.log(gmaps_locator_data);

	//set default map options
  var mapOptions = {
    center: new google.maps.LatLng(40.0171852,-97.240944),
    zoom: 4,
		disableDefaultUI: true
  };

	//init map
  var map = new google.maps.Map(document.getElementById("gmaps-locator"),mapOptions);

	/*
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
	*/

	//location forEach loop
	i = 0;
	var infowindow = new google.maps.InfoWindow({
      content: ''
  });

	gmaps_locator_data.locations.forEach(function(location){
		console.log(location);
		console.log(i);
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


}
google.maps.event.addDomListener(window, 'load', initialize);
console.log('gmaps locator loaded');

# Google Maps Locator

*The first incarnation of this plugin was used for WVFREE's GoEnrollWV initiative. It was primitive, not very mature, and needed a lot of work to make it what it is today. You can see how that works to get a general gist of what the plugin does: [GoEnrollWV](http://goenrollwv.org)*

## What does it do?
Google Maps Locator does three things: gives you, the user, a way to upload multiple locations for whatever you intend to use the map for. Think locations when searching for dealerships on a car website. On top of that it adds in a taxonomy to tag those locations so you can easily organize them as well as filter those on the map. And finally, it uses a simple shortcode implementation to render your map with a few parameters to give you some extra control over the map.

## The Configurator
The admin panel for Google Maps Locator is simple, it's largely hand-holding, and it gets you up and running as fast as possible. Take a look:

![alt text](http://pateason.com/git-plugins/gmaps-locator/config.png "Admin Configurator")

Pretty simple so far. We've got a field for a Google API Key and the rest are automatically filled using the rendered map (you don't need an API key for that). You do, however, need an API Key for the plugin to work on the front-end. If you don't input that correctly then things will crash and burn. You don't want that, so double and triple check your API Key!

The Latitude, Longitude, and Zoom Level fields all fill automatically when you adjust the map. So if you drag the map, your Lat and Long will update. If you zoom the map, then your zoom level will update. Simple stuff, just point and shoot! If you're feeling dangerous you can always input those entries yourself, but it's not necessary.

*If you don't add anything to the configurator then it will default to Anchorage, AK. Unless you're a fan of Anchorage then you should update the configurator.*


## The Shortcode
```php
[gmaps_locator debug=false search=true tags=false geolocate=false]
```
The base usage of the Google Maps Locator shortcode is *[gmaps_locator]* which will render a Google Map using the default settings:

```php
'search'    => false (renders the text-based search bar and enables text search functions for the map),
'tags'      => false (renders tag-based filters and enables tag filtering for the map. Tags are based on the custom taxonomy *location tags*),
'debug'     => false (renders various debug data, if necessary),
'geolocate' => true (enables or disables HTML5 geolocation for the map. If enabled, geolocation overrides the default lat, lng, and zoom levels of the map)
```

## The Map
So now things get interesting, we get to see what the front-end, user-facing portion of the plugin does! Again, simple stuff. Your markers are rendered on the map, which is focused based on the configuration you added in the Configurator.

More to come as I make it, yo. Stay tuned.

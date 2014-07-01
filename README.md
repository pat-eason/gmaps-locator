# Google Maps Locator

*The first incarnation of this plugin was used for WVFREE's GoEnrollWV initiative. It was primitive, not very mature, and needed a lot of work to make it what it is today. You can see how that works to get a general gist of what the plugin does: [GoEnrollWV](http://goenrollwv.org)*

## What does it do?
Google Maps Locator does three things: gives you, the user, a way to upload multiple locations for whatever you intend to use the map for. Think locations when searching for dealerships on a car website. On top of that it adds in a taxonomy to tag those locations so you can easily organize them as well as filter those on the map. And finally, it uses a simple shortcode implementation to render your map with a few parameters to give you some extra control over the map.

## The Shortcode
```php
**[gmaps_locator debug=false search=true tags=false geolocate=false]**
```
The base usage of the Google Maps Locator shortcode is *[gmaps_locator]* which will render a Google Map using the default settings:

```php
'search'    => true (renders the text-based search bar and enables text search functions for the map),
'tags'      => true (renders tag-based filters and enables tag filtering for the map. Tags are based on the custom taxonomy *location tags*),
'debug'     => false (renders various debug data, if necessary),
'geolocate' => true (enables or disables HTML5 geolocation for the map. If enabled, geolocation overrides the default lat, lng, and zoom levels of the map)
```


More to come as I make it, yo. Stay tuned.

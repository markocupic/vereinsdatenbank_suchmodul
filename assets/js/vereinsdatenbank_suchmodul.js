/**
 * get param from the query string
 */
function getParam(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return (false);
}

/**
 * test for object
 */
function is_object(obj) {
    //you may use either of the following 2 lines
    return (typeof(obj) != 'object') ? false : true; //original post's function
    return (typeof(obj) == 'object') //suggested by HB in the comment
}

/**
 *
 * @param object map
 * @param string pos
 * @param string title
 * @param string clickable
 * @param string url
 * @return object google.maps.Marker
 */
function getMarkerFromPosition(map, pos, title, clickable, url, icon) {
    var objOptions = {
        map:map,
        position:pos
    };

    if (title != '') {
        objOptions.title = title;
    }
    if (icon != '') {
        objOptions.icon = icon;
    }
    var marker = new google.maps.Marker(objOptions);

    if (clickable && url != '') {
        google.maps.event.addListener(marker, "click", function () {
            window.location.href = url
        });
    }

    return marker;
}
/**
 *
 * @param map
 * @param address
 * @param title
 * @param url
 */
function getMarkerFromAddress(map, address, title, clickable, url, icon) {
    var geocoder = new google.maps.Geocoder();

    geocoder.geocode({ 'address':address}, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var pos = results[0].geometry.location;
            var marker = getMarkerFromPosition(map, pos, title, clickable, url);
            return marker;
        } else {
            //alert("Geocode was not successful for the following reason: " + status);
            return null;
        }
    });
}


/** form  page **************************/


/**
 * clear/reset the form
 */
var clearForm = function () {
    $$('.mod_organisationenSuche input[type=text]').each(function (el) {
        el.setProperty('value', '');
    });

    $$('.mod_organisationenSuche input[type=checkbox]').each(function (el) {
        el.removeProperty('checked');
    });

    $$('.mod_organisationenSuche input[type=hidden]').each(function (el) {
        el.setProperty('value', '');
    });
    // add value to input[name=submit]
    if (document.id('tl_search_organization')) {
        document.id('tl_search_organization').addEvent('submit', function () {
            $$('input[name=submit]').each(function (el) {
                el.setProperty('value', '1');
            });
        });
    }
    return false;
}

/**
 * slide in the result container
 */
window.addEvent('domready', function () {
    if (document.id('result_container')) {
        mySlide = new Fx.Slide('result_container');
        document.id('result_container').setStyle('visiblity', 'hidden');
        mySlide.hide();
        document.id('result_container').setStyle('visiblity', 'visible');
        mySlide.slideIn();
    }
});


/* https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete?hl=de */
/* https://developers.google.com/maps/documentation/javascript/examples/infowindow-simple?hl=de */
window.addEvent('domready', function () {
    if (document.id('ctrl_address')) {
        // write location to hidden form fields
        var getCoord = function (strLocation) {
            //convert strLocation into longitude and latitude
            if (strLocation == '') return;
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                address:strLocation
            }, function (results, status) {
                if (status == 'OK') {
                    var lat = results[0].geometry.location.lat();
                    var lng = results[0].geometry.location.lng();
                    document.id('ctrl_lat').setProperty('value', lat.round(5));
                    document.id('ctrl_lng').setProperty('value', lng.round(5));
                    initializeLocationMap(lat, lng);
                }
            });
        }

        // initialize the autocomplete address field
        var input = document.getElementById('ctrl_address');
        var options = {
            types:['(regions)']
            //componentRestrictions: {country: 'ch'}
        };
        autocomplete = new google.maps.places.Autocomplete(input, options);
        // add onchange event...
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            // ...and write lat&lng-coord to the hidden input-text-fields
            getCoord(document.id('ctrl_address').getProperty('value'));
        });


        // add the map to the bottom to display the location
        var map;
        var initializeLocationMap = function (lat, lng) {
            if (document.id('map-canvas')) {
                document.id('map-canvas').setStyle('display', 'block');
                var mapOptions = {
                    zoom:10,
                    center:new google.maps.LatLng(lat, lng),
                    mapTypeId:google.maps.MapTypeId.ROADMAP
                };
                map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

                var pos = new google.maps.LatLng(document.id('ctrl_lat').getProperty('value'), document.id('ctrl_lng').getProperty('value'));
                var marker = getMarkerFromPosition(map, pos, 'Ihr Standort', false, null);
                var circle = new google.maps.Circle({
                    map:map,
                    center:pos,
                    radius:document.id('ctrl_radius').getProperty('value') * 1000,
                    strokeWeight:0,
                    fillColor:'green'
                });
            }
        }
    }
});


/** result page **************************/
/**
 * show map with the location of each result
 * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete?hl=de
 */
window.addEvent('domready', function () {
    if (document.id('map-canvas') && typeof objCoord != 'undefined') {
        // add the map to the bottom
        var map;
        initializeResultListMap = function (lat, lng) {
            document.id('map-canvas').setStyle('display', 'block');
            var mapOptions = {
                zoom:9,
                center:new google.maps.LatLng(lat, lng),
                mapTypeId:google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

            for (key in objCoord) {
                var title = objCoord[key]['title'];
                var url = objCoord[key]['url'] != '' ? objCoord[key]['url'] : null;
                var customIcon = objCoord[key]['marker'];
                if (objCoord[key]['lat'] == '' || objCoord[key]['lat'] == '') {
                    var address = objCoord[key]['street'] + ', ' + objCoord[key]['city'] + ', ' + objCoord[key]['country'];
                    var marker = getMarkerFromAddress(map, address, title, true, url, customIcon);

                } else {
                    var pos = new google.maps.LatLng(objCoord[key]['lat'], objCoord[key]['lng']);
                    var marker = getMarkerFromPosition(map, pos, title, true, url, customIcon);
                }

            }
            if (getParam('lat') && getParam('lng') && getParam('radius')) {
                var circle = new google.maps.Circle({
                    map:map,
                    center:new google.maps.LatLng(getParam('lat'), getParam('lng')),
                    radius:getParam('radius') * 1000,
                    strokeWeight:0,
                    fillColor:'green'
                });
            }
        };
        // initialize map
        if (document.id('map-canvas')) {
            initializeResultListMap(objCoord[0]['lat'], objCoord[0]['lng']);
        }
    }
});





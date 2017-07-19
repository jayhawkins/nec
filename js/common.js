function php_crud_api_transform(tables) {
	var array_flip = function (trans) {
		var key, tmp_ar = {};
		for (key in trans) {
			tmp_ar[trans[key]] = key;
		}
		return tmp_ar;
	};
	var get_objects = function (tables,table_name,where_index,match_value) {
		var objects = [];
		for (var record in tables[table_name]['records']) {
			record = tables[table_name]['records'][record];
			if (!where_index || record[where_index]==match_value) {
				var object = {};
				for (var index in tables[table_name]['columns']) {
					var column = tables[table_name]['columns'][index];
					object[column] = record[index];
					for (var relation in tables) {
						var reltable = tables[relation];
						for (var key in reltable['relations']) {
							var target = reltable['relations'][key];
							if (target == table_name+'.'+column) {
								column_indices = array_flip(reltable['columns']);
								object[relation] = get_objects(tables,relation,column_indices[key],record[index]);
							}
						}
					}
				}
				objects.push(object);
			}
		}
		return objects;
	};
	tree = {};
	for (var name in tables) {
		var table = tables[name];
		if (!table['relations']) {
			tree[name] = get_objects(tables,name);
			if (table['results']) {
				tree['_results'] = table['results'];
			}
		}
	}
	return tree;
}

// Haversine Formula for calculating distances between two geo location points - so we don't have to call the Google api again
function rad(x) {
  return x * Math.PI / 180;
};

function getLocationDistance(p1, p2) {
  var R = 6378137; // Earth’s mean radius in meter
  var dLat = rad(p2.lat - p1.lat);
  var dLong = rad(p2.lng - p1.lng);
  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(rad(p1.lat)) * Math.cos(rad(p2.lat)) *
    Math.sin(dLong / 2) * Math.sin(dLong / 2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  var d = R * c;
  //return d; // returns the distance in meter
	return d/1609.344; // Convert to miles before retuning
};

function getDistanceFromGoogle(p1,p2) {

		var o = new google.maps.LatLng(p1.lat, p1.lng);
		var d = new google.maps.LatLng(p2.lat, p2.lng);
		return (google.maps.geometry.spherical.computeDistanceBetween(o, d) / 1609.344).toFixed(2);

    //*********DISTANCE AND DURATION**********************//
/*
    var service = new google.maps.DistanceMatrixService();
		alert(service);
    service.getDistanceMatrix({
        origins: p1.address,
        destinations: p2.address,
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC,
        avoidHighways: false,
        avoidTolls: false
    }, function (response, status) {
			  alert('Response ' + response);
				alert('Status ' + status);
				alert(response.rows[0].elements[0].distance.text);
        if (status == google.maps.DistanceMatrixStatus.OK && response.rows[0].elements[0].status != "ZERO_RESULTS") {
            var distance = response.rows[0].elements[0].distance.text;
            var duration = response.rows[0].elements[0].duration.text;
						return distance/1609.344; // Convert to miles before retuning;
        } else {
            alert("Unable to find the distance via road.");
        }
    });
*/
}

function getMapDirectionFromGoogle(p1,p2,url) {

		var o = new google.maps.LatLng(p1.lat, p1.lng);
		var d = new google.maps.LatLng(p2.lat, p2.lng);

    //**************DIRECTION SERVICE*****************//
    var service = new google.maps.DirectionsService;
    service.route({
        origin: o,
        destination: d,
        travelMode: 'DRIVING',
        unitSystem: google.maps.UnitSystem.METRIC,
        avoidHighways: false,
        avoidTolls: false
    }, function (response, status) {
        if (status === 'OK' && response.status != "ZERO_RESULTS") {
            var distance = response.routes[0].legs[0].distance.value;
            var duration = response.routes[0].legs[0].duration.value;
						var thedistance = (distance/1609.344).toFixed(0);
						var params = {distance: thedistance};
						$.ajax({
							 url: url,
							 type: 'PUT',
							 data: JSON.stringify(params),
							 contentType: "application/json",
							 async: false,
							 success: function(data){
									//alert(data);
							 },
							 error: function() {
									alert('Failed Sending Notifications! - Notify NEC of this failure.');
							 }
						});

						return true;
        } else {
            return ( "Unable to determine distance for writing information." );
        }
    });

}

function newGetMapDirectionFromGoogle(p1,p2,callback) {

		//var o = new google.maps.LatLng(p1.lat, p1.lng);
		//var d = new google.maps.LatLng(p2.lat, p2.lng);

    //**************DIRECTION SERVICE*****************//
    var service = new google.maps.DirectionsService;
    service.route({
        origin: p1,
        destination: p2,
        travelMode: 'DRIVING',
        unitSystem: google.maps.UnitSystem.IMPERIAL,
        avoidHighways: false,
        avoidTolls: false
    }, function (response, status) {
				var distance = (response.routes[0].legs[0].distance.value/1609.344);
				//console.log(distance);
				var originationlat = response.routes[0].legs[0].start_location.lat();
				var originationlng = response.routes[0].legs[0].start_location.lng();
				var destinationlat = response.routes[0].legs[0].end_location.lat();
				var destinationlng = response.routes[0].legs[0].end_location.lng();
				var send = {"distance": distance, "originationlat": originationlat, "originationlng": originationlng, "destinationlat": destinationlat, "destinationlng": destinationlng};
				console.log(JSON.stringify(send));
        if (response.status === 'OK') {
						console.log('run callback');
						callback(send);
        } else {
            return ( "Unable to determine distance for writing information." );
        }
    });

}

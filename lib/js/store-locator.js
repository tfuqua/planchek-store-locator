	var storeData = JSON.parse($("#store-data").html());
	var geocoder = new google.maps.Geocoder();

		function newMap() {

				if (typeof markers === 'undefined'){

					return true;

				} else {
					var z = parseInt($("#zip-val").val());
					brand = $("#brand-val").val();

					var l;

					clearMarkers();
					if (z !== zip && z !== NaN){

						zip = z;

						geocoder.geocode({address: String(zip)}, function(results, status) {

						 l = new google.maps.LatLng(
								results[0].geometry.location.lat(),
								results[0].geometry.location.lng());

							map.setCenter(l);
							paintData(l, map);
						});

					} else {
						var center = map.getCenter();
						l = new google.maps.LatLng(center.lat(), center.lng());
						paintData(l, map)
					}

					return false;
				}
		}

		//Map Init
    function initMap() {

			infowindow = new google.maps.InfoWindow();
			var location =[];

			geocoder.geocode({address: String(zip)}, function(results, status) {
					location[0] = results[0].geometry.location.lat();
					location[1] = results[0].geometry.location.lng();

					drawMap(location, zoom);
			});
		}

		function drawMap(location, zoom){

				var location = new google.maps.LatLng(location[0], location[1]);

				map = new google.maps.Map(document.getElementById('map'), {
						center: location,
						zoom: zoom,
	        	scrollwheel: false,
	    	});

				map.addListener('bounds_changed', function() { mapScroll(map);});

				var mapScroll = debounce(function(map){
					resetMap(map);
				}, 200);
		}

		//Reset Map after scroll
		function resetMap(map){

			clearMarkers();

			var center = map.getCenter();
			var location = new google.maps.LatLng(center.lat(), center.lng());

			paintData(location, map);

		}

		function filterByBrand(store){

			brands = [];
			for (var i = 0; i < store.brand.length; i++){
				brands.push(store.brand[i].toLowerCase());
			}

			if (brands.includes(brand.toLowerCase())){
				return true;
			} else {
				return false;
			}
		}

		//Loop through data and create data points
		function paintData(location, map) {

				var tableData = [];
				var filteredData = (brand === 'all') ? storeData : storeData.filter(filterByBrand);

				for (var i = 0; i < filteredData.length; i++) {

						var position = new google.maps.LatLng(filteredData[i].latitude, filteredData[i].longitude);
						var dist = google.maps.geometry.spherical.computeDistanceBetween(location, position);
						dist *= 0.000621371192;

						addMarker(position, map, filteredData[i]);

						if (map.getBounds().contains(position)){
							tableData.push(addTableData(filteredData[i], dist));
						}
				}

				createTable(tableData);
		}

		//Add Map Marker
		function addMarker(position, map, storeData){
			var marker = new google.maps.Marker({
				position: position,
				map: map,
				title: storeData.name
			});

			marker.addListener('click', function() {
				info(map, marker, storeData);
			});

			markers.push(marker);
		}

		function info(map, marker, storeData){

			var content = '<h5>'+storeData.name+'</h5>'+
										'<div><a href="https://www.google.com/maps/place/'+
										storeData.address +'+'+storeData.city + '+' + storeData.state +
										'">'+storeData.address+ '<br />' +
										storeData.city + ', ' + storeData.state + '</a></div><br />'+
										'<a href="#">'+storeData.phone+'</a><br />'+
										'<h6>Products</h6>'+storeData.products;

			if (infowindow) {
	        infowindow.close();
	    }
			infowindow.setContent(content);
			infowindow.open(map, marker);
		}


		//Clear Map Markers
		function clearMarkers() {
			for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
			}
    }

//Add Data row to Table
function addTableData(storeData, dist){
	return {
		 'address': storeData.address + '<br />' + storeData.city + ',' + storeData.state,
		 'name': 		storeData.name,
		 'distance': Math.round(dist * 100) / 100,
		 'phone': storeData.phone,
		 'products': storeData.products
	 };
}

//Create HTML Table
function createTable(data){
	var rows = '';

	if (data.length > 0) {
		data.sort(function (a, b) {
			if (a.distance > b.distance) { return 1;  }
			if (a.distance < b.distance) { return -1; }
			return 0;
		});
	}
	if (typeof table !== 'undefined'){
		table.destroy();
	}

	$('#store-table').empty();

	table = $('#store-table').DataTable({
			ordering: false,
			searching: false,
			//lengthChange: false,
			lengthMenu: [5, 10, 25, 50],
			pageLength: 5,
			columns: [
        {'title': 'Name', 'data': 'name'},
				{'title': 'Distance', 'data': 'distance',
				'sSortDataType': 'dom-text', 'sType': 'numeric-comma',
				'render':
					function (data, type, row) {
						return data + ' miles';
					},
				},
				{'title': 'Address', 'data': 'address'},
				{'title': 'Phone', 'data': 'phone' },
				{'title': 'Products', 'data': 'products' }
		              ],
			 data: data
	 });
}

//Debounce Function
function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		};
	};


if (typeof zip !== 'undefined'){
	initMap();
}

<?php
/*
  Plugin Name: Ukrops Store Locator
  Version: 1.0
  Author: Taylor Fuqua
*/

?>
<?php
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<!-- Hero Image -->
	    <?php if(get_field('hero_image')) { ?>
	      <?php $background = wp_get_attachment_image_src(get_field('hero_image'), 'full', false); ?>
		     <div class="hero store-hero" style="background-image: url('<?php echo $background[0] ?>');">
	         <div class="hero-text-wrapper">
	           <div>
	             <div class="hero-text">
								 <?php
								 		while ( have_posts() ) : the_post();
											the_title();
					 					endwhile; // End of the loop.
									?>
	             </div>
	           </div>
	         </div>
	      </div>
	    <?php } ?>

		<div class="container-fluid">

      <!-- Store Locator Map -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3 search-column">
              <form class="store-locator" onsubmit="return newMap()">
	              <h3>Store Locator</h3>
                <div class="form-group">
                  <label>Zip Code</label>
                  <input id="zip-val" value="<?php echo $zip; ?>" type="text" class="form-control" name="zip" />
                </div>

								<!--
                <div class="form-group">
                  <label>Brand</label>
                  <select class="form-control" name="brand">
                    <option value="1">All Brands</option>
                    <option value="2">Whitehouse</option>
                    <option value="3">Ukrops</option>
                  </select>
                </div>-->

                <div class="form-group">
                  <input type="submit" value="Search" class="btn search"/>
                </div>
              </form>


          </div> <!-- .col-3 -->
          <div class="col-md-9 map-column">
						<?php if ($firstVisit) {
				      while ( have_posts() ) : the_post(); ?>
								<div class="col-lg-10 col-md-10 store-intro">
									<?php the_content();?>
								</div>
								<div class="clearfix">	</div>
							<?php
						endwhile; // End of the loop.?>

						<div class="store-branding">
							<?php if( have_rows('brands') ) {
									while ( have_rows('brands') ) : the_row(); ?>
										<h3><?php echo get_sub_field('brand'); ?></h3>
										<div class="store-imgs">
												<?php while ( have_rows('images') ) : the_row(); ?>
													<div class="img-wrapper">
														<?php echo wp_get_attachment_image(get_sub_field('image'), 'full', false, array( 'class' => ''));?>
													</div>
												<?php endwhile;?>
										</div>
									<?php
									endwhile;
								} ?>
						</div>
							<?php
							}	else {
							?>
            	<div id="map" style="width:100%; height:400px;"></div>
						<?php
						} ?>
          </div>	<!-- col-9 -->
        </div> <!-- row -->

				<?php if (!$firstVisit) { ?>
				<div class="row">
					<div class="col-xs-12 hidden-xs">
						<br />
						<table id="store-table" class="table store-locator">
							<thead>
								<tr>
									<th>Store Name</th>
									<th>Distance</th>
									<th>Address</th>
									<th>Phone</th>
									<th>Products</th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>
				</div> <!-- row -->
				<?php
				} ?>

      </div> <!-- container-fluid -->
		</div>
		</main><!-- #main -->
	</div><!-- #primary -->

	<?php
	if (count($data) > 0) {
		$i = 1;
		$storeSize = count($data);
		echo '<div id="store-data" style="display:none;">[';
		foreach ($data as $store) {
			$storeJSON = 	'{'.
										'"address": "' 	. $store->address . '", '.
										'"name": "' 		. $store->store_name . '", '.
										'"phone": "' 		. $store->phone . '", '.
										'"city": "' 		. $store->city . '", '.
										'"state": "' 		. $store->state . '", '.
										'"products": "' . $store->products . '", '.
										'"latitude":' 	. $store->latitude . ', '.
										'"longitude":' 	.	$store->longitude .
										' } ';

			echo $storeJSON;

			if ($i < $storeSize){
				echo ', ';
			}
			$i++;
		}
		echo ']</div>';
	} ?>

	<br /><br />
	<?php if (!$firstVisit) { ?>
  <script>

		var storeData = JSON.parse($("#store-data").html());
		var markers = [];
		var map;
		var zoom = <?php echo $zoom; ?>;
		var zip = <?php echo $zip ?>;
		var infowindow;

		function newMap() {

				var z = parseInt($("#zip-val").val());
				var r = parseInt($("#radius-val").val());
				var l;

				clearMarkers();

				if (z !== zip && z !== NaN){

					zip = z;

					var geocoder = new google.maps.Geocoder();
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

		//Map Init
    function initMap() {

			infowindow = new google.maps.InfoWindow();
			var location =[];
			var geocoder = new google.maps.Geocoder();

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

		//Loop through data and create data points
		function paintData(location, map) {

				var tableData = [];

				for (var i = 0; i < storeData.length; i++) {

						var position = new google.maps.LatLng(storeData[i].latitude, storeData[i].longitude);
						var dist = google.maps.geometry.spherical.computeDistanceBetween(location, position);
						dist *= 0.000621371192;

						addMarker(position, map, storeData[i]);

						if (map.getBounds().contains(position)){
							tableData.push(addTableData(storeData[i], dist));
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
			/*for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
			}*/
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

				for (var i = 0; i < data.length; i++) {
					rows += '<tr><td>' +
									data[i].name + '</td><td>' +
									data[i].distance + ' miles </td><td>' +
									data[i].address + '</td><td>' +
									data[i].phone + '</td><td>' +
									data[i].products + '</td></tr>';
				}

			} else {
				rows += '<tr><td colspan="100%"> No Results to Display </td></tr>';
			}

				$("#store-table tbody").html(rows);
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

  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDV9ffngNmBtR8tC_9g37OL7QZhEheyxQw&callback=initMap&libraries=geometry"
  async defer></script>

<?php
	} else { ?>
		<script>
				function newMap() {
					return true;
				}
		</script>
	<?php
	}
  get_footer();
?>

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
      <?php
      while ( have_posts() ) : the_post();
				the_content();
      endwhile; // End of the loop.
      ?>

      <!-- Store Locator Map -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3 search-column">

              <form class="store-locator">
	              <h3>Store Locator</h3>
                <div class="form-group">
                  <label>Zip Code</label>
                  <input value="<?php echo $zip; ?>" type="text" class="form-control" name="zip" />
                </div>

                <div class="form-group">
                  <label>Mile Radius</label>
                  <select class="form-control" name="radius">
										<?php
									    foreach ($radiusList as $r) {
												$option ='<option value="'.$r.'"';

												if ($r == $radius) {
													$option = $option . 'selected="selected"';
													//echo $r;
												}
												$option = $option . '>'. $r .'</option>';

												echo $option;
											}
										?>
                  </select>
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


          </div>
          <div class="col-md-9 map-column">
            <div id="map" style="width:100%; height:400px;"></div>
            <div>
							<br />
							<table id="store-table" class="table store-locator">
								<thead>
									<tr>
								    <th>Store Name</th>
										<th>Distance</th>
								    <th>Address</th>
								    <th>Phone</th>
								  </tr>
								</thead>
								<tbody>

								</tbody>
							  <!--<?php
							  if (count($data) > 0) {
							    foreach ($data as $store) { ?>
							        <tr>
							          <td><?php echo $store->brand?></td>
							          <td><?php echo $store->store_name ?></td>
							          <td><?php echo $store->address ?></td>
							          <td><?php echo $store->state ?></td>
							          <td><?php echo $store->zip ?></td>
							          <td><?php echo $store->phone ?></td>
							        </tr>
							    <?php
							    }
							  } else { ?>
							    <tr>
							      <td colspan="100%">
							        No Stores Entered into System
							      </td>
							    </tr>
							  <?php
							  }
							  ?>-->
							</table>
            </div>
          </div>
        </div>
      </div>
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
  <script>
    function initMap() {

			var location = new google.maps.LatLng(<?php echo $location['latitude'] ?>, <?php echo $location['longitude'] ?>);

      // Create a map object and specify the DOM element for display.
      var map = new google.maps.Map(document.getElementById('map'), {
        //center: {lat: 37.5407, lng: -77.4360},
				center: location,
        scrollwheel: false,
        zoom: <?php echo $zoom; ?>
      });

			map.addListener('center_changed', function() {
				myEfficientFn(map);
			});

			var myEfficientFn = debounce(function(map) {
				console.log(map);
			}, 1000);

			markerData(location, map);
    }

		function markerData(location, map) {
			var storeData = JSON.parse($("#store-data").html());
			var tableData = [];

			for (var i = 0; i < storeData.length; i++) {
				var position = new google.maps.LatLng(storeData[i].latitude, storeData[i].longitude);
				var dist = google.maps.geometry.spherical.computeDistanceBetween(location, position);
				var radius = <?php echo $radius; ?>;
	 			dist *= 0.000621371192;

				if (dist < radius){
					var marker = new google.maps.Marker({
						 position: position,
						 map: map,
						 title: storeData[i].name
					 });

					 tableData.push({
						 	'address': storeData[i].address + '<br />' + storeData[i].city + ',' + storeData[i].state,
						 	'name': 		storeData[i].name,
							'distance': Math.round(dist * 100) / 100,
							'phone': storeData[i].phone
					 });
				}
			}

			createTable(tableData);
		}

		function createTable(data){
			var rows = '';

			if (data.length > 0) {

				data.sort(function (a, b) {
					if (a.distance > b.distance) {
						return 1;
					}
					if (a.distance < b.distance) {
						return -1;
					}
					return 0;
				});

				for (var i = 0; i < data.length; i++) {
					rows += '<tr><td>' +
									data[i].name +
									'</td><td>' +
									data[i].distance +
									' miles </td><td>' +
									data[i].address +
									'</td><td>' +
									data[i].phone +
									'</td></tr>';
				}
			} else {
				rows += '<tr><td colspan="100%"> No Results to Display </td></tr>';
			}

				$("#store-table tbody").append(rows);

		}

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
  get_footer();
?>

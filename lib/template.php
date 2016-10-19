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

		<div class="container-fluid">
      <?php
      while ( have_posts() ) : the_post();

        get_template_part( 'templates/content', 'page' );

      endwhile; // End of the loop.
      ?>

      <!-- Store Locator Map -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-3 pull-right">
              <h3>Store Locator</h3>

              <form>
                <div class="form-group">
                  <label>Zip Code</label>
                  <input value="<?php echo $zip; ?>" type="text" class="form-control" name="zip" />
									<button id="find-zip">Find My Zip</button>
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

                <div class="form-group">
                  <label>Brand</label>
                  <select class="form-control" name="brand">
                    <option value="1">All Brands</option>
                    <option value="2">Whitehouse</option>
                    <option value="3">Ukrops</option>
                  </select>
                </div>

                <div class="">
                  <input type="submit" class="btn btn-danger"/>
                </div>
              </form>


          </div>
          <div class="col-sm-9">
            <div id="map" style="width:100%; height:400px;"></div>
            <div>
							<br />
							<table class="hidden table table-bordered table-striped">
							  <tr>
							    <th>Brand</th>
							    <th>Store Name</th>
							    <th>Address</th>
							    <th>State</th>
							    <th>Zip</th>
							    <th>Phone</th>
							  </tr>
							  <?php
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
							  ?>
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
        zoom: 10
      });

			markerData(location, map);
    }

		function markerData(location, map){
			var storeData = JSON.parse($("#store-data").html());

			for (var i = 0; i < storeData.length; i++) {
				var position = new google.maps.LatLng(storeData[i].latitude, storeData[i].longitude);
				var dist = google.maps.geometry.spherical.computeDistanceBetween(location, position);
				var radius = <?php echo $radius; ?>;
	 			dist *= 0.000621371192;

				console.log(dist);

				if (dist < radius){
					var marker = new google.maps.Marker({
						 position: position,
						 map: map,
						 title: storeData[i].name
					 });
				}
			}
		}
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDq9dRRK6bRF-okoEA4U7Zp4lK9h9iDtYo&callback=initMap&libraries=geometry"
  async defer></script>

<?php
  get_footer();
?>

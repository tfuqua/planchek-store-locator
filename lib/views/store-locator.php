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
		     <div class="hero mini-hero" style="background-image: url('<?php echo $background[0] ?>');">
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

      <!-- Store Locator Map -->
      <div class="container-fluid">
				<div class="flex-row">
					<div class="content">
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
										<div class="partner-imgs">
											<?php while ( have_rows('images') ) : the_row();?>
													<div class="partner-img">
														<?php echo wp_get_attachment_image(get_sub_field('image'), 'medium', false, array( 'class' => '')); ?>
													</div>
												<?php
											endwhile;?>
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
					</div>
					<div class="aside">
						<form class="store-locator" onsubmit="return newMap()">
							<h3>Store Locator</h3>
							<div class="form-group">
								<label>Zip Code</label>
								<input id="zip-val" value="<?php echo $zip; ?>" type="text" class="form-control" name="zip" />
							</div>

							<div class="form-group">
								<label>Brand</label>
								<select id="brand-val" class="form-control" name="brand">
									<option value="all">All Brands</option>
									<?php foreach($brandList as $b){ ?>
										<option value="<?php echo $b ?>" <?php if (strcasecmp($b, $brand) == 0){echo 'selected';}?>>
											<?php echo $b ?>
										</option>
									<?php }?>
								</select>
							</div>

							<div class="form-group">
								<input type="submit" value="Search" class="button search"/>
							</div>
						</form>
					</div>
				</div>

				<?php if (!$firstVisit) { ?>
				<div class="row">
					<div class="col-xs-12 hidden-xs">
						<br />
						<table id="store-table" class="table store-locator"></table>
					</div>
				</div> <!-- row -->
				<?php
				} ?>

      </div> <!-- container-fluid -->
		</main><!-- #main -->
	</div><!-- #primary -->

	<?php
		$i = 1;
		$storeSize = count($data);
		echo '<div id="store-data" style="display:none;">[';
		foreach ($data as $store) {

			$brandsArray = "";
			$brands = unserialize($store->brand);
			$lastElement = end($brands);

			foreach($brands as $b){
				$brandsArray .= '"' . $b . '"';
				if ($b != $lastElement){
					$brandsArray .= ', ';
				}
			}

			$storeJSON = 	'{'.
										'"address": "' 	. $store->address . '", '.
										'"name": "' 		. $store->store_name . '", '.
										'"brand": [' 		. $brandsArray . '], '.
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
		echo ']</div>';?>
	<br />
	<?php if (!$firstVisit) { ?>
		<script>
			var table; //$('#store-table').DataTable();
			var markers = [];
			var map;
			var zoom = <?php echo $zoom; ?>;
			var zip = <?php echo $zip ?>;
			var brand = "<?php echo $brand ?>";
			var infowindow;
		</script>
	<?php
		} else { ?>
		<script> function newMap() { return true; } </script>
	<?php
		}
  get_footer();  ?>

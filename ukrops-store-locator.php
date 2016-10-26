<?php
/*
Plugin Name: Ukrops Store Locator
Version: 1.0
Author: Taylor Fuqua
*/

class Ukrops_Store_Locator {


	/* A reference to an instance of this class. */
	private static $instance;

	/* The array of templates that this plugin tracks. */
	protected $templates;

	/* Returns an instance of this class. */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new Ukrops_Store_Locator();
		}

		return self::$instance;
	}

	/* Initializes the plugin by setting filters and administration functions.*/
	private function __construct() {

		$this->templates = array();

		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ));

		// Add a filter to the save post to inject out template into the page cache
		add_filter('wp_insert_post_data',array( $this, 'register_project_templates' ));

		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter('template_include', array( $this, 'view_project_template'));

		// Add your templates to this array.
		$this->templates = array('lib/template.php' => 'Store Locator',);
	}

	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {


		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( !isset( $this->templates[get_post_meta($post->ID, '_wp_page_template', true)] ) ) {
			return $template;
		}

		$file = plugin_dir_path(__FILE__). get_post_meta($post->ID, '_wp_page_template', true);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {

			$data = self::getStores();
			$zip = $_GET["zip"];
			$radius = $_GET["radius"];
			$zoom = null;
			$radiusList = [5, 10, 25, 50];

			if ($radius == null){
				$radius = 10;
			}

			//Set Map zoom based on radius requested
			switch ($radius) {
		    case 5:
					$zoom = 12;
	        break;
		    case 10:
					$zoom = 10;
	        break;
		    case 25:
	        $zoom = 9;
					break;
				case 50:
	        $zoom = 8;
					break;
		    default:
		  		$zoom = 10;
				}

			if ($zip == null){
				$location['latitude'] = 37.5407;
				$location['longitude'] = -77.4360;
			} else {
				$location = self::googleAPILookup($zip);
			}

			include($file);
			return;

		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

	public function init(){
		self::setupDB();
		add_action( 'admin_menu', array( 'Ukrops_Store_Locator', 'add_menu_item' ));
		add_action( 'admin_post_store_form', array( 'Ukrops_Store_Locator', 'handle_post' )); //Handle Post
	}

	public function store_locator_form() {
		$data = self::getStores();
		include('lib/form.php');
	}

	public function add_menu_item() {
		add_menu_page('Page title', 'Stores', 'manage_options', 'stores',
		array( 'Ukrops_Store_Locator', 'store_locator_form'));
	}

	public function handle_post(){

		if(isset($_POST["submit"])) {

	    $file = $_FILES['store-file'];
	    $csvfile = fopen($file['tmp_name'], "r");

			$i = 0;
	    while (($line = fgetcsv($csvfile)) !== FALSE) {
				if ($i > 0) { //So We Dont' Insert heading row
					self::insertRecord($line);
				}
				$i++;
	    }

	    fclose($csvfile);
	  }
	}

	public function getStores(){
		global $wpdb;
		$table_name = $wpdb->prefix . "stores";

		$rows = $wpdb->get_results( "SELECT * FROM $table_name" );
		return ($rows);
	}

	public function insertRecord($line){

		global $wpdb;
		$table_name = $wpdb->prefix . "stores";
		$address = $line[3]. ', '.$line[4] . '+' . $line[5] . '+' . $line[6];

		$addressInfo = self::googleAPILookup($address);
		$lat = null;
		$long = null;

		if ($addressInfo != null){
			$lat = $addressInfo['latitude'];
			$long = $addressInfo['longitude'];
		}

		$wpdb->insert(
			$table_name,
			array(
				'id' 					=> $line[0],
				'brand'	 			=> $line[1],
				'store_name'	=> $line[2],
				'address' 		=> $line[3],
				'city' 				=> $line[4],
				'state' 			=> $line[5],
				'zip'					=> $line[6],
				'phone' 			=> $line[7],
				'latitude' 		=> $lat,
				'longitude'   => $long,
			)
		);
	}

	public function googleAPILookup($address){

	$address = str_replace (" ", "+", urlencode($address));
	$details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyDV9ffngNmBtR8tC_9g37OL7QZhEheyxQw";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $details_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = json_decode(curl_exec($ch), true);

	// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	if ($response['status'] != 'OK') {
	 	return null;
	}

	$geometry = $response['results'][0]['geometry'];

	 $longitude = $geometry['location']['lat'];
	 $latitude = $geometry['location']['lng'];

	 $array = array(
			 'longitude' => $geometry['location']['lng'],
			 'latitude' => $geometry['location']['lat'],
			 'location_type' => $geometry['location_type'],
	 );

	 return $array;
	}

	public function setupDB(){
		global $wpdb;

    $table_name = $wpdb->prefix . "stores";
		$charset_collate = $wpdb->get_charset_collate();

		//if( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
			$sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  brand varchar(55) NOT NULL,
			  store_name varchar(200) NOT NULL,
				address tinytext NOT NULL,
				city varchar(55) NOT NULL,
				state varchar(55) NOT NULL,
				zip varchar(10) NOT NULL,
				phone varchar(25) NOT NULL,
				latitude double DEFAULT NULL,
				longitude double DEFAULT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
	}
}

add_action('init', array( 'Ukrops_Store_Locator', 'init'));
add_action( 'plugins_loaded', array( 'Ukrops_Store_Locator', 'get_instance' ) )
?>

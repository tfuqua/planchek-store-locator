<?php
/*
Plugin Name: Ukrops Store Locator
Version: 1.0
Author: Taylor Fuqua
*/

class Ukrops_Store_Locator {



	private static $instance;
	protected $templates;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new Ukrops_Store_Locator();
		}

		return self::$instance;
	}


	private function __construct() {

		$this->templates = array();

		add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ));
		add_filter('wp_insert_post_data',array( $this, 'register_project_templates' ));
		add_filter('template_include', array( $this, 'view_project_template'));

		$this->templates = array('lib/store-locator.php' => 'Store Locator',);
	}

	public function register_project_templates( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		wp_cache_delete( $cache_key , 'themes');
		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	public function view_project_template( $template ) {


		global $post;

		if ( !$post ) {
			return $template;
		}

		if ( !isset( $this->templates[get_post_meta($post->ID, '_wp_page_template', true)] ) ) {
			return $template;
		}

		$file = plugin_dir_path(__FILE__). get_post_meta($post->ID, '_wp_page_template', true);

		if ( file_exists( $file ) ) {

			$data = self::getStores();
			$brandList = self::getBrands();

			$zip = $_GET["zip"];
			$brand = $_GET["brand"];
			$zoom = 10;

			$firstVisit = false;
			if ($zip == null && $brand == null){
				$firstVisit = true;
			}

			if ($zip == null){

				$ip = $_SERVER['REMOTE_ADDR'];
				$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
				$zip =$details->postal;

				if ($zip == null){
					$zip = 23219;
				}
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
		add_menu_page('Page title', 'Store Locator', 'manage_options', 'stores', array( 'Ukrops_Store_Locator', 'store_locator_form'));
	}

	public function handle_post(){

		if(isset($_POST["submit"])) {

	    $file = $_FILES['store-file'];
	    $csvfile = fopen($file['tmp_name'], "r");

			self::deleteRecords();

			$i = 0;
	    while (($line = fgetcsv($csvfile)) !== FALSE) {
				if ($i > 0) { //So We Dont' Insert heading row
					self::insertRecord($line);
				}
				$i++;
	    }

	    fclose($csvfile);
	  }

		//wp_redirect(admin_url('admin.php?page=stores'));
	}

	public function getStores(){
		global $wpdb;
		$table_name = $wpdb->prefix . "stores";

		$rows = $wpdb->get_results( "SELECT * FROM $table_name" );

		return ($rows);
	}

	public function deleteRecords(){
		global $wpdb;
		$table_name = $wpdb->prefix . "stores";

		$sql = "DELETE FROM $table_name WHERE id > 0";
		$wpdb->query($sql);
	}

	public function insertRecord($line, $id){

		global $wpdb;
		$table_name = $wpdb->prefix . "stores";
		$address = $line[2]. ', '.$line[3] . '+' . $line[4] . '+' . $line[5];

		$addressInfo = self::googleAPILookup($address);
		$lat = null;
		$long = null;

		if ($addressInfo != null){
			$lat = $addressInfo['latitude'];
			$long = $addressInfo['longitude'];
		}

		echo $addressInfo['latitude'] . ' , ' . $addressInfo['longitude'] . ' - ';
		echo $address;
		echo '<br/></br/>';

		$wpdb->insert(
			$table_name,
			array(
				'id' 					=> $id,
				'brand'	 			=> $line[0],
				'store_name'	=> $line[1],
				'address' 		=> $line[2],
				'city' 				=> $line[3],
				'state' 			=> $line[4],
				'zip'					=> $line[5],
				'phone' 			=> $line[6],
				'products'  	=> $line[7],
				'latitude' 		=> $lat,
				'longitude'   => $long,
			)
		);
	}

	public function getBrands(){
		global $wpdb;
		$table_name = $wpdb->prefix . "stores";

		$sql = "SELECT DISTINCT brand from $table_name";
		$brands = $wpdb->get_results("SELECT DISTINCT brand from $table_name");

		return ($brands);
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

			$sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  brand varchar(55) NOT NULL,
			  store_name varchar(200) NOT NULL,
				address tinytext NOT NULL,
				city varchar(55) NOT NULL,
				state varchar(55) NOT NULL,
				zip varchar(10) NOT NULL,
				phone varchar(25) NOT NULL,
				products varchar(255) NOT NULL,
				latitude double DEFAULT NULL,
				longitude double DEFAULT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
	}
}

function store_scripts() {

	//Only load scripts on page that needs them
	if (get_page_template_slug() == 'lib/store-locator.php') {
		wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js' , array('ukrops-jquery'), '1.0', true);
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDV9ffngNmBtR8tC_9g37OL7QZhEheyxQw&libraries=geometry', array(), '1.0', true);
		wp_enqueue_script( 'store-js', plugin_dir_url( __FILE__ ) . 'lib/js/store-locator.js', array('google-maps'), '1.0', true);
	} else {

	}
}

add_action( 'wp_enqueue_scripts', 'store_scripts' );

add_action('init', array( 'Ukrops_Store_Locator', 'init'));
add_action( 'plugins_loaded', array( 'Ukrops_Store_Locator', 'get_instance' ) )
?>

<?php
/*
Plugin Name: Ukrops Store Locator
Version: 1.0
Author: Taylor Fuqua
*/

class Ukrops_Store_Plugin {

	private static $instance;
	protected $templates;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new Ukrops_Store_Plugin();
		}

		return self::$instance;
	}


	private function __construct() {
		include('lib/ukrops-store-locator.php');
		$this->templates = array();

		add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ));
		add_filter('wp_insert_post_data',array( $this, 'register_project_templates' ));
		add_filter('template_include', array($this, 'view_project_template'));

		$this->templates = array('lib/views/store-locator.php' => 'Store Locator',);
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

	public function init(){
		self::setupDB();
		add_action( 'admin_menu', array( 'Ukrops_Store_Plugin', 'create_menus' ));
		add_action( 'admin_post_store_form', handle_post()); //Handle Post
	}

	public function create_menus() {


		add_menu_page('Store Locator', 'Store Data', 'manage_options',
		'stores', 'store_table');

		add_submenu_page('stores', 'Page Title', 'Import Stores', 'manage_options',
		'data', 'import_stores');
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

			$data = getStores();
			$brandList = getBrands();

			$zip = $_GET["zip"];
			$brand = $_GET["brand"];
			$brand = urldecode(stripslashes($brand));

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

	public function setupDB(){
		global $wpdb;

    $table_name = $wpdb->prefix . "stores";
		$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  brand varchar(255) NOT NULL,
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
	if (get_page_template_slug() == 'lib/views/store-locator.php') {
		wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js' , array('ukrops-jquery'), '1.0', true);
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDV9ffngNmBtR8tC_9g37OL7QZhEheyxQw&libraries=geometry', array(), '1.0', true);
		wp_enqueue_script( 'store-js', plugin_dir_url( __FILE__ ) . 'lib/js/store-locator.js', array('google-maps'), '1.0', true);
	} else {

	}
}


function load_admin_style($hook) {

				wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) . 'lib/css/bootstrap.css', '1.0', false);
				wp_enqueue_style( 'admin-css', plugin_dir_url( __FILE__ ) . 'lib/css/admin.css', '1.0', false);
				
				if( $hook != 'toplevel_page_stores')
					return;

				wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js', true, '1.0');
				wp_enqueue_script( 'admin-js', plugin_dir_url( __FILE__ ) . 'lib/js/admin.js', array('datatables'), '1.0', true);

				wp_enqueue_style( 'datatable-css', 'https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css', '1.0', false);

}

add_action( 'admin_enqueue_scripts', 'load_admin_style' );
add_action( 'wp_enqueue_scripts', 'store_scripts');
add_action('init', array( 'Ukrops_Store_Plugin', 'init'));
add_action( 'plugins_loaded', array( 'Ukrops_Store_Plugin', 'get_instance' ) )

?>

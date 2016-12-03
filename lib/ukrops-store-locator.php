<?php


function import_stores() {
	$data = getStores();
	include('views/import-stores.php');
}

function store_table() {
	$id = $_GET["id"];

	if (isset($id)){ //Edit View
		$data = getStoreById($id);
	  include('views/edit-store.php');

	} else { //Table View
		$data = getStores();
	  include('views/store-table.php');
	}

}

function google_api_key() {
	$data = getAPIKey();
	include('views/google-api.php');
}

function edit_store() {
  $data = getStores();
  include('views/edit.php');
}

function handle_post(){

	if(isset($_POST["submit"]) && isset($_FILES['store-file'])) {

    $file = $_FILES['store-file'];
    $csvfile = fopen($file['tmp_name'], "r");

		deleteRecords();

		$i = 0;

    while (($line = fgetcsv($csvfile)) !== FALSE) {
			if ($i > 0) { //So We Dont' Insert heading row
				insertRecord($line, $i);
			}
			$i++;
    }

    fclose($csvfile);

		wp_redirect(admin_url('admin.php?page=stores'));
  }
}

function handle_google_api_post(){

	if(isset($_POST["submit"]) && $_POST["action"] == "google_api_key") {
		$key = $_POST["key"];

		global $wpdb;
		$table_name = $wpdb->prefix . "stores_google_api";
		$wpdb->query($wpdb->prepare("UPDATE $table_name SET api_key='$key' WHERE id= %d", 1));


		wp_redirect(admin_url('admin.php?page=api_key'));
	}
}

function handle_store_post(){

	if(isset($_POST["submit"]) && $_POST["action"] == "edit_store") {

		$line = array();
		$id 		 = $_POST["id"];
		$line[0] = stripslashes($_POST["brands"]);
		$line[1] = $_POST["store_name"];
		$line[2] = $_POST["address"];
		$line[3] = $_POST["city"];
		$line[4] = $_POST["state"];
		$line[5] = $_POST["zip"];
		$line[6] = $_POST["phone"];
		$line[7] = $_POST["products"];

		insertRecord($line, $id);

		wp_redirect(admin_url('admin.php?page=stores'));
	}
}

function getStores(){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores";
	$rows = $wpdb->get_results( "SELECT * FROM $table_name" );
	return ($rows);
}

function getAPIKey(){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores_google_api";
	$rows = $wpdb->get_results( "SELECT * FROM $table_name where ID = 1" );

	return ($rows[0]);
}

function getStoreById($id){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores";
	$rows = $wpdb->get_results( "SELECT * FROM $table_name where ID=$id" );
	return ($rows[0]);
}

function deleteRecords(){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores";

	$sql = "DELETE FROM $table_name WHERE id > 0";
	$wpdb->query($sql);
}

function insertRecord($line, $id){

	$valid = validateStore($line);

	global $wpdb;
	$table_name = $wpdb->prefix . "stores";
	$address = $line[2]. ', '.$line[3] . '+' . $line[4] . '+' . $line[5];

	$addressInfo = googleAPILookup($address);
	$lat = null;
	$long = null;

	if ($addressInfo != null){
		$lat = $addressInfo['latitude'];
		$long = $addressInfo['longitude'];

		$brands = trimBrands($line[0]);

		$wpdb->replace(
			$table_name,
			array(
				'id' 					=> $id,
				'brand'	 			=> serialize($brands),
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
}


function updateStore($line, $id){

	$valid = validateStore($line);

	global $wpdb;
	$table_name = $wpdb->prefix . "stores";
	$address = $line[2]. ', '.$line[3] . '+' . $line[4] . '+' . $line[5];

	$addressInfo = googleAPILookup($address);
	$lat = null;
	$long = null;

	if ($addressInfo != null){
		$lat = $addressInfo['latitude'];
		$long = $addressInfo['longitude'];

		$brands = trimBrands($line[0]);
		$brands = serialize($brands);

				$query = "UPDATE $table_name SET
									brand 			= '$brands',
									store_name 	= '$line[1]',
									address 		= '$line[2]',
									city 				= '$line[3]',
									state 			=	'$line[4]',
									zip					= '$line[5]',
									phone 			= '$line[6]',
									products  	= '$line[7]',
									latitude 		= '$lat',
									longitude   = '$long'
									WHERE id = %d";

				$wpdb->query($wpdb->prepare($query, $id));

			return;
	}
}

function validateStore($store){

	$brands = $store[0];
	$name = $store[1];
	$address = $store[2];
	$city = $store[3];
	$state = $store[4];
	$zip = $store[5];
	$phone = $store[6];
	$products = $store[7];

	$brands = explode(',', $brands);

}

function trimBrands($line){

	$brandsArr = array();
	$brands = explode(',', $line);

	foreach($brands as $brand){
		if (!empty($brand)) {
			$brand = trim($brand);
			array_push($brandsArr, $brand);
		}
	}

	return $brandsArr;
}


function getBrands(){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores";

	$sql = "SELECT DISTINCT brand from $table_name";
	$results = $wpdb->get_results("SELECT DISTINCT brand from $table_name");

	$resultMap = array();

	foreach($results as $result){
		foreach(unserialize($result->brand) as $brand){
			$resultMap[$brand] = $brand;
		}
	}

	return $resultMap;
}

function googleAPILookup($address){

	$creds = getAPIKey();
  $address = str_replace (" ", "+", urlencode($address));
  $details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$creds->api_key;

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

?>

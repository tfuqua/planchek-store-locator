<?php


function import_stores() {
	$data = getStores();
	include('views/import-stores.php');
}


function store_table() {
  $data = getStores();
  include('views/store-table.php');
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

function getStores(){
	global $wpdb;
	$table_name = $wpdb->prefix . "stores";

	$rows = $wpdb->get_results( "SELECT * FROM $table_name" );

	return ($rows);
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

		$wpdb->insert(
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
		if (! empty($brand)) {
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

  $address = str_replace (" ", "+", urlencode($address));
  $details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyAGkVBDxjmBGDRlTyqiTLi5qJUu4AP_S3o";

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

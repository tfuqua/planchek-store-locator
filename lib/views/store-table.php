
<div class="wrap">
<h1>Ukrops Store Data</h1>

<br />

<table id="store-table" class="table table-bordered table-striped store-locator"></table>

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
                  '"id": "'       . $store->id      .'", '.
                  '"address": "' 	. $store->address . '", '.
                  '"name": "' 		. $store->store_name . '", '.
                  '"brand": [' 		. $brandsArray . '], '.
                  '"phone": "' 		. $store->phone . '", '.
                  '"city": "' 		. $store->city . '", '.
                  '"state": "' 		. $store->state . '", '.
                  '"zip": "'      . $store->zip   . '", '.
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

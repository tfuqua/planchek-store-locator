
<div class="wrap">


<h3>Urkops Store Data</h3>

<table class="table" style="width:100%; border:1px solid #666;">
  <tr>
    <th>Brand</th>
    <th>Store Name</th>
    <th>Address</th>
    <th>State</th>
    <th>Zip</th>
    <th>Location</th>
    <th>Phone</th>
  </tr>
  <?php
  if (count($data) > 0) {
    foreach ($data as $store) { ?>
        <tr>
          <td>
            <?php
              $brands = unserialize($store->brand);
              $lastElement = end($brands);

              foreach($brands as $brand){
                echo $brand;
                if ($brand != $lastElement){
                  echo ', ';
                }
              }
            ?>
          </td>
          <td><?php echo $store->store_name ?></td>
          <td><?php echo $store->address ?></td>
          <td><?php echo $store->state ?></td>
          <td><?php echo $store->zip ?></td>
          <td><?php echo $store->latitude; echo $store->longitude; ?></td>
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
<?php

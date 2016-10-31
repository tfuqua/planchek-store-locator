
<div class="wrap">

<h2>Store Upload</h2>
<hr />


<form method="POST" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
<input type="hidden" name="action" value="store_form">

<div class="form-group">
  <label><b>Upload Store CSV File</b></label> <br/>
  <input type="file" name="store-file" value=""
  accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
</div>

<p class="submit">
<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Upload File') ?>" />
</p>

</form>
</div>
<hr / />
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
          <td><?php echo $store->brand?></td>
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

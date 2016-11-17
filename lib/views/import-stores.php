<?php
  $download = plugins_url() . '/ukrops-store-locator/lib/sample.csv';
?>

<div class="wrap">
  <h1>Store Upload</h1>
  <hr />

  <div class="ukrops-store">
    <p>

      You can use this upload feature to update Ukrop's store data. Please upload a CSV file matching the format below.
      Click <a href="<?php echo $download; ?>">Here</a> to download a sample import file.
    </p>

      <ul>
        <li>Brand (comma delimited list)</li>
        <li>Store Name</li>
        <li>Address</li>
        <li>City</li>
        <li>State</li>
        <li>Zip</li>
        <li>Phone</li>
        <li>Products</li>
      </ul>
  </div>

  <hr />
  <div class="well">
    <form method="POST" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="store_form">

    <div class="form-group">
      <label>Upload Store CSV File</label><br />
      <input type="file" name="store-file" value=""
      accept=".csv" />
    </div>

    <p class="submit">
    <input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Upload File') ?>" />
    </p>

    </form>
  </div>
</div>

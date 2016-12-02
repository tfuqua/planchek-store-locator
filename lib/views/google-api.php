<div class="wrap">
<h1>Google API Key</h1>
<hr />
<br/>

<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
<input type="hidden" name="action" value="google_api_key">

<div class="form-group">
  <label><b>Google API Key</b></label><br />
  <input name="key" type="text" value="<?php echo $data->api_key ?>" style="width:350px;" />
</div>

<p class="submit">
  <input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Save') ?>" />
</p>

</form>

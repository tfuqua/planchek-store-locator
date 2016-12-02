<style>
  input.field{
    width:300px;
  }
</style>

<div class="wrap">
  <h1>Edit Store</h1>
  <hr />

  <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
  <input type="hidden" name="action" value="edit_store" />
  <input type="hidden" name="id" value="<?php echo $data->id ?>"/>

  <table class="form-table">
    <tr valign="top">
    <th scope="row">Store Name</th>
    <td><input type="text" class="field" name="store_name" value="<?php echo esc_attr($data->store_name); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">Brands</th>
    <?php $brands = unserialize($data->brand); ?>
    <td><?php  ?><input type="text" class="field" name="brands" value="<?php echo implode(', ', $brands); ?>"/></td>
    </tr>

    <tr valign="top">
    <th scope="row">Address</th>
    <td><input type="text" class="field" name="address" value="<?php echo esc_attr($data->address); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">City</th>
    <td><input type="text" class="field" name="city" value="<?php echo esc_attr($data->city); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">State</th>
    <td><input type="text" class="field" name="state" value="<?php echo esc_attr($data->state); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">Zip</th>
    <td><input type="text" class="field" name="zip" value="<?php echo esc_attr($data->zip); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">Phone</th>
    <td><input type="text" class="field" name="phone" value="<?php echo esc_attr($data->phone); ?>" /></td>
    </tr>

    <tr valign="top">
    <th scope="row">Products</th>
    <td><input type="text" class="field" name="products" value="<?php echo esc_attr($data->products); ?>" /></td>
    </tr>

    <tr valign="top">
      <th></th>
      <td><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Save') ?>" /></td>
    </tr>

  </table>

  <p class="submit">

  </p>

  </form>

</div>

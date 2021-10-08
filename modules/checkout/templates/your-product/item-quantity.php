<?php
/**
 * Quantity option
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$quantity_hidden = '';
if ( 'yes' !== self::$is_quantity ) {
	$quantity_hidden = 'wcf-qty-hidden';
}
?>
<div class="wcf-qty  <?php echo $quantity_hidden; ?>">
	<input autocomplete="off" type="number" value="<?php echo $data['default_quantity']; ?>" step="<?php echo $data['default_quantity']; ?>" min="<?php echo $data['default_quantity']; ?>" name="wcf_qty_selection" class="wcf-qty-selection">
</div>

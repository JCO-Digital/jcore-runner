<?php
/**
 * Generic input template.
 *
 * @package Jcore\Runner
 */

?>

<label class="jcore-input-label">
	<?php echo esc_html( $input['title'] ); ?>
	<input
	type="<?php echo esc_attr( $input['type'] ); ?>"
	data-jcore-input="<?php echo esc_attr( $params['id'] ); ?>"
	name="<?php echo esc_attr( $field ); ?>"
	value="<?php echo esc_attr( $input['default'] ); ?>" />
</label>

<?php
/**
 * Select input template.
 *
 * @package Jcore\Runner
 */

?>

<label class="jcore-input-label">
	<?php echo esc_html( $input['title'] ); ?>
	<select id="<?php echo esc_attr( $field ); ?>"
			name="<?php echo esc_attr( $field ); ?>"
			<?php
			if ( isset( $input['multiple'] ) ) :
				?>
				multiple="multiple"<?php endif; ?>
			data-jcore-input="<?php echo esc_attr( $params['id'] ); ?>">
		<?php foreach ( $input['options'] as $key => $option ) : ?>
		<option value="<?php echo esc_attr( $key ); ?>"
				<?php
				if ( $key === $input['default'] ) :
					?>
					selected<?php endif; ?>>
			<?php echo esc_html( $option ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	<script>
	jQuery(document).ready(function($) {
		$('#<?php echo esc_js( $field ); ?>').select2();
	})
	</script>
</label>

<select id="<?php echo esc_html( $field ); ?>"
        name="<?php echo esc_html( $field ) ?>"
        <?php if ( isset( $input['multiple'] ) ) : ?>multiple="multiple"<?php endif; ?>
		data-jcore-input="<?php echo esc_html( $params['id'] ) ?>">
	  <?php foreach ( $input['options'] as $key => $option ) : ?>
		<option value="<?php echo esc_html( $key ) ?>"
				<?php if ( $key === $input['default'] ) : ?>selected<?php endif; ?>>
		  <?php echo esc_html( $option ) ?>
		</option>
	  <?php endforeach; ?>
</select>
<script>
  jQuery(document).ready(function($) {
    $('#<?php echo esc_html( $field ); ?>').select2();
  })
</script>

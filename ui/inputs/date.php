<?php
  try {
    $default = new DateTime( $input['default'] );
    $default = $default->format( 'U' );
  } catch ( Exception $e ) {
    return;
  }
?>

<label class="jcore-input-label">
	<?php echo esc_html( $input['title'] ); ?>
  <input type="hidden"
         id="<?php echo esc_html( $field ); ?>"
         name="<?php echo esc_html( $field ); ?>"
         value="<?php echo esc_html( $default ); ?>"
         data-jcore-input="<?php echo esc_html( $params['id'] ); ?>"
  />
	<input type="text"
         class="regular-text"
         name="<?php echo esc_html( $field ); ?>-display"
         id="<?php echo esc_html( $field ); ?>-display"
         value="<?php echo esc_html( $input['default'] ); ?>"
      />
	<script>
      jQuery(document).ready(function($) {
        $('#<?php echo esc_js( $field ); ?>-display').datepicker({
          dateFormat: "dd.mm.yy",
          altField: "#<?php echo esc_js( $field ); ?>",
          altFormat: "@",
        });
      });
	</script>
</label>

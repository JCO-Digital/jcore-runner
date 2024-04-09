<label class="jcore-input-label">
  <?php echo esc_html( $input['title'] ) ?>
  <input
    type="<?php echo esc_html( $input['type'] ) ?>"
    data-jcore-input="<?php echo esc_html( $params['id'] ) ?>"
    name="<?php echo esc_html( $field ) ?>"
    value="<?php echo esc_html( $input['default'] ) ?>" />
</label>

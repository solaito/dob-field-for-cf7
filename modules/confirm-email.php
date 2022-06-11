<?php
if ( function_exists( 'wpcf7_add_form_tag' ) ) {
	wpcf7_add_form_tag( 'confirm_email', 'watts_confirm_email_form_tag_handler', true );
	wpcf7_add_form_tag( 'confirm_email*', 'watts_confirm_email_form_tag_handler', true );
}

function watts_confirm_email_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

	$class .= ' wpcf7-validates-as-' . $tag->basetype;

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class']    = $tag->get_class_option( $class );
	$atts['id']       = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$atts['autocomplete'] = $tag->get_option( 'autocomplete',
		'[-0-9a-zA-Z]+', true );

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid']     = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	} else {
		$atts['aria-invalid'] = 'false';
	}

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' )
		 or $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value               = '';
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( wpcf7_support_html5() ) {
		$atts['type'] = 'email';
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts['data-target-name'] = $tag->get_option( 'target' )[0];

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error
	);

	return $html;
}

add_filter( 'wpcf7_validate_confirm_email', 'watts_confirm_email_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_confirm_email*', 'watts_confirm_email_validation_filter', 10, 2 );

function watts_confirm_email_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[ $name ] )
		? trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", " " ) ) )
		: '';

	if ( $tag->is_required() and '' === $value ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	} elseif ( '' !== $value and ! wpcf7_is_email( $value ) ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );
	}
	$target       = $tag->get_option( 'target' )[0];
	$target_value = isset( $_POST[ $target ] )
		? trim( wp_unslash( strtr( (string) $_POST[ $target ], "\n", " " ) ) )
		: '';
	if ( $value !== $target_value ) {
		$result->invalidate( $tag, __( 'Email addresses you entered do not match.', 'watts' ) );
	}

	return $result;
}

add_action( 'wpcf7_admin_init', 'watts_add_tag_generator_confirm_email', 16, 0 );

function watts_add_tag_generator_confirm_email() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'confirm-email', __( 'confirm email', 'watts' ),
		'watts_tag_generator_confirm_email' );
}

function watts_tag_generator_confirm_email( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'confirm_email';

	$description = __( "Generate a form-tag for a single-line confirm email address input field.", 'watts' );

	?>
	<div class="control-box">
		<fieldset>
			<legend><?php echo esc_html( $description ); ?></legend>

			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><?php echo esc_html( __( 'Field type', 'watts' ) ); ?></th>
					<td>
						<fieldset>
							<legend
								class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'watts' ) ); ?></legend>
							<label><input type="checkbox"
										  name="required"/> <?php echo esc_html( __( 'Required field', 'watts' ) ); ?>
							</label>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th scope="row"><label
							for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'watts' ) ); ?></label>
					</th>
					<td><input type="text" name="name" class="tg-name oneline"
							   id="<?php echo esc_attr( $args['content'] . '-name' ); ?>"/></td>
				</tr>

				<tr>
					<th scope="row"><label
							for="<?php echo esc_attr( $args['content'] . '-target' ); ?>"><?php echo esc_html( __( 'Target name*', 'watts' ) ); ?></label>
					</th>
					<td><input type="text" name="target" class="targetvalue oneline option"
							   id="<?php echo esc_attr( $args['content'] . '-target' ); ?>"/></td>
				</tr>

				<tr>
					<th scope="row"><label
							for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'watts' ) ); ?></label>
					</th>
					<td><input type="text" name="values" class="oneline"
							   id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"/><br/>
				</tr>

				<tr>
					<th scope="row"><label
							for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'watts' ) ); ?></label>
					</th>
					<td><input type="text" name="id" class="idvalue oneline option"
							   id="<?php echo esc_attr( $args['content'] . '-id' ); ?>"/></td>
				</tr>

				<tr>
					<th scope="row"><label
							for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'watts' ) ); ?></label>
					</th>
					<td><input type="text" name="class" class="classvalue oneline option"
							   id="<?php echo esc_attr( $args['content'] . '-class' ); ?>"/></td>
				</tr>
				</tbody>
			</table>
		</fieldset>
	</div>

	<div class="insert-box">
		<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()"/>

		<div class="submitbox">
			<input type="button" class="button button-primary insert-tag"
				   value="<?php echo esc_attr( __( 'Insert Tag', 'watts' ) ); ?>"/>
		</div>

		<br class="clear"/>

		<p class="description mail-tag"><label
				for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'watts' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?>
				<input type="text" class="mail-tag code hidden" readonly="readonly"
					   id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"/></label></p>
	</div>
	<?php
}

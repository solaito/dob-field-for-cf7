<?php

class Watts_Options_Page {
	const PAGE = 'watts-admin';
	const SECTIONS = [
		'VALIDATION' => 'watts_validation_setting_section',
		'CHAR_WIDTH' => 'watts_char_width_setting_section'
	];
	const IDS = [
		'REALTIME_VALIDATION_ENABLE' => 'realtime_validation_enable',
		'VALIDATE_ICON_ENABLE'       => 'validate_icon_enable',
		'VALIDATE_ICON_SIZE'         => 'validate_icon_size',
		'FULL_TO_HALF_ENABLE'        => 'full_to_half_enable'
	];

	private $watts_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'watts_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'watts_page_init' ) );
		add_filter( 'plugin_action_links_' . WATTS_PLUGIN_BASENAME, array( $this, 'watts_settings_link' ) );
	}

	public function watts_settings_link( $links ) {
		$link = "<a href='" . esc_url( add_query_arg( 'page', 'watts', get_admin_url() . 'options-general.php' ) ) . "'>" . __( 'Settings' ) . '</a>';
		array_unshift( $links, $link );

		return $links;
	}

	public function watts_add_plugin_page() {
		add_options_page(
			'Watts', // page_title
			'Watts', // menu_title
			'manage_options', // capability
			'watts', // menu_slug
			array( $this, 'watts_create_admin_page' ) // function
		);
	}

	public function watts_create_admin_page() {
		$this->watts_options = get_option( 'watts_option_name' ); ?>

		<div class="wrap">
			<h1>Watts</h1>
			<p></p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'watts_option_group' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
	<?php }

	public function watts_page_init() {
		register_setting(
			'watts_option_group', // option_group
			'watts_option_name', // option_name
			array( $this, 'watts_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			self::SECTIONS['VALIDATION'], // id
			__( 'Real-time validation settings' ), // title
			array( $this, 'watts_section_info' ), // callback
			self::PAGE // page
		);

		add_settings_field(
			self::IDS['REALTIME_VALIDATION_ENABLE'], // id
			__( 'Validate the user\'s input real time' ), // title
			array( $this, 'display_checkbox_callback' ), // callback
			self::PAGE, // page
			self::SECTIONS['VALIDATION'], // section
			array( self::IDS['REALTIME_VALIDATION_ENABLE'] ) // args
		);

		add_settings_field(
			self::IDS['VALIDATE_ICON_ENABLE'], // id
			__( 'Displays an icon showing the validation result' ), // title
			array( $this, 'display_checkbox_callback' ), // callback
			self::PAGE, // page
			self::SECTIONS['VALIDATION'], // section
			array( self::IDS['VALIDATE_ICON_ENABLE'] ) // args
		);

		add_settings_field(
			self::IDS['VALIDATE_ICON_SIZE'], // id
			__( 'Choose validation icon size' ), // title
			array( $this, 'display_selectbox_callback' ), // callback
			self::PAGE, // page
			self::SECTIONS['VALIDATION'], // section
			array(
				self::IDS['VALIDATE_ICON_SIZE'],
				array( 'small' => __( 'Small' ), 'medium' => __( 'Medium' ), 'large' => __( 'Large' ) )
			) // args
		);

		add_settings_section(
			self::SECTIONS['CHAR_WIDTH'], // id
			__( 'Character width conversion settings' ), // title
			array( $this, 'watts_section_info' ), // callback
			self::PAGE // page
		);

		add_settings_field(
			self::IDS['FULL_TO_HALF_ENABLE'], // id
			__( 'Convert from Fullwidth to Halfwidth forms' ), // title
			array( $this, 'display_checkbox_callback' ), // callback
			self::PAGE, // page
			self::SECTIONS['CHAR_WIDTH'], // section
			array( self::IDS['FULL_TO_HALF_ENABLE'] ) // args
		);
	}

	public function watts_sanitize( $input ) {
		$sanitary_values = array();

		foreach ( self::IDS as $id ) {
			if ( isset( $input[ $id ] ) ) {
				$sanitary_values[ $id ] = $input[ $id ];
			}
		}

		return $sanitary_values;
	}

	public function watts_section_info() {

	}

	public function display_selectbox_callback( $args ) {
		$id      = $args[0];
		$options = $args[1];

		$str = sprintf( '<select name="watts_option_name[%s]" id="%s">', $id, $id );

		$option_selected = isset( $this->watts_options[ $id ] ) ? $this->watts_options[ $id ] : 'medium';
		foreach ( $options as $option => $label ) {
			$str .= sprintf( '<option value="%s" %s>%s</option>',
				$option,
				$option === $option_selected ? ' selected' : '',
				$label
			);
		}
		$str .= '</select>';
		echo $str;
	}

	public function display_checkbox_callback( $args ) {
		$id     = $args[0];
		$option = $this->watts_options[ $id ];
		printf(
			'<input type="checkbox" name="watts_option_name[%s]" id="%s" %s> <label for="%s">%s</label>',
			$id,
			$id,
			( isset( $option ) && $option === 'on' ) ? 'checked' : '',
			$id,
			__( 'On' )
		);
	}

}

if ( is_admin() ) {
	$watts = new Watts_Options_Page();
}

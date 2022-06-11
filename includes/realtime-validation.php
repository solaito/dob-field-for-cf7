<?php

class Watts_Realtime_Validation {
	const target_name = 'watts-validation-target';
	const excluded_basetypes = array( 'file', 'quiz' );

	public function main( WP_REST_Request $request ) {
		try {
			$url_params = $request->get_url_params();

			$contact_form = null;

			if ( ! empty( $url_params['id'] ) ) {
				if ( ! function_exists( 'wpcf7_contact_form' ) ) {
					throw new Watts_Dependency_Exception();
				}
				$contact_form = wpcf7_contact_form( $url_params['id'] );
			}

			if ( ! $contact_form ) {
				return new WP_Error( 'watts_wpcf7_not_found',
					__( "The requested contact form was not found.", 'watts' ),
					array( 'status' => 404 )
				);
			}

			if ( ! function_exists( 'wpcf7_sanitize_unit_tag' ) ) {
				throw new Watts_Dependency_Exception();
			}
			$unit_tag       = wpcf7_sanitize_unit_tag(
				$request->get_param( '_wpcf7_unit_tag' )
			);
			$invalid_fields = $this->validate( $request->get_param( self::target_name ), $contact_form );
			if ( $invalid_fields === false ) {
				return new WP_Error( 'watts_not_allowed_validation',
					__( "The requested validation was not allowed.", 'watts' ),
					array( 'status' => 404 )
				);
			}

			$result   = array(
				'contact_form_id' => $url_params['id'],
				'invalid_fields'  => $invalid_fields,
				'unit_tag'        => $unit_tag,
			);
			$response = $this->result_to_response( $result );
		} catch ( Watts_Dependency_Exception $e ) {
			return new WP_Error( 'watts_server_error',
				__( "We're sorry, a server error occurred auto validation.", 'watts' ),
				array( 'status' => 500 )
			);
		}

		return $response;
	}

	private function validate( $name, $contact_form ) {
		if ( ! class_exists( 'WPCF7_Validation' ) ) {
			throw new Watts_Dependency_Exception();
		}
		$validation = new WPCF7_Validation();
		if ( ! method_exists( $contact_form, 'scan_form_tags' ) ) {
			throw new Watts_Dependency_Exception();
		}
		$tags = $contact_form->scan_form_tags( array(
			'name' => $name,
		) );
		foreach ( $tags as $tag ) {
			if ( in_array( $tag['basetype'], self::excluded_basetypes, true ) ) {
				return false;
			}
			$type       = $tag->type;
			$validation = apply_filters( "wpcf7_validate_{$type}", $validation, $tag );
		}

		$validation = apply_filters( 'wpcf7_validate', $validation, $tags );
		if ( ! method_exists( $validation, 'get_invalid_fields' ) ) {
			throw new Watts_Dependency_Exception();
		}

		return $validation->get_invalid_fields();
	}

	private function result_to_response( $result ) {
		$response                    = array();
		$response['contact_form_id'] = $result['contact_form_id'];
		$response['into']            = sprintf( '#%s', $result['unit_tag'] );

		if ( empty( $result['invalid_fields'] ) ) {
			$response['status']         = 'validation_succeeded';
			$response['invalid_fields'] = array();
		} else {
			$response['status']         = 'validation_failed';
			$response['invalid_fields'] = $this->result_to_invalid_response( $result );
		}

		return $response;
	}

	private function result_to_invalid_response( $result ) {
		$invalid_fields = array();

		foreach ( (array) $result['invalid_fields'] as $name => $field ) {
			$name = sanitize_html_class( $name );

			$invalid_fields[] = array(
				'into'     => sprintf(
					'span.wpcf7-form-control-wrap.%s',
					$name
				),
				'message'  => $field['reason'],
				'idref'    => $field['idref'],
				'error_id' => sprintf(
					'%1$s-ve-%2$s',
					$result['unit_tag'],
					$name
				),
			);
		}

		return $invalid_fields;
	}
}

class Watts_Dependency_Exception extends Exception {
	public function __construct() {
		parent::__construct( 'Call to undefined dependency class, method, or function.' );
	}
}

<?php
require_once 'realtime-validation.php';

class Watts_Rest_Controller {
	const route_namespace = 'watts/v1';

	public function register_routes() {
		register_rest_route(
			self::route_namespace,
			'/(?P<id>\d+)/validation',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'realtime_validation' ),
			]
		);
	}

	public function realtime_validation( WP_REST_Request $request ) {
		$wrv      = new Watts_Realtime_Validation();
		$response = $wrv->main( $request );

		return rest_ensure_response( $response );
	}
}

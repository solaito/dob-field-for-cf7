<?php
require_once 'auto-validation.php';
class Watts_Rest_Controller
{
	const route_namespace = 'watts/v1';
	public function register_routes()
	{
		register_rest_route(
			self::route_namespace,
			'/(?P<id>\d+)/validation',
			[
				'methods' => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback' => array($this, 'auto_validation'),
			]
		);
	}

	public function auto_validation(WP_REST_Request $request)
	{
		$wav = new Watts_Auto_Validation();
		$response = $wav->main($request);
		return rest_ensure_response($response);
	}
}

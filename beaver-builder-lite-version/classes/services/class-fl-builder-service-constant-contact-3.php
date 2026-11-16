<?php

/**
 * Helper class for the Constant Contact API.
 *
 * @since 1.5.4
 */
final class FLBuilderServiceConstantContact3 extends FLBuilderService {

	/**
	 * The ID for this service.
	 *
	 * @since 1.5.4
	 * @var string $id
	 */
	public $id = 'constant-contact-3';

	/**
	 * The api url for this service.
	 *
	 * @since 1.5.4
	 * @var string $api_url
	 */
	public $api_url = 'https://api.cc.email/v3/';

	/**
	 * Test the API connection.
	 *
	 * @since 1.5.4
	 * @param array $fields {
	 *      @type string $api_key A valid API key.
	 *      @type string $access_token A valid access token.
	 * }
	 * @return array{
	 *      @type bool|string $error The error message or false if no error.
	 *      @type array $data An array of data used to make the connection.
	 * }
	 */
	public function connect( $fields = array() ) {
		$response      = array(
			'error' => false,
			'data'  => array(),
		);
		$missing_field = $this->get_missing_field_error( $fields );

		if ( $missing_field ) {
			$response['error'] = $missing_field;
			return $response;
		}

		$error = $this->get_lists_error( $this->request_lists( $fields['access_token'] ) );

		if ( $error ) {
			$response['error'] = $error;
			return $response;
		}

		$response['data'] = array(
			'client_id'     => $fields['client_id'],
			'client_secret' => $fields['client_secret'],
			'access_token'  => $fields['access_token'],
			'refresh_token' => $fields['refresh_token'],
		);

		return $response;
	}

	/**
	 * Get the message for the first required connection field that is missing.
	 *
	 * @since 2.12
	 * @param array $fields The submitted connection fields.
	 * @return string|bool The error message or false if every field is present.
	 */
	private function get_missing_field_error( array $fields ) {
		$messages = array(
			'access_token'  => __( 'Error: You must provide an Access Token.', 'fl-builder' ),
			'refresh_token' => __( 'Error: You must provide a Refresh Token.', 'fl-builder' ),
			'client_id'     => __( 'Error: You must provide a Client ID.', 'fl-builder' ),
			'client_secret' => __( 'Error: You must provide a Client Secret.', 'fl-builder' ),
		);

		foreach ( $messages as $field => $message ) {
			if ( empty( $fields[ $field ] ) ) {
				return $message;
			}
		}

		return false;
	}

	/**
	 * Request the contact lists for an access token.
	 *
	 * @since 2.12
	 * @param string $access_token A valid access token.
	 * @return array|WP_Error The remote request response.
	 */
	private function request_lists( string $access_token ) {
		$url = $this->api_url . 'contact_lists?include_count=true&status=active&include_membership_count=all';

		return wp_remote_get( $url, array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
		) );
	}

	/**
	 * Get the display error for a contact lists response.
	 *
	 * @since 2.12
	 * @param array|WP_Error $request The remote request response.
	 * @return string|bool The error message or false if the response carries lists.
	 */
	private function get_lists_error( $request ) {
		/* translators: %s: error */
		$format = __( 'Error: Could not connect to Constant Contact. %s', 'fl-builder' );

		if ( is_wp_error( $request ) ) {
			return sprintf( $format, $request->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $request ) );

		// A successful response is an object carrying a lists array; every error shape lacks one.
		if ( is_object( $body ) && isset( $body->lists ) && is_array( $body->lists ) ) {
			return false;
		}

		return sprintf( $format, $this->get_api_error_message( $body ) );
	}

	/**
	 * Get the message out of a decoded API error body.
	 *
	 * @since 2.12
	 * @param mixed $body The decoded response body.
	 * @return string The error message.
	 */
	private function get_api_error_message( $body ): string {
		// Validation errors arrive as an array of errors, auth errors as a bare object.
		if ( is_array( $body ) && isset( $body[0]->error_message ) ) {
			return $body[0]->error_message;
		}

		if ( is_object( $body ) && isset( $body->error_message ) ) {
			return $body->error_message;
		}

		return __( 'The API returned an unexpected response.', 'fl-builder' );
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.5.4
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();

		FLBuilder::render_settings_field( 'client_id', array(
			'row_class' => 'fl-builder-service-connect-row',
			'class'     => 'fl-builder-service-connect-input',
			'type'      => 'text',
			'label'     => __( 'Client ID', 'fl-builder' ),
			'help'      => __( 'Your Client ID.', 'fl-builder' ),
			'preview'   => array(
				'type' => 'none',
			),
		));

		FLBuilder::render_settings_field( 'client_secret', array(
			'row_class' => 'fl-builder-service-connect-row',
			'class'     => 'fl-builder-service-connect-input',
			'type'      => 'text',
			'label'     => __( 'Client Secret', 'fl-builder' ),
			'help'      => __( 'Your Client Secret.', 'fl-builder' ),
			'preview'   => array(
				'type' => 'none',
			),
		));

		FLBuilder::render_settings_field( 'access_token', array(
			'row_class' => 'fl-builder-service-connect-row',
			'class'     => 'fl-builder-service-connect-input',
			'type'      => 'textarea',
			'rows'      => 6,
			'label'     => __( 'Access Token', 'fl-builder' ),
			'help'      => __( 'Your generated Access Token.', 'fl-builder' ),
			'preview'   => array(
				'type' => 'none',
			),
		));

		FLBuilder::render_settings_field( 'refresh_token', array(
			'row_class'   => 'fl-builder-service-connect-row',
			'class'       => 'fl-builder-service-connect-input',
			'type'        => 'text',
			'label'       => __( 'Refresh Token', 'fl-builder' ),
			'help'        => __( 'Your generated Refresh Token.', 'fl-builder' ),
			'description' => sprintf(
				/* translators: 1: account link: 2: api key link: 3: line break*/
				__( 'You must create an Application and generate access keys to use this service. %3$s 1. Create the application %1$s. %3$s 2. Generate access keys using this %2$s.', 'fl-builder' ),
				sprintf( '<a target="_blank" href="https://app.constantcontact.com/pages/dma/portal/">%s</a>', __( 'on the portal', 'fl-builder' ) ),
				sprintf( '<a target="_blank" href="https://www.wpbeaverbuilder.com/constant-contact-auth/">%s</a>', __( 'Code Generator', 'fl-builder' ) ),
				'<br />'
			),
			'preview'     => array(
				'type' => 'none',
			),
		));

		return ob_get_clean();
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.5.4
	 * @param string $account The name of the saved account.
	 * @param object $settings Saved module settings.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 * }
	 */
	public function render_fields( $account, $settings ) {
		$account_data = $this->get_account_data( $account );
		$response     = array(
			'error' => false,
			'html'  => '',
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'Error: Could not connect to Constant Contact. The account is no longer connected.', 'fl-builder' );
			return $response;
		}

		$request = $this->request_lists( $account_data['access_token'] );

		// Access tokens last 24 hours, so an expired one is the normal case here rather than an exception.
		if ( 401 === wp_remote_retrieve_response_code( $request ) && $this->update_tokens( $account ) ) {
			$account_data = $this->get_account_data( $account );
			$request      = $this->request_lists( $account_data['access_token'] );
		}

		$error = $this->get_lists_error( $request );

		if ( $error ) {
			$response['error'] = $error;
			return $response;
		}

		$body             = json_decode( wp_remote_retrieve_body( $request ) );
		$response['html'] = $this->render_list_field( $body, $settings );

		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.5.4
	 * @param object $lists List data from the API.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {
		ob_start();

		$options = array(
			'' => __( 'Choose...', 'fl-builder' ),
		);

		foreach ( $lists->lists as $list ) {
			$options[ $list->list_id ] = esc_attr( $list->name );
		}

		FLBuilder::render_settings_field( 'list_id', array(
			'row_class' => 'fl-builder-service-field-row',
			'class'     => 'fl-builder-service-list-select',
			'type'      => 'select',
			'label'     => _x( 'List', 'An email list from a third party provider.', 'fl-builder' ),
			'options'   => $options,
			'preview'   => array(
				'type' => 'none',
			),
		), $settings);

		return ob_get_clean();
	}

	/**
	 * Subscribe an email address to Constant Contact.
	 *
	 * @since 1.5.4
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param string $name Optional. The full name of the person subscribing.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email, $name = false ) {
		$account      = $settings->service_account;
		$account_data = $this->get_account_data( $account );
		$response     = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to Constant Contact. The account is no longer connected.', 'fl-builder' );
			return $response;
		}

		$body    = $this->build_contact_body( $settings, $email, $name );
		$request = $this->post_contact( $account_data['access_token'], $body );

		// Refresh and retry rather than dropping the submission the token happened to expire on.
		if ( 401 === wp_remote_retrieve_response_code( $request ) && $this->update_tokens( $account ) ) {
			$account_data = $this->get_account_data( $account );
			$request      = $this->post_contact( $account_data['access_token'], $body );
		}

		$response['error'] = $this->get_subscribe_error( wp_remote_retrieve_response_code( $request ) );

		return $response;
	}

	/**
	 * Build the contact payload for a subscribe request.
	 *
	 * @since 2.12
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @param string $name Optional. The full name of the person subscribing.
	 * @return array The request body.
	 */
	private function build_contact_body( $settings, $email, $name = false ): array {
		$body = array(
			'email_address'    => array(
				'address'            => $email,
				'permission_to_send' => 'implicit',
			),
			'list_memberships' => array( $settings->list_id ),
			'create_source'    => 'Account',
		);

		if ( ! $name ) {
			return $body;
		}

		$names = explode( ' ', $name );

		if ( isset( $names[0] ) ) {
			$body['first_name'] = $names[0];
		}
		if ( isset( $names[1] ) ) {
			$body['last_name'] = $names[1];
		}

		return $body;
	}

	/**
	 * Post a contact to the API.
	 *
	 * @since 2.12
	 * @param string $access_token A valid access token.
	 * @param array $body The contact payload.
	 * @return array|WP_Error The remote request response.
	 */
	private function post_contact( string $access_token, array $body ) {
		return wp_remote_post( $this->api_url . 'contacts', array(
			'data_format' => 'body',
			'headers'     => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body'        => wp_json_encode( $body ),
		) );
	}

	/**
	 * Get the display error for a subscribe response code.
	 *
	 * @since 2.12
	 * @param int|string $response_code The HTTP response code.
	 * @return string|bool The error message or false if the contact was created.
	 */
	private function get_subscribe_error( $response_code ) {
		switch ( $response_code ) {
			case 201: // 201 = subscribed
				return false;
			case 401:
				return __( 'Please try again', 'fl-builder' );
			case 409:
				return __( 'You are either already subscribed to this list or you have unsubscribed in the past', 'fl-builder' );
			default:
				return __( 'An unknown error has occurred', 'fl-builder' );
		}
	}

	/**
	 * Exchange the saved refresh token for a new access token.
	 *
	 * @since 1.5.4
	 * @param string $account The name of the saved account.
	 * @return bool Whether new tokens were saved.
	 */
	private function update_tokens( string $account ): bool {
		$account_data = $this->get_account_data( $account );

		if ( ! $account_data ) {
			return false;
		}

		$request = $this->request_new_tokens( $account_data );

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $request ) );

		if ( ! isset( $body->access_token, $body->refresh_token ) ) {
			return false;
		}

		// Constant Contact rotates the refresh token on each exchange, so both have to be stored.
		$account_data['access_token']  = $body->access_token;
		$account_data['refresh_token'] = $body->refresh_token;

		FLBuilderModel::update_services( $this->id, $account, $account_data );

		return true;
	}

	/**
	 * Request a new token pair using the saved refresh token.
	 *
	 * @since 2.12
	 * @param array $account_data The saved account data.
	 * @return array|WP_Error The remote request response.
	 */
	private function request_new_tokens( array $account_data ) {
		$url         = sprintf( 'https://authz.constantcontact.com/oauth2/default/v1/token?refresh_token=%s&grant_type=refresh_token', $account_data['refresh_token'] );
		$credentials = sprintf( '%s:%s', $account_data['client_id'], $account_data['client_secret'] );

		return wp_remote_post( $url, array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Authorization' => 'Basic ' . base64_encode( $credentials ),
			),
		) );
	}
}

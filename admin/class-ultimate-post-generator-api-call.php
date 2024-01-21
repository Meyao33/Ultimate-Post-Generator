<?php

class Ultimate_Post_Generator_API_Call {

    /**
	 * Processes AJAX requests to OpenAI's API for content generation.
	 *
	 * Validates the request using a nonce for security, then sanitizes user input and retrieves the OpenAI API key.
	 * Constructs a request to OpenAI with user input, handling API responses and errors. Returns the generated
	 * content from OpenAI or an error message on failure. This method facilitates secure and efficient
	 * content generation via OpenAI within the WordPress admin interface.
	 */

	public function handle_openai_ajax_request() {
		// Check for nonce for security
		check_ajax_referer('open_ai_nonce', 'nonce');
	
		$user_input = sanitize_text_field($_POST['input']);
		$api_key = get_option('ubg_api_key');
		$max_tokens = get_option('ubg_max_tokens', 530);

		$formatted_input = $user_input . ' Please format the response with HTML tags such as <h1> for titles, <ul> for lists, and <p> for paragraphs, and a list for SEO tags. Please limit the response to '.strval($max_tokens).' tokens.';
		error_log("Prompt: " . $formatted_input);
		if (empty($formatted_input) || empty($api_key)) {
			wp_send_json_error(array('error' => 'Missing required parameters.'), 400);
			wp_die();
		}
	
		$endpoint_url = 'https://api.openai.com/v1/chat/completions';
	
		$data = array(
			'max_tokens' => intval($max_tokens),
			'model' => 'gpt-3.5-turbo',
			'messages' => array(array('role' => 'user', 'content' => $formatted_input))
		);
	
		$response = wp_remote_post($endpoint_url, array(
			'body'    => json_encode($data),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $api_key
			),
			'timeout' => 60
		));
	
		if (is_wp_error($response)) {
			error_log('OpenAI request failed: ' . $response->get_error_message());
			wp_send_json_error(array('error' => 'Error connecting to OpenAI.'), 500);
			wp_die();
		}
	
		$result = json_decode(wp_remote_retrieve_body($response), true);
		if (isset($result['choices'][0]['message']['content'])) {
			wp_send_json_success(array('message' => $result['choices'][0]['message']['content']));
		} else {
			wp_send_json_error(array('error' => 'Error fetching response from OpenAI.'), 500);
		}
	
		wp_die();
	}
	

}

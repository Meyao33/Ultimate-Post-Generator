<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://alvarooropesa.com
 * @since      1.0.0
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/admin
 * @author     Alvaro Oropesa <alvarovisiondesing@gmail.com>
 */
class Ultimate_Post_Generator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Post_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Post_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-post-generator-admin.css', array(), $this->version, 'all' );

		if ($hook === $this->chatgpt_page_hook_suffix) {
			// Assuming hide-wp-admin-menu.css is your CSS file
			wp_enqueue_style('hide-admin-menu', plugin_dir_url(__FILE__) . 'css/hide-wp-admin-menu.css');
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Post_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Post_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/ultimate-post-generator-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		
		
		// Create a nonce
		$nonce = wp_create_nonce('open_ai_nonce');

		// Localize the script with new data
		wp_localize_script($this->plugin_name, 'open_ai_script_data', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => $nonce,
		));

	}

	public function addAdminMenu() {
		$api_call_instance = new Ultimate_Post_Generator_API_Call();

        add_menu_page('Ultimate Blog Generator Settings', 'Blog Generator', 'manage_options', 'ubg-settings', array($this, 'adminSettingsPage'));
		
		// Add the submenu page
		$hook_suffix = add_submenu_page(
			'ubg-settings', 
			'ChatGPT Interface', 
			'ChatGPT Interface', 
			'manage_options', 
			'ubg-chatgpt', 
			array($this, 'chatGPTInterfacePage')
		);

		add_submenu_page(
			'ubg-settings', 
			'Instruction', 
			'Instruction', 
			'manage_options', 
			'upg-instructions', 
			array($this, 'upgInstrutions')
		);

		// Add a new submenu page for Prompts
		add_submenu_page(
			'ubg-settings', 
			'Prompts', 
			'Prompts', 
			'manage_options', 
			'ubg-prompts', 
			array($this, 'promptsPage')
		);
	
		// Store the hook suffix for later use
		$this->chatgpt_page_hook_suffix = $hook_suffix;
    }

	public function open_ai_ajax_call() {
		$api_call_instance = new Ultimate_Post_Generator_API_Call();
		$api_call_instance->handle_openai_ajax_request();
	}

	// Render the admin settings page.
    public function adminSettingsPage() {
		// Check if the form is submitted
		if (isset($_POST['ubg_api_key'], $_POST['ubg_max_tokens']) && isset($_POST['ubg_settings_nonce'])) {
			// Verify nonce
			if (!wp_verify_nonce($_POST['ubg_settings_nonce'], 'ubg_save_settings')) {
				// Handle nonce verification failure
				echo '<div class="error"><p>Security check failed. Please try again.</p></div>';
			} else {
				// Nonce is verified, sanitize and process the form
				$api_key = sanitize_text_field($_POST['ubg_api_key']);
				$max_tokens = intval($_POST['ubg_max_tokens']);
			
				// Validate the API key here if needed
				// Add your validation logic
				if (!empty($api_key)) {
					// Save the API key using the WordPress `update_option` function
					update_option('ubg_api_key', $api_key);
					update_option('ubg_max_tokens', $max_tokens);
			
					// Display a success message
					echo '<div class="updated"><p>API key saved successfully!</p></div>';
				} else {
					// Handle validation failure
					echo '<div class="error"><p>Invalid API key format.</p></div>';
				}
			}
		}
	
		// Retrieve the API key from the options
		$this->apiKey = get_option('ubg_api_key');
		$this->maxTokens = get_option('ubg_max_tokens', 530); // Default to 530 if not set
	
		// Output the settings page form
		?>
		<div class="wrap">
			<h2>Ultimate Blog Generator Settings</h2>
			<form method="post" action="">
				<?php wp_nonce_field('ubg_save_settings', 'ubg_settings_nonce'); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="ubg_api_key">OpenAI API Key</label></th>
						<td>
							<input type="password" id="ubg_api_key" name="ubg_api_key" value="<?php echo esc_attr($this->apiKey); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ubg_max_tokens">Max Tokens</label></th>
						<td>
							<input type="number" id="ubg_max_tokens" name="ubg_max_tokens" value="<?php echo esc_attr($this->maxTokens); ?>" class="regular-text" />
						</td>
					</tr>
				</table>
				<?php submit_button('Save API Key'); ?>
			</form>
		</div>
		<?php
	}
	

	/**
	 * Chat GPT Interface
	 */
	public function chatGPTInterfacePage() {
		$sendIcon = file_get_contents(plugin_dir_path( dirname(__FILE__)) . 'assets/img/send-email-svgrepo-com.svg');
		// Fetch custom prompts from user meta
		$user_id = get_current_user_id();
		$custom_prompts = get_user_meta($user_id, 'upg_custom_prompts', true);
		if (!$custom_prompts) {
			$custom_prompts = array();
		}
		?>
		<div class="upg-wrap">
			<h2>ChatGPT Interface</h2>
			<div id="upg-chat-app">
				<aside id="upg-chat-sidebar">
					<h3>Choose a Prompt</h3>
					<form id="upg-default-prompts">
						<label><input type="radio" name="prompt" value="Informative Article" checked> Informative Article</label><br>
						<label><input type="radio" name="prompt" value="Casual Blog Post"> Casual Blog Post</label><br>
						<label><input type="radio" name="prompt" value="Technical Write-up"> Technical Write-up</label><br>
						<!-- Add more default prompts as needed -->
						<!-- User-specific Prompts -->
						<h3>Custom Prompts</h3>
						<?php foreach ($custom_prompts as $prompt): ?>
							<label><input type="radio" name="prompt" value="<?php echo esc_attr($prompt['description']); ?>"> <?php echo esc_html($prompt['title']); ?></label><br>
						<?php endforeach; ?>
					</form>
				</aside>
				<main id="upg-chat-main">
					<div id="loading-indicator">
						<div class="load-wrapper">
							<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
							<p>Generating the post, please wait...</p>
						</div>
					</div>
					<div class="upg-content-wrapper">
						<?php
							$args = array(
								'editor_height' => 325, // In pixels, takes precedence and has no default value
								'textarea_rows' => 20,  // Has no visible effect if editor_height is set, default is 20
							);
							wp_editor('', 'upg-openai-editor', $args); 
						?>
						<div class="upg-post-actions">
							<button class="save-draft">Save as draft</button>
							<button class="start-new">Start a new post</button>
						</div>
					</div>
					
					<div class="prompt-form">
						<form method="post">
							<input type="text" name="input" id="upg-post-input" placeholder="Ask ChatGPT" required="">
							<!-- <input type="submit" name="submit" value="Ask ChatGPT"> -->
							<button><?php echo $sendIcon; ?></button>
						</form>
					</div>
				</main>
			</div>
		</div>
		<?php
	}

	/**
	 * Chat GPT Instructions
	 */
	public function upgInstrutions() { ?>
		<div class="upg-instructions">
			<h1>Instructions for Using the Ultimate Blog Generator</h1>
			<div class="upg-instructions-content">
				<h2>How to Craft Effective Prompts</h2>
				<p>Creating great content with our AI-powered Blog Generator is easy! Follow these guidelines to craft prompts that result in high-quality, relevant posts:</p>
				<ul class="upg-list">
					<li><strong>Be Specific:</strong> Clearly define the topic and scope of your desired content.</li>
					<li><strong>Set the Tone:</strong> Indicate the desired tone, such as professional, casual, humorous, etc.</li>
					<li><strong>Target Audience:</strong> Specify who the content is for to tailor it appropriately.</li>
					<li><strong>Content Structure:</strong> Suggest a structure, like listicles, how-to guides, or in-depth analysis.</li>
					<li><strong>Keywords:</strong> Include relevant keywords for SEO purposes.</li>
					<li><strong>Word Limit:</strong> State the desired length of the post.</li>
					<li><strong>Special Instructions:</strong> Add any specific requirements or points to cover.</li>
					<li><strong>Include SEO Elements:</strong> Request for a title, body, and SEO meta tags for complete post structure.</li>
				</ul>
				<h2>Example Prompt</h2>
				<p>Here's an example of a well-structured prompt:</p>
				<div class="upg-example-prompt">
					"Write a comprehensive guide about sustainable living for beginners. The tone should be friendly and encouraging, and the article should be around 1200 words. Include tips on reducing waste and energy consumption. Please provide a catchy title, a compelling body, and a list of relevant SEO meta tags."
				</div>
				<h2>Advanced Prompt Crafting</h2>
				<p>For more complex posts, you can structure your prompt to specify different parts of a blog post:</p>
				<div class="upg-example-prompt">
					"Generate a complete blog post about 'Sustainable Urban Gardening for Beginners', including a title, body, and SEO meta tags. Aim the post at amateur urban gardeners, with a friendly and encouraging tone. The content should include tips for starting a small garden in city apartments, choosing the right plants, and sustainable gardening practices. Include SEO meta tags focusing on urban gardening, sustainability, and beginner gardening tips."
				</div>
			</div>
		</div>
	<?php }

	/**
	 * Create Prompts
	 */
	public function promptsPage() {
		// Fetch custom prompts from user meta
		$user_id = get_current_user_id();
		$custom_prompts = get_user_meta($user_id, 'upg_custom_prompts', true);
		if (!$custom_prompts) {
			$custom_prompts = array();
		}
		?>
		<div class="wrap">
			<h1>Prompts</h1>
			<p><strong>Tip for a good Propmt:</strong></p>
			<div class="upg-example-prompt">
			"Generate a complete blog post including a title, body, and SEO meta tags. The topic should be [Your Topic], aimed at [Your Target Audience]. The tone should be [Desired Tone, e.g., professional, casual, informative, etc.]. Ensure the content is engaging and provides valuable insights on [Specific Aspects of the Topic]. Also, include a list of SEO meta tags relevant to the topic."
			</div>
			<div id="prompts-page">
				<h2>Generic Prompts</h2>
				<!-- Here you will list the generic prompts -->

				<h2>Create Custom Prompts</h2>
				<form id="custom-prompts-form">
					<label for="prompt-title">Prompt Title:</label>
					<input type="text" id="prompt-title" name="prompt-title" placeholder="Prompt Title:" required><br>

					<label for="prompt-description">Prompt Description:</label>
					<textarea id="prompt-description" name="prompt-description" placeholder="Prompt Description:" required></textarea><br>

					<input type="submit" class="upg-primary" value="Add Prompt">
				</form>

				<!-- List existing custom prompts here -->
			</div>
			<!-- Table for displaying prompts -->
			<h2>Your Custom Prompts</h2>
			<table id="upg-prompts-table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Description</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($custom_prompts as $index => $prompt): ?>
						<tr data-prompt-index="<?php echo $index; ?>">
							<td><?php echo esc_html($prompt['title']); ?></td>
							<td><?php echo esc_html($prompt['description']); ?></td>
							<td>
								<button class="edit-prompt"><svg width="30" height="30" fill="#000000" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path class="st0" d="M12 25l3 3 15-15-3-3-15 15zM11 26l3 3-4 1z"></path></g></svg></button>
								<button class="delete-prompt"><svg width="30" height="30" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="#000000" d="M352 192V95.936a32 32 0 0 1 32-32h256a32 32 0 0 1 32 32V192h256a32 32 0 1 1 0 64H96a32 32 0 0 1 0-64h256zm64 0h192v-64H416v64zM192 960a32 32 0 0 1-32-32V256h704v672a32 32 0 0 1-32 32H192zm224-192a32 32 0 0 0 32-32V416a32 32 0 0 0-64 0v320a32 32 0 0 0 32 32zm192 0a32 32 0 0 0 32-32V416a32 32 0 0 0-64 0v320a32 32 0 0 0 32 32z"></path></g></svg></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
        </table>
		<div id="edit-prompt-modal">
			<div class="inner-edit-prompt-modal">
				<button id="close-modal-prompt" class="close-modal-prompt"><svg viewBox="0 0 1024 1024" fill="#ffffff" width="30" height="30" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M176.662 817.173c-8.19 8.471-7.96 21.977 0.51 30.165 8.472 8.19 21.978 7.96 30.166-0.51l618.667-640c8.189-8.472 7.96-21.978-0.511-30.166-8.471-8.19-21.977-7.96-30.166 0.51l-618.666 640z" fill=""></path><path d="M795.328 846.827c8.19 8.471 21.695 8.7 30.166 0.511 8.471-8.188 8.7-21.694 0.511-30.165l-618.667-640c-8.188-8.471-21.694-8.7-30.165-0.511-8.471 8.188-8.7 21.694-0.511 30.165l618.666 640z" fill="#fff"></path></g></svg></button>
				<input type="text" id="edit-prompt-title">
				<textarea id="edit-prompt-description"></textarea>
				<button class="upg-primary" id="update-prompt">Update</button>
				<button class="upg-secondary" id="cancel-update-prompt">cancel</button>
			</div>
		</div>

		</div>
		<?php
		// Include script and styles here if needed
	}

	/**
	 * AJAX handler for saving a draft post with structured content and SEO tags.
	 */
	public function handle_save_draft_ajax() {
		check_ajax_referer('open_ai_nonce', 'nonce'); // Check nonce for security

		$post_content = isset($_POST['post_content']) ? sanitize_post_field('post_content', $_POST['post_content'], 0, 'db') : '';

		// Extract title, body, and SEO tags from the post content
		// Attempt to parse structured content
		$title_pattern = '/Title: "(.*?)"/';
		$body_pattern = '/Body:\n(.*?)\n\nSEO Tags:/s';
		$seo_tags_pattern = '/SEO Tags:\n(.*)/';
	
		preg_match($title_pattern, $post_content, $title_matches);
		preg_match($body_pattern, $post_content, $body_matches);
		preg_match($seo_tags_pattern, $post_content, $seo_tags_matches);
	
		// Set title and body based on parsed data or default to using post content
		$post_title = !empty($title_matches[1]) ? $title_matches[1] : wp_trim_words($post_content, 10, '...');
		$post_body = !empty($body_matches[1]) ? $body_matches[1] : $post_content;
		$seo_tags = !empty($seo_tags_matches[1]) ? explode(' ', $seo_tags_matches[1]) : [];

		// Create post data array
		$post_data = array(
			'post_title'    => $post_title,
			'post_content'  => $post_body,
			'post_status'   => 'draft',
			'post_author'   => get_current_user_id(), // or a specific author ID
			'post_type'     => 'post', // Modify as needed
		);

		// Insert the post
		$post_id = wp_insert_post($post_data);

		// Handle SEO tags with YOAST SEO Plugin
		if ($post_id && function_exists('wpseo_get_meta_keyword')) {
			// Convert tags array to a comma-separated string
			$seo_keywords = implode(', ', $seo_tags);
			update_post_meta($post_id, '_yoast_wpseo_metakeywords', $seo_keywords);
		}

		if ($post_id) {
			wp_send_json_success(array('message' => 'Draft saved successfully.'));
		} else {
			wp_send_json_error(array('message' => 'Failed to save draft.'));
		}
	}


	/**
	 * Save Custom Prompts
	 */
	public function saveCustomPrompt() {
		check_ajax_referer('open_ai_nonce', 'nonce'); // Check nonce for security
	
		$user_id = get_current_user_id();
		if (!$user_id) {
			wp_send_json_error(array('message' => 'User not logged in.'));
		}
	
		$title = sanitize_text_field($_POST['prompt-title']);
		$description = sanitize_textarea_field($_POST['prompt-description']);
	
		// Save the prompt in user meta
		$prompts = get_user_meta($user_id, 'upg_custom_prompts', true);
		if (!$prompts) {
			$prompts = array();
		}
		$prompts[] = array('title' => $title, 'description' => $description);
		update_user_meta($user_id, 'upg_custom_prompts', $prompts);
	
		wp_send_json_success(array('message' => 'Prompt saved successfully.'));
	}

	/**
	 * Delete Custom Prompts
	 */
	public function deleteCustomPrompt() {
		// Check for the nonce for security
		check_ajax_referer('open_ai_nonce', 'nonce');
	
		// Get the current user ID
		$user_id = get_current_user_id();
		if (!$user_id) {
			wp_send_json_error(array('message' => 'User not logged in.'));
			return;
		}
	
		// Get the index of the prompt to delete
		$prompt_index = isset($_POST['prompt_index']) ? intval($_POST['prompt_index']) : -1;
		if ($prompt_index === -1) {
			wp_send_json_error(array('message' => 'Invalid prompt index.'));
			return;
		}
	
		// Fetch existing prompts
		$prompts = get_user_meta($user_id, 'upg_custom_prompts', true);
		if (!is_array($prompts) || !isset($prompts[$prompt_index])) {
			wp_send_json_error(array('message' => 'Prompt not found.'));
			return;
		}
	
		// Remove the specified prompt
		array_splice($prompts, $prompt_index, 1);
	
		// Update the prompts in user meta
		update_user_meta($user_id, 'upg_custom_prompts', $prompts);
	
		wp_send_json_success(array('message' => 'Prompt deleted successfully.'));
	}
	
	/**
	 * Update/Edit Custom Prompts
	 */
	public function updateCustomPrompt() {
		// Check for the nonce for security
		check_ajax_referer('open_ai_nonce', 'nonce');
	
		// Get the current user ID
		$user_id = get_current_user_id();
		if (!$user_id) {
			wp_send_json_error(array('message' => 'User not logged in.'));
			return;
		}
	
		// Get the index of the prompt to update
		$prompt_index = isset($_POST['prompt_index']) ? intval($_POST['prompt_index']) : -1;
		$title = isset($_POST['prompt-title']) ? sanitize_text_field($_POST['prompt-title']) : '';
		$description = isset($_POST['prompt-description']) ? sanitize_textarea_field($_POST['prompt-description']) : '';
	
		if ($prompt_index === -1 || empty($title) || empty($description)) {
			wp_send_json_error(array('message' => 'Invalid data provided.'));
			return;
		}
	
		// Fetch existing prompts
		$prompts = get_user_meta($user_id, 'upg_custom_prompts', true);
		if (!is_array($prompts) || !isset($prompts[$prompt_index])) {
			wp_send_json_error(array('message' => 'Prompt not found.'));
			return;
		}
	
		// Update the specified prompt
		$prompts[$prompt_index] = array('title' => $title, 'description' => $description);
	
		// Update the prompts in user meta
		update_user_meta($user_id, 'upg_custom_prompts', $prompts);
	
		wp_send_json_success(array('message' => 'Prompt updated successfully.'));
	}

}

<?php
/**
 * Plugin Name: Meta Generator AI
 * Description: This Plugin is a useful way to automatically generate meta data for products based on the product name, which can save time and effort when creating large numbers of products.
 * Version: 1.0.0
 * Requires at least: 6.2.2
 * Tested up to: 6.4.1
 * Requires PHP: 7.4
 * Author: SJ innovation
 * Author URI: https://sjinnovation.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Add a menu item to the admin menu
function mga_meta_gen_ai_menu() {
    add_menu_page(
        'Meta Generator Settings',
        'Meta Generator Settings',
        'manage_options',
        'meta-gen-ai-settings',
        'mga_meta_gen_ai_settings_page'
    );
}
add_action('admin_menu', 'mga_meta_gen_ai_menu');

// Callback function to display the settings page
function mga_meta_gen_ai_settings_page() {
    // Check if the user is allowed to access this page.
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';

    // Save form data if the form is submitted.
    if (isset($_POST['mga_meta_generator_settings_submit'])) {
         //Verify the nonce
        check_admin_referer('mga_meta_gen_ai_settings_save', 'meta_gen_ai_nonce');
        
        $api_key = sanitize_text_field($_POST['api_key']);
        if (empty($api_key)) {
            $message = 'API key is required';
        } else {
            update_option('mga_meta_gen_api_key', $api_key);
        }
        
        $api_link = sanitize_text_field($_POST['api_link']);
        if (empty($api_link) || !filter_var($api_link, FILTER_VALIDATE_URL)) {
            $message = 'Invalid API link';
        } else {
            update_option('mga_meta_gen_api_link', $api_link);
        }

        $full_description = sanitize_text_field($_POST['full_description']);
        if (empty($full_description)) {
            $message = 'Full description is required';
        } else {
            update_option('mga_meta_gen_full_description', $full_description);
        }
        
        $short_description = sanitize_text_field($_POST['short_description']);
        if (empty($short_description)) {
            $message = 'Short description is required';
        } else {
            update_option('mga_meta_gen_short_description', $short_description);
        }
        
        $gpt_model_name = sanitize_text_field($_POST['gpt_model_name']);
        if (empty($gpt_model_name)) {
            $message = 'GPT model name is required';
        } else {
            update_option('mga_meta_gen_gpt_model_name', $gpt_model_name);
        }
       
        $temperature = sanitize_text_field($_POST['temperature']);
        if (!is_numeric($temperature)) {
            $message = 'Invalid temperature';
        } else {
            update_option('mga_meta_gen_temperature', $temperature);
        }

        $message = 'Settings saved';
    }

    // Retrieve saved options.
    $api_key = get_option('mga_meta_gen_api_key');
    $api_link = get_option('mga_meta_gen_api_link');
    $full_description = get_option('mga_meta_gen_full_description');
    $short_description = get_option('mga_meta_gen_short_description');
    $gpt_model_name = get_option('mga_meta_gen_gpt_model_name');
    $temperature = get_option('mga_meta_gen_temperature');

    // Create the form HTML.
    ?>
    <div class="wrap">
        
        <h2>Meta Generator Settings</h2>
        
        <p>Configure the Open AI Settings. You can generate the API key from the <a href="https://platform.openai.com/account/api-keys" target="_blank">Open AI Platform</a>.</p>
        
        <p>Refer to the <a href="https://platform.openai.com/docs" target="_blank">Open AI Documentation</a> for more details.</p>
        
        <?php if (!empty($message)) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('mga_meta_gen_ai_settings_save', 'meta_gen_ai_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">API Key</th>
                    <td>
                        <span class="tooltip" data-tooltip="Enter your API key.">?</span>
                        <input type="text" name="api_key" class="mga-meta-gen-fields" value="<?php echo esc_attr($api_key); ?>" required/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Link</th>
                    <td>
                        <span class="tooltip" data-tooltip="Enter the API link.">?</span>    
                        <input type="text" name="api_link" class="mga-meta-gen-fields" value="<?php echo esc_url($api_link); ?>" required/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Full Description</th>
                    <td>
                        <span class="tooltip" data-tooltip="Enter your full description.">?</span>    
                        <input type="text" name="full_description" class="mga-meta-gen-fields" value="<?php echo esc_attr($full_description); ?>" required/>
                        <p><b> Example: </b> "Generate full meta description for: " </p>
                        <p><b>Note: </b>Full Description will be generated considering the title of your post/product and the example as default prompt if none given.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Short Description</th>
                    <td>
                        <span class="tooltip" data-tooltip="Enter short description.">?</span>    
                        <input type="text" name="short_description" class="mga-meta-gen-fields" value="<?php echo esc_attr($short_description); ?>" required/>
                        <p><b> Example:</b> "Generate short meta description for: " </p>
                        <p> <b> Note: </b>Short Description will be generated considering the title of your post/product and the example as default prompt if none given.</p><b>
                    </td>
                </tr>
                <tr>
                    <th scope="row">GPT-3.5 Model Name</th>
                    <td>
                        <span class="tooltip" data-tooltip="Select the AI model.">?</span>
                        <select name="gpt_model_name" class="mga-meta-gen-fields" required>
                            <option value="text-davinci-002" <?php selected($gpt_model_name, 'text-davinci-002'); ?>>text-davinci-002 (Legacy)</option>
                            <option value="text-davinci-003" <?php selected($gpt_model_name, 'text-davinci-003'); ?>>text-davinci-003 (Legacy)</option>
                            <option value="gpt-3.5-turbo-16k-0613" <?php selected($gpt_model_name, 'gpt-3.5-turbo-16k-0613'); ?>>gpt-3.5-turbo-16k-0613</option>
                            <option value="gpt-3.5-turbo-0613" <?php selected($gpt_model_name, 'gpt-3.5-turbo-0613'); ?>>gpt-3.5-turbo-0613</option>
                            <option value="gpt-3.5-turbo-16k" <?php selected($gpt_model_name, 'gpt-3.5-turbo-16k'); ?>>gpt-3.5-turbo-16k</option>
                            <option value="gpt-3.5-turbo" <?php selected($gpt_model_name, 'gpt-3.5-turbo'); ?>>gpt-3.5-turbo</option>
                        </select>
                        <p><b>Note: </b> If you are not sure which model to use, then you can use the default model. </p>
                        <p>Our default model is, <b>Text Davinci 002 </b> model. <br/>
                        Refer to <a href="https://platform.openai.com/docs/models/gpt-3-5" target="_blank">Open AI Models</a> for more details. </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Temperature</th>
                    <td>
                        <span class="tooltip" data-tooltip="Select the temperature value.">?</span>
                        <select name="temperature" class="mga-meta-gen-fields" required>
                            <option value="0" <?php selected($temperature, '0'); ?>>0</option>
                            <option value="0.1" <?php selected($temperature, '0.1'); ?>>0.1</option>
                            <option value="0.2" <?php selected($temperature, '0.2'); ?>>0.2</option>
                            <option value="0.3" <?php selected($temperature, '0.3'); ?>>0.3</option>
                            <option value="0.4" <?php selected($temperature, '0.4'); ?>>0.4</option>
                            <option value="0.5" <?php selected($temperature, '0.5'); ?>>0.5</option>
                            <option value="0.6" <?php selected($temperature, '0.6'); ?>>0.6</option>
                            <option value="0.7" <?php selected($temperature, '0.7'); ?>>0.7</option>
                            <option value="0.8" <?php selected($temperature, '0.8'); ?>>0.8</option>
                            <option value="0.9" <?php selected($temperature, '0.9'); ?>>0.9</option>
                            <option value="1" <?php selected($temperature, '1'); ?>>1</option>
                        </select>
                        <p><b> Note: </b> If you are unsure about the optimal temperature to use, you can rely on the default setting. </p>
                        <p>Our default temperature is set to <b>0.7</b>. <br/>
                        Refer to <a href="https://community.openai.com/t/cheat-sheet-mastering-temperature-and-top-p-in-chatgpt-api-a-few-tips-and-tricks-on-controlling-the-creativity-deterministic-output-of-prompt-responses/172683" target="_blank">Open AI Temperature</a> for more details. </p>
                    </td>
                </tr>
            </table>
            <input type="submit" name="mga_meta_generator_settings_submit" class="button-primary" value="Save Settings">
        </form>
    </div>
    <?php
}

function mga_enqueue_chatgpt_files() {
    wp_enqueue_script('meta-gen-data', plugins_url('/meta-gen-data.js', __FILE__), array('jquery'), '1.0', true);

    wp_localize_script('meta-gen-data', 'metaSettings', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mga_meta_gen_ai_settings_save'), // Match the nonce name here
    ));

    wp_enqueue_style('chatgpt-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('admin_enqueue_scripts', 'mga_enqueue_chatgpt_files');

// Function to display the "Generate Description" button
function mga_display_generate_button() {
    global $post;

    // Check if the current page is a product page
    if (isset($post) && 'product' === $post->post_type) {
        echo '<button id="generate-meta-data">Generate Meta Data</button>';
        echo '<button id="generate-custom-description-button">Enter Custom Description</button>';
    }
}
add_action('edit_form_after_title', 'mga_display_generate_button', 20);

function mga_meta_gen_plugin_add_settings($actions) {
    // Add your custom button to the plugin row actions
    $custom_button = '<a href="' .admin_url('admin.php?page=meta-gen-ai-settings'). '">Settings</a>';
    array_push($actions, $custom_button);
    return $actions;
}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'mga_meta_gen_plugin_add_settings');

//Function for checking Plugins Settings is properly configured
function mga_show_admin_notice() {
    $missing_settings = array();
    $required_settings = array('mga_meta_gen_api_key', 'mga_meta_gen_api_link', 'mga_meta_gen_gpt_model_name', 'mga_meta_gen_temperature');

    // Check if the current screen is the plugin settings page
    $current_screen = get_current_screen();
    if ($current_screen && $current_screen->id === 'toplevel_page_meta-gen-ai-settings') {
        return; // Do not show the notice on the settings page
    }

    foreach ($required_settings as $setting) {
        if (empty(get_option($setting))) {
            $missing_settings[] = $setting;
        }
    }

    if (!empty($missing_settings)) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e( 'You need to configure the settings in order to use the "Meta Gen AI" plugin. <a href="admin.php?page=meta-gen-ai-settings">Click here</a> to configure.', 'meta-gen-ai' ); ?></p>
        </div>
        <?php
    }
}

add_action( 'admin_notices', 'mga_show_admin_notice' );

function mga_meta_settings_callback() {
    // Verify the nonce
    if (!isset($_POST['nonce']) || !check_ajax_referer('mga_meta_gen_ai_settings_save', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        wp_die();
    }

    // Sanitize input
    $productName = sanitize_text_field($_POST['productName']);

    // Generate data
    $meta_gen_response = [];
    $full_desc_result = mga_generate_open_ai_data('full_description',$productName);    
    if ($full_desc_result) {
        $meta_gen_response['full_description_resp'] = $full_desc_result;
    } 

    $short_desc_result = mga_generate_open_ai_data('short_description',$productName);
    if ($short_desc_result) {
        $meta_gen_response['short_description_resp'] = $short_desc_result;
    }

    $product_tags_result = mga_generate_open_ai_data('generate_tags',$productName);
    if ($product_tags_result) {
        $meta_gen_response['product_tags_resp'] = $product_tags_result;
    }
    
    // Send response
    if ($meta_gen_response['full_description_resp']['status'] == 'success' && $meta_gen_response['short_description_resp']['status'] == 'success' && $meta_gen_response['product_tags_resp']['status'] == 'success') {
        wp_send_json_success($meta_gen_response);
    }else{
        wp_send_json_error($meta_gen_response);
    }

    wp_die();
    
}

// Hook the AJAX action
add_action('wp_ajax_get_meta_settings', 'mga_meta_settings_callback');
add_action('wp_ajax_nopriv_get_meta_settings', 'mga_meta_settings_callback');

function mga_custom_meta_settings_callback() {
    // Verify the nonce
    if (!isset($_POST['nonce']) || !check_ajax_referer('mga_meta_gen_ai_settings_save', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        wp_die();
    }
     // Sanitize input
    $productDetails = sanitize_text_field($_POST['productDetails']);

     // Generate data
    $meta_gen_response = [];
    $custom_product_title_result = mga_generate_open_ai_data('custom_product_title',$productDetails);    
    if ($custom_product_title_result) {
        $meta_gen_response['custom_product_title_resp'] = $custom_product_title_result;
    }

    // Send response
    if ($meta_gen_response['custom_product_title_resp']['status'] == 'success') {
        wp_send_json_success($meta_gen_response);
    }else{
        wp_send_json_error($meta_gen_response);
    }

    wp_die();
}

// Hook the AJAX action
add_action('wp_ajax_get_custom_meta_settings', 'mga_custom_meta_settings_callback');
add_action('wp_ajax_nopriv_get_custom_meta_settings', 'mga_custom_meta_settings_callback');

function mga_generate_open_ai_data($type, $appendData) {

    $response = [];
    $api_key = get_option('mga_meta_gen_api_key'); // Replace with your OpenAI API key
    $endpoint = get_option('mga_meta_gen_api_link');
    $temperature = (float) get_option('mga_meta_gen_temperature');

    if ($type == "full_description") {
        $prompt = get_option('mga_meta_gen_full_description');
    }

    if ($type == "short_description") {
        $prompt = get_option('mga_meta_gen_short_description');
    }

    if ($type == "generate_tags") {
        $prompt = "Can you provide tags for the mentioned product which has a higher focus keyphrase value, also separate each tag with a comma, and don't include '#' in the tags:";
    }

    if ($type == "custom_product_title") {
        $prompt = "With the given description generate a short product title for Description:";
    }

    $prompt = $prompt . ' ' . $appendData;

    // Prepare the request data
    $data = array(
        'model' => get_option('mga_meta_gen_gpt_model_name'),
        'prompt' => $prompt,
        'max_tokens' => 70,
        'temperature' => $temperature,
    );

    $api_response = wp_remote_post(
        $endpoint,
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
        )
    );

    $body = wp_remote_retrieve_body($api_response);
    $result = json_decode($body, true);

    if (isset($result['choices'][0]['text'])) {
        $response['status'] = 'success';
        $response[$type] = $result['choices'][0]['text'];
        $response['message'] = 'Data generated successfully';
        $response['prompt'] = $prompt;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error generating data for ' . $type;
    }
    return $response;
}









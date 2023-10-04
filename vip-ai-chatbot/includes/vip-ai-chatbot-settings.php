<?php
/**
 * VIP AI CHATBOT for WordPress - Settings Page
 *
 * This file contains the code for the VIP AI CHATBOT settings page.
 * It allows users to configure the API key and other parameters
 * required to access the ChatGPT API from their own account.
 *
 * @package vip-ai-chatbot
 */

global $default_primary_color;
$default_primary_color = '#009933';

function vip_ai_chatbot_settings_page() {
    add_options_page('VIP AI CHATBOT Settings', 'VIP AI CHATBOT', 'manage_options', 'vip-ai-chatbot', 'vip_ai_chatbot_settings_page_html');
}
add_action('admin_menu', 'vip_ai_chatbot_settings_page');

// Settings page HTML - Ver 1.3.0
function vip_ai_chatbot_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $api_host;

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

    if (isset($_GET['settings-updated'])) {
        add_settings_error('vip_ai_chatbot_messages', 'vip_ai_chatbot_message', 'Settings Saved', 'updated');
    }

    // Get usage
    $usage_queries = 0;
    $api_key = esc_attr(get_option('chatgpt_api_key'));
    $response = wp_remote_post( $api_host . '/sites/usage', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-KEY' => $api_key,
        ),
        'method' => 'GET',
        'data_format' => 'json'
    ) );

    if ( ! is_wp_error($response) ) {
        $response_body = json_decode( wp_remote_retrieve_body($response), true );
        $usage_queries = ( isset( $response_body["response"] ) ? $response_body["response"] : 0 );
    }
    ?>
    <script src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/js/jscolor.min.js"></script>
    <div class="wrap">
        <h1><span class="dashicons dashicons-format-chat"></span> <?php echo esc_html(get_admin_page_title()); ?></h1>

        <!-- Message Box - Ver 1.3.0 -->
        <div id="message-box-container"></div>

        <style>
            .usage-indicator-wrapper { max-width: 300px; position: relative; margin: 0 0 30px; }
            .usage-indicator-wrapper h4 { width: 100%; display: inline-block; margin: 0 0 5px; }
            .usage-indicator-wrapper h4 span { float: left; }
            .usage-indicator-wrapper h4 strong { float: right; }
            .usage-indicator-wrapper h5 { font-weight: normal; }
            .usage-queries-wrapper { width: 100%; height: 8px; background: #CCC; border-radius: 4px; overflow: hidden; }
            .usage-queries-wrapper .queries-bar { height: 8px; float: left; background: #2271b1; }
        </style>

        <!-- Message Box - Ver 1.3.0 -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatgptSettingsForm = document.getElementById('chatgpt-settings-form');
                // Read the start status - Ver 1.4.1
                const chatgptStartStatusInput = document.getElementById('chatGPTChatBotStatus');
                const reminderCount = localStorage.getItem('reminderCount') || 0;

                if (reminderCount < 5) {
                    const messageBox = document.createElement('div');
                    messageBox.id = 'rateReviewMessageBox';
                    messageBox.innerHTML = `
                    <div id="rateReviewMessageBox" style="background-color: white; border: 1px solid black; padding: 10px; position: relative;">
                        <div class="message-content" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>If you and your visitors are enjoying having this chatbot on your site, please take a moment to <a href="https://wordpress.org/support/plugin/vip-ai-chatbot/reviews/" target="_blank">rate and review this plugin</a>. Thank you!</span>
                            <button id="closeMessageBox" class="dashicons dashicons-dismiss" style="background: none; border: none; cursor: pointer; outline: none; padding: 0; margin-left: 10px;"></button>
                            
                        </div>
                    </div>
                    `;

                    document.querySelector('#message-box-container').insertAdjacentElement('beforeend', messageBox);

                    document.getElementById('closeMessageBox').addEventListener('click', function() {
                        messageBox.style.display = 'none';
                        localStorage.setItem('reminderCount', parseInt(reminderCount, 10) + 1);
                    });
                }
            });
        </script>
        <script>
            jQuery(document).ready(function($) {
                var chatgptSettingsForm = document.getElementById('chatgpt-settings-form');

                if (chatgptSettingsForm) {

                    chatgptSettingsForm.addEventListener('submit', function() {

                        // Get the input elements by their ids
                        const chatgptNameInput = document.getElementById('chatgpt_bot_name');
                        const chatgptInitialGreetingInput = document.getElementById('chatgpt_initial_greeting');
                        const chatgptSubsequentGreetingInput = document.getElementById('chatgpt_subsequent_greeting');
                        const chatgptStartStatusInput = document.getElementById('chatGPTChatBotStatus');
                        const chatgptDisclaimerSettingInput = document.getElementById('chatgpt_disclaimer_setting');
                        const chatgptPrimaryColorInput = document.getElementById('chatgpt_primary_color_setting');
                        const chatgptDefaultVisibilityInput = document.getElementById('chatgpt_default_visibility');
                        // New options for max tokens and width - Ver 1.4.2
                        const chatgptMaxTokensSettingInput = document.getElementById('chatgpt_max_tokens_setting');
                        const chatgptWidthSettingInput = document.getElementById('chatgpt_width_setting');

                        // Update the local storage with the input values, if inputs exist
                        if(chatgptNameInput) localStorage.setItem('chatgpt_bot_name', chatgptNameInput.value);
                        if(chatgptInitialGreetingInput) localStorage.setItem('chatgpt_initial_greeting', chatgptInitialGreetingInput.value);
                        if(chatgptSubsequentGreetingInput) localStorage.setItem('chatgpt_subsequent_greeting', chatgptSubsequentGreetingInput.value);
                        if(chatgptStartStatusInput) localStorage.setItem('chatGPTChatBotStatus', chatgptStartStatusInput.value);
                        if(chatgptDisclaimerSettingInput) localStorage.setItem('chatgpt_disclaimer_setting', chatgptDisclaimerSettingInput.value);
                        if(chatgptPrimaryColorInput) localStorage.setItem('chatgpt_primary_color_setting', chatgptDisclaimerSettingInput.value);
                        if(chatgptDefaultVisibilityInput) localStorage.setItem('chatgpt_disclaimer_setting', chatgptDefaultVisibilityInput.value);
                        if(chatgptMaxTokensSettingInput) localStorage.setItem('chatgpt_max_tokens_setting', chatgptMaxTokensSettingInput.value);
                        if(chatgptWidthSettingInput) localStorage.setItem('chatgpt_width_setting', chatgptWidthSettingInput.value);
                    });
                }
            });
        </script>

        <div class="usage-indicator-wrapper">
            <h2><span class="dashicons dashicons-info"></span> Free Plan</h2>

            <h4>
                <span>Queries</span>
                <strong><?php echo $usage_queries; ?>/100</strong>
            </h4>
            <div class="usage-queries-wrapper">
                <div class="queries-bar" style="width: <?php echo ($usage_queries * 0.01) * 100; ?>%;"></div>
            </div>
            <h5>Month usage resets on the 1st of every month</h5>
        </div>

        <h2 class="nav-tab-wrapper">
            <a href="?page=vip-ai-chatbot&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
            <!-- Coming Soon in Ver 2.0.0 -->
            <!-- <a href="?page=vip-ai-chatbot&tab=premium" class="nav-tab <?php echo $active_tab == 'premium' ? 'nav-tab-active' : ''; ?>">Premium</a> -->
            <a href="?page=vip-ai-chatbot&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
        </h2>

        <!-- Updated id - Ver 1.4.1 -->
        <form id="chatgpt-settings-form" action="options.php" method="post">
            <?php
            if ($active_tab == 'settings') {
                settings_fields('vip_ai_chatbot_settings');
                do_settings_sections('vip_ai_chatbot_settings');
            } elseif ($active_tab == 'api_model') {
                settings_fields('vip_ai_chatbot_api_model');
                do_settings_sections('vip_ai_chatbot_api_model');
            // Coming Soon in Ver 2.0.0
            // } elseif ($active_tab == 'premium') {
            //     settings_fields('vip_ai_chatbot_premium');
            //     do_settings_sections('vip_ai_chatbot_premium');
            } elseif ($active_tab == 'support') {
                settings_fields('vip_ai_chatbot_support');
                do_settings_sections('vip_ai_chatbot_support');
            }
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <!-- Added closing tags for body and html - Ver 1.4.1 -->
    </body>
    </html>
    <?php
}


// Register settings
function vip_ai_chatbot_settings_init() {

    // API/Model settings tab - Ver 1.3.0
    register_setting('vip_ai_chatbot_api_model', 'chatgpt_api_key');
    register_setting('vip_ai_chatbot_api_model', 'chatgpt_model_choice');
    // Max Tokens setting options - Ver 1.4.2
    register_setting('vip_ai_chatbot_api_model', 'chatgpt_max_tokens_setting');

    add_settings_section(
        'vip_ai_chatbot_api_model_section',
        'API/Model Settings',
        'vip_ai_chatbot_api_model_section_callback',
        'vip_ai_chatbot_api_model'
    );

    add_settings_field(
        'chatgpt_api_key',
        'ChatGPT API Key',
        'vip_ai_chatbot_api_key_callback',
        'vip_ai_chatbot_api_model',
        'vip_ai_chatbot_api_model_section'
    );

    add_settings_field(
        'chatgpt_model_choice',
        'ChatGPT Model Choice',
        'vip_ai_chatbot_model_choice_callback',
        'vip_ai_chatbot_api_model',
        'vip_ai_chatbot_api_model_section'
    );
    
    // Setting to adjust in small increments the number of Max Tokens - Ver 1.4.2
    add_settings_field(
        'chatgpt_max_tokens_setting',
        'Maximum Tokens Setting',
        'chatgpt_max_tokens_setting_callback',
        'vip_ai_chatbot_api_model',
        'vip_ai_chatbot_api_model_section'
    );


    // Settings settings tab - Ver 1.3.0
    register_setting('vip_ai_chatbot_settings', 'chatgpt_bot_name');
    register_setting('vip_ai_chatbot_settings', 'chatGPTChatBotStatus');
    register_setting('vip_ai_chatbot_settings', 'chatgpt_initial_greeting');
    register_setting('vip_ai_chatbot_settings', 'chatgpt_subsequent_greeting');
    // Option to remove the OpenAI disclaimer - Ver 1.4.1
    register_setting('vip_ai_chatbot_settings', 'chatgpt_disclaimer_setting');
    register_setting('vip_ai_chatbot_settings', 'chatgpt_primary_color_setting');
    register_setting('vip_ai_chatbot_settings', 'chatgpt_default_visibility');
    // Option to select narrow or wide chatboat - Ver 1.4.2
    register_setting('vip_ai_chatbot_settings', 'chatgpt_width_setting');

    add_settings_section(
        'vip_ai_chatbot_settings_section',
        'Settings',
        'vip_ai_chatbot_settings_section_callback',
        'vip_ai_chatbot_settings'
    );

    add_settings_field(
        'chatgpt_bot_name',
        'Bot Name',
        'vip_ai_chatbot_bot_name_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    add_settings_field(
        'chatGPTChatBotStatus',
        'Start Status',
        'vip_ai_chatbotChatBotStatus_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    add_settings_field(
        'chatgpt_initial_greeting',
        'Initial Greeting',
        'vip_ai_chatbot_initial_greeting_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    add_settings_field(
        'chatgpt_subsequent_greeting',
        'Subsequent Greeting',
        'vip_ai_chatbot_subsequent_greeting_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    // Option to remove the OpenAI disclaimer - Ver 1.4.1
    add_settings_field(
        'chatgpt_disclaimer_setting',
        'chatgpt_disclaimer_setting_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    add_settings_field(
        'chatgpt_primary_color_setting',
        'Primary Color',
        'chatgpt_primary_color_setting_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    add_settings_field(
        'chatgpt_default_visibility',
        'Show the Chatbot in every page',
        'chatgpt_default_visibility_callback',
        'vip_ai_chatbot_settings',
        'vip_ai_chatbot_settings_section'
    );

    // Premium settings tab - Ver 1.3.0
    register_setting('vip_ai_chatbot_premium', 'chatgpt_premium_key');

    add_settings_section(
        'vip_ai_chatbot_premium_section',
        'Premium Settings',
        'vip_ai_chatbot_premium_section_callback',
        'vip_ai_chatbot_premium'
    );

    add_settings_field(
        'chatgpt_premium_key',
        'Premium Options',
        'vip_ai_chatbot_premium_key_callback',
        'vip_ai_chatbot_premium',
        'vip_ai_chatbot_premium_section'
    );

    // Support settings tab - Ver 1.3.0
    register_setting('vip_ai_chatbot_support', 'chatgpt_support_key');

    add_settings_section(
        'vip_ai_chatbot_support_section',
        'Support',
        'vip_ai_chatbot_support_section_callback',
        'vip_ai_chatbot_support'
    );
        
}

add_action('admin_init', 'vip_ai_chatbot_settings_init');

// API/Model settings section callback - Ver 1.3.0
function vip_ai_chatbot_api_model_section_callback($args) {
    ?>
    <p>Configure settings for the VIP AI CHATBOT plugin by adding your API key and selection the GPT model of your choice.</p>
    <p>This plugin requires an API key from OpenAI to function. You can obtain an API key by signing up at <a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a>.</p>
    <p>More information about ChatGPT models and their capability can be found at <a href="https://platform.openai.com/docs/models/overview" taget="_blank">https://platform.openai.com/docs/models/overview</a>.</p>
    <p>Enter your ChatGPT API key below and select the OpenAI model of your choice.</p>
    <p>As soon as the API for GPT-4 is available for general use, you will be able to select from the latest available models.</p>
    <?php
}

// Settings section callback - Ver 1.3.0
function vip_ai_chatbot_settings_section_callback($args) {
    ?>
    <p>Configure settings for the VIP AI CHATBOT plugin, including the bot name, start status, and greetings.</p>
    <?php
}

// Premium settings section callback - Ver 1.3.0
function vip_ai_chatbot_premium_section_callback($args) {
    ?>
    <p>Enter your premium key here.</p>
    <?php
}

// Support settings section callback - Ver 1.3.0
function vip_ai_chatbot_support_section_callback($args) {
    ?>
    <div>
	<h3>Description</h3>
    <p>Meet VIP AI CHATBOT - the ultimate wingbot for your website! Hassle-free, AI-powered charm in less than a coffee break #ChatDelight</p>
    <p>Introducing our AI-powered chatbot – your website's new best friend, here to make your online interactions smoother and more delightful than ever before! Imagine having a virtual assistant with a sense of humor and lightning-fast responses, available 24/7 to assist your visitors. We've harnessed the power of cutting-edge AI technology to create a hassle-free experience that's as entertaining as it is efficient.</p>
    <h3>Official Sites:</h3>
    <ul style="list-style-type: disc; list-style-position: inside; padding-left: 1em;"> 
    <li><a href="https://www.htech.vip/vip-ai-chatbot/" target="_blank">htech.vip</a></li>
    <li><a href="https://github.com/htechvip/vip-ai-chatbot" target="_blank">source code</a></li>
    <li><a href="https://wordpress.org/plugins/vip-ai-chatbot/" target="_blank">plugin</a></li>
    </ul>
    <h3>Features</h3>
    <ul style="list-style-type: disc; list-style-position: inside; padding-left: 1em;">
    <li>Effortless one-click setup for quick integration</li>
    <li>AI model seamlessly fine-tuned to match your website's content</li>
    <li>Instant responses that keep visitors engaged and satisfied</li>
    <li>Customizable color picker to perfectly match your website's theme</li>
    <li>Welcoming initial greeting for first-time users to ensure a friendly start</li>
    <li>No need for an OpenAI API key – hassle-free implementation</li>
    <li>Powered by the advanced capabilities of gpt for top-notch performance</li>
    <li>Chatbot name and initial and subsequent greetings are configurable</li>
    </ul>
    <h3>Getting Started</h3>
    <ol>
    <li>Install and activate the VIP AI CHATBOT plugin.</li>
    <li>Go to website home page to see it in action.</li>
    <li>(optional) By default, the chatbot is shown every page. You can turn that off and just add the chatbot to a specific page using the provided shortcode: [vip_ai_chatbot]</li>
    </ol>
    <p>Now your website visitors can enjoy a seamless and personalized chat experience powered by H TECH VIP API.</p>
    <h3>Installation</h3>
	<ol>
    <li>Upload the 'vip-ai-chatbot' folder to the '/wp-content/plugins/' directory.</li>
    <li>Activate the plugin through the 'Plugins' menu in WordPress.</li>
    </ol>
    <h3>How does it work?</h3>
    <ol>
    <li>After the plugin is install, the model will learn about your web pages (up to about 40K-50K characters) and it will answer any user questions based on the information gathered.</li>
    <li>If the user asks irrelevant questions, it will answer 'I don't know'.</li>
    <li>We load up various API keys (anthropic, openai, etc) so that you don't have to.</li>
    </ol>
    </div>
    <?php
}

// API key field callback
function vip_ai_chatbot_api_key_callback($args) {
    $api_key = esc_attr(get_option('chatgpt_api_key'));
    ?>
    <input type="text" id="chatgpt_api_key" name="chatgpt_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
    <?php
}

// Model choice
function vip_ai_chatbot_model_choice_callback($args) {
    // Get the saved chatgpt_model_choice value or default to "gpt-3.5-turbo"
    $model_choice = esc_attr(get_option('chatgpt_model_choice', 'gpt-3.5-turbo'));
    ?>
    <select id="chatgpt_model_choice" name="chatgpt_model_choice">
        <!-- Allow for gpt-4 in Ver 1.4.2 -->
        <option value="<?php echo esc_attr( 'gpt-4' ); ?>" <?php selected( $model_choice, 'gpt-4' ); ?>><?php echo esc_html( 'gpt-4' ); ?></option>
        <option value="<?php echo esc_attr( 'gpt-3.5-turbo' ); ?>" <?php selected( $model_choice, 'gpt-3.5-turbo' ); ?>><?php echo esc_html( 'gpt-3.5-turbo' ); ?></option>
    </select>
    <?php
}

// VIP AI CHATBOT Name
function vip_ai_chatbot_bot_name_callback($args) {
    $bot_name = esc_attr(get_option('chatgpt_bot_name', 'VIP AI CHATBOT'));
    ?>
    <input type="text" id="chatgpt_bot_name" name="chatgpt_bot_name" value="<?php echo esc_attr( $bot_name ); ?>" class="regular-text">
    <?php
}

function vip_ai_chatbotChatBotStatus_callback($args) {
    $start_status = esc_attr(get_option('chatGPTChatBotStatus', 'closed'));
    ?>
    <select id="chatGPTChatBotStatus" name="chatGPTChatBotStatus">
        <option value="open" <?php selected( $start_status, 'open' ); ?>><?php echo esc_html( 'Open' ); ?></option>
        <option value="closed" <?php selected( $start_status, 'closed' ); ?>><?php echo esc_html( 'Closed' ); ?></option>
    </select>
    <?php
}

function vip_ai_chatbot_initial_greeting_callback($args) {
    $initial_greeting = esc_attr(get_option('chatgpt_initial_greeting', 'Hello! I can answer anything related to this website. How can I help?'));
    ?>
    <textarea id="chatgpt_initial_greeting" name="chatgpt_initial_greeting" rows="2" cols="50"><?php echo esc_textarea( $initial_greeting ); ?></textarea>
    <?php
}

function vip_ai_chatbot_subsequent_greeting_callback($args) {
    $subsequent_greeting = esc_attr(get_option('chatgpt_subsequent_greeting', 'Hello again! Need help?'));
    ?>
    <textarea id="chatgpt_subsequent_greeting" name="chatgpt_subsequent_greeting" rows="2" cols="50"><?php echo esc_textarea( $subsequent_greeting ); ?></textarea>
    <?php
}

function chatgpt_primary_color_setting_callback($args) {
    global $default_primary_color;
    $primary_color = esc_attr(get_option('chatgpt_primary_color_setting', $default_primary_color));
    ?>
    <input type="text" id="chatgpt_primary_color_setting" name="chatgpt_primary_color_setting" value="<?php echo esc_attr( $primary_color ); ?>" class="regular-text" data-jscolor="{}">
    <?php
}

// Max Tokens choice - Ver 1.4.2
function chatgpt_max_tokens_setting_callback($args) {
    // Get the saved chatgpt_max_tokens_setting or default to 150
    $max_tokens = esc_attr(get_option('chatgpt_max_tokens_setting', '150'));
    ?>
    <select id="chatgpt_max_tokens_setting" name="chatgpt_max_tokens_setting">
        <option value="<?php echo esc_attr( '100' ); ?>" <?php selected( $max_tokens, '100' ); ?>><?php echo esc_html( '100' ); ?></option>
        <option value="<?php echo esc_attr( '150' ); ?>" <?php selected( $max_tokens, '150' ); ?>><?php echo esc_html( '150' ); ?></option>
        <option value="<?php echo esc_attr( '200' ); ?>" <?php selected( $max_tokens, '200' ); ?>><?php echo esc_html( '200' ); ?></option>
        <option value="<?php echo esc_attr( '250' ); ?>" <?php selected( $max_tokens, '250' ); ?>><?php echo esc_html( '250' ); ?></option>
        <option value="<?php echo esc_attr( '300' ); ?>" <?php selected( $max_tokens, '300' ); ?>><?php echo esc_html( '300' ); ?></option>
        <option value="<?php echo esc_attr( '350' ); ?>" <?php selected( $max_tokens, '350' ); ?>><?php echo esc_html( '350' ); ?></option>
        <option value="<?php echo esc_attr( '400' ); ?>" <?php selected( $max_tokens, '400' ); ?>><?php echo esc_html( '400' ); ?></option>
        <option value="<?php echo esc_attr( '450' ); ?>" <?php selected( $max_tokens, '450' ); ?>><?php echo esc_html( '450' ); ?></option>
        <option value="<?php echo esc_attr( '500' ); ?>" <?php selected( $max_tokens, '500' ); ?>><?php echo esc_html( '500' ); ?></option>
    </select>
    <?php
}

function chatgpt_default_visibility_callback($args) {
    $chatgpt_default_visibility = esc_attr(get_option('chatgpt_default_visibility', 'Yes'));
    ?>
    <select id="chatgpt_default_visibility" name="chatgpt_default_visibility">
        <option value="Yes" <?php selected( $chatgpt_default_visibility, 'Yes' ); ?>><?php echo esc_html( 'Yes' ); ?></option>
        <option value="No" <?php selected( $chatgpt_default_visibility, 'No' ); ?>><?php echo esc_html( 'No' ); ?></option>
    </select>
    <?php    
}

// Premium Key - Ver 1.3.0
function vip_ai_chatbot_premium_key_callback($args) {
    $premium_key = esc_attr(get_option('chatgpt_premium_key'));
    ?>
    <input type="text" id="chatgpt_premium_key" name="chatgpt_premium_key" value="<?php echo esc_attr( $premium_key ); ?>" class="regular-text">
    <?php
}
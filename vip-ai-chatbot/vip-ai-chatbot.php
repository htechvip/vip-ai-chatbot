<?php
/*
 * Plugin Name: VIP AI CHATBOT
 * Plugin URI:  https://github.com/htechvip/vip-ai-chatbot
 * Description: Meet VIP AI CHATBOT - the ultimate wingbot for your website! Hassle-free, AI-powered charm in less than a coffee break #ChatDelight
 * Version:     1.0.0
 * Author:      htechvip
 * Author URI:  https://www.htech.vip
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * You should have received a copy of the GNU General Public License
 * along with VIP AI CHATBOT. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 * 
*/

// If this file is called directly, die.
defined( 'WPINC' ) || die;

// If this file is called directly, die.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

global $api_host;
$api_host = 'https://chatbot.htechvip.com';

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/vip-ai-chatbot-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/vip-ai-chatbot-shortcode.php';

// Diagnostics On or Off - Ver 1.4.2
update_option('chatgpt_diagnostics', 'Off');

// Enqueue plugin scripts and styles
function vip_ai_chatbot_enqueue_scripts() {
    // Ensure the Dashicons font is properly enqueued - Ver 1.1.0
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style('vip-ai-chatbot-css', plugins_url('assets/css/vip-ai-chatbot.css', __FILE__));
    wp_enqueue_script('vip-ai-chatbot-js', plugins_url('assets/js/vip-ai-chatbot.js', __FILE__), array('jquery'), '1.0', true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
    
    // Ver 1.4.1
    // Enqueue the vip-ai-chatbot-local.js file
    wp_enqueue_script('vip-ai-chatbot-local', plugins_url('assets/js/vip-ai-chatbot-local.js', __FILE__), array('jquery'), '1.0', true);
    $chatbot_settings = array(
        'chatgpt_bot_name' => esc_attr(get_option('chatgpt_bot_name')),
        'chatgpt_initial_greeting' => esc_attr(get_option('chatgpt_initial_greeting')),
        'chatgpt_subsequent_greeting' => esc_attr(get_option('chatgpt_subsequent_greeting')),
        'chatGPTChatBotStatus' => esc_attr(get_option('chatGPTChatBotStatus')),
        'chatgpt_disclaimer_setting' => esc_attr(get_option('chatgpt_disclaimer_setting')),
        'chatgpt_max_tokens_setting' => esc_attr(get_option('chatgpt_max_tokens_setting')),
        'chatgpt_width_setting' => esc_attr(get_option('chatgpt_width_setting')),
    );
    wp_localize_script('vip-ai-chatbot-local', 'chatbotSettings', $chatbot_settings);

    wp_localize_script('vip-ai-chatbot-js', 'vip_ai_chatbot_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'api_key' => esc_attr(get_option('chatgpt_api_key')),
    ));
}
add_action('wp_enqueue_scripts', 'vip_ai_chatbot_enqueue_scripts');

// Handle Ajax requests
function vip_ai_chatbot_send_message() {
    // Get the save API key
    $api_key = esc_attr(get_option('chatgpt_api_key'));
    // Get the saved model from the settings or default to gpt-3.5-turbo
    $model = esc_attr(get_option('chatgpt_model_choice', 'gpt-3.5-turbo'));
    // Max tokens - Ver 1.4.2
    $max_tokens = esc_attr(get_option('chatgpt_max_tokens_setting', 150));
    // Send only clean text via the API
    $message = sanitize_text_field($_POST['message']);

    // Check API key and message
    // if (!$api_key || !$message) {
    //     wp_send_json_error('Invalid API key or message');
    // }

    // Send message to ChatGPT API
    $response = vip_ai_chatbot_call_api($api_key, $message);

    // Return response
    wp_send_json_success($response);
}

// Add link to chatgtp options - setting page
function vip_ai_chatbot_plugin_action_links($links) {
    $settings_link = '<a href="../wp-admin/options-general.php?page=vip-ai-chatbot">' . __('Settings', 'vip-ai-chatbot') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_action('wp_ajax_vip_ai_chatbot_send_message', 'vip_ai_chatbot_send_message');
add_action('wp_ajax_nopriv_vip_ai_chatbot_send_message', 'vip_ai_chatbot_send_message');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vip_ai_chatbot_plugin_action_links');

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

// Call the ChatGPT API
function vip_ai_chatbot_call_api($api_key, $message) {
    global $api_host;

    // Diagnostics = Ver 1.4.2
    $chatgpt_diagnostics = esc_attr(get_option('chatgpt_diagnostics', 'Off'));

    $api_url = $api_host . '/sites/prompts';

    $response = wp_remote_post( $api_url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-KEY' => $api_key,
        ),
        'body' => json_encode( array(
            'usr_msg' => $message
        ) ),
        'method' => 'POST',
        'data_format' => 'body'
    ) );
   

    // Handle any errors that are returned from the chat engine
    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message() . ' Please check Settings for a valid API key or your OpenAI account for additional information.';
    }

    $response_body = json_decode( wp_remote_retrieve_body($response), true );

    if ( isset($response_body) ) {
        return $response_body;
    } else {
        // Handle any errors that are returned from the chat engine
        return 'Error: Unable to fetch response from ChatGPT API. Please check Settings for a valid API key or your OpenAI account for additional information.';
    }
}


function enqueue_greetings_script() {
    wp_enqueue_script('greetings', plugin_dir_url(__FILE__) . 'assets/js/greetings.js', array('jquery'), null, true);

    $greetings = array(
        'initial_greeting' => esc_attr(get_option('chatgpt_initial_greeting', 'Hello! I can answer anything related to this website. How can I help?')),
        'subsequent_greeting' => esc_attr(get_option('chatgpt_subsequent_greeting', 'Hello again! Need help?')),
    );

    wp_localize_script('greetings', 'greetings_data', $greetings);
}
add_action('wp_enqueue_scripts', 'enqueue_greetings_script');

/*
* Scrape content and send it to the endpoint.
*/
register_activation_hook(__FILE__, 'chat_activation_content_scrape');
function chat_activation_content_scrape() {
    global $api_host;

    $pages_content = [];

    // Scrape the homepage first
    $pages_content[] = get_page_html_content( get_option('home'), true );

    // Get homepage ID
    $front_page_id = get_option('page_on_front');

    // Get most common pages first
    $scapped_page_IDs = [$front_page_id];
    $posts_objs = get_posts( array(
        'post_type' => 'page',
        'post_name__in'  => ['about', 'about-us', 'faq', 'contact', 'contact-us', 'home', 'homepage'],
        'post__not_in' => array($front_page_id),
        'posts_per_page' => 5,
    ) );
    if( ! empty( $posts_objs ) ) {
        foreach( $posts_objs as $post ) {
            $pages_content[] = $post->post_content;
            $scapped_page_IDs[] = $post->ID;
        }
    }

    // Too few pages, get html content instead
    if( count( $posts_objs ) <= 5 ) {
        $randon_pages_objs = get_pages( array(
            'number' => 5,
            'sort_column' => 'rand',
            'exclude' => array($scapped_page_IDs),
        ) );
        if( ! empty( $randon_pages_objs ) ) {
            foreach( $randon_pages_objs as $post ) {
                $pages_content[] = get_page_html_content( get_option('home') . "/?page_id=" . $post->ID );
            }
        }
    }

    $unified_page_content = implode("\\n", $pages_content);

    // Send it to the sites endpoint
    $api_url = $api_host . '/sites/';
    $body = array(
        'site_url' => get_option('home'),
        'knowledge' => $unified_page_content,
    );

    $args = array(
        'headers' => array( 'Content-Type' => 'application/json' ),
        'body' => json_encode($body),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 50,
    );

    $response = wp_remote_post($api_url, $args);

    if ( ! is_wp_error($response) ) {
        $response_obj = json_decode($response["body"]);
        $api_key = $response_obj->api_key;
        update_option('chatgpt_api_key', $api_key);
    }
}

function get_page_html_content( $page_url, $keep_headers_footers = false ) {
    $c = curl_init($page_url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($c);
    if( curl_error($c) ) die( curl_error($c) );
    curl_close($c);

    if( ! empty( $html ) ) {
        // Remove everyting inside <head>
        $head_tag_init_pos = strpos($html, "<head");
        $head_tag_end_pos = strpos($html, "</head>");
        $edited_html = substr_replace($html, "", $head_tag_init_pos, $head_tag_end_pos - $head_tag_init_pos);

        // Remove every <script> occurence
        $edited_html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $edited_html);

        // Remove every <style> occurence
        $edited_html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $edited_html);

        if ( ! $keep_headers_footers ) {
            // Remove every <header> occurence
            $edited_html = preg_replace('#<header(.*?)>(.*?)</header>#is', '', $edited_html);

            // Remove every <footer> occurence
            $edited_html = preg_replace('#<footer(.*?)>(.*?)</footer>#is', '', $edited_html);
        }

        // Remove all other tags and encode it
        $edited_html = strip_tags($edited_html);

        // Remove shortcodes
        $edited_html = strip_shortcodes($edited_html);

        // Remove special characters
        $edited_html = str_replace( array( '\'', '"', '<', '>' ), ' ', $edited_html);

        // Remove empty lines and indentation
        $edited_html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $edited_html);
        $edited_html = trim( preg_replace('/\t+/', '', $edited_html) );

        // Remove extra white spaces
        $edited_html = preg_replace('/[\s]+/', ' ', $edited_html);

        // Finally, encode it
        $edited_html = json_encode($edited_html);

        return $edited_html;
    }
}

// Show the chat on every page by default on page load
function vip_ai_chat_show() {
    $chatgpt_default_visibility = esc_attr(get_option('chatgpt_default_visibility', 'Yes'));
    if ( $chatgpt_default_visibility == "Yes" ) {
        if ( function_exists("vip_ai_chatbot_shortcode") ) echo vip_ai_chatbot_shortcode();
    }
}
add_action('wp_footer', 'vip_ai_chat_show');
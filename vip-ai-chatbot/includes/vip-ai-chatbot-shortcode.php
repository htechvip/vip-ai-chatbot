<?php
/**
 * VIP AI CHATBOT for WordPress - Shortcode Registration
 *
 * This file contains the code for registering the shortcode used
 * to display the VIP AI CHATBOT on the website.
 *
 * @package vip-ai-chatbot
 */

function vip_ai_chatbot_shortcode() {
    // Retrieve the bot name - Ver 1.1.0
    // Add styling to the bot to ensure that it is not shown before it is needed Ver 1.2.0
    global $default_primary_color;

    $bot_name = esc_attr(get_option('chatgpt_bot_name', 'VIP AI Chatbot'));
    $primary_color = esc_attr(get_option('chatgpt_primary_color_setting', $default_primary_color));

    ob_start();
    $icons_url =  plugins_url('/icons', __FILE__);
    ?>
    <div id="vip-ai-chatbot" style="display: none; <?php if(wp_is_mobile()) echo('right:50px;');?>" class="rounded-15">
        <div id="vip-ai-chatbot-header" class="header-back header-rounded flex flex-row justify-between" >
            <div id="chatgptTitle" class="title"><span><?php echo $bot_name; ?></span></div>
            <div class="right-header flex items-center">
                <div id="collapse_action" class="action-btn">
                  <i class="fa fa-chevron-down"></i> 
                </div>
            </div>
        </div>
        <div class="state-display">
            <div class="state-text" style=""><div class="state-badge"></div> <span>AI Agent at your service 24/7</span></div>
        </div>
        <div id="vip-ai-chatbot-conversation"></div>
        <div id="vip-ai-chatbot-input">
            <textarea id="vip-ai-chatbot-message" rows="1" placeholder="<?php echo esc_attr( 'Enter your message...' ); ?>" maxlength="2000"></textarea>
            <div id="vip-ai-chatbot-submit">
               <i class="fa fa-paper-plane" style="color:white; font-size:20px"></i>
            </div>
        </div>
        
        <div class="flex justify-end">
            <div class="flex logo">
                <span class="com-log"> POWERED BY H TECH VIP </span>
                <div class="logo"> </div>
            </div> 
        </div>
    </div>
    <button id="chatgpt-open-btn" style="display: none;"><i class="dashicons dashicons-format-chat"></i></button>
    <style>
        .header-back,
        .state-display,
        #vip-ai-chatbot-submit,
        #chatgpt-open-btn,
        #chatgpt-open-btn:hover,
        .user-text,
        .chatbot-collapse-btn:hover,
        .action-btn:hover {
            background: <?php echo $primary_color; ?>;
        }
        .bot-text,
        .typing-indicator {
            border-color: <?php echo $primary_color; ?>;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('vip_ai_chatbot', 'vip_ai_chatbot_shortcode');

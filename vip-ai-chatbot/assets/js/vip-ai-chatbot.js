jQuery(document).ready(function ($) {

    // Logging for Diagnostics - Ver 1.4.2
    var chatgpt_diagnostics = localStorage.getItem('chatgpt_diagnostics') || 'Off';
    localStorage.setItem('chatgpt_diagnostics', chatgpt_diagnostics); // Set if not set

    var messageInput = $('#vip-ai-chatbot-message');
    var conversation = $('#vip-ai-chatbot-conversation');
    var submitButton = $('#vip-ai-chatbot-submit');
    var optionbtn = $('#action-option');
    var itemGroup = $('#item-group');

    // Set bot width with the default Narrow or from setting Wide - Ver 1.4.2
    var chatgpt_width_setting = localStorage.getItem('chatgpt_width_setting') || 'Narrow';

    var chatGptChatBot = $('#vip-ai-chatbot');
    if (chatgpt_width_setting === 'Wide') {
        chatGptChatBot.addClass('wide');
    } else {
        chatGptChatBot.removeClass('wide');
    }

    // Diagnostics = Ver 1.4.2
    if (chatgpt_diagnostics === 'On') {
        console.log(messageInput);
        console.log(conversation);
        console.log(submitButton);
        console.log(chatGptChatBot);
        console.log('chatgpt_width_setting: ' + chatgpt_width_setting);
    }

    var chatGptOpenButton = $('#chatgpt-open-btn');
    // Use 'open' for an open chatbot or 'closed' for a closed chatbot - Ver 1.1.0
    var chatgpt_start_status = 'closed';

    // Initially hide the chatbot - Ver 1.1.0
    chatGptChatBot.hide();
    chatGptOpenButton.show();

    var chatbotContainer = $('<div></div>').addClass('chatbot-container');
    // var chatbotCollapseBtn = $('<button></button>').addClass('chatbot-collapse-btn').addClass('dashicons dashicons-format-chat'); // Add a collapse button
    var chatbotCollapseBtn = $('#collapse_action'); // Add a collapse button
    var chatbotCollapsed = $('<div></div>').addClass('chatbot-collapsed'); // Add a collapsed chatbot icon dashicons-format-chat f125

    // Support variable greetings based on setting - Ver 1.1.0
    var initialGreeting = localStorage.getItem('chatgpt_initial_greeting') || 'Hello! I can answer anything related to this website. How can I help?';
    localStorage.setItem('chatgpt_initial_greeting', initialGreeting);
    var subsequentGreeting = localStorage.getItem('chatgpt_subsequent_greeting') || 'Hello again! Need help?';
    localStorage.setItem('chatgpt_subsequent_greeting', subsequentGreeting);
    // Handle disclaimer - Ver 1.4.1
    var chatgpt_disclaimer_setting = localStorage.getItem('chatgpt_disclaimer_setting') || 'Yes';

    // Append the collapse button and collapsed chatbot icon to the chatbot container
    // chatbotContainer.append(chatbotCollapseBtn);
    // chatbotContainer.append(chatbotCollapsed);

    // Add initial greeting to the chatbot
    // conversation.append(chatbotContainer);

    function initializeChatbot() {
        var chatgpt_diagnostics = localStorage.getItem('chatgpt_diagnostics') || 'Off';
        var isFirstTime = !localStorage.getItem('chatgptChatbotOpened');
        var initialGreeting;
        // Remove any legacy conversations that might be store in local storage for increased privacy - Ver 1.4.2
        localStorage.removeItem('chatgpt_conversation');

        if (isFirstTime) {
            initialGreeting = localStorage.getItem('chatgpt_initial_greeting') || 'Hello! I can answer anything related to this website. How can I help?';

            // Logging for Diagnostics - Ver 1.4.2
            if (chatgpt_diagnostics === 'On') {
                console.log("initialGreeting".initialGreeting);
            }

            // Don't append the greeting if it's already in the conversation
            if (conversation.text().includes(initialGreeting)) {
                return;
            }

            appendMessage(initialGreeting, 'bot', 'initial-greeting');
            localStorage.setItem('chatgptChatbotOpened', 'true');
            // Save the conversation after the initial greeting is appended - Ver 1.2.0
            sessionStorage.setItem('chatgpt_conversation', conversation.html());

        } else {

            initialGreeting = localStorage.getItem('chatgpt_subsequent_greeting') || 'Hello again! Need help?';

            // Logging for Diagnostics - Ver 1.4.2
            if (chatgpt_diagnostics === 'On') {
                console.log("initialGreeting".initialGreeting);
            }

            // Don't append the greeting if it's already in the conversation
            if (conversation.text().includes(initialGreeting)) {
                return;
            }

            appendMessage(initialGreeting, 'bot', 'initial-greeting');
            localStorage.setItem('chatgptChatbotOpened', 'true');
        }
    }


    // Add chatbot header, body, and other elements - Ver 1.1.0
    var chatbotHeader = $('<div></div>').addClass('chatbot-header');
    chatGptChatBot.append(chatbotHeader);

    // Fix for Ver 1.2.0
    // chatbotHeader.append(chatbotCollapseBtn);
    chatbotHeader.append(chatbotCollapsed);

    // Attach the click event listeners for the collapse button and collapsed chatbot icon
    chatbotCollapseBtn.on('click', toggleChatbot);
    chatbotCollapsed.on('click', toggleChatbot);
    chatGptOpenButton.on('click', toggleChatbot);
    optionbtn.on('click', toggleChatOption);

    document.addEventListener('click', event => {
        var itemG = document.getElementById('item-group');
        if( itemG != null ) {
            var optionG = document.getElementById('action-option');
            var isClickInside = itemG.contains(event.target);
            var isClickOption = optionG.contains(event.target);

            if (!isClickInside && !isClickOption) {
                itemGroup.hide();
                $('.tooltip-text').html("Open options");
            }
        }
    })

    function appendMessage(message, sender, cssClass) {
        var messageElement = $('<div></div>').addClass('chat-message');
        var textElement = $('<span>' + message + '</span>');

        // Add initial greetings if first time
        if (cssClass) {
            textElement.addClass(cssClass);
        }

        if (sender === 'user') {
            messageElement.addClass('user-message');
            textElement.addClass('user-text');
        } else if (sender === 'bot') {
            messageElement.addClass('bot-message');
            textElement.addClass('bot-text');
        } else {
            messageElement.addClass('error-message');
            textElement.addClass('error-text');
        }

        messageElement.append(textElement);
        conversation.append(messageElement);

        // Add space between user input and bot response
        if (sender === 'user' || sender === 'bot') {
            var spaceElement = $('<div></div>').addClass('message-space');
            conversation.append(spaceElement);
        }

        // Ver 1.2.4
        conversation[0].scrollTop = conversation[0].scrollHeight;

        // Save the conversation locally between bot sessions - Ver 1.2.0
        sessionStorage.setItem('chatgpt_conversation', conversation.html());

    }

    function showTypingIndicator() {
        var typingIndicator = $('<div></div>').addClass('typing-indicator');
        var dot1 = $('<span>.</span>').addClass('typing-dot');
        var dot2 = $('<span>.</span>').addClass('typing-dot');
        var dot3 = $('<span>.</span>').addClass('typing-dot');

        typingIndicator.append(dot1, dot2, dot3);
        conversation.append(typingIndicator);
        conversation.scrollTop(conversation[0].scrollHeight);
    }

    function isImgUrl(url) {
        return /\.(jpg|jpeg|png|webp|avif|gif)$/.test(url);
    }

    function urlify(text) {
        var urlRegex = /(https?:\/\/[^\s]+)/g;
        var emailRegex = /(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
        var imgUrls = [];

        // Replace and add links
        var updated_text = text.replace(urlRegex, function(url) {
            if ( isImgUrl(url) ) imgUrls.push(url);
            if ( url[url.length-1] === "." ) url = url.slice(0, -1);
            return '<a href="' + url + '">' + url + '</a>';
        });

        // Replace and add emails
        updated_text = updated_text.replace(emailRegex, function(url) {
            if ( url[url.length-1] === "." ) url = url.slice(0, -1);
            return '<a href="mailto:' + url + '">' + url + '</a>';
        });

        if( imgUrls.length > 0 ) {
            imgUrls.forEach(function(item){
                updated_text += '<a href="' + item + '" target="_blank"><img src="' + item + '" /></a>';
            });
        }

        return updated_text;
    }

    function removeTypingIndicator() {
        $('.typing-indicator').remove();
    }

    submitButton.on('click', function () {
        var message = messageInput.val().trim();
        var chatgpt_disclaimer_setting = localStorage.getItem('chatgpt_disclaimer_setting') || 'Yes';

        if ( ! message ) {
            return;
        }

        messageInput.val('');
        appendMessage(message, 'user');

        $.ajax({
            url: vip_ai_chatbot_params.ajax_url,
            method: 'POST',
            data: {
                action: 'vip_ai_chatbot_send_message',
                message: message,
            },
            beforeSend: function () {
                showTypingIndicator();
                submitButton.prop('disabled', true);
            },
            success: function (response) {
                removeTypingIndicator();
                if (response.success) {
                    let botResponse = response.data.response;
                    if( botResponse != undefined ) {
                        const prefix_a = "As an AI language model, ";
                        const prefix_b = "I am an AI language model and ";

                        if (botResponse.startsWith(prefix_a) && chatgpt_disclaimer_setting === 'No') {
                            botResponse = botResponse.slice(prefix_a.length);
                        } else if (botResponse.startsWith(prefix_b) && chatgpt_disclaimer_setting === 'No') {
                            botResponse = botResponse.slice(prefix_b.length);
                        }

                        botResponse = urlify(botResponse);

                        appendMessage(botResponse, 'bot');
                    } else {
                        appendMessage('Error: Unable to process response', 'error');
                    }
                } else {
                    appendMessage('Error: ' + response.data, 'error');
                }
            },
            error: function () {
                removeTypingIndicator();
                appendMessage('Error: Unable to send message', 'error');
            },
            complete: function () {
                removeTypingIndicator();
                submitButton.prop('disabled', false);
            },
        });
    });

    messageInput.on('keydown', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            submitButton.click();
        }
    });

    // Add the toggleChatbot() function - Ver 1.1.0
    function toggleChatbot() {
        if (chatGptChatBot.is(':visible')) {
            chatGptChatBot.hide();
            chatGptOpenButton.show();
            localStorage.setItem('chatGPTChatBotStatus', 'closed');
            // Clear the conversation when the chatbot is closed - Ver 1.2.0
            // Keep the conversation when the chatbot is closed - Ver 1.2.4
            // sessionStorage.removeItem('chatgpt_conversation');
        } else {
            chatGptChatBot.show();
            chatGptOpenButton.hide();
            localStorage.setItem('chatGPTChatBotStatus', 'open');
            loadConversation();
            scrollToBottom();
        }
    }
    // Add the toggleChatOption() function - Ver 0.0.1
    function toggleChatOption() {
        if (itemGroup.is(':visible')) {
            itemGroup.hide();
            $('.tooltip-text').html("Open options");
        } else {
            itemGroup.show();
            $('.tooltip-text').html("Close options");
        }
    }
    // Add this function to maintain the chatbot status across page refreshes and sessions - Ver 1.1.0 and updated for Ver 1.4.1
    function loadChatbotStatus() {
        const chatGPTChatBotStatus = localStorage.getItem('chatGPTChatBotStatus');
        // const chatGPTChatBotStatus = localStorage.getItem('chatgpt_start_status');

        // If the chatbot status is not set in local storage, use chatgpt_start_status
        if (chatGPTChatBotStatus === null) {
            if (chatgpt_start_status === 'closed') {
                chatGptChatBot.hide();
                chatGptOpenButton.show();
            } else {
                chatGptChatBot.show();
                chatGptOpenButton.hide();
                // Load the conversation when the chatbot is shown on page load
                loadConversation();
                scrollToBottom();
            }
        } else if (chatGPTChatBotStatus === 'closed') {
            if (chatGptChatBot.is(':visible')) {
                chatGptChatBot.hide();
                chatGptOpenButton.show();
            }
        } else if (chatGPTChatBotStatus === 'open') {
            if (chatGptChatBot.is(':hidden')) {
                chatGptChatBot.show();
                chatGptOpenButton.hide();
                loadConversation();
                scrollToBottom();
            }
        }
    }

    // Add this function to scroll to the bottom of the conversation - Ver 1.2.1
    function scrollToBottom() {
        setTimeout(() => {
            // Logging for Diagnostics - Ver 1.4.2
            if (chatgpt_diagnostics === 'On') {
                console.log("Scrolling to bottom");
                console.log("Scroll height: " + conversation[0].scrollHeight);
            }
            conversation.scrollTop(conversation[0].scrollHeight);
        }, 100);  // delay of 100 milliseconds    
    }

    // Load conversation from local storage if available - Ver 1.2.0
    function loadConversation() {
        var storedConversation = sessionStorage.getItem('chatgpt_conversation');
        if (storedConversation) {
            conversation.append(storedConversation);
            // Use setTimeout to ensure scrollToBottom is called after the conversation is rendered
            setTimeout(scrollToBottom, 0);
        } else {
            initializeChatbot();
        }
    }

    // Call the loadChatbotStatus function here - Ver 1.1.0
    loadChatbotStatus();

    // Load the conversation when the chatbot is shown on page load - Ver 1.2.0
    // Let the convesation stay persistent in session storage for increased privacy - Ver 1.4.2
    // loadConversation();

});
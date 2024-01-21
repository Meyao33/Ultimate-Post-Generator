<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://alvarooropesa.com
 * @since      1.0.0
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/public/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="upg-chat-app">
    <aside id="upg-chat-sidebar">
        <button id="upg-new-chat">New Chat</button>
        <ul id="upg-chat-list">
            <!-- Chat sessions will be listed here -->
        </ul>
    </aside>
    <main id="upg-chat-main">
        <div id="upg-chat-messages"></div>
        <input type="text" id="upg-chat-input" placeholder="Type your message..." />
        <button id="upg-send-message">You</button>
    </main>
</div>
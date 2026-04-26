<header class="chat-header">
    <div class="chat-header-left">
        <span class="chat-app-name">💬 ChatApp</span>
        <span class="chat-room-label"># global</span>
    </div>
    <div class="chat-header-right">
        <span class="chat-greeting">Hi, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <form method="POST" action="/logout" class="logout-form">
            <?= csrfInput() ?>
            <button type="submit" class="btn-logout">Sign out</button>
        </form>
    </div>
</header>

<main class="messages-area" id="messages" aria-live="polite" aria-label="Chat messages"></main>

<footer class="input-area">
    <div id="send-error" class="send-error" style="display:none;"></div>
    <div class="input-row">
        <textarea
            id="message-input"
            placeholder="Message #global… (Enter to send, Shift+Enter for new line)"
            rows="1"
            maxlength="1000"
            aria-label="Message input"
        ></textarea>
        <button id="send-btn" class="btn-send" aria-label="Send message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </div>
</footer>

<?php
$scripts   = ['/assets/js/chat.js'];
$bodyAttrs = ' data-user-id="' . (int) $user['id'] . '" data-csrf-token="' . htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') . '"';
?>

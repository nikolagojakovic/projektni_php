<header class="chat-header">
    <div class="chat-header-left">
        <a href="/chat" class="chat-logo">
            <div class="chat-logo-mark">💬</div>
            <span class="chat-logo-name">MojChat</span>
        </a>
        <span class="chat-room-badge"># globalni</span>
        <span class="online-dot" title="Aktivno"></span>
    </div>
    <div class="chat-header-right">
        <div class="chat-user-pill">
            <div class="chat-user-avatar"><?= mb_strtoupper(mb_substr($user['name'], 0, 1, 'UTF-8'), 'UTF-8') ?></div>
            <span class="chat-user-name"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <form method="POST" action="/logout" class="logout-form">
            <?= csrfInput() ?>
            <button type="submit" class="btn-logout">Odjavi se</button>
        </form>
    </div>
</header>

<main class="messages-area" id="messages" aria-live="polite" aria-label="Poruke">
    <div class="empty-state" id="empty-state">
        <div class="empty-state-icon">💬</div>
        <div class="empty-state-title">Dobrodošao u MojChat!</div>
        <div class="empty-state-sub">Budi prvi — napiši poruku.</div>
    </div>
</main>

<footer class="input-area">
    <div id="send-error" class="send-error" style="display:none;"></div>
    <div class="input-row">
        <textarea
            id="message-input"
            placeholder="Poruka u #globalni… (Enter za slanje)"
            rows="1"
            maxlength="1000"
            aria-label="Unesi poruku"
        ></textarea>
        <button id="send-btn" class="btn-send" aria-label="Pošalji poruku">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </div>
    <div class="char-counter" id="char-counter">0 / 1000</div>
</footer>

<?php
$scripts   = ['/assets/js/chat.js'];
$bodyAttrs = ' data-user-id="' . (int) $user['id'] . '" data-csrf-token="' . htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') . '"';
?>

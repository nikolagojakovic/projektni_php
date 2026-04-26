'use strict';

// State
let lastMessageId = 0;
const currentUserId = parseInt(document.body.dataset.userId, 10);

// DOM refs
const messagesArea = document.getElementById('messages');
const textarea     = document.getElementById('message-input');
const sendBtn      = document.getElementById('send-btn');
const sendError    = document.getElementById('send-error');

// Initialize: fetch all messages, then start polling
async function init() {
    await fetchMessages();
    scrollToBottom(true);
    startPolling();
    textarea.focus();
}

function startPolling() {
    setInterval(fetchMessages, 2000);
}

async function fetchMessages() {
    try {
        const res = await fetch(`/api/messages?after=${lastMessageId}`);
        if (!res.ok) return;
        const data = await res.json();
        if (data.messages && data.messages.length > 0) {
            const wasAtBottom = isAtBottom();
            data.messages.forEach(appendMessage);
            lastMessageId = data.messages[data.messages.length - 1].id;
            if (wasAtBottom) scrollToBottom();
        }
    } catch (_) {
        // Network hiccup — silently retry on next interval
    }
}

function appendMessage(msg) {
    const isOwn    = parseInt(msg.user_id, 10) === currentUserId;
    const wrapper  = document.createElement('div');
    wrapper.className = `message-wrapper ${isOwn ? 'own' : 'other'}`;

    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    let html = '';
    if (!isOwn) {
        html += `<div class="message-meta">${escapeHtml(msg.user_name)}</div>`;
    }
    html += `<div class="message-bubble">${escapeHtml(msg.content)}</div>`;
    html += `<div class="message-time">${time}</div>`;

    wrapper.innerHTML = html;
    messagesArea.appendChild(wrapper);
}

async function sendMessage() {
    const content = textarea.value.trim();
    if (!content) return;

    sendBtn.disabled  = true;
    textarea.disabled = true;
    hideSendError();

    try {
        const res = await fetch('/api/messages', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-Token':  document.body.dataset.csrfToken,
            },
            body: JSON.stringify({ content }),
        });

        if (res.ok) {
            const data = await res.json();
            textarea.value = '';
            textarea.style.height = 'auto';
            appendMessage(data.message);
            lastMessageId = data.message.id;
            scrollToBottom();
        } else if (res.status === 401) {
            window.location.href = '/login';
        } else {
            const err = await res.json().catch(() => ({}));
            showSendError(err.error || 'Failed to send message. Try again.');
        }
    } catch (_) {
        showSendError('Network error. Check your connection.');
    } finally {
        sendBtn.disabled  = false;
        textarea.disabled = false;
        textarea.focus();
    }
}

// Helpers
function isAtBottom() {
    return messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight < 80;
}

function scrollToBottom(instant = false) {
    messagesArea.scrollTo({ top: messagesArea.scrollHeight, behavior: instant ? 'instant' : 'smooth' });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showSendError(msg) {
    if (sendError) {
        sendError.textContent = msg;
        sendError.style.display = 'block';
        setTimeout(hideSendError, 4000);
    }
}

function hideSendError() {
    if (sendError) sendError.style.display = 'none';
}

// Events
sendBtn.addEventListener('click', sendMessage);

textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

textarea.addEventListener('input', () => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
});

// Kick off
init();

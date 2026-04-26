<?php

declare(strict_types=1);

requireAuth();

$user = currentUser();

$pageTitle = 'Chat — ChatApp';
$bodyClass = 'chat-page';
ob_start();
require VIEWS . '/chat.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';

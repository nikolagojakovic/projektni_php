<div class="auth-card">
    <div class="auth-logo">
        <span class="auth-logo-icon">💬</span>
        <span class="auth-logo-name">ChatApp</span>
    </div>
    <h1 class="auth-title">Verify your email</h1>
    <p class="auth-subtitle">
        We sent a 6-digit code to
        <strong><?= htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['email_warning'])): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['email_warning'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['email_warning']); ?>
    <?php endif; ?>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="/verify" novalidate>
        <?= csrfInput() ?>

        <div class="form-group">
            <label for="code">Verification Code</label>
            <input
                type="text"
                id="code"
                name="code"
                maxlength="6"
                placeholder="000000"
                autocomplete="one-time-code"
                inputmode="numeric"
                pattern="\d{6}"
                class="code-input"
                required
            >
        </div>

        <button type="submit" class="btn-primary">Verify Email</button>
    </form>

    <form method="POST" action="/verify/resend" style="margin-top: 1rem;">
        <?= csrfInput() ?>
        <p class="auth-footer" style="margin-bottom: 0.5rem;">Didn't receive it?</p>
        <button type="submit" class="btn-secondary">Resend code</button>
    </form>

    <p class="auth-footer" style="margin-top: 1.25rem;">
        <a href="/register" class="btn-link">← Use a different email</a>
    </p>
</div>

<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-mark">💬</div>
        <span class="auth-logo-name">MojChat</span>
    </div>

    <h1 class="auth-title">Potvrdi email</h1>
    <p class="auth-subtitle">
        Poslali smo kod na
        <strong style="color: var(--text);"><?= htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8') ?></strong>
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
            <label for="code">6-cifreni kod</label>
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

        <button type="submit" class="btn-primary">Potvrdi →</button>
    </form>

    <div class="auth-divider"></div>

    <form method="POST" action="/verify/resend">
        <?= csrfInput() ?>
        <p class="auth-footer" style="margin-bottom: 0.625rem;">Nisi dobio kod?</p>
        <button type="submit" class="btn-secondary">Pošalji ponovo</button>
    </form>

    <p class="auth-footer" style="margin-top: 1rem;">
        <a href="/register" class="btn-link">← Koristi drugi email</a>
    </p>
</div>

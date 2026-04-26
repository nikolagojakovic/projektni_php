<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-mark">💬</div>
        <span class="auth-logo-name">MojChat</span>
    </div>

    <h1 class="auth-title">Dobrodošao nazad</h1>
    <p class="auth-subtitle">Prijavi se na svoj nalog</p>

    <?php if ($verified): ?>
        <div class="alert alert-success">✓ Email potvrđen! Možeš se prijaviti.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= $err ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="/login" novalidate>
        <?= csrfInput() ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="255"
                value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="ti@primer.com"
                autocomplete="email"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Lozinka</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Tvoja lozinka"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">Prijavi se →</button>
    </form>

    <div class="auth-divider"></div>
    <p class="auth-footer">Nemaš nalog? <a href="/register" class="btn-link">Registruj se</a></p>
</div>

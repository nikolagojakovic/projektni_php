<div class="auth-card">
    <div class="auth-logo">
        <span class="auth-logo-icon">💬</span>
        <span class="auth-logo-name">ChatApp</span>
    </div>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your account</p>

    <?php if ($verified): ?>
        <div class="alert alert-success">Email verified! You can now sign in.</div>
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
                placeholder="you@example.com"
                autocomplete="email"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Your password"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">Sign in</button>
    </form>

    <p class="auth-footer">New here? <a href="/register" class="btn-link">Create an account</a></p>
</div>

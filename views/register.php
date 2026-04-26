<div class="auth-card">
    <div class="auth-logo">
        <span class="auth-logo-icon">💬</span>
        <span class="auth-logo-name">ChatApp</span>
    </div>
    <h1 class="auth-title">Create account</h1>
    <p class="auth-subtitle">Start chatting in seconds</p>

    <?php if (!empty($errors) && isset($errors[0])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="/register" novalidate>
        <?= csrfInput() ?>

        <div class="form-group">
            <label for="name">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                value="<?= htmlspecialchars($formData['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Your name"
                autocomplete="name"
                required
            >
            <?php if (!empty($errors['name'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

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
            <?php if (!empty($errors['email'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
            >
            <?php if (!empty($errors['password'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                placeholder="Repeat your password"
                autocomplete="new-password"
                required
            >
            <?php if (!empty($errors['confirm_password'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">Create account</button>
    </form>

    <p class="auth-footer">Already have an account? <a href="/login" class="btn-link">Sign in</a></p>
</div>

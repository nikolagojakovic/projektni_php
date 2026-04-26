<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-mark">💬</div>
        <span class="auth-logo-name">MojChat</span>
    </div>

    <h1 class="auth-title">Napravi nalog</h1>
    <p class="auth-subtitle">Pridruži se i počni da ćaskaš</p>

    <?php if (!empty($errors) && isset($errors[0])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="/register" novalidate>
        <?= csrfInput() ?>

        <div class="form-group">
            <label for="name">Ime</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                value="<?= htmlspecialchars($formData['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Tvoje ime"
                autocomplete="name"
                required
            >
            <?php if (isset($errors['name']) && $errors['name'] !== ''): ?>
                <span class="field-error"><?= htmlspecialchars((string) $errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
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
                placeholder="ti@primer.com"
                autocomplete="email"
                required
            >
            <?php if (isset($errors['email']) && $errors['email'] !== ''): ?>
                <span class="field-error"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Lozinka</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                placeholder="Najmanje 8 karaktera"
                autocomplete="new-password"
                required
            >
            <?php if (isset($errors['password']) && $errors['password'] !== ''): ?>
                <span class="field-error"><?= htmlspecialchars((string) $errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password">Potvrdi lozinku</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                placeholder="Ponovi lozinku"
                autocomplete="new-password"
                required
            >
            <?php if (isset($errors['confirm_password']) && $errors['confirm_password'] !== ''): ?>
                <span class="field-error"><?= htmlspecialchars((string) $errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">Napravi nalog →</button>
    </form>

    <div class="auth-divider"></div>
    <p class="auth-footer">Već imaš nalog? <a href="/login" class="btn-link">Prijavi se</a></p>
</div>

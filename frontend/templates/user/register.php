### 4.5. Шаблон для формы регистрации

**Файл: prognozai/frontend/templates/user/register.php**

```php
<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Регистрация</h1>
                    <p>Создайте аккаунт на PrognozAi.ru</p>
                </div>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?= $baseUrl ?>register" method="POST" class="auth-form">
                    <div class="form-group <?= isset($errors['username']) ? 'has-error' : '' ?>">
                        <label for="username">Имя пользователя</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group <?= isset($errors['confirm_password']) ? 'has-error' : '' ?>">
                        <label for="confirm_password">Подтверждение пароля</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Уже есть аккаунт? <a href="<?= $baseUrl ?>login">Войти</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
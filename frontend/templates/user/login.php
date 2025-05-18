**Файл: prognozai/frontend/templates/user/login.php**

```php
<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Вход</h1>
                    <p>Войдите в свой аккаунт</p>
                </div>
                
                <form action="<?= $baseUrl ?>login" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="login">Имя пользователя или Email</label>
                        <input type="text" id="login" name="login" class="form-control" value="<?= htmlspecialchars($oldInput['login'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Войти</button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Нет аккаунта? <a href="<?= $baseUrl ?>register">Зарегистрироваться</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
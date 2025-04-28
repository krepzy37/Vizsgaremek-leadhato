<div id="auth-container">
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Üdv, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
        <button id="logoutButton">Kilépés</button>
    <?php else: ?>
        <button onclick="document.getElementById('loginOverlay').style.display='block'">Belépés</button>
        <button onclick="document.getElementById('registerOverlay').style.display='block'">Regisztráció</button>
    <?php endif; ?>
</div>
<div id="loginOverlay" style="display: none;">
    <form>
        <input type="text" placeholder="Felhasználónév" />
        <input type="password" placeholder="Jelszó" />
        <button type="button">Bejelentkezés</button>
    </form>
</div>

<div id="registerOverlay" style="display: none;">
    <form>
        <input type="text" placeholder="Felhasználónév" />
        <input type="email" placeholder="E-mail" />
        <input type="password" placeholder="Jelszó" />
        <input type="password" placeholder="Jelszó megerősítése" />
        <button type="button">Regisztráció</button>
    </form>
</div>

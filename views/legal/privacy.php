<?php $pageTitle = t('legal.privacy_title', $lang); ?>
<div class="gf-container gf-container--narrow gf-section">
    <h1 class="gf-article__headline"><?= e(t('legal.privacy_title', $lang)) ?></h1>
    <div class="gf-prose">

    <?php if ($lang === 'en'): ?>

    <p><strong>Last updated:</strong> February 2026</p>

    <h2>1. Information We Collect</h2>
    <p>When you register on GoalFeed, we collect: your name, email address, and an encrypted password. If you sign in with Google, we receive your public profile name, email, and profile picture from Google.</p>
    <p>We also automatically collect: your preferred language, browser cookies needed for session management, and basic access logs (IP address, pages visited) maintained by our hosting provider.</p>

    <h2>2. How We Use Your Information</h2>
    <ul>
        <li>To manage your account and authenticate your sessions</li>
        <li>To display your name and initials when you post comments</li>
        <li>To remember your language preference (ES/EN)</li>
        <li>To improve the site and fix technical issues</li>
    </ul>

    <h2>3. Cookies</h2>
    <p>We use the following cookies:</p>
    <ul>
        <li><strong>gf_session</strong> — Keeps you logged in (expires after 30 days)</li>
        <li><strong>gf_lang</strong> — Stores your language preference (ES or EN)</li>
        <li><strong>gf-theme</strong> — Stores your theme preference (light/dark) via localStorage</li>
    </ul>
    <p>We do not use advertising or third-party tracking cookies.</p>

    <h2>4. Data Sharing</h2>
    <p>We do not sell, rent, or share your personal data with third parties. If you use Google sign-in, your authentication is handled directly by Google; we only store the resulting profile information.</p>

    <h2>5. Data Security</h2>
    <p>Passwords are stored using bcrypt hashing and are never stored in plain text. Sessions are managed via secure, HTTP-only cookies. All traffic is transmitted over HTTPS.</p>

    <h2>6. Your Rights</h2>
    <p>You may request deletion of your account and associated data by contacting us. Comments you have posted may be anonymized upon account deletion.</p>

    <h2>7. Third-Party Content</h2>
    <p>Articles on GoalFeed are generated from publicly available RSS feeds. Original sources are cited in every article. We use the Tailwind CSS CDN, which may set its own cache headers.</p>

    <h2>8. Contact</h2>
    <p>For any privacy-related questions, contact us at: <strong>admin@goal-feed.com</strong></p>

    <?php else: ?>

    <p><strong>Última actualización:</strong> Febrero 2026</p>

    <h2>1. Información que recopilamos</h2>
    <p>Al registrarte en GoalFeed, recopilamos: tu nombre, correo electrónico y una contraseña cifrada. Si inicias sesión con Google, recibimos tu nombre de perfil público, correo electrónico y foto de perfil de Google.</p>
    <p>También recopilamos automáticamente: tu idioma preferido, las cookies del navegador necesarias para gestionar la sesión, y registros básicos de acceso (dirección IP, páginas visitadas) mantenidos por nuestro proveedor de hosting.</p>

    <h2>2. Cómo usamos tu información</h2>
    <ul>
        <li>Para gestionar tu cuenta y autenticar tus sesiones</li>
        <li>Para mostrar tu nombre e iniciales cuando publiques comentarios</li>
        <li>Para recordar tu preferencia de idioma (ES/EN)</li>
        <li>Para mejorar el sitio y resolver problemas técnicos</li>
    </ul>

    <h2>3. Cookies</h2>
    <p>Utilizamos las siguientes cookies:</p>
    <ul>
        <li><strong>gf_session</strong> — Te mantiene conectado (expira a los 30 días)</li>
        <li><strong>gf_lang</strong> — Almacena tu preferencia de idioma (ES o EN)</li>
        <li><strong>gf-theme</strong> — Almacena tu preferencia de tema (claro/oscuro) via localStorage</li>
    </ul>
    <p>No utilizamos cookies de publicidad ni de rastreo de terceros.</p>

    <h2>4. Compartición de datos</h2>
    <p>No vendemos, alquilamos ni compartimos tus datos personales con terceros. Si usas el inicio de sesión con Google, la autenticación se gestiona directamente por Google; solo almacenamos la información de perfil resultante.</p>

    <h2>5. Seguridad de los datos</h2>
    <p>Las contraseñas se almacenan con cifrado bcrypt y nunca se guardan en texto plano. Las sesiones se gestionan mediante cookies seguras HTTP-only. Todo el tráfico se transmite por HTTPS.</p>

    <h2>6. Tus derechos</h2>
    <p>Puedes solicitar la eliminación de tu cuenta y los datos asociados contactándonos. Los comentarios que hayas publicado podrán ser anonimizados tras la eliminación de la cuenta.</p>

    <h2>7. Contenido de terceros</h2>
    <p>Los artículos de GoalFeed se generan a partir de fuentes RSS públicas. Las fuentes originales se citan en cada artículo. Utilizamos el CDN de Tailwind CSS, que puede establecer sus propias cabeceras de caché.</p>

    <h2>8. Contacto</h2>
    <p>Para cualquier consulta relacionada con la privacidad, contáctanos en: <strong>admin@goal-feed.com</strong></p>

    <?php endif; ?>

    </div>
</div>

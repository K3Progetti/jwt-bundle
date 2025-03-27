# JwtBundle

Bundle Symfony per la gestione avanzata dei token JWT, con supporto a:

- Login e generazione token JWT
- Refresh token
- Logout e invalidazione dei token
- Comandi da terminale per la pulizia dei token scaduti

---

## ✅ Requisiti

- PHP >= 8.2
- Symfony 7.0

---

## 🚀 Installazione

```bash
composer require k3progetti/jwt-bundle
```

---

## ⚙️ Configurazione

Aggiungi il bundle al tuo `config/bundles.php` se non viene registrato automaticamente:

```php
return [
    // ...
    JwtBundle\JwtBundle::class => ['all' => true],
];
```

---

## 🧭 Struttura del Progetto

```
JwtBundle/
├── JwtBundle.php
├── bin/
│   └── register-mercure-bundle.php
├── src/
│   ├── Command/
│   │   ├── RemoveJwtRefreshTokenExpired.php
│   │   ├── RemoveJwtTokenExpired.php
│   │   └── RemoveJwtTokenUser.php
│   ├── Controller/
│   │   └── AuthController.php
│   ├── DependencyInjection/
│   │   ├── Configuration.php
│   │   └── JwtExtension.php
│   ├── Entity/
│   │   ├── JwtToken.php
│   │   └── JwtRefreshToken.php
│   ├── Event/
│   │   └── JwtUserLoggedOutEvent.php
│   ├── Helper/
│   │   └── AuthHelper.php
│   ├── Repository/
│   │   ├── JwtTokenRepository.php
│   │   └── JwtRefreshTokenRepository.php
│   ├── Security/
│   │   ├── JwtAuthenticator.php
│   │   └── Handler/
│   │       ├── LoginHandler.php
│   │       ├── LogoutHandler.php
│   │       └── RefreshTokenHandler.php
│   └── Service/
│       ├── JwtService.php
│       └── JwtRefreshService.php
```

---

## 🔧 Comandi Console Disponibili

```bash
bin/console jwt:remove-jwt-refresh-token-expired
bin/console jwt:remove-jwt-token-expired
bin/console jwt:remove-jwt-token-user
```

---

## 🤝 Contributing

Sono aperto a qualsiasi richiesta

---
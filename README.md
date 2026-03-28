# JwtBundle

Bundle Symfony per la gestione avanzata dei token JWT, con supporto a:

- Login e generazione token JWT
- Autenticazione a due fattori (2FA)
- Refresh token
- Logout e invalidazione dei token
- Payload JWT personalizzabile tramite interfaccia
- Comandi da terminale per la pulizia dei token scaduti

---

## Requisiti

- PHP >= 8.2
- Symfony ~8.0

---

## Installazione

```bash
composer require k3progetti/jwt-bundle
```

```bash
php composer.phar install --ignore-platform-req=ext-redis
```

---

## Configurazione

### Registrazione del bundle

Aggiungi il bundle al tuo `config/bundles.php` se non viene registrato automaticamente:

```php
return [
    // ...
    K3Progetti\JwtBundle\JwtBundle::class => ['all' => true],
];
```

### Configurazione JWT (`config/packages/jwt.yaml`)

Copia il file di esempio `resources/config/packages/jwt.yaml.dist` e adattalo:

```yaml
jwt:
  secret_key: '%env(JWT_PASSPHRASE)%'
  token_ttl: 3600            # Scadenza del token in secondi (default: 1 ora)
  refresh_token_ttl: 2592000 # Scadenza del refresh token in secondi (default: 30 giorni)
  algorithm: 'HS256'         # Algoritmo di firma (default: HS256)
  time_zone: 'Europe/Rome'   # Fuso orario (default: Europe/Rome)
  2fa_expired_code: 10       # Validità codice 2FA in minuti (default: 10)
  user_class: 'App\Entity\User'
  user_repository_class: 'App\Repository\UserRepository'
  # mailer_class: 'App\Service\External\PostmarkService'  # Richiesto solo se si usa il 2FA
```

Aggiungi nel tuo `.env`:

```
JWT_PASSPHRASE=la_tua_chiave_segreta
```

### Configurazione del firewall (`config/packages/security.yaml`)

```yaml
firewalls:
    api:
        pattern: ^/api/
        stateless: true
        custom_authenticator: K3Progetti\JwtBundle\Security\JwtAuthenticator
```

---

## Implementazione delle interfacce

### Entità utente — `JwtUserInterface`

La tua entità `User` deve implementare `K3Progetti\JwtBundle\Security\JwtUserInterface`:

```php
use K3Progetti\JwtBundle\Security\JwtUserInterface;

class User implements JwtUserInterface
{
    public function getId(): mixed { ... }
    public function getUsername(): string { ... }
    public function getName(): string { ... }
    public function getSurname(): string { ... }
    public function isActive(): bool { ... }

    // Campi richiesti per il 2FA
    public function isTwoFactorAuth(): bool { ... }
    public function getTwoFactorAuthCode(): ?string { ... }
    public function setTwoFactorAuthCode(?string $code): static { ... }
    public function setTwoFactorAuthCodeExpired(\DateTimeInterface $dt): static { ... }
}
```

### Repository utente — `JwtUserRepositoryInterface`

Il tuo `UserRepository` deve implementare `K3Progetti\JwtBundle\Repository\JwtUserRepositoryInterface`:

```php
use K3Progetti\JwtBundle\Repository\JwtUserRepositoryInterface;
use K3Progetti\JwtBundle\Security\JwtUserInterface;

class UserRepository implements JwtUserRepositoryInterface
{
    public function findOneBy(array $criteria, ?array $orderBy = null): ?JwtUserInterface { ... }
    public function save(JwtUserInterface $user): void { ... }
}
```

### Mailer 2FA — `TwoFactorMailerInterface` _(opzionale)_

Se vuoi abilitare il 2FA, implementa `K3Progetti\JwtBundle\Mailer\TwoFactorMailerInterface` e registra la classe in `mailer_class`:

```php
use K3Progetti\JwtBundle\Mailer\TwoFactorMailerInterface;
use K3Progetti\JwtBundle\Security\JwtUserInterface;

class PostmarkService implements TwoFactorMailerInterface
{
    public function sendTwoFactorCode(JwtUserInterface $user, string $code): void { ... }
}
```

### Payload personalizzato — `JwtPayloadInterface` _(opzionale)_

Per aggiungere dati custom al token JWT, implementa `K3Progetti\JwtBundle\Service\JwtPayloadInterface` e taggala come servizio:

```php
use K3Progetti\JwtBundle\Service\JwtPayloadInterface;
use K3Progetti\JwtBundle\Security\JwtUserInterface;

class MyPayloadModifier implements JwtPayloadInterface
{
    // Aggiunge dati prima della costruzione del payload base
    public function onBeforePayload(JwtUserInterface $user): array { ... }

    // Modifica il payload dopo la costruzione base
    public function onAfterPayload(array $payload, JwtUserInterface $user): array { ... }

    // Sovrascrive completamente il payload (restituire null per non sovrascrivere)
    public function overridePayload(JwtUserInterface $user): ?array { ... }
}
```

---

## Migrazioni

Il bundle include due entità: `JwtToken` e `JwtRefreshToken`.
Dopo aver installato il bundle, **genera e applica le migrazioni**:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

---

## Endpoint disponibili

| Metodo | URL | Descrizione |
|--------|-----|-------------|
| `POST` | `/login_check` | Login standard |
| `POST` | `/login_check_2fa` | Login con codice 2FA |
| `POST` | `/token/refresh` | Rinnovo del token tramite refresh token |
| `GET` | `/api/logout` | Logout e invalidazione del token |

### Esempio login

```json
POST /login_check
{
  "username": "utente@example.com",
  "password": "password"
}
```

Risposta:
```json
{
  "token": "eyJ...",
  "refresh_token": "abc123..."
}
```

### Esempio 2FA

Se l'utente ha il 2FA abilitato, il primo `/login_check` invia il codice via email. Il client deve poi completare il login su `/login_check_2fa`:

```json
POST /login_check_2fa
{
  "username": "utente@example.com",
  "password": "password",
  "code": "123456"
}
```

---

## Comandi Console

```bash
bin/console jwt:remove-jwt-refresh-token-expired   # Rimuove i refresh token scaduti
bin/console jwt:remove-jwt-token-expired           # Rimuove i token JWT scaduti
bin/console jwt:remove-jwt-token-user              # Rimuove i token di un utente specifico
```

---

## Struttura del Progetto

```
JwtBundle/
├── JwtBundle.php
├── resources/
│   └── config/
│       └── packages/
│           └── jwt.yaml.dist
└── src/
    ├── Command/
    │   ├── RemoveJwtRefreshTokenExpired.php
    │   ├── RemoveJwtTokenExpired.php
    │   └── RemoveJwtTokenUser.php
    ├── Controller/
    │   └── AuthController.php
    ├── DependencyInjection/
    │   ├── Configuration.php
    │   └── JwtExtension.php
    ├── Entity/
    │   ├── JwtToken.php
    │   └── JwtRefreshToken.php
    ├── EventListener/
    │   └── ExceptionListener.php
    ├── Exception/
    │   └── JwtAuthorizationException.php
    ├── Helper/
    │   └── AuthHelper.php
    ├── Http/
    │   └── Result.php
    ├── Mailer/
    │   └── TwoFactorMailerInterface.php
    ├── Repository/
    │   ├── JwtRefreshTokenRepository.php
    │   ├── JwtTokenRepository.php
    │   └── JwtUserRepositoryInterface.php
    ├── Security/
    │   ├── JwtAuthenticator.php
    │   ├── JwtUserInterface.php
    │   └── Handler/
    │       ├── LoginHandler.php
    │       ├── LogoutHandler.php
    │       └── RefreshTokenHandler.php
    └── Service/
        ├── JwtPayloadInterface.php
        ├── JwtRefreshService.php
        └── JwtService.php
```

---

## Contributi

Sono aperto a qualsiasi confronto.
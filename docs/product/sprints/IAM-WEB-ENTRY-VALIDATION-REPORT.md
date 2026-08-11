# IAM Web Entry — Validation Report

## Campagnes terminales

| Gate | Résultat | Preuve |
|---|---|---|
| Feature ciblée | PASS | 12 tests, 60 assertions |
| Architecture ciblée | PASS | 7 tests, 146 assertions |
| PHPStan ciblé | PASS | 0 erreur |
| Pint ciblé | PASS | fichiers du sprint conformes |
| Vite | PASS | build production terminal |
| git diff --check | PASS | aucune erreur whitespace |

Le Pint global, non requis pour ce périmètre ciblé, signale deux fichiers historiques hors sprint : `bootstrap/providers.php` et `tests/PostgreSQL/MediaIngestionRuntime/PostgreSqlMediaIngestionRuntimeTest.php`. Ils n’ont pas été modifiés pour ce chantier.

## Preuve navigateur HTTPS

| Contrôle | Résultat |
|---|---|
| Formulaire réel + CSRF | PASS |
| Idempotency-Key par requête | PASS |
| Login via Runtime IAM réel | PASS |
| Cookie sécurisé accepté | PASS, démontré par accès puis reload authentifié |
| Workspace après reload | PASS |
| Logout réel | PASS |
| Session refusée après logout | PASS, `authentication_required` |
| Console navigateur | PASS, 0 entrée |
| Desktop 1440×900 | PASS |
| Tablette 768×1024 | PASS |
| Mobile 390×844 | PASS |

## Intégrité

Aucun fichier IAM Foundation, Runtime, Cookie, HTTPS, Property, Media, Search ou Projection n’a été modifié par ce sprint. Aucun staging, commit ou tag.

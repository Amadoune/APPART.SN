# Phase 5.1I — Endpoint Catalog

Préfixe unique : `/api/identity-access`.

| Méthode | Endpoint | Autorité |
|---|---|---|
| POST | `/login` | public + limiter login |
| POST | `/password-recovery` | public + limiter recovery |
| POST | `/password-recovery/complete` | public + limiter recovery |
| POST | `/logout` | session courante |
| POST | `/sessions/renew` | session courante |
| GET | `/sessions` | session courante |
| GET/PATCH | `/profile` | compte courant |
| POST | `/profile/contact-changes` | compte courant |
| POST | `/profile/contact-changes/verify` | compte courant |
| POST | `/closure` | compte courant |
| POST | `/closure/confirm` | compte courant |
| POST | `/reopen` | compte courant |

Aucun endpoint authentifié n'accepte `AccountId` dans son chemin ou son body. Le compte provient exclusivement de la session inspectée.

Les mutations exigent un header `Idempotency-Key` UUID. Les champs inconnus sont refusés.

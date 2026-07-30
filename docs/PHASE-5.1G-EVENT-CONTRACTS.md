# Phase 5.1G — Event Contracts V1

## Statut

Jalon ouvert, implémenté et proposé à la certification. Le GO appartient à l'autorité.

Les contrats V1 sont propriétaires d'Identity & Access et distincts des événements Account Status gelés.

| Owner | Type |
|---|---|
| IdentityAccess.Profile | `identity.profile.name_changed` |
| IdentityAccess.Profile | `identity.profile.email_changed` |
| IdentityAccess.Profile | `identity.profile.phone_changed` |
| IdentityAccess.Closure | `identity.account.closure_requested` |
| IdentityAccess.Closure | `identity.account.closed` |
| IdentityAccess.Closure | `identity.account.reopened` |

L'enveloppe contient uniquement `eventId`, type, version 1, owner, `AccountId`, version d'agrégat, dates, correlation, causation et checksum. L'identité et le checksum sont dérivés canoniquement.

Nom, email, téléphone, fingerprints, credentials, tokens, session IDs, IP, device et reason libre sont interdits.

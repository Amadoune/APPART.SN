# Phase 5.2C — Persistence Foundation

## Statut proposé

**IMPLÉMENTATION CONFORME — NO GO PROBATOIRE EN ATTENTE DE POSTGRESQL.**

## Owners persistants

| Owner | Tables |
|---|---|
| ProfessionalPublicProfile | `public_profiles`, `public_profile_revisions`, `public_profile_intents` |
| ProfessionalVerification | `verifications`, `verification_decisions`, `verification_intents` |
| ProfessionalPublicPortfolio | `public_portfolios`, `public_portfolio_intents` |

Toutes les tables appartiennent au schéma `professional_profile`. Il n’existe
aucune FK cross-domain ni cascade.

## Garanties

- états Application indépendants de PDO et Laravel ;
- trois ports propriétaires sans dépendance cross-owner ;
- mapper déterministe et bijectif ;
- révisions Profile et décisions Verification append-only ;
- intents permanents par `(professionalId, intentId)` ;
- résultats fermés Applied, AlreadyApplied, DivergentIntent, VersionConflict,
  CheckpointRegression et Rejected ;
- optimistic locking ;
- advisory lock transactionnel par ProfessionalId ;
- checkpoint Portfolio monotone ;
- transaction locale et participation sûre à une transaction englobante ;
- aucune lecture F-05 et aucune mutation Listing.

## Frontières préservées

Migrations 058–060, F-05, F-17, F-19 et F-21 restent inchangés. Aucun Runtime,
HTTP, Event, Delivery, Outbox, provider, route ou consumer n’est introduit.

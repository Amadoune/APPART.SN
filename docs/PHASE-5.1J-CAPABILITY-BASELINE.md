# Phase 5.1J — Identity & Access Capability Baseline

## Capacités incluses

- Authentication et Attempts/Lockout ;
- Sessions V1, sans remember-me ;
- Password Recovery ;
- User Profile et Profile Revisions ;
- Identity Claims et Pending Contact Changes ;
- Account Closure, Closed et Reopened ;
- Account Availability Status + Closure ;
- Seed/cutover Historical vers Profile Claims ;
- orchestration atomique multi-owner ;
- six événements IAM V1 ;
- transport, routing, delivery, retry et replay ;
- Outbox IAM propriétaire ;
- convergence concurrente certifiée sur les unicités `message_id` et
  `event_id`, avec comparaison canonique comme autorité de résultat ;
- treize opérations HTTP sécurisées.

## Baseline technique

- migrations additives 044 à 054 ;
- schéma propriétaire `identity_access_completion` ;
- providers IAM 5.1E, 5.1F, 5.1H et 5.1I ;
- contrats et résultats fermés 5.1B ;
- normalisation `iam-profile-v1` ;
- PostgreSQL 18.x ;
- PHP 8.5 et Laravel 13.

## Exclusions permanentes de la baseline

- anonymisation ;
- destruction physique ;
- crypto-erasure ;
- suppression de Historical Account ou Snapshot V1 ;
- remember-me ;
- modification d'Account Status V1 ou des migrations 041–043.

Ces exclusions exigent un amendement versionné préalable. L'effacement relève
spécifiquement de `A-5.1-IAM-ERASURE-01`.

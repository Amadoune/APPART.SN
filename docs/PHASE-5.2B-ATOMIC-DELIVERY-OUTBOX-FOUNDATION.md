# Phase 5.2B — Atomic Delivery / Outbox Integration Foundation

## Statut

GO CERTIFIÉE — FERMÉE.

## Ownership

`MediaIngestion.Asset` demeure Event Owner. L’Outbox Media Ingestion est
l’unique propriétaire de `media_ingestion.event_outbox_messages` et
`media_ingestion.event_outbox_deliveries`. Elle est indépendante de F-15 et des
Outbox IAM/Account Status gelées.

## Garanties

- migration 060 strictement additive, sans FK et sans cascade ;
- `message_id varchar(83)`, dimension contractuelle exacte du préfixe
  `media-ingestion-v1-` et du SHA-256 hexadécimal ;
- résultats fermés `Applied`, `AlreadyApplied`, `DivergentMessage` ;
- convergence sur `message_id` et `event_id` via `ON CONFLICT DO NOTHING`, puis
  comparaison canonique ;
- message et destinations persistés dans une transaction unique ;
- participation à une transaction englobante par savepoint ;
- claims concurrents `FOR UPDATE SKIP LOCKED` ;
- lease de 30 secondes, reprise des claims expirés, retry différé et
  quarantaine terminale ;
- diagnostics techniques fermés, sans PII ni secret ;
- aucune modification des contrats Event V1, de Persistence 058–059, Runtime,
  HTTP ou d’une capacité gelée.

Application ne dépend ni de PDO ni de SQL. Le contrat transactionnel appartient
à l’Application Media ; les détails PostgreSQL restent confinés à
l’Infrastructure propriétaire.

La preuve PostgreSQL ciblée est terminale : 4 tests, 24 assertions, PASS. La
migration 060 est certifiée mais non gelée avant le GO FINAL 5.2B. La réserve
globale Reservation Lifecycle est indépendante et ne rouvre pas cette
Foundation.

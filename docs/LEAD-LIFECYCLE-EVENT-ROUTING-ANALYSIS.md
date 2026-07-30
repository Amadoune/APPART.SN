# Lead Lifecycle Event Routing Analysis

## Architecture

`DurableLeadLifecycleEventRouter` vérifie la cohérence de l'enveloppe 4.4F puis délègue exclusivement à `LeadLifecycleInboxStore`. Le repository PostgreSQL est l'unique implémentation durable. Le routeur ne lit ni workflow, ni catalogue d'éligibilité et n'interprète aucun payload métier.

Le résultat interne distingue `Stored`, `AlreadyStored`, `Unavailable`, `RetryableFailure` et `Rejected`. Le routeur applique exhaustivement la politique 4.4F : seuls `Stored` et `AlreadyStored` deviennent `Routed`.

## Intégrité

L'Inbox conserve `canonicalEvent` et l'enveloppe complète sous forme de texte. Leur ordre et leurs octets restent identiques. `messageId`, `eventId`, checksum, type et version sont conservés séparément uniquement pour vérifier l'intégrité et permettre une reprise déterministe.

## Frontière

La fondation ne crée aucun Consumer, Worker, Outbox, producteur, intégration atomique, HTTP ou Projection. Aucun binding Runtime n'est introduit dans ce sprint.

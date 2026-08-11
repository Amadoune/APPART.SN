# Certification Note

## Résultat du Discovery

La Foundation minimale est complètement identifiée sans modifier les capacités certifiées :

- owner applicatif unique `PublicationReview` ;
- queue owner-scoped distincte de Moderation Reports ;
- commandes explicites BeginReview et ApproveAndPublish ;
- autorité IAM dédiée, sans réemploi implicite de `moderator` ;
- activation idempotente de Public Projection après publication confirmée ;
- Search et Public Listing maintenus hors décision.

## Conditions avant implémentation

La décision d’autorité ouvrant une future Contracts Foundation devra confirmer le rôle et les capacités IAM proposés. Aucune UI P08, Persistence, migration, Provider, route ou binding n’est ouvert par ce Discovery.

## Verdict

**GO PROPOSÉ — APPART.SN PUBLICATION REVIEW FOUNDATION v1 — DISCOVERY 01**

---

## Publication Review Queue Authority 01

La file est entièrement qualifiée sans dépendre du consumer Projection :

- owner : `PublicationReview` ;
- source : événements Lifecycle `ListingSubmitted` / `ListingResubmitted` ;
- transport : delivery indépendante ;
- consumer logique : unique, partageable par plusieurs workers ;
- identité : dérivée de l'événement source ;
- persistance : owner-scoped additive ;
- claim : atomique, versionné et idempotent ;
- replay : `AlreadyApplied` pour identité/checksum identiques, conflit explicite sinon ;
- historique : aucun backfill implicite ;
- Projection : consumer, claims et retries strictement indépendants.

Aucun code, contrat PHP, Provider, binding, test ou migration n'est créé. F1 et P08 restent fermés.

**GO PROPOSÉ — APPART.SN PUBLICATION REVIEW FOUNDATION v1 — PUBLICATION REVIEW QUEUE AUTHORITY 01**

# Phase 4.7 — Administrative Action Lifecycle Outbox Compatibility Analysis

## Objet

Le Sprint 4.7H étend exclusivement l'infrastructure Outbox générique aux quatre
événements `AdministrativeActionLifecycle` certifiés en 4.7E. L'owner
`AdministrationAudit` et son schéma `administration_audit` sont ceux certifiés
en 4.7H-R1.

## Chaîne de compatibilité

```text
AdministrativeActionLifecycleEvent 4.7E
→ AdministrativeActionLifecycleDeliveryPayload 4.7F
→ PublicProjectionDeliveryMessage
→ Writer générique
→ Outbox administration_audit
→ Reader générique
→ restauration du payload Delivery
→ AdministrativeActionLifecycleDeliveryConsumer
→ routeur 4.7G
→ politique de consommation 4.7G-R1
```

## Extensions autorisées

- quatre entrées dans le catalogue Delivery existant ;
- une branche de restauration dans le mapper PostgreSQL existant ;
- un Consumer de transport sans décision métier ;
- quatre inscriptions type/version dans le registre Worker générique.

Le Writer, le Reader, les tables Outbox et la migration 037 restent inchangés.

## Invariants

- `canonicalEvent` est restauré byte-for-byte ;
- `eventId` métier et `messageId` technique restent distincts ;
- owner, agrégat, identité, version causale et index sont vérifiés avant routage ;
- le Consumer appelle exactement une fois le routeur puis la politique ;
- aucune transition, décision ou donnée historique n'est reconstruite ;
- aucune capacité Runtime Health n'est ajoutée.

## Frontières

Le sprint ne produit aucun événement, n'intègre pas le journal Lifecycle à
l'Outbox et n'ajoute aucune responsabilité HTTP. L'atomicité entre transition
et Outbox appartient exclusivement au Sprint 4.7I.

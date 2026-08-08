# Phase 5.4A — Lead Ingress & Contact Delivery — Contracts Foundation

## Statut

Contracts Foundation ouverte. Aucune implémentation ni Foundation suivante
n'est ouverte.

## Autorité contractuelle

`ContactsLeads` est l'owner unique de Lead Ingress, de son identité, de son
accusé, de son futur journal d'intents et de sa future persistence. Les
contrats résident donc dans :

```text
Appart\Modules\ContactsLeads\Application\LeadIngressContracts
```

Cette enclave Application owner-scoped ne consomme ni l'Aggregate Lead, ni son
lifecycle historique, ni ses stores. La Foundation ne consomme ni ne
recompose les trois décisions owner certifiées :

- `ListingContactabilityReaderV1` ;
- `ListingContactPrincipalReaderV1` ;
- `ProfessionalLeadRecipientReaderV1`.

Ces décisions restent indépendantes et seront composées uniquement dans une
future orchestration explicitement autorisée.

## Port de commande

```text
LeadIngressCommandPortV1::submit(
    SubmitLeadIngressV1
) -> LeadIngressSubmissionResultV1
```

La commande transporte :

- un intent id ;
- un checksum ;
- un ListingId opaque ;
- des références opaques requester, contact et message ;
- un instant d'observation explicite ;
- un instant d'occurrence explicite.

Aucune coordonnée ou contenu libre n'est exposé directement.

## Port de Query

```text
LeadIngressQueryPortV1::readOwnReceipt(
    ReadOwnLeadIngressReceiptV1
) -> LeadIngressReadResultV1
```

La Query est privée et auto-scopable ultérieurement grâce à une référence
requester opaque. Elle n'expose qu'un accusé minimal.

## Value Objects

- `LeadIngressId` ;
- `LeadIngressIntentId` ;
- `LeadIngressObservedAt` ;
- `LeadIngressOccurredAt`.

Les identifiants sont canoniques. Les instants sont readonly, normalisés UTC
et représentés par `Y-m-dTH:i:s.uZ`. Aucune horloge implicite n'est admise.

## Invariants

- un intent et son checksum forment l'identité d'idempotence contractuelle ;
- seul `Accepted` ou `AlreadyAccepted` expose un `LeadIngressId` ;
- seul `Found` expose un accusé ;
- les décisions owner externes ne sont jamais reconstruites ;
- aucune transaction cross-domain n'est définie ;
- aucune donnée interne ou diagnostic ne traverse un résultat ;
- les références sensibles restent opaques ;
- toutes les défaillances sont représentées par un résultat fermé.

## Hors périmètre

- implémentation et orchestration ;
- persistence, migration et transaction ;
- Runtime, Provider et binding ;
- HTTP, Event, Delivery et Outbox ;
- retry, replay, monitoring et quarantaine ;
- consommation de l'Aggregate, du lifecycle ou de la persistence ContactsLeads
  historiques ;
- 5.4B et 5.4C.

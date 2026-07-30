# Phase 5.2A — Listing Publication Handoff Contract

## Audit de la réserve

Le dépôt contient `CreateDraft`, classe Application concrète qui dépend de
`ListingRegistry`, `PropertyCatalog` et `ListingTransitionPolicy`. Elle retourne
un Aggregate `Listing` et ne définit ni résultat fermé, ni intent idempotent,
ni port public versionné.

Conclusion : **ce use case n'est pas un contrat public consommable par 5.2A**.
L'appeler directement ou injecter `ListingRegistry` contournerait la frontière
gelée F-01.

## Décision normative

Un amendement versionné est requis avant implémentation :

```text
A-5.2A-LISTING-CREATION-BOUNDARY-01
→ IDENTIFIÉ
→ NON OUVERT
→ BLOQUANT AVANT IMPLÉMENTATION
```

L'amendement devra exposer, sans changer la sémantique lifecycle :

- `CreateListingDraftV1` avec intentId déterministe ;
- résultats `Applied`, `AlreadyApplied`, `DivergentIntent`,
  `PropertyUnavailable`, `ListingIdConflict`, `DependencyUnavailable` ;
- association immuable ListingId–PropertyId ;
- transaction et optimistic locking existants préservés ;
- aucun changement du catalogue Listing Publication V1.

## Soumission

Après création, `RequestListingSubmission` appelle exclusivement le
`ListingPublicationHandoffPortV1` public. Il transmet ListingId, actor,
evidenceChecksum, intentId et instant ; aucun contenu privé.

Tant que l'amendement n'est pas GO CERTIFIÉ, aucun adapter, binding ou
orchestrateur de création Listing 5.2A ne peut être implémenté.

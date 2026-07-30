# Lead Eligibility Contract Reconciliation Decision

## Statut

Décision retenue : **stratégie 2 — réutilisation des contrats existants**.

Les ports `ContactsLeads\Application\Contract\ListingCatalog` et `AdvertiserCatalog`, leurs évidences Domain et `LeadEligibilityProof` sont conservés sans amendement ni dépréciation.

## Architecture unique

- les seules sources autorisées sont les deux ports ContactsLeads existants ;
- les adaptateurs futurs retournent exclusivement `ListingContactEvidence` et `AdvertiserEligibilityEvidence` ;
- aucun Aggregate, Repository, modèle Laravel ou Entity ne traverse ces ports ;
- la combinaison des évidences reste exclusivement `LeadEligibilityProof::fromEvidence()` ;
- aucune seconde famille `ListingEligibilityProof` / `AdvertiserEligibilityProof` / `LeadEligibilityProof` n'est créée ;
- les ports homonymes d'autres modules restent privés à leur bounded context.

## Reformulation obligatoire de 4.4C-S1

Le Sprint 4.4C-S1 créera uniquement les adaptateurs de lecture de production des deux ports existants, leurs bindings paresseux et les deux capacités Runtime Health. Il ne créera aucun nouveau contrat de preuve et ne modifiera aucun consommateur historique.

La construction de `LeadEligibilityProof` demeure hors de 4.4C-S1. Elle sera coordonnée par l'orchestration 4.4D en réutilisant la règle Domain existante.

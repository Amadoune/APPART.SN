# Phase 5.4A — Lead Ingress Ownership Decision

## Décision

`ContactsLeads` est l'owner unique de Lead Ingress.

Il possède :

- `LeadIngressId` ;
- `LeadIngressIntentId` ;
- `SubmitLeadIngressV1` ;
- `ReadOwnLeadIngressReceiptV1` ;
- `LeadIngressReceiptV1` ;
- le futur journal d'idempotence ;
- la future persistence owner-locale ;
- l'accusé de soumission.

## Analyse des candidats

### ContactsLeads

Le Blueprint 5.4 lui attribue Lead, l'intention de contact et la delivery
propriétaire. Le module possède déjà le lifecycle historique Lead. Héberger les
nouveaux contrats dans une enclave Application dédiée du même owner évite une
nouvelle autorité tout en empêchant leur couplage au lifecycle existant.

Décision : retenu.

### Capacité LeadIngress autonome

Une nouvelle capacité autonome posséderait les mêmes identités et la même
intention que ContactsLeads. Elle introduirait un owner artificiel, une
persistence concurrente et un futur handoff entre deux owners pour un seul
parcours métier.

Décision : rejetée.

### app/Application global

`app/Application` est un emplacement technique, pas une autorité métier. Il ne
peut posséder ni identités, ni journal d'intents, ni persistence.

Décision : rejeté.

### Autres candidats

ListingLifecycle, Professional et IdentityAccess possèdent uniquement leurs
décisions publiques respectives. Runtime, HTTP et projections ne sont jamais
des owners métier.

Décision : rejetés.

## Emplacement normatif

```text
src/Modules/ContactsLeads/Application/LeadIngressContracts
```

L'emplacement reflète l'owner tout en isolant les contrats du Domain, de
l'Infrastructure et du lifecycle historique.

## Frontières

L'owner ContactsLeads ne peut pas reconstruire :

- la contactabilité Listing ;
- le principal Listing ;
- l'éligibilité Professional.

Ces décisions restent portées par leurs trois frontières certifiées.

## Consentement et anti-abus

`A-5.4A-CONSENT-AND-ABUSE-BOUNDARY-01` reste
`IDENTIFIÉ — NON OUVERT`. Aucun contrat, champ, résultat ou erreur n'en anticipe
la future décision.

## Garanties

- aucun nouvel Aggregate ;
- aucune duplication du lifecycle Lead ;
- aucune écriture cross-domain ;
- aucune dépendance circulaire ;
- aucune persistence ou implémentation ;
- aucune consommation du lifecycle historique ContactsLeads.

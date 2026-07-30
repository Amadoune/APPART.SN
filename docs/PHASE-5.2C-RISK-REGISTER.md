# Phase 5.2C — Risk Register

| Risque | Criticité | Traitement Discovery |
|---|---:|---|
| duplication de l’Aggregate Professional | critique | réutilisation obligatoire de l’Aggregate historique |
| confusion Profile IAM / profil professionnel public | critique | owners et données séparés |
| contournement de F-05 pour lire Active/Suspended | critique | amendement read-boundary identifié |
| mandat professionnel assimilé à délégation Listing | élevée | droits séparés, handoff par contrat F-19 |
| portefeuille devenant owner des Listings | élevée | projection read-only par références |
| exposition de preuves de vérification | critique | références opaques et projection minimale |
| badge visible après expiration/révocation | élevée | availability fail-closed et checkpoints |
| établissement retiré avec mandat actif | élevée | invariant historique conservé |
| Account fermé mais accès conservé | critique | composition F-17 obligatoire |
| FK/transaction cross-domain | critique | interdites par architecture |
| événement contenant PII ou document | critique | catalogue futur minimal, revue confidentialité |
| double autorité entre événements historiques et futurs | élevée | catalogue/ownership à certifier avant classes Event |
| divergence normalisation registration number | élevée | aucune nouvelle normalisation sans audit |
| dépendance au stockage fournisseur | moyenne | ports documentaires futurs, aucun SDK au domaine |
| réserve globale Reservation Lifecycle | faible pour 5.2C | hors périmètre, aucune modification |

## Risques bloquants avant implémentation

1. absence de frontière publique read-only F-05 ;
2. absence de persistence de production pour `ProfessionalRegistry` ;
3. qualification du contrat public d’attribution Listing pour Portfolio ;
4. politique de conservation des preuves de vérification ;
5. séparation exacte entre contacts publics Professional et claims IAM.

Ces sujets doivent être fermés par Contracts Foundation ou amendement
versionné avant toute implémentation.

## Hors périmètre enregistré

- lifecycle Professional Status ;
- KYC financier et paiement ;
- modération/enforcement ;
- erasure/anonymisation ;
- création et publication de Listing ;
- ingestion de médias ;
- gestion des sessions et credentials ;
- scoring, ranking ou publicité sponsorisée.

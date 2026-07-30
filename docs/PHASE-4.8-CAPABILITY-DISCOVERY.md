# Phase 4.8A — Next Business Capability Discovery / Blueprint

## 1. Décision proposée

La capacité retenue est **Place Lifecycle**, propriété exclusive du bounded
context `Geography`.

Le propriétaire métier à désigner nominativement est le **Responsable
Référentiels géographiques**, avec validation du Responsable Produit Annonces
pour les impacts de disponibilité. La fonction, et non une personne implicite,
porte l'autorité sur l'activation, la désactivation et la fusion d'un lieu.

Verdict de Discovery : **GO CERTIFIÉ**. Les cinq conditions de sortie ont été
formellement validées par l'autorité de certification. Le seul sprint désormais
autorisé est **4.8A-R1 — Place Merge Context Contract**. Aucun autre jalon de
la Phase 4.8 ne peut être ouvert directement.

## 2. Méthode de sélection

Les candidats ont été comparés selon sept critères : frontière autonome,
machine d'états déjà prouvée, owner unique, valeur métier, dépendances
maîtrisables, risque de composition et compatibilité avec les fondations
gelées.

Échelle : 1 faible, 3 moyen, 5 fort. Pour les risques, 5 signifie un risque
faible et donc une meilleure aptitude.

| Candidat | Frontière | États prouvés | Owner | Valeur | Dépendances | Risque maîtrisable | Total / 30 |
|---|---:|---:|---:|---:|---:|---:|---:|
| **Place Lifecycle** | 5 | 5 | 5 | 5 | 4 | 4 | **28** |
| Account Status Lifecycle | 4 | 4 | 5 | 5 | 4 | 3 | 25 |
| Moderation Case Lifecycle | 3 | 3 | 5 | 5 | 2 | 2 | 20 |
| Professional Establishment Lifecycle | 3 | 3 | 4 | 3 | 3 | 3 | 19 |
| Professional Mandate Lifecycle | 3 | 3 | 3 | 4 | 2 | 2 | 17 |
| Payment Lifecycle | 2 | 5 | 4 | 4 | 1 | 1 | 17 |

### Justification objective

`Place` expose déjà :

- une identité et une version ;
- les conditions exclusives `enabled`, `disabled` et `mergedInto` ;
- les actions `disable`, `enable` et `mergeInto` ;
- les faits historiques `PlaceDisabled`, `PlaceEnabled` et `PlaceMerged` ;
- un port propriétaire `PlaceRegistry` avec lecture, ajout et sauvegarde
  optimiste ;
- une règle de terminalité : un lieu fusionné ne peut plus être activé,
  désactivé, renommé ou fusionné de nouveau ;
- une validation locale de la cible de fusion : autre identité, active, non
  fusionnée, de même type et du même pays.

Le domaine est suffisamment mature pour décrire la décision, mais ne possède
pas encore la verticale Runtime standardisée. Cette combinaison permet une
progression incrémentale sans modifier une capacité certifiée.

## 3. Candidats non retenus

- **Account Status Lifecycle** : candidat solide, différé car l'Aggregate
  `Account` combine statut, credentials, vérifications, rôles et consentements.
  Un Blueprint de séparation des données de sécurité doit précéder sa
  verticalisation.
- **Moderation Case Lifecycle** : la suite `Report → Finding → Decision →
  Closure` est un processus composite, pas encore une machine d'états fermée.
  Elle dépend en outre d'une politique explicite d'effet sur Listing.
- **Professional Establishment Lifecycle** : ajout et retrait sont prouvés,
  mais la cardinalité, l'identité légale et l'effet du statut professionnel
  doivent être isolés.
- **Professional Mandate Lifecycle** : la responsabilité est partagée avec
  `IdentityAccess`; l'autorité de révocation et l'expiration doivent être
  arbitrées avant sélection.
- **Payment Lifecycle** : états riches mais risque financier élevé, plusieurs
  Aggregates et frontières transactionnelles, dépendances fournisseur et
  règles commerciales encore conditionnelles.

Ces candidats restent disponibles pour une Discovery future. Aucun ordre
d'implémentation n'est créé par leur classement.

## 4. Owner et autorité

| Élément | Décision |
|---|---|
| Bounded context | `Geography` |
| Owner architectural | module `Geography` |
| Owner métier fonctionnel | Responsable Référentiels géographiques |
| Validateur d'impact | Responsable Produit Annonces |
| Décisionnaire d'état | owner métier Geography uniquement |
| Consommateurs | Catalogue, Search, Public Projection, SEO et Migration Legacy, en lecture ou par faits |
| Interdiction | un consommateur ne peut activer, désactiver, fusionner ni reconstruire l'état d'un lieu |

**Amadoune Gueye**, Architecte logiciel et responsable de l'architecture du
programme APPART-REBUILD, exerce la fonction de Responsable Référentiels
géographiques pour la gouvernance de `Place Lifecycle`.

## 5. Périmètre fonctionnel

### Inclus

- représenter la disponibilité d'un lieu ;
- désactiver un lieu actif ;
- réactiver un lieu désactivé ;
- fusionner définitivement un lieu actif ou désactivé vers une cible valide ;
- distinguer refus métier, rejeu identique, conflit de version et donnée
  absente ;
- préserver l'identité de la source et la référence vers la cible après
  fusion ;
- publier ultérieurement un fait minimal pour chaque transition appliquée.

### Exclus

- création d'un lieu ;
- renommage et gestion des aliases ;
- modification de parent, type, pays, code ou coordonnées ;
- définition ou réparation de la hiérarchie ;
- suppression physique d'un lieu ;
- réécriture des références détenues par Catalogue, Search, SEO ou les
  projections ;
- décision de redirection publique ou canonique ;
- migration ou nettoyage des données Legacy ;
- validation éditoriale du nom d'un lieu ;
- autorisation et authentification de l'acteur ;
- notification, interface d'administration et traitement en masse.

La création fixe l'état initial mais reste hors Workflow. Le renommage est une
mutation de contenu, pas une transition de disponibilité.

## 6. États

| État pressenti | Définition | Terminal |
|---|---|---:|
| `Enabled` | lieu utilisable comme référence active | non |
| `Disabled` | lieu conservé mais indisponible pour de nouvelles décisions | non |
| `Merged` | identité source remplacée définitivement par une cible | oui |

État initial après création : `Enabled`.

Règles :

1. `Merged` implique `enabled = false` et une cible présente.
2. `Enabled` et `Disabled` impliquent l'absence de cible de fusion.
3. aucune transition ne sort de `Merged`.
4. la cible de fusion reste distincte de la source, active, non fusionnée, du
   même type et du même pays au moment de la décision.
5. la fusion ne transfère pas l'ownership des données des consommateurs.

## 7. Actions et transitions pressenties

| État courant | Action | État cible | Décision pressentie |
|---|---|---|---|
| `Enabled` | `Disable` | `Disabled` | `Applied` |
| `Disabled` | `Enable` | `Enabled` | `Applied` |
| `Enabled` | `Merge(target)` | `Merged` | `Applied` si contexte cible valide |
| `Disabled` | `Merge(target)` | `Merged` | `Applied` si contexte cible valide |
| `Enabled` | `Enable` | — | `AlreadyInState` |
| `Disabled` | `Disable` | — | `AlreadyInState` |
| `Merged` | toute action | — | `TerminalState` |

Refus additionnels pour `Merge` :

- `SameIdentity`;
- `TargetMissing`;
- `TargetDisabled`;
- `TargetMerged`;
- `DifferentType`;
- `DifferentCountry`;
- `TargetVersionConflict`;
- `SourceVersionConflict`;
- `InvalidContext`;
- `ReplayConflict`.

Les libellés sont contractuels uniquement après certification du Workflow. Le
Blueprint impose cependant que l'ensemble des résultats soit fermé, sans
exception technique traversant la frontière applicative.

## 8. Invariants et contexte de décision

Une transition doit recevoir explicitement :

- identité de la source ;
- action ;
- version attendue de la source ;
- acteur ;
- instant métier ;
- identifiant d'intention/idempotence ;
- pour une fusion : identité, état observé, type, pays et version observée de
  la cible.

Le Workflow doit rester pur. Il ne lit ni Registry, ni projection, ni
fondation 4.1–4.7. La preuve de cible est préparée en amont et figée dans un
`Place Merge Context` certifié.

La persistance future devra empêcher qu'une cible change entre sa lecture et
le commit d'une fusion. La stratégie exacte sera décidée en Persistence
Foundation; aucune solution SQL n'est prescrite ici.

## 9. Dépendances envers les fondations certifiées

| Fondation | Usage autorisé futur | Contrainte |
|---|---|---|
| Repository Protocol / Transaction Policy | conventions de résultat, concurrence et rollback | aucune modification du contrat partagé |
| PostgreSQL Foundation | future tranche locale Geography | owner et schéma à auditer avant migration |
| Runtime Composition / Health | bindings paresseux et capacité observable | ajout uniquement; 50 capacités restent saines |
| Event Contract / Transport / Routing | patrons de faits versionnés et livraison durable | nouveaux contrats Geography, aucune extension destructive |
| Delivery Consumption | matrice fermée ack/retry/quarantaine | politique certifiée avant Consumer |
| Outbox | compatibilité générique future | owner Geography résolu avant toute migration |
| HTTP Runtime | validation transport et délégation unique | aucun endpoint avant atomicité certifiée |
| Public Geography projections | consommateurs aval éventuels | jamais source de décision du Workflow |

Les Phases 2, 3 et 4.1 à 4.7 sont des fondations consommables mais gelées. Toute
incompatibilité impose un amendement versionné préalable et arrête 4.8.

## 10. Flux conceptuel cible

```text
Intention explicite
    → chargement de la source
    → preuve de cible si Merge
    → décision pure du Workflow
    → append durable de la transition et du contexte
    → fait versionné minimal
    → Outbox dans la même transaction
    → routage durable
    → consommation selon matrice fermée
```

Ce flux est une séquence de responsabilités, pas une autorisation
d'implémentation.

## 11. Confidentialité et minimisation futures

Les faits du lifecycle ne devront contenir que :

- identité de la source ;
- état source et état cible ;
- action ;
- identité de cible uniquement pour `Merged`;
- version résultante ;
- instant et identités techniques nécessaires à l'idempotence.

Les noms, aliases, coordonnées, détails de hiérarchie, justification libre et
identité personnelle de l'opérateur sont exclus du payload public par défaut.
Toute donnée supplémentaire exigera une justification et un gate de
confidentialité.

## 12. Critères de certification de la Discovery

La sélection et le Blueprint reçoivent un **GO CERTIFIÉ**.

La certification repose sur les critères suivants :

- la capacité, l'owner et les exclusions sont uniques et non ambigus ;
- le métier confirme que `Merged` est terminal ;
- le métier confirme qu'une source `Disabled` peut être fusionnée ;
- la cible de fusion et sa stabilité transactionnelle sont traitées avant le
  Workflow par `R1`;
- aucune décision ne dépend d'une projection publique ;
- les contrats gelés restent inchangés ;
- la roadmap, les risques et les gates sont audités ;
- le livrable ne contient aucun artefact d'implémentation.

Les cinq confirmations de sortie sont **VALIDÉES** :

1. **Amadoune Gueye** est nommé Responsable Référentiels géographiques ;
2. `Merged` est irréversiblement terminal ;
3. une source `Disabled` peut être fusionnée vers une cible valide ;
4. les consommateurs aval traitent uniquement les faits, sans commander,
   reconstruire ni réécrire une décision appartenant à `Geography` ;
5. la baseline 4.7 reste la baseline d'entrée gelée de la Phase 4.8, sans
   régression autorisée.

Le GO conditionnel est levé. `4.8A-R1` est le seul sprint autorisé.

## 13. Contraintes de la phase

Le présent livrable est exclusivement documentaire. Il n'autorise ni code, ni
migration, ni table, ni Repository, ni Aggregate, ni Workflow, ni Runtime, ni
Event, ni Transport, ni Outbox, ni endpoint HTTP.

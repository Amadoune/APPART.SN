# Public Listing Query Production — analyse

## Verdict de l'audit

**NO GO.** Aucune source de production existante ne permet de résoudre une canonical courante vers un `ListingId`, puis de reconstruire un `PublicListingReadModel` complet. Une preuve sans fausse source est impossible.

La seule stratégie techniquement cohérente avec les contrats actuels est une projection publique persistée, reconstruisible et propriétaire de son index de lecture par canonical courante. Elle constitue une nouvelle infrastructure et requiert une autorisation explicite avant toute implémentation.

## Inventaire réel

### Contrat et composition publique

- `PublicListingQuery` accepte un canonical path relatif exact et retourne `PublicListingReadModel|null`.
- `SearchListingProjectionBuilder` reconstruit une projection depuis les trois Aggregates `Property`, `MediaCollection` et `Listing` déjà chargés.
- `SeoListingProjectionBuilder` exige une `ListingSeoDecision` complète.
- `PublicListingReadModelBuilder` compose les deux projections, mais ne sait ni les retrouver ni les persister.
- Aucun de ces builders ne constitue une source de données ou un index de résolution.

### Sources ContentSeo

`ListingSeoDecisionSources` exige cinq ports :

1. `ListingCatalog` ;
2. `SearchCatalog` ;
3. `PropertyCatalog` ;
4. `PublicGeographyCatalog` ;
5. `PublicMediaCatalog`.

Toutes les implémentations trouvées sont sous `tests/`. Aucun adaptateur de production ni binding Laravel n'existe. `ListingSeoSource` exige notamment headline, description et canonical path : ces valeurs ne figurent pas dans l'Aggregate `Listing` ni dans son schéma PostgreSQL.

### SearchDiscovery

`SearchIndexRegistry` définit une recherche par `SearchIndexId` ou `ListingId`, mais ne possède aucun backend de production. `SearchListingProjection` est uniquement reconstruite en mémoire dans les tests et depuis des Aggregates préalablement résolus.

### ContentSeo historique

`SeoProjection` possède conceptuellement la canonical courante et son historique. `SeoProjectionRegistry` expose uniquement `find(SeoProjectionId)` ; il n'expose ni recherche par canonical ni recherche par `ListingId`. Sa seule implémentation est un fake de test. Aucun schéma ContentSeo PostgreSQL n'existe.

### Quatre repositories PostgreSQL certifiés

| Repository | Lecture exposée | Information utile | Manque déterminant |
|---|---|---|---|
| Listing | `find(ListingId)` | PropertyId, statut, révisions, dates | canonical, headline, description, recherche inverse |
| Property | `find(PropertyId)` | caractéristiques et GeographicPlaceId | référentiel géographique public, canonical |
| MediaCollection | `find(MediaCollectionId)` | items, primaire, statut | résolution Property→collection et URL média publique |
| AdministrativeAction | `find(AdministrativeActionId)` | audit administratif | aucune donnée de page publique |

Les quatre ports sont orientés par identifiant d'Aggregate. Aucun ne peut être détourné en query publique sans modifier son contrat certifié. Aucun binding Laravel de ces repositories n'est présent dans `AppServiceProvider`.

### Schémas PostgreSQL

Les seules migrations applicatives concernent AdministrationAudit, ListingLifecycle, RealEstateCatalog et Media. Il n'existe aucune table Search, ContentSeo, canonical registry ou PublicListingReadModel. Aucun index canonical n'existe.

## Réponses aux dix questions

1. **Quelle source retrouve un Listing depuis une canonical courante ?** Aucune.
2. **Existe-t-elle réellement en production ?** Non ; seules des données/fakes de test associent une décision SEO à un Listing.
3. **Où est conservé l'historique canonical ?** Dans `SeoProjection` et `ListingSeoDecision` en mémoire. Aucun backend de production ne le conserve.
4. **Comment distinguer canonical courante et ancienne ?** `CanonicalDisposition` le permet dans l'historique en mémoire. Aucun index persistant interrogeable ne permet cette distinction au runtime.
5. **Search et SEO sont-elles disponibles en production ?** Non. Elles sont reconstructibles en mémoire si toutes leurs sources sont déjà disponibles.
6. **Tous les Catalogs ont-ils un adaptateur de production ?** Non. Aucun des Catalogs ContentSeo nécessaires n'en possède.
7. **Reconstruction à la demande sans scan global ?** Non. L'entrée est une canonical et aucune lecture indexée canonical→Listing n'existe.
8. **Les quatre repositories exposent-ils les lectures nécessaires ?** Non. Ils chargent uniquement leur Aggregate par son identifiant propre et ne couvrent pas le contenu/public geography/public media.
9. **Une nouvelle persistance est-elle techniquement indispensable ?** Avec les sources actuellement disponibles, oui : au minimum un index canonical courant et la projection publique complète doivent être matérialisés. Un futur référentiel externe équivalent pourrait changer cette conclusion, mais il n'existe pas.
10. **Qui possède aujourd'hui la fraîcheur ?** Personne au runtime. Les policies Search/SEO savent comparer des révisions, mais aucun orchestrateur de production, dispatcher, outbox ou projection store ne maintient le read model public.

## Évaluation des stratégies

### Option A — reconstruction synchrone à la demande

**Rejetée.** La première étape canonical→Listing est impossible. Même avec un `ListingId`, les Catalogs ContentSeo, le référentiel Geography, l'URL média publique et les stores Search/SEO manquent. Les Aggregates sont dans des transactions locales distinctes : plusieurs lectures successives ne garantiraient pas une photographie temporelle cohérente. Ajouter des scans SQL directs violerait les ports et ne fournirait toujours pas le contenu SEO manquant.

### Option B — projection publique persistée

**Candidate recommandée, non autorisée dans ce sprint.** Un Projection Store dédié pourrait indexer atomiquement le canonical courant et stocker le `PublicListingReadModel` sérialisé ainsi que des métadonnées de fraîcheur. Il resterait une lecture dérivée reconstruisible, distincte d'un Repository d'Aggregate.

Il doit définir avant implémentation :

- son schéma propriétaire ;
- l'unicité de la canonical courante ;
- le remplacement atomique current→historical ;
- le retrait/noindex et la suppression logique ;
- le vecteur de révisions ou watermark de fraîcheur ;
- le mécanisme de mise à jour et de reconstruction complète ;
- la politique de redirection historique, séparée du query de page courante.

### Option C — SeoProjectionRegistry

**Rejetée comme implémentation directe.** Il n'a aucun backend, aucune recherche par canonical et ne contient pas les caractéristiques Search/Property ni la ressource média publique finale. Le détourner ferait d'un Aggregate/projection SEO le read model public et mélangerait ownership SEO et livraison Web.

### Option D — index canonical minimal + reconstruction synchrone

**Rejetée.** Cet index serait déjà une nouvelle persistance, tout en laissant cinq Catalogs sans adaptateur et en imposant plusieurs lectures non atomiques. Elle cumule la complexité des options A et B sans matérialiser un résultat fiable.

## Tableau de décision

| Critère | A Synchrone | B Projection persistée | C SeoRegistry | D Index + synchrone |
|---|---|---|---|---|
| DDD / frontières | faible | forte si store de lecture dédié | faible | moyenne |
| Faisable aujourd'hui | non | non, autorisation requise | non | non |
| Reconstruction | théorique, sources absentes | complète à concevoir | partielle | partielle |
| Fraîcheur | non garantie | explicite via révisions/watermark | SEO seulement | non garantie |
| Performance HTTP | mauvaise/inconnue | lecture indexée | inconnue | plusieurs lectures |
| Canonical courante | aucune résolution | clé unique | modèle interne non interrogeable | index dédié |
| Historique | indisponible | séparation page/redirection possible | en mémoire seulement | incomplet |
| Concurrence / rollback | multi-sources non atomiques | remplacement local atomique possible | non démontré | multi-sources |
| Observabilité | complexe | lag/rebuild mesurables | absente | complexe |
| Impact PostgreSQL | scans/adapters nouveaux | nouveau schéma autorisé requis | backend nouveau requis | nouvelle table requise |
| Impact 4 repositories | risque de détournement | aucun changement | aucun, mais insuffisant | nouveaux accès nécessaires |
| Duplication de règles | élevée | faible si projection passive | élevée | élevée |
| HTTP 200/404 réel | non | oui après implémentation | non | non démontré |

## Ownership proposé

- **Canonical et historique métier SEO :** ContentSeo.
- **Construction Search :** `SearchListingProjectionBuilder` depuis les sources autorisées.
- **Décision SEO :** ContentSeo uniquement.
- **Composition du read model :** `PublicListingReadModelBuilder`.
- **Store et implémentation de `PublicListingQuery` :** futur sous-système de lecture Public Delivery, couche Infrastructure, distinct des modules Aggregate.
- **Fraîcheur :** futur projector/updater Public Delivery, avec révisions sources persistées et reconstruction idempotente.
- **Redirections historiques :** futur adaptateur dédié alimenté par l'historique ContentSeo ; jamais `PublicListingQuery` qui reste current-only.
- **Binding Laravel :** `AppServiceProvider`, câblage pur vers l'adaptateur concret, sans logique.

## Preuve de faisabilité

Aucune preuve conforme ne peut être produite. Une preuve positive nécessiterait soit une fausse source, soit une nouvelle persistance, soit un scan SQL hors contrats. Les trois sont interdits. La preuve négative est l'inventaire reproductible : aucune classe de production n'implémente les Catalogs Search/ContentSeo ou `SeoProjectionRegistry`, aucun schéma ne contient de canonical et les repositories certifiés ne recherchent que par identifiant.

## Capacité manquante exacte

Un store de projection public de production, indexé par canonical courante et alimenté par une chaîne de reconstruction/fraîcheur réelle, avec conservation explicite de la distinction current/historical. Sans autorisation de cette cinquième infrastructure, `PublicListingQuery` ne peut pas être implémenté honnêtement.

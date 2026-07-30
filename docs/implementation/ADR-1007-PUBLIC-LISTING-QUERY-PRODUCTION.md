# ADR-1007 — Public Listing Query Production

- **Date :** 18 juillet 2026
- **Statut :** Superseded by ADR-1008
- **Décision visée :** projection publique persistée dédiée

## Contexte

Le Web Adapter dépend de `PublicListingQuery`, mais aucun binding ni adaptateur de production n'existe. Le contrat exige une résolution exacte par canonical courante, sans fallback `ListingId`, sans historique servi comme page courante et sans décision métier pendant la lecture.

## Problème

Les quatre repositories PostgreSQL certifiés chargent uniquement leurs Aggregates par identifiant. Aucun stockage ne fournit canonical→Listing, headline/description publics, géographie publique complète, URL média publique, projections Search/SEO ou read model composé.

## Options

1. **Reconstruction synchrone : rejetée.** Résolution initiale et adaptateurs sources absents ; cohérence multi-sources non garantie.
2. **Projection publique persistée : proposée.** Seule option capable d'offrir une lecture indexée, déterministe et sans règles au moment HTTP.
3. **SeoProjectionRegistry : rejetée.** Backend et lookup canonical absents ; données publiques incomplètes ; mauvais ownership.
4. **Index canonical + reconstruction : rejetée.** Nouvelle persistance malgré tout, sources toujours absentes et coût multi-lectures.

## Décision proposée

Créer, après autorisation explicite, un Projection Store PostgreSQL propriétaire du sous-système Public Delivery. Il matérialise le `PublicListingReadModel` final et l'indexe par canonical courante. `PublicListingQuery` devient un adaptateur de lecture de ce store.

Cette proposition a été arbitrée par ADR-1008, qui accepte PostgreSQL comme Public Projection Store et fixe le mécanisme hybride d'alimentation. Les preuves d'implémentation restent réparties dans les sprints de la roadmap 3.6.

## Justification

- aucune reconstruction complète n'est possible depuis les sources de production actuelles ;
- la requête HTTP doit rester une lecture simple et stable ;
- le store ne devient pas une source métier : il est supprimable et entièrement reconstruisible ;
- aucune modification des quatre repositories certifiés n'est nécessaire ;
- canonical courante et historique peuvent recevoir des contraintes et transitions atomiques propres à la projection.

## Ownership

- ContentSeo décide canonical, historique, indexabilité, robots et JSON-LD.
- SearchDiscovery et les sources Aggregate décident leurs propres faits.
- `PublicListingReadModelBuilder` compose passivement les projections.
- Public Delivery Infrastructure possède le Projection Store et l'adaptateur `PublicListingQuery`.
- Un updater/projector Public Delivery possède la fraîcheur technique, l'idempotence et la reconstruction.
- `AppServiceProvider` réalise uniquement le binding Laravel.

## Données sources

La reconstruction requiert les faits validés de ListingLifecycle, RealEstateCatalog, Media, Geography, SearchDiscovery et ContentSeo. Les adaptateurs de production nécessaires ne sont pas encore tous disponibles. Leur fourniture ou une alimentation événementielle approuvée est une précondition.

## Stratégie de reconstruction

Le futur updater produit d'abord `SearchListingProjection`, puis `ListingSeoDecision`, `SeoListingProjection` et enfin `PublicListingReadModel`. Il écrit le résultat final et ses métadonnées de révision dans une transaction locale au store de projection. Une reconstruction complète rejoue toutes les sources publiques autorisées, sans utiliser le store comme source métier.

## Fraîcheur

Chaque ligne doit conserver un vecteur de versions/révisions sources ou un watermark équivalent. Les mises à jour sont idempotentes et refusent une version plus ancienne. Le retard, la dernière version appliquée, les échecs et les divergences doivent être observables. Aucun SLA ne peut être accepté avant le choix du mécanisme d'alimentation.

## Canonical courante

Une seule canonical courante est unique et directement interrogeable. Son chemin relatif exact est la clé publique de `PublicListingQuery`. Aucun `ListingId` n'est exposé comme fallback.

## Historique et redirections

Le changement de canonical doit remplacer atomiquement la clé courante et préserver une entrée historique non réattribuable. `PublicListingQuery` ne sert jamais cette entrée. Un futur port de redirection distinct pourra retourner une destination explicite décidée par ContentSeo.

## Conséquences

### Positives

- HTTP 200/404 par lecture indexée ;
- aucune règle métier dans le query ;
- stabilité sous concurrence locale ;
- reconstruction, observabilité et rollback du read side possibles ;
- repositories Aggregate inchangés.

### Négatives

- cinquième infrastructure PostgreSQL ;
- duplication technique assumée des faits publics ;
- cohérence éventuellement différée ;
- nécessité d'un mécanisme d'alimentation et d'une stratégie de rebuild.

## Risques

- projection obsolète visible ;
- canonical courante remplacée partiellement ;
- réutilisation abusive du store comme source métier ;
- duplication d'une policy dans le projector ;
- absence d'adaptateurs sources rendant le rebuild incomplet.

## Critères d'acceptation

- autorisation explicite de la nouvelle persistance ;
- canonical courante→read model et inconnue→null sur PostgreSQL réel ;
- historique→null pour `PublicListingQuery` ;
- remplacement canonical atomique et concurrent ;
- reconstruction déterministe complète ;
- preuve de fraîcheur/idempotence ;
- runtime Laravel 200/404 sans harness ;
- aucun changement des quatre repositories certifiés ;
- aucun accès Aggregate depuis le Controller.

## Rollback conceptuel

Le store est dérivé : son schéma peut être désactivé puis supprimé après arrêt du binding et conservation des métriques nécessaires. Les sources métier restent intactes. Le retour au Web Adapter en isolation ne modifie aucun Aggregate. Une ancienne canonical ne peut être réattribuée pendant un rollback.

## Travaux autorisés ensuite, sous réserve d'approbation

- détailler et approuver le schéma du Projection Store ;
- créer sa migration propriétaire et ses tests PostgreSQL ;
- créer writer/updater et query adapter ;
- câbler le binding Laravel ;
- démontrer reconstruction, fraîcheur, concurrence et HTTP runtime.

## Travaux toujours interdits

- modifier les repositories Aggregate pour des lectures publiques opportunistes ;
- reconstruire dans le Controller ou Blade ;
- servir une ancienne canonical comme page courante ;
- fallback `ListingId` ;
- dupliquer les règles Search/SEO ;
- réactiver ADR-1007 comme décision normative ; ADR-1008 la remplace.

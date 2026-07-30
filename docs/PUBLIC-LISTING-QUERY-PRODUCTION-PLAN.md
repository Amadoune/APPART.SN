# Public Listing Query Production — plan historique, remplacé par la roadmap 3.6

> Ce plan proposé lors du Sprint 3.5C est remplacé par `PUBLIC-PROJECTION-STORE-ROADMAP.md` et ADR-1008. Il ne constitue plus la séquence d'exécution normative.

## Porte préalable

Cette porte architecturale a été franchie par ADR-1008. L'exécution doit toutefois suivre exclusivement la roadmap 3.6 et ses portes GO successives.

## Objectif proposé

Implémenter et certifier un Projection Store PostgreSQL reconstruisible pour `PublicListingReadModel`, puis lever la réserve runtime du Web Adapter.

## Fichiers à créer

Noms historiques proposés ; la roadmap 3.6 gouverne désormais leur confirmation :

- `app/Application/Contract/PublicListingProjectionWriter.php` — port d'écriture technique du read side ;
- `app/Application/PublicListingProjectionUpdater.php` — orchestration passive des builders et versions sources ;
- `app/Infrastructure/PublicListing/PostgreSqlPublicListingQuery.php` — implémentation de `PublicListingQuery` ;
- `app/Infrastructure/PublicListing/PostgreSqlPublicListingProjectionWriter.php` — upsert/retrait atomique ;
- `app/Infrastructure/PublicListing/PublicListingProjectionMapper.php` — mapping strict, sans règle ;
- `app/Infrastructure/PublicListing/Migrations/001_public_listing_projection.sql` — schéma propriétaire, planifié au Sprint 3.6D ;
- tests unitaires de mapper/updater ;
- tests de contrat Fake/PostgreSQL du query et writer ;
- tests de reconstruction et de fraîcheur ;
- tests HTTP runtime sans harness.

## Fichiers à modifier

- `app/Providers/AppServiceProvider.php` — bindings purs query/writer vers leurs adaptateurs ;
- configuration PostgreSQL uniquement si une connexion propriétaire explicitement approuvée est nécessaire ;
- documentation et ADR-1008, déjà `Accepted` pour l'architecture ; chaque implémentation conserve sa certification propre.

Le Controller, Blade, les Aggregates et les quatre repositories PostgreSQL certifiés ne doivent pas changer.

## Schéma à faire approuver

Le schéma doit au minimum représenter :

- canonical path courant unique ;
- payload typé du read model ou colonnes explicitement mappées ;
- ListingId interne pour reconstruction, jamais fallback public ;
- versions/révisions sources ;
- date de décision et date d'application ;
- état actif/retiré/noindex ;
- historique canonical réservé avec destination explicite, séparé de la lecture current-only.

Le SQL exact ne doit être écrit qu'après revue de l'ownership, de la rétention et de l'atomicité.

## Alimentation et reconstruction

1. Obtenir les sources publiques via des adaptateurs de production autorisés ou un flux événementiel approuvé.
2. Construire passivement Search, décision SEO, projection SEO puis read model public.
3. Comparer les révisions sources au watermark stocké.
4. Écrire/remplacer/retirer dans une transaction locale au Projection Store.
5. Rendre l'opération idempotente.
6. Fournir une commande de rebuild complet observable et interruptible.

Le prochain sprint doit d'abord trancher le mécanisme d'alimentation, car Dispatcher/Outbox/Queue ne sont actuellement ni implémentés ni implicitement autorisés.

## Bindings

`AppServiceProvider::register()` liera :

- `PublicListingQuery` à `PostgreSqlPublicListingQuery` ;
- le futur writer à son adaptateur PostgreSQL.

Le provider ne contiendra ni SQL, ni reconstruction, ni policy, ni fallback.

## Tests obligatoires

- canonical courante retourne le modèle exact ;
- canonical inconnue retourne `null` ;
- canonical historique retourne `null` au query public ;
- aucun fallback ListingId ;
- remplacement canonical atomique ;
- collisions et concurrence réelle ;
- upsert idempotent et rejet des versions obsolètes ;
- retrait et noindex ;
- reconstruction déterministe ;
- rebuild depuis sources autorisées ;
- rollback local sans état partiel ;
- conteneur Laravel résout le port ;
- requêtes runtime réelles 200 et 404 ;
- suite complète, Architecture, Pint, Larastan et quality.

## Fraîcheur et observabilité

Mesurer au minimum : dernière révision appliquée, retard par source, durée d'update/rebuild, échecs, conflits, retraits et divergences. Tant que la fraîcheur n'est pas démontrée, une projection incertaine doit être retirée ou noindex selon la décision ContentSeo déjà produite, jamais re-décidée par l'infrastructure.

## Critères GO

- nouvelle infrastructure explicitement autorisée ;
- ADR-1008 `Accepted` ;
- PostgreSQL réel certifie lookup, atomicité et concurrence ;
- reconstruction complète sans fake ;
- runtime Laravel sert 200/404 ;
- historique non servi comme current ;
- quatre repositories et tous les Aggregates inchangés.

## Critères NO GO

- source publique ou mécanisme d'alimentation toujours absent ;
- impossibilité de reconstruire sans fake ;
- canonical non unique ou remplacement non atomique ;
- fraîcheur non mesurable ;
- dépendance HTTP vers Aggregates/projections intermédiaires ;
- modification opportuniste d'un repository certifié ;
- création de persistance avant le Sprint 3.6D et sa porte d'exécution.

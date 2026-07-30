# Public Projection Store — contrat

## Rôle

Le Public Projection Store conserve un `PublicListingReadModel` final déjà décidé. Il applique des transitions techniques versionnées et fournit à `PublicListingQuery` uniquement les projections `Current` de la génération active.

Il n'est ni un Repository d'Aggregate, ni une source métier, ni un cache, ni un moteur de reconstruction. Il ne décide aucune canonical, règle Search, règle SEO ou redirection.

## Ownership

- Aggregates : faits métier et versions officielles.
- ContentSeo : canonical courante/historique, indexabilité, robots, JSON-LD et redirection.
- SearchDiscovery : faits et visibilité Search.
- Public Projection Store : conservation technique du read model, watermark, état et génération.
- Futur Updater : composition préalable et intention d'écriture.
- Laravel : aucun rôle dans ce contrat ; le binding reste un sprint ultérieur.

## Audit des versions

Les versions Listing, Property et Media existent sur leurs Aggregates. Search et ContentSeo possèdent des révisions explicites. `PublicGeographySeoSource` et `PublicMediaSeoSource` n'exposent aucune révision stable.

Le contrat ne leur invente aucun compteur. Leur composante est nullable et produit une readiness bloquante explicite :

- `MissingPublicGeographyVersion` ;
- `MissingPublicMediaVersion` ;
- `MissingPublicGeographyAndMediaVersions`.

Un watermark incomplet ne peut jamais être promu par le writer Fake. Le Sprint 3.6B reste bloqué tant que les deux sources ne fournissent pas une stratégie réelle de révision.

## Watermark vectoriel

`PublicProjectionWatermark` est immutable et contient sept composantes nommées :

1. Listing ;
2. Property ;
3. Media ;
4. Search ;
5. ContentSeo ;
6. Geography publique ;
7. ressource média publique.

Aucun tableau libre, map, timestamp, `decidedAt`, `updatedAt` ou horloge serveur n'intervient dans l'ordre causal.

### Relation

- composantes identiques : `Equal` ;
- toutes supérieures ou égales et au moins une supérieure : `Newer` ;
- toutes inférieures ou égales et au moins une inférieure : `Older` ;
- composantes croisées : `Incomparable` ;
- au moins une source obligatoire non versionnée : `Incomplete`.

Il n'existe aucun last-write-wins.

## Snapshot logique

`PublicListingProjectionRecord` est l'enregistrement logique spécialisé et readonly du Store. Il transporte :

- `ListingId` interne ;
- canonical path décidé en amont ;
- read model final nullable selon l'état ;
- watermark ;
- état technique ;
- génération ;

Les factories `current`, `historical` et `tombstone` empêchent les combinaisons ambiguës. Un snapshot `Current` exige le read model correspondant au même Listing et à la même canonical. `Historical` et `Tombstone` n'exposent aucun read model affichable.

## États techniques

### Current

Projection servable uniquement si elle appartient à la génération active. Une page noindex reste `Current` : indexabilité SEO et état du Store restent distincts.

### Historical

Canonical réservée après remplacement, jamais retournée par `PublicListingQuery`. Elle pourra alimenter un futur port de redirection, sans que le Store décide la destination.

### Tombstone

Retrait technique versionné, non servable. Son watermark empêche la résurrection par un ancien événement. Une restauration est autorisée uniquement par un snapshot `Current` strictement `Newer` sur la même canonical.

## Générations

- `Active` : seule génération lisible par le Web ;
- `Candidate` : isolée et invisible ;
- `Retired` : contrat d'état pour le futur rebuild, jamais cible d'écriture dans ce sprint.

`PublicProjectionGenerationId` est une identité opaque. Aucune horloge ne choisit l'état. Le sprint ne contient aucune bascule ni aucun Rebuilder.

Une même canonical peut exister dans Active et Candidate, car les générations sont isolées. L'unicité reste obligatoire à l'intérieur de chaque génération.

## Port d'écriture

`PublicListingProjectionWriter` expose uniquement :

- `applyCurrent(snapshot)` sur Active ;
- `replaceCanonical(previousPath, replacement)` atomiquement dans le contrat ;
- `applyTombstone(snapshot)` sur Active ;
- `writeCandidate(snapshot)` sur Candidate.

Il n'existe ni `save(object)`, ni `put(key, payload)`, ni méthode permettant de réserver arbitrairement une canonical historique.

Le port ne reconstruit rien, n'appelle aucune Policy, ne charge aucun Aggregate et ne dépend ni de Laravel, PDO ou PostgreSQL.

## Résultats techniques

`PublicProjectionWriteResult` distingue :

- `Applied` ;
- `AlreadyApplied` ;
- `RejectedObsolete` ;
- `DivergentWatermark` ;
- `IncompleteWatermark` ;
- `CanonicalCollision` ;
- `CanonicalReplacementRequired` ;
- `HistoricalReservationConflict` ;
- `GenerationMismatch`.

Ces valeurs représentent des résultats techniques attendus. Une combinaison d'arguments impossible reste une violation du contrat et peut lever `InvalidArgumentException`. Les futures erreurs PostgreSQL appartiendront à l'adaptateur Infrastructure, absent de ce sprint.

## Idempotence, obsolescence et divergence

- même Listing, canonical, état, génération, watermark et payload : `AlreadyApplied` ; l'identité technique est cette combinaison complète et aucun identifiant supplémentaire n'est nécessaire ;
- watermark `Older` : `RejectedObsolete`, aucune mutation ;
- watermark `Incomparable` ou même watermark avec payload différent : `DivergentWatermark` ;
- watermark incomplet : `IncompleteWatermark` ;
- changement de canonical via `applyCurrent` : `CanonicalReplacementRequired` ;
- remplacement spécialisé : ancienne canonical devient Historical et nouvelle devient Current sans état intermédiaire visible dans le contrat.

## Fake contractuel

Le Fake existe uniquement sous `tests/`. Il implémente exactement le writer et `PublicListingQuery`, conserve des copies de snapshots, retourne un read model détaché, isole Active/Candidate et applique toutes les relations de watermark.

Il démontre notamment : collisions sans écrasement, historique non recyclable, tombstone anti-résurrection, restauration explicitement plus récente, invisibilité Candidate et absence de fallback `ListingId`.

La suite abstraite `PublicListingProjectionStoreContract` est destinée à être réutilisée par l'adaptateur PostgreSQL du Sprint 3.6D.

## Limites et préparation PostgreSQL

Ce contrat ne définit ni schéma, SQL, transaction, mapping PostgreSQL, binding, updater ou rebuild. Le futur adaptateur devra reproduire exactement les résultats du Fake et exécuter les opérations de remplacement canonical/tombstone avec atomicité locale.

Avant 3.6B, Geography publique et média public doivent obtenir une révision stable réelle. Avant 3.6D, les contrats seront traduits en contraintes PostgreSQL sans modifier leurs sémantiques.

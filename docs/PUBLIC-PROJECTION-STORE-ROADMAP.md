# Public Projection Store — roadmap

## Décision de référence

ADR-1008 est `Accepted` : PostgreSQL dédié, read side dérivé, fraîcheur hybride, générations de rebuild et canonical décidée par ContentSeo.

Chaque sprint ci-dessous possède un GO indépendant. Aucun sprint ne peut anticiper les artefacts du suivant.

## Sprint 3.6A — Projection Store Contract Foundation

**Statut : GO — certifié le 19 juillet 2026.**

Le watermark vectoriel, les snapshots Current/Historical/Tombstone, les générations, le writer spécialisé et le contrat Fake réutilisable sont livrés. Geography publique et média public restent explicitement non promotables sans révision stable ; cette précondition est reportée à la porte 3.6B sans compteur inventé.

### Objectif

Définir les ports, snapshots techniques, watermark vectoriel, états current/historical/tombstone et contrats de génération, sans PostgreSQL.

### Livrables

- contrat de lecture `PublicListingQuery` conservé ;
- port d'écriture technique spécialisé ;
- modèle immutable de snapshot/store record ;
- `PublicProjectionWatermark` couvrant toutes les sources ;
- protocole d'idempotence, divergence et génération ;
- harness Fake contractuel ;
- tests canonical current, historique non servi, tombstone et versions.

### Porte GO

Tous les composants Geography et média public possèdent une stratégie de révision explicite. Aucun timestamp ne remplace une version.

## Sprint 3.6B — Projection Updater Foundation

**Statut : GO — certifié le 19 juillet 2026.**

L'Updater applicatif, son port spécialisé, la reconstruction par la chaîne officielle, la porte `PromotionReadiness` et l'interprétation exhaustive du writer sont livrés sans infrastructure. Les révisions publiques Geography et Media restent absentes : leur absence bloque explicitement toute promotion et devra être résolue avant le runtime.

### Objectif

Orchestrer passivement Search→SEO→Public Read Model et écrire via le port du Store.

### Livrables

- updater applicatif sans framework ;
- sources de reconstruction spécialisées ;
- comparaison de watermark ;
- update, retrait, noindex, changement canonical et rebuild partiel ;
- tests déterministes et concurrents sur Fake ;
- aucune policy métier déplacée.

### Porte GO

Une reconstruction complète d'un Listing depuis des sources de production autorisables est démontrée sans scan global et sans faux contenu.

## Sprint 3.6C — Delivery Durability Decision

**Statut : GO — certifié le 19 juillet 2026.**

ADR-1009 est `Accepted` : Outbox transactionnelle locale à chaque module source, même transaction que l'Aggregate, livraison at-least-once, ordre `(aggregateVersion, eventIndex)`, idempotence durable, retry/quarantaine et réconciliation secondaire. Aucun runtime ni schéma n'est créé par ce sprint.

### Objectif

Décider et certifier le transport durable post-commit nécessaire à la fraîcheur événementielle.

### Livrables

- ADR Outbox/Dispatcher ou mécanisme durable équivalent ;
- ownership par source ;
- idempotency key et ordre ;
- retry, dead-letter/quarantaine, lag et reprise ;
- articulation avec le batch de réconciliation.

### Porte GO

Aucun événement volatil ne peut être perdu silencieusement entre commit Aggregate et projection.

## Sprint 3.6D — Projection PostgreSQL Foundation

**Statut : GO — certifié le 19 juillet 2026.**

Le Store, Writer, Reader, Mapper, schéma et migration PostgreSQL sont opérationnels. Le contrat partagé passe sur PostgreSQL réel, avec rollback et concurrence multiprocessus certifiés, sans Runtime ni modification des fondations applicatives.

### Objectif

Créer le cinquième schéma persistant conformément aux contrats certifiés.

### Livrables

- migration propriétaire ;
- mapper explicite ;
- adaptateurs PostgreSQL query/writer ;
- transactions locales ;
- contraintes current/historical/generation/watermark ;
- tests PostgreSQL réels de contrat, rollback et concurrence.

### Porte GO

Canonical courante unique, remplacement atomique, historique jamais servi par le query, tombstone anti-résurrection et aucun diff sur les quatre repositories certifiés.

## Sprint 3.6E — Rebuild and Reconciliation

**Statut : GO — certifié le 19 juillet 2026.**

### Objectif

Implémenter rebuild partiel/complet, générations, high-watermark et batch de réconciliation.

### Livrables

- énumération via ports de rebuild ;
- génération candidate isolée ;
- replay après high-watermark ;
- validation/checksums ;
- bascule et rollback atomiques ;
- métriques de progression, lag et divergence.

### Porte GO

Un rebuild parallèle ne modifie jamais la génération active avant bascule et peut revenir à la génération précédente sans toucher aux sources métier.

## Sprint 3.6F — Laravel Runtime Binding

**Statut : GO — certifié le 19 juillet 2026.**

### Objectif

Câbler les adaptateurs certifiés dans Laravel, sans logique dans le Service Provider.

### Livrables

- bindings `PublicListingQuery` et writer/updater nécessaires ;
- configuration de connexion/schéma ;
- health/readiness du Store ;
- tests du conteneur sans harness ;
- audit Controller/Blade inchangés.

### Porte GO

Le bootstrap réel résout tous les ports ; aucun binding null/fake et aucune policy dans Laravel.

## Sprint 3.6G — HTTP Runtime Certification

**Statut : GO — certifié le 19 juillet 2026.**

### Objectif

Lever la réserve du Public Web Adapter.

### Scénarios

- canonical courante : HTTP 200 et modèle exact ;
- inconnue : 404 ;
- historique : jamais page courante, redirection uniquement via futur port autorisé ;
- ListingId : aucune résolution ;
- noindex et JSON-LD conformes ;
- Store indisponible : comportement explicite et observable ;
- projection obsolète/divergente : fail-safe conforme aux décisions existantes.

### Porte GO

Requêtes réelles hors harness, fraîcheur mesurée, 200/404 déterministes et aucune lecture directe des Aggregates par HTTP.

## Sprint 3.6C.8 — Runtime Certification

**Statut : reprise finale en cours de certification au 19 juillet 2026.**

Les blocages de composition Worker et de participation transactionnelle sont levés par 3.6H et 3.6I. La campagne finale exécute désormais la chaîne Mutation → Outbox → Worker → Projection → HTTP sans assemblage manuel.

## Sprint 3.6H — Delivery Runtime Composition Foundation

**Statut : GO — certifié le 19 juillet 2026.**

Le Worker Delivery, ses ports Outbox PostgreSQL, sa retry policy, son registre, ses identités et son horloge de production sont désormais composés explicitement par Laravel. La résolution reste paresseuse et ne déclenche aucun traitement au bootstrap.

### Porte GO

Le conteneur résout le Worker et tout son graphe sans Fake, Null Object, fallback ni instanciation extérieure à la racine de composition. Après certification, la reprise de 3.6C.8 est autorisée.

## Sprint 3.6I — Aggregate/Outbox Transactional Runtime Composition

**Statut : GO — certifié le 19 juillet 2026.**

Laravel compose la transaction Aggregate/Outbox, son participant et les Repositories producteurs Listing, Property et Media avec l'unique PDO `pgsql`. Search et Content/SEO sont explicitement hors périmètre du participant Repository et conservent leur participation PDO native.

### Porte GO

Mutation et append Outbox commit ou rollback ensemble, sans transaction imbriquée, sans modification des Repositories ni du protocole certifié. Après certification, une nouvelle reprise de 3.6C.8 est autorisée.

## Sprint ultérieur — Historical Redirect Adapter

Ce sprint séparé créera, après contrat explicite, la résolution historical canonical→destination décidée par ContentSeo. Il ne modifiera pas `PublicListingQuery`, qui reste current-only.

## Invariants transverses

- PostgreSQL reste le store primaire du read side public.
- ContentSeo reste propriétaire des canonicales et redirections.
- Le Store n'est jamais une source métier.
- Aucun Repository Aggregate n'est étendu pour le Web.
- `ListingId` reste interne.
- Fraîcheur événementielle durable + réconciliation batch.
- Rebuild par génération et bascule atomique.
- Toute projection est supprimable et reconstruisible.
- Les futurs caches ou moteurs Search sont secondaires.

## Conditions d'arrêt

Un sprint conclut `NO GO` si une source ne possède pas de version, si la canonical ne peut être remplacée atomiquement, si un rebuild dépend d'un scan HTTP/global opportuniste, si la livraison peut perdre silencieusement un fait, ou si une règle métier apparaît dans le Store, Laravel ou l'Updater.

# ADR-1008 — Public Projection Store

- **Date :** 18 juillet 2026
- **Statut :** Accepted
- **Décision :** adoption d'un Public Projection Store PostgreSQL dédié
- **Nature :** cinquième sous-système de persistance, exclusivement dérivé et reconstruisible

## 1. Contexte

Le Public Web Adapter est certifié en isolation, mais `PublicListingQuery` ne possède aucune implémentation de production. L'audit 3.5C démontre qu'aucune source existante ne résout canonical courante→Listing et qu'une reconstruction synchrone complète est impossible sans scan global, faux adaptateur ou violation des frontières.

Cette décision autorise l'architecture fondamentale du nouveau sous-système. Elle n'autorise encore ni SQL, migration, store, binding runtime, Dispatcher, Queue ou Outbox : ces travaux sont découpés dans la roadmap et conservent leurs propres portes de certification.

## 2. Décision

APPART.SN adopte un **Public Projection Store PostgreSQL** propriétaire du read side public final.

Il matérialise des `PublicListingReadModel` reconstruisibles et expose une lecture indexée par canonical path courante. Il n'est ni un Repository d'Aggregate, ni une source de décision métier, Search ou SEO, ni un cache.

PostgreSQL est retenu parce que le besoin principal est une résolution exacte et fortement contrainte, avec remplacement canonical atomique, historique non réattribuable, concurrence observable et rollback transactionnel. La plateforme est déjà celle du système et son exploitation est certifiée.

## 3. Comparaison des stratégies

### A — PostgreSQL dédié : retenu

- **Ownership :** schéma Public Projection Store autonome ; faits métier conservés par leurs modules sources.
- **Reconstruction :** complète, partielle et par générations.
- **Fraîcheur :** alimentation événementielle durable, réconciliation batch.
- **Concurrence :** transactions locales, unicité canonical, contrôle de version et verrous ciblés.
- **Rollback :** transaction locale pour un update ; bascule de génération pour un rebuild.
- **Observabilité :** requêtes, verrous, retards, watermarks et divergences mesurables.
- **Coût :** nouveau schéma et nouvelles tables, mais aucune nouvelle technologie d'exploitation.
- **Évolutivité :** index exact initial ; réplication/partitionnement envisageables sans changer le contrat.
- **DDD :** excellente si le store reste dérivé, supprimable et interdit aux décisions métier.

### B — Elasticsearch / OpenSearch : rejeté comme store primaire

Le moteur est pertinent pour la recherche multi-critères, non pour le contrat courant exact canonical→read model. La visibilité après refresh est différée, le remplacement multi-document et la réservation historique sont plus difficiles à rendre atomiques, et une nouvelle plateforme opérationnelle serait introduite. Il pourra recevoir une projection secondaire Search ultérieure, jamais devenir l'autorité du Web Adapter.

### C — Redis : rejeté comme store primaire

Redis convient à un cache dérivé après existence d'une source durable. En faire l'unique store public compliquerait la durabilité, la restauration vérifiable, l'historique canonical et les garanties de concurrence. Il n'apporte aucun avantage décisif pour le volume ou la latence démontrés. Un cache futur reste optionnel et invalidable.

### D — reconstruction synchrone : rejetée

La résolution canonical→Listing, plusieurs Catalogs de production et la cohérence multi-sources manquent. Son coût et son nombre de lectures seraient payés par chaque requête. Elle déplacerait la gestion d'échec et de fraîcheur vers le chemin HTTP et encouragerait les scans ou accès inter-modules.

### E — pages statiques/object storage : rejeté comme autorité

Une livraison statique/CDN peut devenir un adaptateur secondaire. Elle ne résout pas seule l'atomicité canonical/historique, la fraîcheur et le contrat Laravel `PublicListingQuery`. Elle dépendrait donc encore d'un manifeste durable équivalent à un Projection Store.

## 4. Tableau de décision

| Critère | PostgreSQL | OpenSearch | Redis | Synchrone | Statique |
|---|---|---|---|---|---|
| Reconstruction vérifiable | forte | moyenne | faible | faible | moyenne |
| Fraîcheur maîtrisable | forte | moyenne | moyenne | instantanée mais incohérente multi-sources | moyenne |
| Canonical atomique | forte | faible/moyenne | moyenne | absente | faible |
| Historique durable | forte | moyenne | faible/moyenne | absent | moyenne |
| Rollback | transaction + génération | snapshots complexes | restauration/cache | aucun état dérivé | versions d'objets |
| Observabilité | forte et connue | forte mais nouvelle | forte mais nouvelle | distribuée | dépend du fournisseur |
| Simplicité d'exploitation | forte | faible | moyenne | faible côté application | moyenne |
| Coût initial | moyen | élevé | moyen | élevé en développement | moyen |
| DDD / frontières | forte | moyenne | faible si cache devient vérité | faible | moyenne |
| HTTP exact 200/404 | direct | soumis au refresh | direct mais durabilité faible | non faisable | nécessite manifeste |
| Impact PostgreSQL | nouveau schéma | faible | faible | lectures fortes sur sources | métadonnées nécessaires |
| Évolutivité | suffisante et progressive | excellente pour Search | excellente en cache | mauvaise | forte en diffusion |

## 5. Ownership sans ambiguïté

| Responsabilité | Propriétaire |
|---|---|
| État métier Listing/Property/Media | Aggregates de leurs modules |
| Canonical courante, historique, indexabilité, robots, JSON-LD | ContentSeo |
| Données de recherche et visibilité Search | SearchDiscovery |
| Projection publique matérialisée | Public Projection Store |
| Fraîcheur technique et application des versions | Public Projection Updater |
| Rebuild complet/partiel et générations | Public Projection Rebuilder |
| Suppression métier/retrait/noindex | décision des sources et ContentSeo |
| Application physique de retrait/tombstone | Public Projection Updater/Store |
| Décision de redirection historique | ContentSeo |
| Projection et résolution HTTP des redirections | futur port de redirection distinct |
| Binding, transport HTTP et rendu | Laravel |

Le Store reproduit la canonical pour l'indexation, mais ne la décide pas. Toute collision avec une décision ContentSeo est une divergence à arrêter et observer, jamais un arbitrage du Store.

## 6. Frontières

### Aggregates

Ils décident et persistent leur état officiel. Ils ne connaissent ni read model, canonical publique, HTML, Laravel, génération de projection ou schema public.

### ContentSeo

Il décide canonical courante/historique, traitement expiré, indexabilité, robots, breadcrumb, contenu SEO et JSON-LD. Il publie des faits/versionnements exploitables ; il ne rend pas la page et ne stocke pas les caractéristiques Search.

### SearchDiscovery

Il décide la visibilité et les faits Search reconstruisibles. Il ne devient jamais la source métier d'une page et ne décide aucune canonical.

### Public Projection Store

Il conserve exactement le read model final, son chemin courant, les références historiques nécessaires, son watermark et sa génération. Il applique uniquement des transitions techniques validées par version. Il n'appelle aucune policy métier.

### Laravel

Il lie `PublicListingQuery` à l'adaptateur PostgreSQL, transmet canonicalPath, retourne 200/404 et rend Blade. Le Service Provider ne contient que du wiring.

## 7. Stratégie de fraîcheur : hybride

### Flux principal événementiel

Après commit des sources, des faits durables déclenchent une mise à jour idempotente par Listing. L'infrastructure de livraison durable sera décidée et certifiée dans un sprint séparé ; aucun effet volatile en mémoire ne peut constituer la garantie.

Chaque update transporte une identité idempotente et les versions/révisions observées. Le Store refuse une régression et accepte une répétition strictement identique sans double effet.

### Batch de réconciliation

Un batch périodique compare les watermarks du Store aux versions sources et programme les divergences. Il ne remplace pas le flux principal et ne décide aucune valeur. Il couvre perte opérationnelle, dérive, remise en service et contrôle exhaustif.

### Politique fail-safe

Une source obligatoire absente, incohérente ou non versionnable empêche la promotion de la nouvelle projection. Une décision explicite de retrait/noindex déjà produite est appliquée ; l'Updater ne transforme jamais seul une erreur technique en décision SEO.

## 8. Watermark et versions

Le watermark est un vecteur technique par Listing, pas une horloge murale unique. Il inclut au minimum les versions/révisions Listing, Property, Media, Search, ContentSeo, Geography publique et ressource média publique.

Conditions :

- aucune source obligatoire sans version stable ;
- égalité du vecteur = idempotence ;
- composante inférieure = update obsolète rejeté ;
- composantes concurrentes/incomparables = divergence à reconstruire, jamais last-write-wins ;
- `decidedAt` et l'heure serveur ne remplacent pas les versions.

Les contrats Geography et média public devront exposer une révision avant l'activation runtime.

## 9. Rebuild

### Rebuild partiel

Une canonical ou un `ListingId` interne permet de reconstruire un seul read model depuis les sources autorisées. L'écriture conditionnelle compare le watermark actuel. Le `ListingId` reste interne et ne devient jamais une identité HTTP.

### Rebuild complet

1. créer une génération inactive N+1 ;
2. capturer un high-watermark durable du flux de changements ;
3. reconstruire toutes les annonces éligibles dans N+1 ;
4. valider cardinalité, unicité canonical, checksums et watermarks ;
5. rejouer les changements postérieurs au high-watermark ;
6. atteindre le retard zéro défini ;
7. basculer atomiquement le pointeur de génération active ;
8. conserver N pendant une fenêtre de rollback ;
9. purger N uniquement après validation, sans libérer les réservations canonical ContentSeo.

Un scan du Projection Store est autorisé pendant son propre rebuild. Un scan global des repositories métier sur le chemin HTTP reste interdit. L'énumération de reconstruction devra passer par des ports de source explicitement conçus pour le rebuild.

### Rollback

Un update unitaire rollbacke sa transaction locale. Un rebuild rollbacke par retour atomique vers la génération précédente, puis réapplique le flux depuis son watermark. Aucun rollback ne réécrit un Aggregate ni ne réattribue une ancienne canonical.

## 10. Concurrence

### Remplacement canonical

Une transaction locale verrouille l'entrée Listing de projection, vérifie le watermark, réserve la nouvelle canonical dans la projection, matérialise l'ancienne comme historique non-current et remplace le payload. L'unicité du chemin current est contrainte. La décision et la réservation normatives restent ContentSeo.

### Suppression et retrait

Retrait, expiration supprimée et suppression physique sont distingués. L'Updater applique un tombstone/version pour empêcher la résurrection par un événement ancien. La suppression physique éventuelle suit une politique de rétention et ne supprime pas l'historique canonical normatif.

### Updates concurrents

Un seul update gagne pour un watermark supérieur compatible. Les doublons sont idempotents ; les régressions sont rejetées ; les vecteurs incomparables déclenchent une reconstruction ciblée.

### Rebuild parallèle

Les générations sont isolées. Les requêtes lisent uniquement la génération active. Les événements alimentent l'active et sont rejoués dans la candidate depuis le high-watermark ; aucune bascule n'a lieu tant que la candidate n'est pas rattrapée et validée.

## 11. Observabilité

Mesures obligatoires : génération active, high-watermark, lag maximal et par source, âge de la projection, updates acceptés/rejetés/dupliqués, divergences, collisions canonical, tombstones, durée et progression de rebuild, bascules, rollbacks et erreurs HTTP imputables au Store.

Les logs n'exposent pas de contenu personnel ou de payload JSON-LD complet. Des checksums déterministes permettent la comparaison.

## 12. Conséquences

### Positives

- implémentation honnête de `PublicListingQuery` ;
- HTTP stable sans reconstruction ;
- canonical courante indexée et changement atomique ;
- rebuild et rollback sans toucher aux sources ;
- même moteur d'exploitation que les persistances certifiées ;
- aucune modification des quatre repositories.

### Négatives

- cinquième schéma persistant et nouvelles procédures opérationnelles ;
- cohérence différée explicitement gérée ;
- nécessité future d'une livraison événementielle durable ;
- contrats de révision Geography/média public à compléter ;
- duplication technique assumée du read model.

## 13. Alternatives et réversibilité

OpenSearch, Redis ou CDN pourront être ajoutés comme projections/caches secondaires alimentés depuis le même pipeline. Ils ne remplacent pas le Store PostgreSQL sans nouvel ADR.

Le Store PostgreSQL est supprimable et reconstruisible. Son retrait consiste à arrêter les updates, retirer le binding, conserver les sources métier, exporter les métriques nécessaires puis supprimer le schéma lors d'un sprint autorisé.

## 14. Critères d'acceptation de l'ADR

Cette ADR est `Accepted` parce qu'elle fixe : plateforme, ownership, frontières, modèle de fraîcheur, watermark, rebuild, rollback, concurrence, redirections et découpage d'exécution. Les futurs sprints doivent démontrer l'implémentation sans rouvrir ces choix fondamentaux. Un échec technique d'un sprint bloque son GO ; il ne change pas automatiquement l'ADR.

Tout changement de moteur primaire, d'ownership canonical, de stratégie non événementielle ou d'usage du Store comme source métier exige un nouvel ADR.

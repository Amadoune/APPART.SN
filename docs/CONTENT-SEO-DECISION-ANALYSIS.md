# Content SEO Decision Analysis — Sprint 3.3A

## Synthèse

ContentSeo possédait déjà la majorité des invariants SEO, mais ils étaient répartis entre `SeoGenerationPolicy`, `SeoMaterial` et l'Aggregate `SeoProjection`. Il manquait une décision immutable réunissant tous les faits nécessaires à une future projection publique, ainsi que deux sources explicites pour la géographie et le média publics.

La fondation retenue complète le Domain existant; elle ne remplace ni `SeoProjection`, ni son Registry, ni ses événements.

## Audit de l'existant

### ListingSeoSource

Existant : `ListingId`, état SEO du Listing, headline, description, canonical path et révision de source.

Incomplet : absence d'état `Expired`, dates de publication/expiration et directive explicite de conservation d'une page expirée. Ces éléments sont ajoutés comme données de source; `ListingId` ne sert jamais de slug ou de canonical de secours.

### SeoGenerationPolicy et SeoMaterial

Existant : décision active/removed, validation du titre et de la meta description, canonical APPART.SN, robots, sitemap, priorité, structured data et cohérence des révisions.

Incomplet : la policy supposait ville et visibilité Search déjà disponibles, sans média public, breadcrumb, dates, traitement d'expiration ni décision autonome transportable. Elle reste intacte pour préserver le comportement certifié. La nouvelle `ListingSeoDecisionPolicy` porte la décision complète du Sprint 3.3A.

### CanonicalHistoryPolicy

Existant et réutilisé : une canonical courante unique dans l'historique, interdiction de réutiliser une ancienne canonical, réservation des anciennes URL pour redirection directe vers la nouvelle canonical.

Complément : la policy de décision initialise un historique vide, conserve strictement un historique lorsque la canonical est inchangée et refuse un historique sans entrée courante valide ou antidaté.

### SeoProjectionRegistry

Existant : port mutable pour l'ancien Aggregate `SeoProjection`; le Fake démontre unicité globale de Listing et canonical, concurrence optimiste et réservation de tout l'historique.

Décision : aucune modification. `ListingSeoDecision` n'est pas persistée dans ce sprint. L'unicité globale reste la responsabilité du Registry existant lorsqu'une décision alimente ultérieurement l'Aggregate propriétaire.

### Événements

Existant : `SeoProjectionGenerated`, `SeoProjectionUpdated`, `CanonicalChanged`, `SitemapChanged`, `SeoFailSafeApplied`.

Décision : aucun nouvel événement. Une décision pure et non persistée ne constitue pas encore un fait d'Aggregate. Les événements existants restent produits uniquement quand `SeoProjection` acquiert un changement.

### Value Objects

Réutilisés : `CanonicalUrl`, `SeoTitle`, `MetaDescription`, `RobotsPolicy`, `StructuredData`, états et révisions SEO.

Ajoutés : `SeoIndexability`, `ExpiredListingTreatment`, `PublicMediaUrl`, puis `SeoPageTreatment` au Sprint 3.3B. Ce dernier distingue explicitement une page noindex conservée d'une page supprimée, distinction impossible avec la seule indexabilité. Ces types rendent explicites des décisions auparavant absentes sans importer un modèle Media externe.

## Ownership

### Domain ContentSeo

- validation du contenu SEO minimal;
- construction de la canonical depuis un canonical path explicitement fourni;
- stabilité et évolution de l'historique canonical;
- décision indexable/non indexable;
- cohérence robots/indexabilité;
- traitement d'une annonce expirée;
- composition du breadcrumb à partir de destinations publiques fournies;
- structured data composée uniquement de faits publics;
- retrait prudent lorsqu'une source obligatoire manque.

### Référentiels externes lus par ports locaux

- Listing : état SEO, headline, description, canonical path et dates;
- Search : visibilité publique acquise;
- Property : disponibilité et type public;
- géographie publique : localité et ancêtres breadcrumb déjà publiables;
- média public : URL HTTPS d'une ressource autorisée.

ContentSeo ne connaît aucun Aggregate étranger. Tous les échanges utilisent des modèles source locaux.

## Décision cible

`ListingSeoDecision` est un modèle métier `readonly`, construit seulement par sa factory validante et contenant :

- ListingId;
- canonical courante et historique;
- headline et description validés, éventuellement absents pour une décision non indexable;
- indexabilité et robots cohérents;
- conservation ou suppression explicite de la page;
- breadcrumb public;
- structured data;
- ressource média publique éventuelle;
- traitement d'expiration;
- dates de publication, expiration et décision.

Une décision indexable exige toutes les données publiques. Une décision non indexable reste explicite et porte obligatoirement `noindex,follow`.

## Ce qui ne doit jamais être déduit

- aucun slug ou canonical depuis `ListingId`;
- aucun nom de ville/quartier depuis un identifiant géographique;
- aucune URL média depuis un MediaId;
- aucun breadcrumb vers une destination non fournie comme publique;
- aucune indexabilité depuis le seul état `Published`;
- aucune conservation d'une annonce expirée sans directive explicite;
- aucun headline ou description synthétique depuis le type Property.

# Public Web Adapter — analyse

## Contrats disponibles

`PublicListingQuery` définit le contrat permettant de résoudre un `PublicListingReadModel` immutable depuis le canonical path courant décidé par ContentSeo. Aucune implémentation ni aucun binding de production de ce port n'existe actuellement. Le read model contient déjà toutes les valeurs affichables ainsi que les représentations SEO finales : canonical URL, directive robots HTML, JSON-LD public et breadcrumb.

Le Controller et Blade n'ont donc aucune information à rechercher, reconstruire ou décider.

## Responsabilités

### Controller

Le Controller reçoit exactement le paramètre `canonicalPath`, appelle une seule fois `PublicListingQuery::findByCanonicalPath()` puis :

- lève une réponse 404 lorsque le port retourne `null` ;
- retourne la vue `public-listing` avec le read model inchangé lorsqu'il est présent.

Sa seule dépendance applicative injectée est `PublicListingQuery`. Il ne connaît ni Domain, Aggregate, Repository, Builder, Policy, Catalog, projection Search, projection SEO ou `ListingSeoDecision`.

### Vue

Blade lit exclusivement le `PublicListingReadModel` reçu : headline, description, média public, caractéristiques Property et breadcrumb. Elle injecte directement :

- `canonicalUrl` dans `<link rel="canonical">` ;
- `htmlRobotsDirective` dans `<meta name="robots">` ;
- `publicJsonLd` dans `<script type="application/ld+json">` lorsqu'il existe.

La vue n'encode pas le JSON, ne traduit pas robots, ne construit pas le breadcrumb et ne recalcule pas le canonical. L'échappement HTML standard reste une responsabilité de rendu Laravel ; le JSON-LD, déjà validé, est émis sans recomposition.

### Laravel

Lorsque `PublicListingQuery` sera lié à une implémentation de production autorisée, Laravel devra assurer uniquement :

- la reconnaissance de la route publique ;
- l'injection du port dans le Controller ;
- la conversion de l'absence en réponse HTTP 404 ;
- la réponse 200 et le rendu Blade.

La route accepte la forme déjà démontrée `annonces/{canonical}` et transmet exactement la valeur capturée. Elle rejette un `ListingId` isolé. La route ne peut pas distinguer syntaxiquement un ancien canonical d'un canonical courant : cette distinction appartient exclusivement à `PublicListingQuery`, dont le contrat interdit de servir l'historique comme page courante. Aucun fallback n'est présent dans la route ou le Controller.

## Limite d'infrastructure

Ce sprint ne fournit aucune implémentation persistante de `PublicListingQuery`. Les tests injectent le harness en mémoire certifié en 3.5A. Aucun Service Provider, Repository, SQL, migration, cache, table ou nouvelle persistance n'est ajouté.

## Vérification runtime hors harness

Le bootstrap Laravel réel, exécuté sans binding de test, produit les résultats suivants :

- résolution du conteneur : `BindingResolutionException`, cible `PublicListingQuery` non instanciable ;
- requête `GET /annonces/appartement-moderne-dakar` : HTTP 500, et non 200/404 métier ;
- capture de route : `canonicalPath = annonces/appartement-moderne-dakar`, sans concaténation ni normalisation ;
- chemin constitué uniquement d'un `ListingId` : aucune route correspondante ;
- un chemin de forme `annonces/ancienne-url` correspond syntaxiquement à la route, mais ne pourra être rejeté comme historique que par l'implémentation future du query.

Le runtime Laravel reste donc bloqué par l'absence de binding `PublicListingQuery`. Les réponses 200 et 404 sont certifiées uniquement en isolation avec le harness de test.

## Conclusion d'audit

La frontière HTTP est architecturalement terminale et passive. Toute tentative d'importer les modules Domain, les repositories ou les projections intermédiaires dans le Controller constituerait une violation d'architecture couverte par les tests dédiés.

Verdict corrigé : **GO avec réserve — Public Web Adapter Contract/Test Foundation**. L'adaptateur est certifié en isolation, non encore exploitable en production.

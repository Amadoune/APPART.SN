# HTTP Runtime Certification Specification

La route publique accepte exclusivement `annonces/{slug}` comme canonical path. Le contrôleur :

1. transmet exactement ce chemin à `PublicListingQuery` ;
2. rend 404 lorsque le Query retourne `null` ;
3. rend la vue avec le ReadModel inchangé lorsqu'il existe ;
4. rend 503 lorsque le Projection Store ne peut fournir un résultat fiable.

La vue copie `canonicalUrl`, `htmlRobotsDirective`, `publicJsonLd`, breadcrumb, contenu et média du
ReadModel. Elle ne calcule ni SEO, ni JSON-LD, ni canonical.

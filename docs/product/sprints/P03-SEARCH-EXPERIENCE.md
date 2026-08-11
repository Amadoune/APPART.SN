# P03 — Search Experience Implementation

## Expérience livrée

La Home conserve son design certifié et son panneau devient un formulaire GET réel. Le parcours public est désormais :

`/` → `/recherche?transaction=sale&city=Dakar&propertyType=apartment` → carte réelle → `/annonces/p03-appartement-a-vendre-dakar`.

Les libellés UX sont traduits mécaniquement : Acheter → `sale`, Louer → `rent`, Appartement → `apartment`. Les paramètres restent visibles, lisibles et partageables dans l'URL.

## Résultats

La page `/recherche` consomme exclusivement `PublicSearchResultsReaderV1` via `PublicSearchResultsRequest`. Elle n'accède ni à Authoring, ni aux Aggregates, ni à PostgreSQL directement.

Les cartes affichent uniquement les champs publics disponibles : image, headline, transaction, ville, type, surface, pièces et canonicalPath. Faute de prix public autoritatif, elles indiquent « Prix non communiqué ».

## États et pagination

- `AVAILABLE` : cartes publiques et nombre d'éléments de la page.
- `EMPTY` : message utile et retour vers les critères.
- `CORRUPTED` et `DEPENDENCY_UNAVAILABLE` : message neutre sans détail interne.
- `nextCursor` : lien « Voir plus de résultats » conservant les filtres et envoyant le curseur au Reader.

Le filtrage demeure exécuté côté PostgreSQL avant ordre et pagination ; aucun filtrage navigateur n'est ajouté.

## Accessibilité et responsive

Les contrôles possèdent des labels, les boutons ont un type explicite, les liens de cartes ont un nom accessible, la hiérarchie H1/H2/H3 est cohérente et le focus visible du Design Language est conservé.

Les largeurs 1440, 768 et 390 px ont été contrôlées sans débordement horizontal.

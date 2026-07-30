# Public Page Adapter — Sprint 3.5

## Statut

`NO GO — adaptateur non créé`.

La Vue et le Controller ne peuvent pas encore consommer le Read Model sans introduire une transformation SEO ou une dépendance de résolution non autorisée.

## Responsabilité cible

Le futur adaptateur devra :

- recevoir un `PublicListingReadModel` ou son absence depuis une orchestration autorisée;
- retourner 404 lorsque le modèle est absent;
- transmettre le modèle inchangé à la Vue;
- rendre headline, description, média public, caractéristiques et breadcrumb;
- injecter canonical, robots HTML et JSON-LD déjà finalisés en amont.

Il ne devra jamais consulter un Aggregate, Repository ou Catalog, reconstruire une projection, choisir une canonical, traduire une policy robots ou composer des structured data.

## Dépendances manquantes

1. Valeur robots directement conforme au contrat HTML.
2. Structured data JSON-LD public final et déterministe.
3. Mécanisme applicatif autorisé fournissant le Read Model à une requête HTTP.

## Reconstruction

La page ne sera pas une nouvelle source de vérité. À chaque réponse, elle consommera un Read Model déjà reconstruit depuis Search et SEO. La suppression du Read Model devra conduire à 404 sans conservation locale, cache métier ou fallback.

## Limites et exclusions maintenues

- aucun Controller, Route ou Blade factice;
- aucun ViewModel transformant robots ou JSON-LD;
- aucun Repository, SQL, migration ou stockage;
- aucune API REST;
- aucun Dispatcher, Outbox ou Unit of Work;
- aucune modification des Aggregates ou repositories PostgreSQL.

## Condition de reprise

Le Sprint 3.5 pourra reprendre lorsque les trois dépendances manquantes auront un contrat explicite. Les tests devront alors couvrir réponse 200, 404, indexable, noindex, canonical, robots, JSON-LD, absence de dépendance métier et rendu exact des valeurs déjà décidées.

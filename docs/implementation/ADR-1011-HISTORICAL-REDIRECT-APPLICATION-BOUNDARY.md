# ADR-1011 — Historical Redirect Application Boundary

## Statut

Accepté pour le Sprint 3.10A.

## Décision

La résolution des canonicals historiques est un port applicatif autonome possédé par `ContentSeo`. Son entrée est `HistoricalCanonical`, et non `string`, afin d'interdire structurellement l'usage d'une canonical courante. Sa sortie fermée expose seulement une canonical publique typée.

`PublicListingQuery` reste Current-only. Le Web ne calcule ni ne suit une destination et n'accède à aucun Aggregate ou Repository métier.

## Conséquences

La persistance future devra adapter ses données à sept résultats explicites. Les chaînes, boucles, ambiguïtés et corruptions deviennent des résultats observables, pas des exceptions d'infrastructure ou des heuristiques HTTP. Une incohérence construite en mémoire, telle qu'un résultat `Resolved` pointant vers sa source, est rejetée par une exception contractuelle.

## Alternatives écartées

- Étendre `PublicListingQuery` mélangerait identité courante et historique.
- Retourner une URL nullable rendrait les anomalies indiscernables.
- Exposer un `ListingId` transférerait une décision interne au Web.
- Suivre les redirections reconstruirait dynamiquement une chaîne interdite.

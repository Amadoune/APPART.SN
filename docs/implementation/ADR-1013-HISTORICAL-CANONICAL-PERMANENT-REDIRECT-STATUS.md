# ADR-1013 — Historical Canonical Permanent Redirect Status

## Statut

Accepté pour le Sprint 3.10D.

## Décision

Une résolution Historical Redirect valide produit **HTTP 301 Moved Permanently**.

## Justification

Le changement représente le remplacement durable d'une canonical publique et non une indisponibilité temporaire. Le 301 est le statut historique et largement reconnu par les agents SEO pour transférer une ancienne URL vers sa canonical courante. La route publique est limitée à GET; la préservation de méthode apportée par 308 n'est donc pas nécessaire à ce contrat.

## Conséquences

Le statut est explicite et testé. La cible provient exclusivement de `HistoricalRedirectTarget`. Aucun autre résultat n'émet une redirection ou un en-tête `Location`.

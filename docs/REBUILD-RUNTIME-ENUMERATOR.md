# Rebuild Runtime Enumerator

## Full

Lecture PostgreSQL ordonnée par UUID, bornée à `limit + 1`. Le dernier identifiant rendu devient le curseur keyset de la page suivante.

## Listings

La liste explicite est dédupliquée par le scope, triée de façon stable puis paginée. Une identité absente de PostgreSQL reste énumérée afin que la Candidate Factory et le rapport du Rebuilder la classent explicitement comme manquante.

## Range

Les bornes UUID sont inclusives. Le checkpoint ajoute uniquement une borne basse stricte à l'intérieur de la plage. Aucune extension temporelle ou lexicale n'est appliquée.

## Sécurité

Le reader est strictement read-only. Il n'effectue ni scan HTTP, ni chargement d'Aggregate, ni construction de projection.

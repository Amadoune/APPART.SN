# Canonical Ordering

La politique existante déduplique par `key|value|source`, applique les cardinalités puis ordonne canoniquement les résultats avec `ksort`.

Cet ordre garantit un résultat déterministe une fois les facettes fournies. Il ne définit ni leur sélection ni l'ordre de classement des annonces.

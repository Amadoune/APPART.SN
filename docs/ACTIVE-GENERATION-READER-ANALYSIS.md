# Sprint 3.8F — Analyse Active Generation Reader

## Décision

Le besoin Runtime est une lecture spécialisée de l'identité actuellement Active, et non un accès au Manager de générations. Le port `ActiveGenerationReader` retourne un résultat fermé sans exposer PDO, le schéma PostgreSQL ou les opérations de transition.

Le schéma 3.6D suffit. L'index partiel unique `public_projection_one_active_generation` cible déjà `state = 'active'`; aucune migration ni index supplémentaire ne sont justifiés.

## Séparation

- le contrat et les résultats appartiennent à Application ;
- le mapper transforme uniquement l'identité et l'état persistés ;
- l'adaptateur PostgreSQL exécute une seule lecture ;
- le Generation Manager et le Validator restent seuls propriétaires des transitions.

## Détection d'incohérence

La requête est ordonnée et bornée à deux résultats. Zéro ligne produit `Missing`, une ligne Active valide produit `Found`, plusieurs lignes ou un mapping invalide produisent `Corrupted`. Aucun choix arbitraire n'est effectué.

## Non-mutation

Le reader ne contient aucun `INSERT`, `UPDATE`, `DELETE`, DDL, verrou d'écriture, activation, promotion ou rollback. Il n'utilise ni horloge ni fallback.

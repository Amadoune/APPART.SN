# Ownership de `decisionAt`

## Propriétaire

Le propriétaire officiel est `ContentSeoSourceDecision`.

La valeur est décidée en même temps que les sources Content/SEO et enregistrée dans leur snapshot durable. Elle représente le temps de la décision originale, pas une observation technique.

## Propriétaires refusés

- le Runtime ne décide rien et ne peut utiliser son horloge ;
- le Rebuild reconstruit sans redater ;
- le Replay redélivre sans redater ;
- le Projection Store conserve le résultat public, pas l'origine temporelle de la décision ;
- PostgreSQL `updated_at` est une métadonnée technique sans valeur métier.

## Conséquence

À snapshot identique, `decisionAt` reste strictement identique, indépendamment de la machine, du fuseau Runtime, de la date de lecture ou du nombre de replays.

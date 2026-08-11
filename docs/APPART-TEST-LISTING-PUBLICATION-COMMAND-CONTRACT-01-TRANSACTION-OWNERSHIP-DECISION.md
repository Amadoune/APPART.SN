# APPART.TEST LISTING PUBLICATION COMMAND CONTRACT 01 — Transaction / Ownership Decision

## Comparaison

| Option | Owner | Transaction | Risque | Verdict |
|---|---|---|---|---|
| composition Application workflow puis use case Aggregate | Listing Lifecycle | locale, même PDO envisageable | impossible sans command complet | BLOCKED |
| handoff workflow vers owner Listing | Listing Lifecycle | cohérence différée | payload actuel incomplet ; convergence immédiate non prouvée | BLOCKED |
| composition existante | aucun mécanisme trouvé | n/a | workflow et Registry séparés | MISSING |

## Décision

L'option de composition transactionnelle est la seule compatible avec le critère d'alignement immédiat, mais elle ne doit pas être implémentée avant qualification des sources manquantes. Le workflow conserverait la décision et les use cases Aggregate conserveraient la mutation ; le composite ne calculerait rien.

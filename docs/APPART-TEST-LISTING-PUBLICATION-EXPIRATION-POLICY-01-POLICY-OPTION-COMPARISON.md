# Policy Option Comparison

| Option | Autorité disponible | Avantage | Défaut bloquant | Décision |
|---|---:|---|---|---|
| A. Policy Domain Listing Lifecycle | non | owner correct, règle centralisée | durée absente | non implémentable |
| B. Policy Application configurée | non | déploiement configurable | aucune valeur/configuration métier approuvée | non implémentable |
| C. Date portée par command owner-scoped | non | date explicite avant mutation | déplace la décision vers un appelant non qualifié | rejetée en l'état |
| D. `publishedAt + durée` | durée absente | mécanique et reproductible une fois la durée fixée | opérande autoritatif manquant | meilleur mécanisme futur, actuellement bloqué |
| E. Mécanisme existant | non | réutilisation | aucun mécanisme trouvé | indisponible |

Aucune option ne satisfait les critères sans une décision préalable sur la durée et ses variations. Une constante 30/60/90/365 jours serait une nouvelle règle métier arbitraire.

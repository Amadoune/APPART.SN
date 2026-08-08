# Phase 5.8B — Reliability & Operations — Owner Reader Risk Register

| Risque | Contrôle | Résiduel |
|---|---|---|
| décision non homonyme | enum `from` sur décision Found | faible |
| fallback structurel | match exhaustif sans default | faible |
| agrégation des streams | un Reader et un appel par stream | faible |
| lecture Runtime ou Infrastructure | dépendance unique au port OwnerSource | faible |
| mutation de observedAt | recopie du même Value Object | faible |
| alias ambigu | un alias public par Reader concret | faible |
| modification migration 088 | preuve SHA-256 Architecture | faible |
| ouverture aval implicite | interdiction normative | faible |

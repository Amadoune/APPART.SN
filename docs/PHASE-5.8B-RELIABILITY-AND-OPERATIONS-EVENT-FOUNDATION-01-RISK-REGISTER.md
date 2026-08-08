# Phase 5.8B — Reliability & Operations — Event Risk Register

| Risque | Contrôle | Résiduel |
|---|---|---|
| statut perdu ou transformé | enum `from` homonyme | faible |
| observedAt réécrit | recopie directe du Result | faible |
| payload enrichi | deux propriétés et canonical fermé | faible |
| agrégation de Readers | une Factory par Reader | faible |
| fallback implicite | aucun default | faible |
| dépendance Infrastructure | preuve Architecture | faible |
| ouverture Delivery implicite | interdiction normative | faible |
| modification migration 088 | preuve SHA-256 | faible |

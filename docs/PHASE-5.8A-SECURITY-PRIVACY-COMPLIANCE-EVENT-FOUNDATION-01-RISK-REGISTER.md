# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| Event créé sans Reader disponible | cinq sources exclusives et trois familles interdites vérifiées | faible |
| payload sensible | forme fermée à status et observedAt | faible |
| perte de bijection | enum Event homonyme et conversion `from` exhaustive | faible |
| fallback ou agrégation | une Factory par Reader, aucun état par défaut | faible |
| transport implicite | absence de Provider, Routing, Delivery et Outbox | faible |
| modification de Persistence | empreintes de la migration 086 protégées | faible |

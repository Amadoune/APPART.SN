# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| Reader créé sans source owner-scoped | interdiction et preuve Architecture pour les trois Readers incomplets | faible |
| contournement de l'Owner Source | dépendance exclusive au port | faible |
| exposition de SubjectKey ou RevisionState | Results publics limités à status et observedAt | faible |
| fallback ou agrégation | Policy fermée, conversion homonyme par enum | faible |
| alias ambigu ou multiple | cinq aliases publics et un alias Policy uniques | faible |
| ouverture implicite d'une Foundation ultérieure | absence de Runtime Read, HTTP, Event, Delivery et Outbox | faible |

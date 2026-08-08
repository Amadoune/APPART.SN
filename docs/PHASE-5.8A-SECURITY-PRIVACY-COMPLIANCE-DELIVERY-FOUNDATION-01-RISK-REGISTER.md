# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| Delivery créée sans Event certifié | dépendances limitées aux cinq EventV1 autorisés | faible |
| type Event transformé | même enum EventType portée par la Delivery | faible |
| payload enrichi | forme fermée à status et observedAt | faible |
| perte de bijection | Status homonyme construit par `from` | faible |
| famille interdite créée | preuve Architecture sur les trois préfixes interdits | faible |
| ouverture implicite Outbox/Transport | absence de Provider, Binding, Routing et Consumer | faible |

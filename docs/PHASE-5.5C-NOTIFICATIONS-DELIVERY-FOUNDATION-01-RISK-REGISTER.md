# Notifications Delivery Risk Register

| Risque | Maîtrise |
|---|---|
| Décision ajoutée | Conversion homonyme exhaustive |
| Perte du type Event | Type propagé dans le payload |
| Perte temporelle | observedAt recopié sans transformation |
| Source secondaire | Signature limitée à Event V1 |
| Couplage transport | Aucun Provider, Transport ou Routing |
| Ouverture Outbox | Aucun composant Outbox |

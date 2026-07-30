# Reservation Lifecycle Outbox Compatibility Analysis

## Décision

Les onze événements certifiés de Reservation Lifecycle rejoignent le pipeline générique Public Projection Delivery sans nouvelle infrastructure. Le catalogue reconnaît leur payload, le mapper PostgreSQL restaure celui-ci, le Consumer remet une enveloppe certifiée au routeur, puis applique la politique de consommation 4.3G-R1.

## Frontières

- aucun événement n'est produit ;
- aucune écriture Outbox n'est orchestrée ;
- aucun workflow ou diagnostic métier n'est consulté ;
- aucun Worker spécifique n'est créé ;
- Runtime Health reste inchangé à 27 capacités.

L'owner `reservation_lifecycle` et les structures physiques proviennent exclusivement de 4.3H-R1.

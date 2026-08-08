# Administration Console Delivery Foundation — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Source autre qu'un Event V1 | Signature de Factory limitée à son Event V1 |
| R2 | Plusieurs Deliveries pour un Event | Retour unique par Factory |
| R3 | Transformation du type Event | Copie de l'instance de type dans la Delivery |
| R4 | Mapping de statut incomplet | `match` exhaustif sans `default` |
| R5 | Réduction ou décision nouvelle | Catalogues homonymes de même cardinalité |
| R6 | Transformation de `observedAt` | Copie directe de la valeur Event |
| R7 | Type ajouté au payload | Type porté séparément par DeliveryV1 |
| R8 | PII, clé sujet, révision ou Runtime | Payload limité structurellement à deux propriétés |
| R9 | Couplage Provider, HTTP, Runtime ou Infrastructure | Interdiction et test d'architecture |
| R10 | Introduction prématurée d'Outbox ou Transport | Aucun composant ni dépendance associé |
| R11 | Altération de migration 082 | Contrôle des deux empreintes SHA-256 |

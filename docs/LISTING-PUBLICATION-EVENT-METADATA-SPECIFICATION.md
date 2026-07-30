# Listing Publication Event Metadata Specification

## Instants obligatoires

`occurredAt` et `recordedAt` sont des instants UTC canoniques au format `YYYY-MM-DDTHH:MM:SS.ffffffZ`. Ils sont obligatoires et immuables. `recordedAt` ne peut pas précéder `occurredAt`.

## Provenance certifiée

La fondation ne possède aucune horloge. Les deux instants devront être fournis explicitement par une future enveloppe d'exécution événementielle :

- `occurredAt` est l'instant métier déclaré pour la demande de transition ;
- `recordedAt` est l'instant d'enregistrement déclaré par cette même enveloppe transactionnelle.

L'enveloppe devient une entrée du futur use case 4.1E. Elle doit être rejouée à l'identique lors d'une nouvelle tentative. Ni l'orchestrateur 4.1D, ni le catalogue, ni l'adaptateur Outbox ne pourront générer ou remplacer ces valeurs.

Le déterminisme est défini sur l'entrée complète : identité, état, action, version et métadonnées explicites identiques produisent un événement strictement identique.

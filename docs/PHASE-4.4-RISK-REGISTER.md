# Phase 4.4 — Lead Lifecycle Risk Register

| ID | Risque | Niveau | Prévention planifiée |
|---|---|---|---|
| L-01 | `Lead` existant contient déjà règles et événements alors que le futur workflow doit devenir référence unique | élevé | 4.4A établit une matrice contractuelle sans modifier l'Aggregate ; ADR sur la coexistence avant persistance |
| L-02 | contactabilité Listing reconstruite depuis un Aggregate | élevé | port `ListingCatalog`, source certifiée en 4.4C-S1, aucune lecture Repository intermodule |
| L-03 | éligibilité Advertiser absente du Runtime | élevé | port `AdvertiserCatalog` et source durable explicitement composés en 4.4C-S1 |
| L-04 | déduplication glissante dépend d'un timestamp | élevé | instant fourni explicitement ; stratégie et fenêtre figées dans 4.4B, aucune horloge implicite |
| L-05 | owner PostgreSQL absent | élevé | migration journal propriétaire dans 4.4B |
| L-06 | owner Outbox absent et migration 005 gelée | élevé | sprint planifié 4.4H-R1 avec migration additive |
| L-07 | port routeur `void` ou résultat incomplet | élevé | résultat fermé défini dès 4.4F, avant implémentation 4.4G |
| L-08 | politique d'acquittement choisie dans le Consumer | élevé | 4.4G-R1 certifie la matrice routage → consommation |
| L-09 | sink silencieux | critique | seul `Stored`/`AlreadyStored` peut devenir acquittement ; Inbox durable obligatoire |
| L-10 | événement Domain existant incompatible avec enveloppe canonique | moyen | catalogue applicatif versionné en 4.4E ; aucune sérialisation directe implicite des objets Domain |
| L-11 | transaction journal + Outbox distincte | critique | port atomique Lead et réutilisation exclusive de la transaction générique en 4.4I |
| L-12 | données personnelles exposées dans Delivery/Inbox | critique | payload événementiel minimal ; aucun message, coordonnées ou consent proof dans le transport public générique sans ADR explicite |
| L-13 | dépendance circulaire avec Listing/Professionals | élevé | dépendances unidirectionnelles par snapshots/preuves ; aucun callback vers Lead depuis ces modules |
| L-14 | Runtime Health provoque une lecture | moyen | inspection de binding/constructibilité uniquement |

## Risque résiduel principal

La donnée personnelle rend Lead plus sensible que les trois lifecycles précédents. Le catalogue d'événements devra privilégier identités, statut, version et codes fermés. Toute inclusion de coordonnées ou de texte libre imposera un sprint de confidentialité distinct et ne peut être supposée dans 4.4E.

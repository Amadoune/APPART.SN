# Place Lifecycle Outbox Owner — Matrice des risques

| Risque | Niveau sans décision | Gate préventif |
|---|---|---|
| réutilisation d'un schéma historique | élevé | owner `geography` obligatoire |
| table partagée entre modules | élevé | quatre tables dans `geography` uniquement |
| collision catalogue | élevé | namespace `place.lifecycle.*` fermé |
| confusion `eventId`/`messageId`/id Outbox | élevé | identités séparées et mapper générique |
| Writer ou Reader spécialisé | moyen | réutilisation générique obligatoire |
| Worker dédié concurrent | élevé | Worker générique exclusif |
| double enregistrement Consumer | moyen | une entrée par type V1 |
| migration redondante | élevé | réservation exclusive de la migration 040 |
| extension Runtime Health ambiguë | moyen | capacité réservée unique |

Toute collision constatée produit un NO GO de 4.8J.

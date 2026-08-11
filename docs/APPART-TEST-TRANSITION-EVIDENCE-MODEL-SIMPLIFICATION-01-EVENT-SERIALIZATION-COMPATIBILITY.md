# Event / Serialization Compatibility

Les Events Listing conservent le même accesseur `reason()`, désormais nullable. Aucun type d'événement, trigger, origin, acteur ou timestamp n'est modifié.

- événement historique avec reason : même `TransitionReason`, même valeur ;
- nouvel événement cible sans reason : `null` explicite ;
- révision historique : relue sans transformation ;
- révision cible : rejouée avec `null` ;
- projections, Outbox et transports : aucun consommateur du reason identifié, donc aucune surface modifiée.

La preuve Unit vérifie la conservation de la raison Draft et l'absence de raison dans la révision et l'événement Submit. La preuve PostgreSQL vérifie la même propriété après sérialisation et reconstruction.

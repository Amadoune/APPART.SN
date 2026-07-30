# Lead Lifecycle Routing Runtime Health Decision

Décision : Runtime Health est étendu explicitement avec deux capacités :

* `LeadLifecycleInboxStore` ;
* `LeadLifecycleEventRouter`.

Le total passe de 33 à 35 capacités. L'inspection vérifie uniquement binding, compatibilité contractuelle et constructibilité. Elle n'appelle jamais `store()`, `route()` ou la politique de consommation.

La politique pure n'est pas une capacité Health autonome : elle n'a aucune dépendance et sa matrice est certifiée par tests Unit et Architecture.

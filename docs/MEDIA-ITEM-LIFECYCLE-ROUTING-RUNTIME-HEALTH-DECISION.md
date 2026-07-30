# Media Item Lifecycle Routing Runtime Health Decision

Runtime Health est explicitement étendu avec :

- `MediaItemLifecycleInboxStore` ;
- `MediaItemLifecycleEventRouter`.

La politique de consommation reste un détail stateless de composition et n'est pas exposée comme capacité autonome. L'inspection vérifie uniquement le binding, la compatibilité contractuelle et la constructibilité.

Elle n'appelle ni `store()`, ni `route()`, ni `consumptionFor()`. Le Runtime certifié comporte désormais 45 capacités.

# Public Media Item contract

Le contrat V1 `mediaId + url + variants` couple la décision à un host et ne convient pas à la portabilité requise.

Le futur contrat minimal doit porter : `mediaId`, `publicLocator`, `deliveryRevision`, `variants` (vide en V1). L'ordre et le primary restent au niveau de la décision (gallery/cover), non du locator.

L'adapter de lecture construit l'URL absolue pour les consumers V1 pendant la transition.

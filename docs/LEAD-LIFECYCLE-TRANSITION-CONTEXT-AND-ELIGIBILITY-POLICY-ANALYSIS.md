# Lead Lifecycle Transition Context and Eligibility Policy Analysis

`LeadEligibilityProof` est une précondition de création certifiée par `CreateLead`. Elle n'est pas une précondition des transitions : `Deliver`, `Reject` et `Close` ne relisent ni `ListingCatalog` ni `AdvertiserCatalog`. Une indisponibilité ultérieure des sources n'empêche donc aucune transition.

Cette décision découle des contrats existants : le workflow décide exclusivement sur `(state, action)` et l'Aggregate reçoit la preuve lors de `Lead::create()`.

`actor` et `occurredAt` sont des données d'audit obligatoires, explicitement fournies, mais ne participent pas à la décision. Le port 4.4B reste gelé. `LeadLifecycleContextualTransitionStore` est son successeur versionné ; aucune implémentation n'est fournie ici.

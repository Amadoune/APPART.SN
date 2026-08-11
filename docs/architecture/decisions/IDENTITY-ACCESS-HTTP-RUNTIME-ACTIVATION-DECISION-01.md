# Identity Access HTTP Runtime Activation Decision 01

## Réponse exécutive

`IdentityAccessHttpRuntime` est lié à `FailClosedIdentityAccessHttpRuntime` parce que la Foundation HTTP 5.1I a matérialisé le port, l'adapter HTTP, les règles de sécurité et un fallback de composition, mais jamais un Runtime applicatif réel traduisant les opérations HTTP vers les capacités IAM.

Le fallback est donc volontaire et destiné à la composition de production lorsque cette dépendance manque. Il évite qu'une route IAM partiellement branchée tente une opération incomplète. Il n'est ni un adapter de développement, ni un choix fonctionnel d'authentification.

## Réponses aux questions A–C

### A. Le Runtime HTTP réel existe-t-il ?

**NON.** La recherche exhaustive du repository et de son historique ne trouve qu'une classe concrète implémentant le port en production : `FailClosedIdentityAccessHttpRuntime`. Les autres implémentations sont des fakes/stubs définis dans les tests.

### B. Pourquoi n'est-il pas utilisé ?

Sans objet : aucun Runtime réel non branché n'existe.

### C. Pourquoi le fallback est-il seul ?

La 5.1I a certifié la frontière HTTP indépendamment de l'implémentation métier. Le Controller sait valider, limiter, sérialiser et poser le cookie à partir d'un `IdentityAccessHttpResult`, mais aucune classe ne produit réellement ce résultat depuis les comptes, credentials, sessions, profils et stores IAM.

## Stratégie de reprise

Un changement de binding seul est impossible : aucune cible concrète recevable n'existe. La reprise exige un chantier d'implémentation IAM versionné qui matérialise au minimum l'authentification et l'inspection de session, puis remplace explicitement le fallback après certification.

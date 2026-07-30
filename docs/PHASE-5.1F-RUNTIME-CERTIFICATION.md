# Phase 5.1F — Runtime Certification

`IdentityAccessOrchestrationServiceProvider` enregistre la transaction PostgreSQL et l'orchestrateur déterministe comme singletons, avec leurs ports Application comme aliases. Ce provider 5.1F distinct préserve intégralement le provider 5.1E gelé. Toutes les résolutions sont lazy et partagent la connexion PostgreSQL IAM.

La phase ne modifie ni le catalogue Runtime Health historique à 58 capacités, ni HTTP, les routes, les middlewares, les événements, Delivery ou Outbox.

Preuves normatives :

- Unit : huit points d'entrée et refus d'une opération incohérente ;
- Feature : bindings singleton et connexion PDO partagée ;
- Architecture : Application indépendante du framework et absence des couches interdites ;
- PostgreSQL : atomicité, rollback, replay et concurrence.

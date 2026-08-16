# Failure Modes

| Cas | Source | Réduction | Effet RegisterProperty | Retry | Mutation |
|---|---|---|---|---|---|
| Place usable | snapshot valide + policy d’adressabilité | `Usable` | poursuite | non | aucune |
| Place absente | `find === null` | `NotFound` | refus Domain | non, sauf nouvelle donnée | aucune |
| Place disabled | non merged, enabled false | `Disabled` | refus Domain | non, sauf réactivation autorisée ailleurs | aucune |
| Place merged | `mergedInto !== null` | `Merged` | refus Domain | non ; aucune redirection automatique | aucune |
| Place non adressable | snapshot valide + policy d’adressabilité | `NotAddressable` | refus Domain | non | aucune |
| Snapshot corrompu | `PersistentPlaceIntegrity` | aucune réduction en statut | exception, arrêt avant add | non automatique ; correction de données requise | aucune |
| Geography indisponible | échec de lecture | aucune réduction en statut | exception, arrêt avant add | oui, après restauration de la dépendance | aucune |

La corruption et l’indisponibilité sont toutes deux fail-closed. Leur distinction opérationnelle ne doit pas modifier les statuts métier. Si une surface future exige un résultat applicatif fermé, le besoin minimal sera une réduction d’erreurs de dépendance en dehors du contrat de statut, sans transformer l’erreur en état de Place.

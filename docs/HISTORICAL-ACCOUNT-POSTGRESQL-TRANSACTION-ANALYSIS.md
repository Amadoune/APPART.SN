# Historical Account — PostgreSQL Transaction Analysis

## Ownership

Le Repository ouvre une transaction uniquement si PDO n'en possède aucune.
Dans une transaction externe, il participe sans commit ni rollback.

Toutes les écritures racine, Credential, Verification, RoleAssignment et
Consent utilisent la même instance PDO.

## Add

```text
BEGIN si owner
→ INSERT accounts
→ DELETE défensif des enfants inexistants
→ INSERT credential
→ INSERT email verification
→ INSERT phone verification
→ INSERT roles ordonnés
→ INSERT consents ordonnés
→ COMMIT si owner
```

Toute erreur déclenche un rollback intégral lorsque le Repository est owner.

## Save

Le contrôle optimiste est porté exclusivement par l'UPDATE conditionnel. Il
n'est pas remplacé par une lecture préalable. Deux processus présentant la
même version produisent exactement un succès et un
`ConcurrentAccountModification`.

## Corruption

Une racine présente avec Credential ou Verification manquant, snapshot version
incompatible, données illisibles ou chronologie invalide produit
`CorruptedHistoricalAccountPersistence`. Elle ne devient jamais `null`.

## Compatibilité 4.9

La fondation ne lit et n'écrit jamais
`identity_access.account_status_lifecycle_transitions`. Elle n'appelle aucune
mutation Account Status et ne publie aucun événement.

Le rollback d'un environnement partagé suit strictement l'ordre inverse des
migrations : 042 est démontée avant 041. Cette règle permet à 041 de supprimer
son schéma historique sans `CASCADE` et sans modifier son contrat certifié.

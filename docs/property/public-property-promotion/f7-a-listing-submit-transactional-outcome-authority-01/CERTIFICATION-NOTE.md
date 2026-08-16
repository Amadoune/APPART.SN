# Note de certification F7-A

## Décision

Les outcomes committables sont exclusivement `Applied` et `AlreadyApplied` avec transition. `Denied`, `ConcurrencyConflict`, `PersistenceFailure`, tout succès structurellement incomplet et toute exception imposent le rollback Listing.

Le mécanisme minimal est `AuthoringOperationRollback`, déjà présent, déclenché dans la closure transactionnelle avec conservation locale du résultat applicatif pour sa réduction après rollback.

Toutes les mutations Listing concernées partagent la même connexion PostgreSQL. Aucun side effect externe irréversible, redesign, changement F6 ou migration n’est nécessaire. La frontière future est bornée à un orchestrateur et ses tests ciblés.

## Verdict

**GO PROPOSÉ**

APPART.SN LISTING / PROPERTY FOUNDATION
F7-A — LISTING SUBMIT TRANSACTIONAL OUTCOME AUTHORITY 01

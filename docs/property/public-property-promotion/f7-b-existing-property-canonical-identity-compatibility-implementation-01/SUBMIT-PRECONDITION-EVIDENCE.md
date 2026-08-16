# F7-B — Preuve de précondition Submit

La composition PostgreSQL existante a été exercée sans modifier le code Submit.

## Property canonique

Une Property créée par une autre commande de promotion, donc sans ledger correspondant au Submit, est reconnue `AlreadyApplied` par la promotion. Submit poursuit et le Listing atteint `Submitted`.

État final : une Property et un ledger historique, sans ledger de rattrapage.

## AddressId divergent

Une Property persistée avec les mêmes faits descriptifs mais un AddressId non canonique est refusée par la promotion. Submit retourne son résultat divergent et le Listing reste `Draft`.

État final : une Property inchangée et aucun ledger de promotion réussi.

Cette preuve confirme que Submit poursuit uniquement après compatibilité canonique, sans changement de son implémentation.

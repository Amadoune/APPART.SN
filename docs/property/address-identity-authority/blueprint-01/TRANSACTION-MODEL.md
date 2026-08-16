# Transaction Model

L'émission est une fonction pure et ne nécessite ni persistance, ni réservation, ni ledger. Elle s'exécute dans l'orchestration de promotion, mais n'est pas elle-même une ressource transactionnelle.

La création de `Address`, de l'Aggregate Property et sa persistance restent dans la transaction locale RealEstateCatalog définie par Public Property Promotion. Un rollback ne doit pas annuler l'identité : le même input la recalcule au retry.

Aucune transaction distribuée et aucun statut `AlreadyIssued` persistant ne sont nécessaires.

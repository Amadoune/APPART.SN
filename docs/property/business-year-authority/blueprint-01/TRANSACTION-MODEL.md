# Transaction Model

BusinessYearAuthorityV1 est une fonction pure : aucune transaction, configuration read-only, persistance ou ledger.

Elle s'exécute avant `RegisterProperty` ou `UpdateProperty` dans leur orchestration. La transaction locale RealEstateCatalog couvre ensuite les mutations Domain/Registry ; un rollback puis retry recalcule la même année depuis le même occurredAt.

Aucune transaction distribuée n'est introduite.

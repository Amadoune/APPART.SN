# Quarantine model

Réutilisation exclusive de Public Projection Outbox delivery : RetryableFailure, PermanentFailure/quarantine, ordering et replay existants.

Aucun second framework de retry ou ledger cross-domain. Le consumer Public Geography est un consumer aval de ce transport, sans lecture Projection.

# Preuves d’idempotence

Le ledger demeure prioritaire : une commande attestée avec checksum identique retourne `AlreadyApplied`; le même commandId avec checksum divergent retourne `DivergentCommand`.

Sans ledger correspondant, l’Aggregate existant est comparé à l’effet canonique complet. Une correspondance exacte retourne `AlreadyApplied` sans écrire de ledger. Toute divergence retourne `DivergentCommand`.

Le retry end-to-end réutilise donc l’effet Promotion durable sans dupliquer Property, ledger, Workflow ou handoff.

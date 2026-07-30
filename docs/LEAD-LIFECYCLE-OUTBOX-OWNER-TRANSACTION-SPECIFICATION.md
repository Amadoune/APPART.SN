# Lead Lifecycle Outbox Owner Transaction Specification

Le Writer générique rejoint toute transaction PDO externe ; sinon il ouvre et contrôle sa transaction locale. Message et livraison sont donc validés ou annulés ensemble.

L'unicité de `idempotency_key` et celle du tuple événementiel garantissent l'idempotence. Un rejeu identique produit `AlreadyApplied`; un checksum divergent produit `DivergentMessage`.

Cette fondation démontre la capacité transactionnelle du Writer, mais ne l'intègre encore à aucun workflow ou orchestrateur Lead.

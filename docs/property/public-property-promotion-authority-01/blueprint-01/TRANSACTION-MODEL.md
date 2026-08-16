# Transaction Model

## Stratégie V1 retenue

**Composition synchrone locale RealEstateCatalog.** L'orchestrateur lit le snapshot, verrouille/inspecte son ledger, appelle `RegisterProperty`, persiste Aggregate et événements selon les mécanismes Domain, puis valide le ledger dans une transaction locale au même owner.

Point de commit : ledger de promotion + Registry Property + événements/outbox Domain, si une outbox Domain est requise par l'implémentation, sont cohérents dans la transaction RealEstateCatalog. La transaction Listing Submit commence seulement après un résultat de promotion réussi.

## Échecs

- Refus Domain : rollback local complet, résultat `DomainRejected`.
- Indisponibilité avant commit : aucune mutation visible, `DependencyUnavailable`, retry même commande.
- Résultat perdu après commit : replay retrouve ledger/Aggregate et retourne `AlreadyApplied`.
- Échec ultérieur du Submit Listing : aucune compensation Property. Le Property enregistré n'est pas public ; le retry Submit réutilise `AlreadyApplied`.

## Option événementielle

Rejetée en V1 : elle introduirait un état d'attente et permettrait `Submitted` avant la certitude de promotion. Aucun événement de demande n'est nécessaire au chemin retenu.

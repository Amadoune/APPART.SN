# Lead Lifecycle Transition Idempotence and Transaction Policy

L'unité idempotente est `(leadId, nextVersion, transition, actor, occurredAt)`.

- rejeu identique : `AlreadyApplied` ;
- même version avec transition différente : conflit d'état ou de version ;
- même transition/version avec contexte différent : `ContextDivergence` ;
- donnée persistée invalide : `Corrupted`.

La future implémentation réalisera lecture critique, contrôle de version et append contextuel dans une transaction PostgreSQL unique par Lead. Aucune lecture d'éligibilité n'appartient à cette transaction.

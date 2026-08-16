# Modèle de retry et d’idempotence

## État après rollback Listing

- Property : durable ;
- ledger Promotion : `Applied` ;
- Listing Aggregate : `Draft` à sa version pré-Submit ;
- Workflow : état pré-Submit cohérent ;
- public-facts candidate de cette tentative : absente si créée dans la tentative ;
- transition/outbox/queue Submitted de cette tentative : absentes.

## Retry

La même identité de commande Promotion retourne `AlreadyApplied`. La transaction Listing repart ensuite de la même paire Aggregate/Workflow et peut appliquer Submit de façon déterministe.

Les révisions Aggregate, transition Workflow, outbox et queue utilisent la même PDO ; leur rollback retire aussi leurs traces idempotentes locales. Le ledger Promotion survit intentionnellement, car il appartient à la transaction précédente. Le `listing_creation_intents` concerne Create Draft et n’est pas muté par Submit.

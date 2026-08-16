# F7-A — Listing Submit Transactional Outcome Authority 01

## Décision normative

La transaction Listing Submit est une transaction à **outcome explicite**. L’absence d’exception n’est pas une autorisation implicite de commit.

Le commit est autorisé exclusivement lorsque l’orchestration Workflow retourne `Applied` ou `AlreadyApplied` avec une transition non nulle et que tous les handoffs locaux requis ont convergé. Tout autre résultat fermé obtenu après une mutation Aggregate impose le rollback de la tentative Listing.

Le résultat fermé demeure une décision applicative. Il doit être conservé pendant le rollback puis réduit vers le statut Authoring existant ; il ne devient pas artificiellement une erreur technique publique.

## Invariants

- Aggregate et Workflow convergent ensemble ou aucune mutation Listing de la tentative ne subsiste.
- Promotion et Listing restent deux transactions locales séparées.
- Une Property promue demeure durable après rollback Listing.
- Aucun ledger/handoff Listing de succès ne survit au rollback de l’Aggregate correspondant.

## Verdict d’autorité

La correction est réalisable sans migration, sans modification F6 et sans redesign transactionnel.

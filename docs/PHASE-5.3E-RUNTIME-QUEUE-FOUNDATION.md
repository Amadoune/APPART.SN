# Phase 5.3E — Runtime & Queue Foundation

## Statut proposé

`GO PROPOSÉ`

Cette fondation compose exclusivement les persistences propriétaires certifiées en
5.3D. Elle ne consomme aucune frontière externe et n'introduit ni HTTP, ni Event,
ni Delivery, ni Outbox.

## Runtime propriétaire

`ModerationRuntimeV1` est la façade publique owner-local. Sa composition est
limitée à :

- `ModerationCaseStore` ;
- `ModerationDecisionStore` ;
- `ModerationQueueRuntimeV1`, lui-même adossé exclusivement à
  `ModerationQueueStore`.

Les bindings Laravel sont paresseux, singleton et uniques. Le provider propriétaire
est `ModerationRuntimeServiceProvider`.

## Queue propriétaire

`ModerationQueueRuntimeV1` expose seulement :

- la projection locale d'un item ;
- le claim avec lease ;
- la lecture locale ;
- l'avancement monotone du checkpoint.

La Queue est une projection owner-local. Elle ne porte aucune décision métier et ne
déclenche aucun handoff.

## Disponibilité et diagnostics

`DeterministicModerationRuntimeAvailabilityPolicy` vérifie de manière fermée les
trois stores propriétaires. Toute absence produit `Unavailable`; aucune solution de
repli n'est autorisée.

Les diagnostics sont fermés et techniques :

- `case_store_missing` ;
- `decision_store_missing` ;
- `queue_store_missing`.

Ils ne contiennent ni PII, ni secret, ni SQL, ni Aggregate.

## Runtime Health

L'extension ajoute uniquement :

- `ModerationRuntime` ;
- `ModerationQueue`.

Les 60 exigences historiques de la baseline restent inchangées. L'inspection
compose le résultat historique et les deux nouvelles capacités de manière
fail-closed.

## Frontières transactionnelles

Les opérations Runtime délèguent aux stores certifiés de 5.3D. Les transactions,
savepoints, advisory locks, leases et checkpoints restent sous l'autorité de la
Persistence propriétaire. Aucune transaction transverse ou distribuée n'est
introduite.

## Hors périmètre démontré

- IAM, Listing, Media, Account, Professional et Administration Audit ;
- les six amendements identifiés en 5.3C ;
- Reader ou Gateway externe ;
- orchestration métier et politique des quatre yeux ;
- HTTP, Controller, Route et projection publique ;
- Event, Transport, Delivery, Consumer et Outbox ;
- migration PostgreSQL supplémentaire.

## Conclusion

La Runtime & Queue Foundation est additive, owner-local, déterministe et
fail-closed. Elle est éligible à une décision `GO`.

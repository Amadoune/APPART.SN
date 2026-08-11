# Listing Transition Reason Authority 01 — Audit

## Verdict

`NO GO PROPOSÉ`

Submit ne porte aucun reason. Le `reasonCode` de Moderation appartient à la validation d'un report ; il n'est pas inclus dans `IssueModerationDecisionV1`, dans la décision persistée ni dans le handoff Listing.

Le handoff actuel cible Suspend, RequestChanges, Reject et Archive. Il ne constitue donc pas une autorité existante pour BeginReview ou ApproveAndPublish.

`TransitionReason` et `PublicationReason` valident du texte libre mais ne fournissent aucun catalogue ni source de décision.

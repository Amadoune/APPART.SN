# Listing Transition Evidence Authority 01 — Audit

## Verdict

`NO GO PROPOSÉ`

Trigger, origin, actor et occurredAt disposent d'autorités existantes. `TransitionReason` n'en possède aucune pour Submit, BeginReview et ApproveAndPublish.

`ListingTransitionPolicy` porte explicitement les couples trigger/origin autorisés. La session IAM et l'ownership portent l'acteur Submit. La décision de modération porte l'acteur Review/Publish. Les commandes et métadonnées portent l'instant autoritatif.

Le reason n'est présent dans aucun command de ces transitions. La constante utilisée pour la création initiale du draft ne peut pas être étendue arbitrairement.

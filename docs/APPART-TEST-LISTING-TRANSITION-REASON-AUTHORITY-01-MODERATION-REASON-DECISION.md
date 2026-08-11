# Listing Transition Reason Authority 01 — Moderation Decision

Le `reasonCode` est autoritatif uniquement pour la validation du report. La décision finale conserve disposition, findings, policyVersion et targetAction, mais pas reasonCode.

La transmission mécanique ne peut donc pas être qualifiée : il faudrait d'abord décider quel reason parmi les reports/findings justifie la décision, puis ouvrir explicitement BeginReview/ApproveAndPublish dans le handoff. Ces choix ne sont pas de simples mappings.

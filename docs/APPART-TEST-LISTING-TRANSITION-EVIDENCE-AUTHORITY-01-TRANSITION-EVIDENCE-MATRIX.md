# Listing Transition Evidence Authority 01 — Matrix

| Transition | Champ | Owner/source | Disponible | Transformation | Statut |
|---|---|---|---|---|---|
| Submit | trigger | Listing policy: SubmissionConfirmed | oui | mapping owner-scoped | PASS |
| Submit | origin | Listing policy: Advertiser | oui | mapping owner-scoped | PASS |
| Submit | actor | IAM session + ownership | oui | typage ActorId | PASS |
| Submit | occurredAt | authoring command | oui | UTC typé | PASS |
| Submit | reason | aucune commande | non | aucune | MISSING |
| BeginReview | trigger | Listing policy: ReviewStarted | oui | mapping owner-scoped | PASS |
| BeginReview | origin | Listing policy: Moderation | oui | mapping owner-scoped | PASS |
| BeginReview | actor/time | décision de modération | oui | typage mécanique | PASS |
| BeginReview | reason | aucune donnée qualifiée | non | aucune | MISSING |
| ApproveAndPublish | trigger | Listing policy: FavorableReview | oui | mapping owner-scoped | PASS |
| ApproveAndPublish | origin | Listing policy: Moderation | oui | mapping owner-scoped | PASS |
| ApproveAndPublish | actor/time | décision de modération | oui | typage mécanique | PASS |
| ApproveAndPublish | reason | reasonCode non transmis au handoff Listing | non | aucune | MISSING |

# Listing Publication Workflow Transition Matrix

| Depuis | Action | Vers |
|---|---|---|
| Draft | Submit | Submitted |
| Draft | Withdraw | Withdrawn |
| Draft | Archive | Archived |
| Submitted | BeginReview | UnderReview |
| Submitted | Withdraw | Withdrawn |
| UnderReview | ApproveAndPublish | Published |
| UnderReview | RequestChanges | ChangesRequested |
| UnderReview | Reject | Rejected |
| UnderReview | Withdraw | Withdrawn |
| ChangesRequested | Submit | Submitted |
| ChangesRequested | Withdraw | Withdrawn |
| ChangesRequested | Archive | Archived |
| Published | ReviewMaterialChange | UnderReview |
| Published | Suspend | Suspended |
| Published | Expire | Expired |
| Published | Withdraw | Withdrawn |
| Suspended | Reinstate | Published |
| Suspended | RequestChanges | ChangesRequested |
| Suspended | Reject | Rejected |
| Suspended | Archive | Archived |
| Expired | ReviewRenewal | UnderReview |
| Expired | RenewDirectly | Published |
| Expired | Withdraw | Withdrawn |
| Expired | Archive | Archived |
| Withdrawn | ApproveRepublication | UnderReview |
| Withdrawn | Archive | Archived |
| Rejected | Archive | Archived |

Toute combinaison absente est refusée explicitement. Archived n'a aucune transition sortante.

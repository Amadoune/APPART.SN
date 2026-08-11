# Transition Scope Matrix

| Transition | Trigger | Reason absent | Reason présent |
|---|---|---:|---:|
| Draft → Submitted | `SubmissionConfirmed` | autorisé | conservé |
| Submitted → UnderReview | `ReviewStarted` | autorisé | conservé |
| UnderReview → Published | `FavorableReview` | autorisé | conservé |
| Toute autre transition | catalogue existant | refusé | requis |
| Création Draft | `DraftStarted` | refusé | requis |

La portée est contrôlée dans `ListingTransitionPolicy` après validation du couple état/trigger/origin. Elle n'introduit ni fallback ni nouvelle transition.

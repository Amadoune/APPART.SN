# Publication Review — Capability Matrix

Le catalogue V1 est fermé exclusivement à :

| Capacité canonique | Autorise | N'autorise pas |
|---|---|---|
| `read_publication_review_queue` | lire la file et une fiche de candidature | claim ou mutation |
| `claim_publication_review` | réclamer un QueueItem disponible | Begin ou Approve |
| `begin_publication_review` | appeler `BeginPublicationReviewV1` sur un item claimé | Approve |
| `approve_publication` | appeler `ApprovePublicationV1`, puis permettre l'intention de Projection | Report Moderation ou modification Listing |

Aucune capacité générique `review`, `moderate`, `publish` ou `admin` n'est admise. `ProjectPublishedListingV1` est la conséquence applicative d'un Approve réussi ; aucune cinquième capacité n'est ajoutée, car elle ne représente pas une décision humaine autonome.

Chaque endpoint associe statiquement son opération à une capacité. Le client ne choisit jamais la capacité demandée.

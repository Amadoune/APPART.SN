# Listing Publication Workflow State Matrix

| État | Justification | Terminal |
|---|---|---:|
| `Draft` | annonce créée et éditable avant soumission | Non |
| `Submitted` | soumission reçue, pas encore instruite | Non |
| `UnderReview` | contrôle actif par la modération | Non |
| `ChangesRequested` | corrections explicites attendues | Non |
| `Published` | annonce publiquement active | Non |
| `Suspended` | visibilité interrompue avec résolution possible | Non |
| `Expired` | échéance de publication atteinte, renouvellement possible | Non |
| `Withdrawn` | retrait volontaire, republication contrôlée possible | Non |
| `Rejected` | refus non régularisable, archivage après recours | Non |
| `Archived` | clôture définitive conservée pour audit | Oui |

`Approved` n'est pas stocké séparément : l'approbation réussie produit Published. `Deleted` est exclu afin de préserver l'historique. `PendingReview` est représenté précisément par Submitted puis UnderReview.

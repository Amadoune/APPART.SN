# Promotion Timing Decision

| Option | Cohérence et données | Couplage / reprise | Risque | Décision |
|---|---|---|---|---|
| A — création Authoring | Données encore incomplètes et mutables | Couplage précoce | Nombreux Properties abandonnés | Rejetée |
| B — Submit | Owner authentifié présent ; frontière naturelle de complétude | Appel synchrone rejouable avant mutation Listing | Property peut subsister si Submit échoue ensuite, sans être public | **Retenue** |
| C — BeginReview | Reviewer n'est pas owner ; défaut découvert tard | Couple Review à Authoring | Queue bloquée par préparation propriétaire | Rejetée |
| D — ApprovePublication | Trop tard ; publication dépend d'une création cachée | Couple Gateway de publication à Authoring | Échec au dernier instant | Rejetée |
| E — handoff événementiel dédié | Découplé mais succès non disponible immédiatement | Outbox/consumer et état d'attente supplémentaires | `Submitted` peut précéder une promotion invalide | Rejetée en V1 |

La promotion est exécutée pendant l'orchestration Submit, **avant** la transition Listing vers `Submitted`. Un succès Domain est une précondition ; il n'est pas une partie atomique de la transaction Listing. Si le Submit Listing échoue après promotion, l'Aggregate compatible demeure et le rejeu retourne `AlreadyApplied`.

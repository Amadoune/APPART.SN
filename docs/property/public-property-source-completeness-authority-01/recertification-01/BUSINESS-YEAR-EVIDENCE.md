# Business Year Evidence

F3 compose :

`occurredAt absolu → PropertyDecisionOccurredAt → UtcCalendarBusinessYearAuthorityV1 → BusinessYear`.

La résolution utilise explicitement UTC, accepte des représentations à offset équivalentes et rejoue de façon stable. Elle ne lit aucune clock courante. BusinessYear n’est pas stockée dans Property Authoring et reste une donnée de commande future.

Les régressions Unit, Feature binding et Architecture F3 sont PASS. Cette autorité n’est pas la divergence F5.

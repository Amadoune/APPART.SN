# Concurrency model

Deux workers sur la même représentation convergent via le verrou placeId vers Applied + AlreadyApplied. Une source plus récente doit dominer; une source stale est RejectedObsolete; même version différente est Divergent.

Une modification parentale pendant l'assemblage exige un snapshot/revision composite cohérent. Ce mécanisme dépend de l'autorité préalable et ne peut être inventé par le writer.

# Concurrency Model

Plusieurs workers peuvent calculer le même candidat. L’advisory lock et le verrou de ligne sérialisent l’écriture par ListingId.

Le premier candidat dominant est `Applied`; le replay identique devient `AlreadyApplied`; l’ancien devient `RejectedObsolete`; un candidat incohérent au même niveau devient `Divergent`. Aucun last-write-wins silencieux.

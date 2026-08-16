# PlaceRenamed transport gap

`RenamePlace` sauvegarde l'Aggregate via `PlaceRegistry`; il ne compose pas `PlaceLifecycleAtomicEventOrchestrator`. Le catalogue lifecycle ne contient que Enabled, Disabled, Merged.

Décision : transporter le Domain event existant `PlaceRenamed` dans le même Public Projection Outbox atomique, avec payload V1 versionné. Ce n'est ni un nouvel événement métier ni une nouvelle Foundation; c'est l'admission d'un fait existant dans l'infrastructure certifiée.

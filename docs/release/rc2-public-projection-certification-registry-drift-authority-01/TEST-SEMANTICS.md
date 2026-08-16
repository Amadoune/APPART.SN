# Test Semantics

Les quatre tests observés certifient deux invariants :

1. aucun doublon dans le registre complet;
2. présence et consumer corrects pour leur sous-catalogue respectif.

Le count exact sert de garde d'admission fermée, mais sans catalogue attendu indépendant il ne permet pas d'identifier une admission autorisée. Les tests ne certifient donc pas réellement, à eux seuls, la whitelist complète qu'ils prétendent fermer.

Deux autres tests stale ont été identifiés : `ReservationLifecycleOutboxCompatibilityRuntimeTest` et `PublicProjectionRuntimeBindingTest`.

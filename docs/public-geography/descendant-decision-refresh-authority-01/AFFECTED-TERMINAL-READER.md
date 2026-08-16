# Affected terminal reader

Contrat futur : `AffectedPublicGeographyTerminalReaderV1::read(mutatedPlaceId, afterPlaceId?, limit)`.

Résultats fermés : Available(items,nextCursor), Empty, Corrupted, DependencyUnavailable. Items : terminalPlaceId uniquement. Limit positif et borné; cursor opaque validé; identités uniques et strictement ordonnées.

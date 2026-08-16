# Source revision model

La source revision comprend le vecteur ordonné `(placeId, aggregateVersion)` de toute la chaîne et le checksum SHA-256 du payload canonique.

Tout changement de nom, état, merge ou autre mutation versionnée du terminal ou d'un ancêtre change le vecteur et/ou rend la représentation indisponible. La causalité V1 est dérivée déterministement de `schemaVersion + terminalPlaceId + vector canonical checksum`.

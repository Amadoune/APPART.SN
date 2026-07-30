# Public Media Durable Source

## Contenu durable

Une décision est identifiée par `mediaCollectionId` et contient :

- une `PublicMediaRevision` positive avec checksum et causalité ;
- une couverture publique optionnelle déjà décidée ;
- une galerie publique ordonnée ;
- pour chaque média, l'identité, l'URL publique et les variantes déjà décidées.

`PublicMediaDecision::canonicalPayload()` conserve strictement l'ordre fourni. La révision doit porter le SHA-256 exact de ce payload.

## Lecture

`PostgreSqlPublicMediaReader` implémente à la fois le reader de décision spécialisé et `PublicMediaRevisionReader` :

- `Found` : payload décodable, typé et certifié par ses checksums ;
- `Missing` : aucune ligne pour la collection ;
- `Corrupted` : payload, type, révision ou checksum invalide.

Une décision corrompue ne fournit jamais une révision stable.

## Écriture

- `Applied` : première version ou version plus récente ;
- `AlreadyApplied` : même version, même contenu et même causalité ;
- `RejectedObsolete` : version plus ancienne ;
- `Divergent` : même version avec checksum ou causalité différente.

Il n'existe ni fallback, ni correction, ni sélection silencieuse.

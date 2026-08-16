# Decision contract

`App\Application\PublicMediaSource\PublicMediaDecision` est identifié par `mediaCollectionId` et porte `PublicMediaRevision`, un `cover` nullable et une galerie ordonnée.

Chaque `PublicMediaItem` exige `mediaId`, une URL valide et une liste de `PublicMediaVariant(name,url)`. Le checksum de révision doit être celui du payload canonique.

`PublicMediaDecisionReader::read()` ferme `Found`, `Missing`, `Corrupted`. `PublicMediaDecisionWriter::store()` ferme `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`.

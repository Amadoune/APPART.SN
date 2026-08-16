# Final Failure Matrix

| Situation | Résultat materializer | Effet Projection |
|---|---|---|
| Listing absent | SourceMissing | reste NotReady |
| non Published | SourceNotReady | reste NotReady |
| title absent/corrompu | SourceCorrupted | reste NotReady |
| description invalide | SourceCorrupted | reste NotReady |
| canonical rejetée | SourceCorrupted | reste NotReady |
| indexabilité non décidable | SourceNotReady | reste NotReady |
| decisionAt absent/divergent | SourceCorrupted | reste NotReady |
| révision absente/invalide | SourceCorrupted | reste NotReady |
| writer indisponible | DependencyUnavailable | aucune progression |
| source obsolète | RejectedObsolete | snapshot courant conservé |
| divergence | Divergent | aucune progression |
| replay | AlreadyApplied | reader Found |
| première/nouvelle version | Applied | reader Found |

Aucun cas technique n’est réduit en faux succès.

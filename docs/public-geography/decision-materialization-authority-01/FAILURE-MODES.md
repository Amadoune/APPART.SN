# Failure modes

| Situation | Réduction fail-closed |
|---|---|
| Listing/Property/Address/Place absent | SourceMissing |
| Place disabled/merged ou hiérarchie invalide | SourceNotReady/SourceCorrupted |
| représentation publique absente | SourceMissing |
| révision/checksum invalide | SourceCorrupted |
| writer indisponible | DependencyUnavailable |
| version stale | RejectedObsolete |
| même version différente | Divergent |

Aucun fallback legacy ou payload partiel.

# Failure modes

| Condition | Résultat attendu/état |
|---|---|
| Listing missing/non Published | NotReady/ListingMissing |
| Media collection missing | NotReady/MediaCollectionMissing |
| Media/asset corrompu | MediaCorrupted |
| état non supporté | UnsupportedState |
| ordre invalide | OrderingInvalid |
| primary absent/non éligible | PrimaryUnresolved |
| URL publique absente | PublicDeliveryUnresolved |
| révision invalide | RevisionInvalid |
| dépendance/writer indisponible | DependencyUnavailable |
| stale | RejectedObsolete |
| même version divergente | Divergent |
| replay identique | AlreadyApplied |
| write nouveau | Applied |

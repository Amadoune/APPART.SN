# Final Status Mapping

| Priorité | Observation | Action | GeographicPlaceStatus |
|---:|---|---|---|
| 1 | `find(...) === null` | arrêter | `NotFound` |
| 2 | `mergedInto() !== null` | arrêter, aucune redirection | `Merged` |
| 3 | non merged et `!isEnabled()` | arrêter | `Disabled` |
| 4 | enabled, non merged, policy `NotAddressable` | arrêter | `NotAddressable` |
| 5 | enabled, non merged, policy `Addressable` | succès | `Usable` |

Le mapping est exhaustif pour tout `Place` valablement reconstitué. Il ne comporte aucun `default` permissif.

`Merged` prime sur `Disabled`, car une Place fusionnée est désactivée par invariant Geography et la fusion est la cause lifecycle la plus précise. La policy n’est pas appelée dans ce cas.

Une corruption de snapshot/type ou une indisponibilité de Geography lève une erreur fail-closed. Aucun `GeographicPlaceStatus` n’est fabriqué pour transporter un incident technique.

RegisterProperty et ChangeAddress n’acceptent que `Usable`; tous les autres statuts restent réduits par leur comportement existant en `UnavailableGeographicPlace`.

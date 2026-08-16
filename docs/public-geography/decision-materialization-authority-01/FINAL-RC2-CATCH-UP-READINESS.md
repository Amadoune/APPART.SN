# Final RC2 catch-up readiness

Inspection read-only de la base applicative `appart_rebuild` :

| Ordre | PlaceId | officialName | type | parent | active | merged | version |
|---|---|---|---|---|---|---|---:|
| 1 | `c3120000-0000-4000-8000-000000000001` | Senegal | country | null | oui | non | 1 |
| 2 | `c3120000-0000-4000-8000-000000000002` | Dakar Region | region | Senegal | oui | non | 1 |
| 3 | `c3120000-0000-4000-8000-000000000003` | Dakar | city | Dakar Region | oui | non | 1 |

Le vecteur réel est `[1,1,1]`; le watermark dérivé est `3`. Le terminal productif est exclusivement `c3120000-0000-4000-8000-000000000003`. L'identité `65000000-0000-4000-8000-000000000010` reste une fixture historique.

La hiérarchie est complète et consommable par les adapters V2 certifiés. Le catch-up RC2 est donc **READY** pour la future Implementation; aucune décision n'a été créée pendant cet audit.

# Promotion Readiness Matrix

| Groupe | État de readiness | Autorité terminale |
|---|---|---|
| identité Property | EXECUTABLE | PropertyId snapshot/Domain |
| référence | EXECUTABLE | PropertyReference + Registry |
| type et faits physiques | EXECUTABLE | Value Objects + PropertyTypePolicy |
| identité Address | EXECUTABLE | F2 |
| Place Address | EXECUTABLE/REVALIDABLE | F1/F4 puis F5-A |
| ligne Address | EXECUTABLE | AddressLine |
| BusinessYear | EXECUTABLE | F3 |
| occurredAt | REPRESENTABLE PAR COMMANDE | Blueprint Promotion/F6 |
| owner scope | EXECUTABLE | snapshot F4 + session/commande future |

La matrice est fermée. F6 devra seulement orchestrer ces sources, conserver l’instant stable et déléguer aux autorités existantes. L’absence actuelle de la commande de Promotion n’est pas une absence de source.

Gate : F6 reste non ouverte par cette recertification.

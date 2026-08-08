# Notifications Owner Reader Boundary Audit

## Décision

`Notifications` est l'owner unique des trois frontières de lecture publiques : Preference, Template et Channel. La frontière manquante est une adaptation Application owner-scoped entre `NotificationsOwnerSource` et les trois contrats V1 certifiés.

## Chaîne retenue

`NotificationsOwnerSource` → futurs Owner Readers Notifications → Readers publics V1 → HTTP.

La réduction est exhaustive, déterministe et mécanique. Un état trouvé transmet uniquement sa décision publique ; Missing, Corrupted et DependencyUnavailable restent homonymes. Aucun Revision State, checksum, timestamp interne ou détail de Persistence ne franchit la frontière.

## Architectures écartées

- Runtime : qualifie uniquement la disponibilité technique et ne porte aucune décision Notification.
- HTTP : consommateur des Readers publics, jamais autorité.
- Projection ou source externe : seconde autorité et reconstruction interdite.

# Account Status — Consumer Compatibility Matrix

| Option auditée | J5 | Conservation 4.9H | Conservation 4.9I | Owners historiques | Verdict |
|---|---:|---:|---:|---:|---|
| Implémentation directe du port générique | Non | Non : routage déplacé | Non : signature et responsabilité modifiées | Oui | NO GO |
| Destination constante dans le Consumer | Non | Non : décision reconstruite | Non : entrée certifiée contournée | Oui | NO GO |
| Consumer/adapter IdentityAccess spécialisé | Techniquement | Oui | Oui | Oui | NO GO : spécialisation interdite |
| Modification du Worker ou du port générique | Indéterminé | Indéterminé | Indéterminé | Non démontré pour dix owners | NO GO |
| Frontière générique de livraison routée, versionnée et auditée | À démontrer | Exigé | Exigé | Exigé | Amendement préalable |

## Types de sortie

Même si l'entrée routée était disponible, une traduction fermée resterait à
certifier :

| Résultat Account Status | Diagnostic | Sortie générique candidate |
|---|---|---|
| `Consumed` | aucun | `Consumed` |
| `Rejected` | `UnsupportedMessage` | `UnsupportedEventType` |
| `Rejected` | `CorruptedMessage` | `PermanentFailure` |
| `Rejected` | `UnsupportedDestination` | `PermanentFailure` |

Cette table est uniquement une hypothèse d'audit. R2 ne la certifie pas et
n'autorise aucune implémentation.

## Gates

| Gate | État après R2 | Motif |
|---|---|---|
| J1 Owner | Satisfait | Certifié par 4.9J-R1 |
| J2 Payload | Satisfait | Port et checksum déjà compatibles |
| J3 Catalogue | Ouvert | Extension réservée à 4.9J |
| J4 Mapper | Ouvert | Restauration réservée à 4.9J |
| J5 Consumer | Bloquant | Destination routée absente du contrat générique |

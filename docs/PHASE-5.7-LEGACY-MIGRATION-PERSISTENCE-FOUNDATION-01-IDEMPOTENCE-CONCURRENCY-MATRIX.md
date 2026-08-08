# Idempotence & Concurrency Matrix — Legacy Migration

| Situation | Mécanisme | Résultat |
|---|---|---|
| première écriture valide | append journal + index courant | Applied |
| même stream, sujet, révision et checksum | comparaison canonique SHA-256 | AlreadyApplied |
| même identité de révision, contenu différent | conflit de checksum | DivergentRevision |
| révision non monotone | contrôle sous verrou | VersionConflict |
| écritures concurrentes d'un même stream/sujet | advisory lock transactionnel | ordre déterministe |
| transaction déjà ouverte | savepoint local | rollback externe préservé |
| streams différents | clé de verrou incluant le stream | indépendance conservée |

Le journal est append-only. Le repository ne commit ni ne rollback une transaction qu'il n'a pas ouverte.

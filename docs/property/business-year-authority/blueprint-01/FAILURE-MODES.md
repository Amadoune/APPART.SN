# Failure Modes

| Cas | Responsable | Résultat/comportement |
|---|---|---|
| Instant invalide | Value Object d'entrée | Rejet avant autorité, aucune mutation |
| Timezone système différente | Autorité | Sans effet ; conversion UTC explicite |
| Replay identique | Autorité | Même `Resolved` et même année |
| Replay après changement d'année | Orchestration/autorité | occurredAt original, année originale |
| Version de policy divergente | Command ledger | V1 fait partie de l'identité contractuelle ; divergence fermée lors d'une autre version |
| ConstructionYear futur | PropertyTypePolicy | Rejet Domain normal, aucune mutation |

Il n'existe aucun mode configuration/storage unavailable en V1.

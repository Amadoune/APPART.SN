# Transport Matrix

| Champ Outbox | Champ Transport | Garantie |
|---|---|---|
| messageId | messageId | identité conservée |
| eventId | eventId | identité conservée |
| message type | type | EventType conservé |
| deliveryStatus | status | valeur inchangée |
| observedAt | observedAt | valeur inchangée |
| checksum | checksum | valeur conservée et revérifiée |

L'ordre JSON est fixe et la sérialisation/désérialisation est bijective.


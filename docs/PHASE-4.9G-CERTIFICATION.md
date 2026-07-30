# Phase 4.9G — Account Status Event Transport Certification

## Livrables

- `AccountStatusDeliveryMessage`;
- `AccountStatusDeliveryPayload`;
- `AccountStatusDeliveryMetadata`;
- `AccountStatusMessageId`;
- `AccountStatusTransportChecksum`;
- `AccountStatusTransportVersion::V1`;
- `AccountStatusTransportSerializer`;
- rejets fermés via `AccountStatusEventTransportException`.

## Matrice GO

| Critère | Résultat |
|---|---|
| mapping Event V1 mécanique | SATISFAIT |
| round-trip byte-for-byte | SATISFAIT |
| eventId conservé | SATISFAIT |
| messageId distinct et déterministe | SATISFAIT |
| checksum déterministe | SATISFAIT |
| métadonnées fermées | SATISFAIT |
| divergences rejetées | SATISFAIT |
| port Delivery générique directement implémenté | SATISFAIT |
| aucune donnée privée ajoutée | SATISFAIT |
| aucun Routing, Outbox ou HTTP | SATISFAIT |

## Validations

```text
Transport ciblé
7 / 7 tests
30 assertions
PASS

Architecture complète
574 / 574 tests
43 969 assertions
PASS

Suite complète
2 688 / 2 688 tests
51 784 assertions
PASS

PHPStan
0 erreur

Pint
PASS

Runtime Health
Healthy — 58 capacités, inchangé
```

La baseline PostgreSQL demeure `559 / 559`, `2 372 assertions`. Elle n'est pas
relancée car le sprint ne modifie aucune persistance.

## Verdict certifié

```text
4.9G
→ GO CERTIFIÉ
→ FERMÉ

4.9H Event Routing
→ OUVERT
```

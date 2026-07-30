# Phase 4.8J — Place Lifecycle Outbox Compatibility Certification

## Gates J1 à J6

| Gate | Preuve | Statut |
|---|---|---|
| J1 | amendements R2 et R3 appliqués exactement | conforme |
| J2 | migration 040 additive, réversible et isolée | conforme |
| J3 | trois types Place dans le catalogue générique | conforme |
| J4 | round-trip Writer / Mapper / Reader génériques | conforme |
| J5 | trois registrations Worker uniques | conforme |
| J6 | owners historiques et Runtime Health sans régression | conforme |

## Garanties

- invariant `checksum() === transportChecksum()->value`;
- JSON, octets, SHA-256, `eventId` et `messageId` 4.8G inchangés;
- matrice Ack / Retry / Quarantine 4.8I conservée;
- quatre tables uniquement dans `geography`;
- mapping direct et inverse `Geography ↔ geography`;
- aucun composant Outbox spécialisé;
- migrations 038 et 039 inchangées;
- Runtime Health **Healthy — 55 capacités**.

## Validations

```text
Tests ciblés Outbox / Geography / Runtime : 179 / 179, 655 assertions
PostgreSQL ciblé Outbox + Place : 48 / 48, 340 assertions
PostgreSQL complet : 535 / 535, 2 270 assertions
Architecture complète : 533 / 533, 42 223 assertions
Suite complète : 2 589 / 2 589, 49 811 assertions
Pint : PASS
Analyse statique : 0 erreur
Runtime Health : Healthy — 55 capacités
```

## Verdict

```text
4.8J — Place Lifecycle Outbox Compatibility
→ GO CERTIFIÉ et fermé

4.8K — Atomic Event Integration
→ AUTORISÉ
```

La décision de l'autorité de certification ouvre exclusivement 4.8K.

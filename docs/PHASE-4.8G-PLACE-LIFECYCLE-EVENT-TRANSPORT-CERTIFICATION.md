# Phase 4.8G — Place Lifecycle Event Transport Certification

## Livrables

- payload Delivery opaque;
- enveloppe de transport V1;
- identités typées `eventId` et `messageId`;
- checksum SHA-256 déterministe;
- sérialisation et restauration canoniques;
- port de routage pur;
- statuts et diagnostics fermés;
- matrices contractuelles et tests.

## Garanties

- le contrat événementiel 4.8F reste inchangé;
- le fait V1 est transporté comme chaîne opaque;
- la restauration est exacte byte-for-byte;
- toute altération d'identité, checksum ou payload est refusée;
- `eventId` et `messageId` sont strictement distincts;
- aucun routeur concret ou mécanisme de publication n'existe;
- aucune persistance, migration ou composition Runtime n'est ajoutée.

## Validation ciblée

```text
18 / 18 tests
61 assertions
```

## Validation finale

```text
Architecture : 519 / 519 tests, 42 038 assertions
Suite complète : 2 557 / 2 557 tests, 49 576 assertions
Pint : PASS
Analyse statique ciblée : 0 erreur
```

Aucune campagne PostgreSQL distincte n'est revendiquée : 4.8G ne crée ni
persistance, ni migration, ni requête.

## Verdict

**GO CERTIFIÉ**.

Le sprint 4.8G est fermé. Le seul sprint autorisé est
**4.8H — Place Lifecycle Event Routing**.

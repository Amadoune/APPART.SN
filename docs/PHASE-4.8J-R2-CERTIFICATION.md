# Phase 4.8J-R2 — Generic Delivery Contract Compatibility Certification

## Solution retenue

Amendement versionné direct des classes existantes :

- `PlaceLifecycleDeliveryPayload` implémentera le port marker générique;
- `PlaceLifecycleDeliveryConsumer` implémentera le port Consumer générique;
- le message générique sera validé puis converti vers l'enveloppe 4.8G
  existante;
- Router et Policy certifiés resteront les seuls propriétaires du routage et
  de la décision Ack/Retry/Quarantine.

## Garanties

- aucun nouveau Payload, Consumer ou adapter;
- aucune duplication de Writer, Reader, Mapper ou Worker;
- aucun changement des ports historiques;
- aucun impact sur les neuf owners existants;
- sémantique, octets et identités 4.8G inchangés;
- matrice de consommation 4.8I inchangée;
- aucune implémentation produite pendant R2.

## Validations

```text
Tests documentaires ciblés : 3 / 3, 9 assertions
Architecture complète : 525 / 525, 42 184 assertions
Suite complète : 2 574 / 2 574, 49 747 assertions
```

Aucune campagne PostgreSQL n'est requise : R2 ne crée ni migration, ni table,
ni requête.

## Verdict

**GO CERTIFIÉ**.

Le sprint 4.8J-R2 est fermé. Sa mise en application a révélé deux signatures
de sortie incompatibles, documentées par 4.8J-R3.

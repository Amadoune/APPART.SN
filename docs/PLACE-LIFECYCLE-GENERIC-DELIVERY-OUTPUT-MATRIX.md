# Place Lifecycle — Generic Delivery Output Matrix

## Checksum

| Étape | Type | Valeur normative |
|---|---|---|
| Entrée canonique | `string` | JSON canonique 4.8G inchangé |
| Calcul | `PlaceLifecycleTransportChecksum` | SHA-256 certifié 4.8G |
| Projection générique | `string` | propriété `value`, sans transformation |

Invariant :

```text
payload.checksum() === payload.transportChecksum().value
```

## Consommation

| Statut Router 4.8H | Décision 4.8I | Sortie générique R3 | Effet |
|---|---|---|---|
| `Routed` | `Acknowledged` | `Consumed` | Ack |
| `Deferred` | `Retry` | `RetryableFailure` | Retry |
| `RetryableFailure` | `Retry` | `RetryableFailure` | Retry |
| `Rejected` | `Quarantined` | `PermanentFailure` | Quarantaine |
| exception capturée | `Retry` | `RetryableFailure` | Retry |

La première colonne demeure la propriété du Router. La deuxième demeure la
propriété de la Policy. La troisième est une traduction totale et non
décisionnelle appartenant au Consumer existant.

## Fermeture

```text
Acknowledged ↔ Consumed
Retry        ↔ RetryableFailure
Quarantined ↔ PermanentFailure
```

Les sept autres résultats génériques ne sont pas des sorties possibles du
Consumer Place Lifecycle.

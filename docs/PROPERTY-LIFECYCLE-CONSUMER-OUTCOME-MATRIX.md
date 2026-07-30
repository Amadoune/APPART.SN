# Property Lifecycle Consumer Outcome Matrix

| Résultat de routage | Acquittement futur autorisé | Disposition attendue |
|---|---:|---|
| `Routed` | oui | consommation réussie après transfert réel |
| `Deferred` | non | conserver pour reprise lorsque la destination est prête |
| `RetryableFailure` | non | appliquer ultérieurement la politique de retry certifiée |
| `Rejected` | non | échec permanent explicite, jamais sink silencieux |

Cette matrice est contractuelle. Aucun Consumer n'est créé dans 4.2F et aucun résultat autre que `Routed` ne peut être assimilé à une consommation réussie.

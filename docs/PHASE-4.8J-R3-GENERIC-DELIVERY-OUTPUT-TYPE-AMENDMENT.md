# Phase 4.8J-R3 — Generic Delivery Output Type Compatibility Amendment

## Blocage reproductible

R2 autorise l'implémentation directe des interfaces génériques, mais deux
signatures certifiées restent incompatibles en PHP.

### Checksum

```php
PublicProjectionDeliveryPayload::checksum(): string

PlaceLifecycleDeliveryPayload::checksum(): PlaceLifecycleTransportChecksum
```

Le type objet n'est pas covariant de `string`. L'ajout de l'interface marker
produirait une erreur de compatibilité de méthode.

### Résultat Consumer

```php
PublicProjectionDeliveryConsumer::consume(
    PublicProjectionDeliveryMessage
): PublicProjectionDeliveryConsumptionResult

PlaceLifecycleDeliveryConsumer::consume(
    PlaceLifecycleTransportEnvelope
): PlaceLifecycleDeliveryConsumptionResult
```

R2 a décidé l'adaptation de l'entrée, mais n'a pas attribué la traduction des
résultats :

```text
Acknowledged / Retry / Quarantined
→ résultat générique exact à déterminer
```

## Conséquence

Une implémentation directe de R2 nécessiterait soit :

- de changer le type de retour `checksum()` et tous ses consommateurs 4.8G/H;
- de modifier le port générique historique;
- de créer un adapter interdit;
- d'inventer une traduction de résultat non certifiée.

Aucune option n'est autorisée par le mandat 4.8J actuel.

## Décision normative — checksum

```text
PlaceLifecycleDeliveryPayload::transportChecksum()
→ PlaceLifecycleTransportChecksum
→ calcul certifié 4.8G strictement inchangé

PlaceLifecycleDeliveryPayload::checksum()
→ string
→ retourne exclusivement transportChecksum()->value
```

L'opération typée existante est renommée `transportChecksum()`. Tous les usages
internes 4.8G et 4.8H qui exigent le Value Object utilisent exclusivement cette
opération. `checksum()` devient la projection scalaire requise par le port
générique.

Cette évolution est une modification de signature explicitement versionnée par
R3. Elle ne modifie ni l'algorithme SHA-256, ni l'entrée canonique, ni la valeur
produite. Aucune double source de calcul n'est autorisée.

## Décision normative — résultat de consommation

La traduction appartient exclusivement au
`PlaceLifecycleDeliveryConsumer` existant, à sa frontière générique :

```text
consume(PublicProjectionDeliveryMessage)
→ validation et restauration décidées par R2
→ consumeEnvelope(PlaceLifecycleTransportEnvelope)
→ Router 4.8H
→ Policy 4.8I
→ résultat Place
→ traduction R3
→ résultat générique
```

Matrice fermée :

| Résultat Place 4.8I | Résultat générique | Sémantique conservée |
|---|---|---|
| `Acknowledged` | `Consumed` | Ack définitif |
| `Retry` | `RetryableFailure` | Retry |
| `Quarantined` | `PermanentFailure` | Quarantaine, sans retry |

La bijection porte sur l'ensemble fermé des résultats émis par Place Lifecycle
et le sous-ensemble générique `{Consumed, RetryableFailure, PermanentFailure}`.
Aucun autre cas générique ne peut être produit par ce Consumer.

## Ownership

| Responsabilité | Propriétaire unique |
|---|---|
| Calcul SHA-256 typé | `PlaceLifecycleDeliveryPayload::transportChecksum()` |
| Projection scalaire du checksum | `PlaceLifecycleDeliveryPayload::checksum()` |
| Décision Ack / Retry / Quarantine | Policy 4.8I |
| Traduction vers le résultat générique | `PlaceLifecycleDeliveryConsumer` |
| Interprétation générique par le Worker | Worker PublicProjection historique |

Le Consumer ne redécide jamais la politique. Il applique une table totale après
la décision 4.8I.

## Preuve de conservation

- les champs et le JSON canonique 4.8G restent inchangés;
- le même Value Object est calculé une seule fois à partir des mêmes octets;
- `checksum()` expose exactement la propriété `value` de ce Value Object;
- les identités Event, Transport et Delivery ne changent pas;
- Router 4.8H et Policy 4.8I restent propriétaires de leurs décisions;
- chaque issue Place conserve exactement son effet Ack, Retry ou Quarantine.

## Compatibilité des neuf owners

Les ports PublicProjection, leurs types de retour et leurs dix cas génériques
restent inchangés. Les neuf owners historiques ne reçoivent aucune nouvelle
obligation et conservent leurs mappings actuels. Geography utilise seulement
trois cas génériques déjà existants.

La solution n'ajoute aucun Payload, Consumer, Writer, Reader, Mapper, Worker ou
adapter.

## Amendement versionné autorisé après certification

La reprise de 4.8J pourra exclusivement :

1. faire implémenter `PublicProjectionDeliveryPayload` par le payload existant;
2. renommer l'accès typé en `transportChecksum()` et mettre à jour ses seuls
   appels internes;
3. exposer `checksum(): string` comme projection exacte de `.value`;
4. faire implémenter `PublicProjectionDeliveryConsumer` par le Consumer
   existant;
5. conserver la consommation Place dans `consumeEnvelope`;
6. appliquer la matrice totale R3 à la frontière de retour générique.

Toute autre évolution exige un nouvel amendement.

## Contraintes

Aucune migration 040, mapping owner, catalogue, mapper, registration Worker,
table ou implémentation Outbox n'est autorisée pendant R3.

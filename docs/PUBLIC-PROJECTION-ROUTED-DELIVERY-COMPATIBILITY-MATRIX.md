# PublicProjection — Routed Delivery Compatibility Matrix

## Compatibilité des owners

| Population | Port utilisé | Impact R3 |
|---|---|---|
| Dix owners historiques | `PublicProjectionDeliveryConsumer` | Aucun : port et méthode `consume` inchangés |
| IdentityAccess / AccountStatus | `PublicProjectionRoutedDeliveryConsumerV1` | Nouvelle entrée additive `consumeRouted` |
| Futur owner exigeant une destination préalable | Port Routed V1 | Opt-in explicite |

Le Worker reste unique et générique. Son registre déclare explicitement, pour
chaque owner, le mode `legacy` ou `routed-v1`. Aucun test de classe ou branche
spécifique à IdentityAccess n'est autorisé.

## Compatibilité des composants

| Composant | Décision |
|---|---|
| Catalogue | Extension additive des deux types `account.status.*` en 4.9J |
| Mapper | Restauration additive de `AccountStatusDeliveryPayload` en 4.9J |
| Writer | Même Writer générique ; accepte la frontière Routed V1 |
| Reader | Même Reader générique ; restaure la frontière Routed V1 |
| Worker | Même Worker générique ; sélectionne le port déclaré par le registre |
| Consumer | Classe Account Status existante ; entrée additive versionnée |

## Gates

| Gate | Résultat R3 |
|---|---|
| J1 Owner | Satisfait par R1 |
| J2 Payload | Satisfait par R1 |
| J3 Catalogue | Solution additive définie, implémentation réservée à 4.9J |
| J4 Mapper | Solution additive définie, implémentation réservée à 4.9J |
| J5 Consumer | Solution contractuelle définie par Routed Delivery V1 |

## Absence de spécialisation

Sont interdits :

- un Writer, Reader, Mapper ou Worker IdentityAccess ;
- un adapter `AccountStatus*Outbox*` ;
- une branche Worker testant une classe Account Status ;
- une destination calculée depuis `eventType` dans l'Outbox ou le Consumer ;
- toute modification du chemin legacy des dix owners.

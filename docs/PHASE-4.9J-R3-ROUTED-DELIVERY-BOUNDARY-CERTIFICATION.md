# Phase 4.9J-R3 — Routed Delivery Boundary Amendment

## Décision normative

Une frontière générique, additive et versionnée est retenue :

```text
PublicProjectionRoutedDeliveryMessageV1
PublicProjectionDeliveryDestination
PublicProjectionDeliveryRoutingProofV1
PublicProjectionRoutedDeliveryConsumerV1::consumeRouted(...)
```

Elle transmet exclusivement une destination déjà décidée. Elle ne contient
aucune règle de routage.

## Conservation des fondations

- le Router 4.9H reste l'unique propriétaire de la destination ;
- le Consumer 4.9I conserve son opération `consume(message, destination)` ;
- l'entrée `consumeRouted` délègue à cette opération sans rerouter ;
- les ports historiques restent inchangés ;
- les dix owners historiques restent sur le mode `legacy` ;
- aucun composant spécialisé IdentityAccess n'est créé.

## Autorisations différées pour 4.9J

Après certification de R3, 4.9J pourra exclusivement :

1. créer les contrats génériques Routed Delivery V1 ;
2. ajouter `consumeRouted` au Consumer Account Status existant ;
3. étendre additivement catalogue et mapper pour `account.status.*` ;
4. étendre les composants génériques Writer, Reader et Worker au mode
   `routed-v1` ;
5. enregistrer IdentityAccess par configuration générique explicite ;
6. créer la migration 043 conformément à l'owner certifié par R1.

## Interdictions maintenues pendant R3

Aucun contrat, Consumer, Worker, Writer, Reader, Mapper, binding, table,
migration 043, événement, publication ou endpoint HTTP n'est implémenté.

## Verdict officiel

```text
4.9J-R2
→ NO GO CERTIFIÉ ET FERMÉ

4.9J-R3
→ GO CERTIFIÉ ET FERMÉ

4.9J
→ OUVERT POUR IMPLÉMENTATION APRÈS CERTIFICATION DE R3
```

## Validations

```text
Test documentaire ciblé : 1 / 1, 11 assertions
Architecture complète   : 581 / 581, 44 194 assertions
Suite complète          : 2 701 / 2 701, 52 057 assertions
PHPStan                 : 0 erreur
Pint                    : PASS
git diff --check        : PASS
Runtime Health          : Healthy — 58 capacités
```

La baseline PostgreSQL demeure `559 / 559`, `2 372 assertions`. Elle n'est pas
rejouée : R3 ne crée ni migration, ni table, ni requête, ni persistance.

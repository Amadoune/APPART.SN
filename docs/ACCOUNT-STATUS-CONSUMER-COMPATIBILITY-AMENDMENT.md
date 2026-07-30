# Account Status — Consumer Compatibility Amendment

## Contrats composés

Le Worker PublicProjection connaît exclusivement :

```text
PublicProjectionDeliveryConsumer::consume(
    PublicProjectionDeliveryMessage
): PublicProjectionDeliveryConsumptionResult
```

La fondation 4.9I certifiée expose exclusivement :

```text
AccountStatusDeliveryConsumer::consume(
    AccountStatusDeliveryMessage,
    AccountStatusRoutingDestination
): AccountStatusConsumptionResult
```

La destination est une décision déjà produite par le Router 4.9H. Le Consumer
4.9I ne route jamais et ne peut pas reconstruire cette décision.

## Blocage normatif

`PublicProjectionDeliveryMessage` ne transporte aucune
`AccountStatusRoutingDestination`. Le port générique ne fournit pas non plus
de résultat de routage. Une implémentation directe de son opération `consume`
devrait donc choisir l'une des voies interdites suivantes :

1. injecter ou appeler le Router depuis le Consumer 4.9I ;
2. déduire implicitement la destination unique actuelle ;
3. ignorer la destination certifiée ;
4. créer un Consumer ou un adapter spécialisé IdentityAccess ;
5. modifier le port générique et ses dix owners historiques.

Ces voies déplacent la propriété de la décision, modifient une fondation gelée
ou introduisent une spécialisation interdite par 4.9J-R1.

## Décision de l'amendement

```text
J5 Consumer Compatibility
→ NON SATISFAIT

4.9J-R2
→ NO GO PROPOSÉ

4.9J Outbox Compatibility
→ RESTE FERMÉ
```

Le problème n'est pas une conversion de type locale. Il manque une frontière
générique capable de transmettre une décision de routage déjà établie, sans la
rejouer.

## Amendement préalable requis

La reprise nécessite une décision versionnée sur une frontière de livraison
routée. Cette décision devra établir comment le Worker générique reçoit ou
obtient une destination déjà décidée, tout en garantissant :

- que le Router 4.9H reste l'unique propriétaire du routage ;
- que le Consumer 4.9I reste l'unique propriétaire de la consommation ;
- qu'aucune destination n'est déduite implicitement ;
- qu'aucun adapter IdentityAccess spécialisé n'est créé ;
- que les dix owners historiques restent compatibles ;
- que les ports génériques historiques ne changent pas silencieusement.

R2 n'autorise aucune implémentation de cette frontière.

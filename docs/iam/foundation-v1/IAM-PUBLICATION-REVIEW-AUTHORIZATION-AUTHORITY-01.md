# IAM Publication Review Authorization Authority 01

## Décision d'autorité

`IdentityAccess` est l'unique owner de l'autorisation Publication Review. `PublicationReview` consomme une décision ponctuelle et ne lit ni rôle, ni assignment, ni registre IAM.

L'autorité retenue est distincte de Report Moderation :

- rôle administratif dédié : `publication_reviewer` ;
- catalogue V1 fermé de quatre capacités ;
- Reader read-only owner-scoped ;
- décision fail-closed observée à un instant explicite.

Le rôle `moderator` et `ModeratorAuthorizationReaderV1` ne confèrent aucune capacité Publication Review.

## Séparation reviewer / approver

V1 autorise explicitement un même principal portant le rôle `publication_reviewer` à recevoir les quatre capacités. Aucune règle métier certifiée n'impose aujourd'hui un contrôle à quatre yeux ; l'inventer bloquerait le produit et modifierait la politique IAM.

La décision reste évaluée capacité par capacité. Une future politique explicite pourra donc attribuer `approve_publication` à un rôle distinct sans modifier le contrat Reader ni les commandes PublicationReview. Cette possibilité n'est pas active en V1.

## Frontière

La session IAM fournit exclusivement l'`AccountId`. La route choisit une capacité constante selon l'opération ; aucune capacité ni aucun actor n'est accepté depuis le client. Après `Allowed`, l'`AccountId` canonique devient mécaniquement l'actor transmis à PublicationReview.

Aucun code, contrat PHP, Provider, binding ou stockage n'est ouvert par cette décision.

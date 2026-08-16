# Authoring Owner Access Authority 01

## Décision

L’accès Authoring appartient à tout principal IAM authentifié dont la session est valide. Aucun rôle ni capability IAM supplémentaire n’est requis.

L’ownership naît lors de la création d’une ressource : l’`AccountId` issu exclusivement de la session devient `ownerAccountId`. Les opérations suivantes comparent ce même principal à l’owner persistant ou à une délégation explicite.

Cette décision décrit l’autorité déjà exécutée par le système. Elle ne crée aucun rôle, capability ou comportement.

## Modèle retenu

`RegisterAccount → credential autoritatif → Login → session valide → AccountId → création owner-scoped → contrôles d’ownership ressource`

Le principal local RC2 ne doit recevoir aucun `GrantRole` artificiel.

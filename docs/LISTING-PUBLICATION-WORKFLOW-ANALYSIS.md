# Listing Publication Workflow Analysis

## Alignement métier

Le domaine Listing certifié possède déjà dix états et vingt-sept transitions normatives. La fondation 4.1A les expose comme décision métier pure afin que les futures capacités n'introduisent pas une seconde taxonomie contradictoire.

`PendingReview` est décomposé en `Submitted` (en attente de prise en charge) et `UnderReview` (instruction active). `Approved` n'est pas durable : l'avis favorable et la publication forment une action atomique `ApproveAndPublish`. `Deleted` est exclu, car la conservation de l'historique et des preuves impose `Archived` comme état terminal. `ChangesRequested` et `Withdrawn` restent distincts d'un brouillon.

## Modèle

`ListingPublicationWorkflow::decide(state, action)` retourne toujours une décision immuable Allowed ou Denied. Une décision autorisée contient exactement une transition. Une décision refusée contient exactement un diagnostic typé.

Le workflow n'évalue ni preuve, ni disponibilité Property/Media, ni date d'expiration : ces préconditions appartiennent aux politiques Domain certifiées. Il répond exclusivement à la possibilité structurelle d'une action depuis un état.

## Déterminisme

La matrice est constante, sans horloge, I/O ou dépendance externe. Les 150 couples état × action sont classifiés; exactement 27 sont autorisés.

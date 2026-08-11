# P08 — Implementation Evidence

## Composition

- `DeterministicPublicationReviewExperience` : orchestration produit sans SQL.
- `PublicationReviewExperienceController` et Request stricte : transport HTTP uniquement.
- `PublicationReviewExperienceServiceProvider` : singleton nominatif.
- routes `/publication-review` protégées par la session IAM et throttle.
- vue `publication-review.blade.php` et styles responsive dédiés.

## Garanties prouvées

- un refus IAM arrête la chaîne avant lecture de Queue ;
- chaque opération utilise la capacité exacte ;
- Approve appelle Projection uniquement après succès ;
- actor dérivé de l'AccountId session ;
- aucune dépendance Search, SQL, Report Moderation ou Projection Updater dans la composition ;
- les Foundations IAM, PublicationReview et Listing Lifecycle restent inchangées.

## Limite de preuve terminale

L'implémentation et les contrats PostgreSQL sont validés. La démonstration navigateur réelle n'a pas pu être exécutée : le navigateur contrôlable bloque `appart.test` avant réponse et le client HTTPS système échoue avant connexion. Aucune capture artificielle n'est produite.

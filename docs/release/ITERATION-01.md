# RC2 Stabilization — Iteration 01

## Périmètre

Correction exclusive de la disponibilité de Submit Listing.

## Qualification

La cause n'était ni HTTP, ni IAM, ni Media, ni une règle de transition. Le snapshot initial du workflow Listing Lifecycle manquait pour les Listings créés par la surface publique.

La correction est placée chez l'owner de la création Listing :

`CreateListingDraftV1 transaction → Aggregate Draft → Workflow Draft → intent applied`

Cette composition garantit :

- initialisation déterministe ;
- replay `AlreadyApplied` ;
- rollback commun avec la création Aggregate ;
- aucune initialisation tardive ou implicite au moment du Submit ;
- règles de transition inchangées.

## Validations

- test Unit Submit/Handoff : PASS ;
- test PostgreSQL création, replay, rollback et Submit : PASS ;
- Architecture Authoring Operations : PASS ;
- PHPStan ciblé : PASS ;
- Pint ciblé : PASS ;
- navigateur : Media HTTP 201, Preview PASS, Submit PASS, état Submitted ;
- `git diff --check` : PASS.

Le rejeu final s'arrête à la Queue absente. Claim, BeginReview, Approve, Projection, Search et Public Listing ne sont pas exécutés dans cette itération.

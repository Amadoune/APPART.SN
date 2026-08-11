# Owner Matrix

| Élément | Owner | Source autoritative | Décision |
|---|---|---|---|
| état Submitted/UnderReview/Published | Listing Lifecycle | Publication Workflow Store | inchangé |
| règles de transition | Listing Lifecycle | ListingPublicationWorkflow | inchangé |
| révisions | Listing Lifecycle | ListingRevisionAllocatorV1 | réutilisé |
| file de revue | PublicationReview | projection des événements Lifecycle | à créer |
| claim/ordre de traitement | PublicationReview | queue owner-scoped | à créer |
| BeginReview command | PublicationReview, déléguée à Listing Lifecycle | SendToReview / orchestrateur | à créer |
| ApproveAndPublish command | PublicationReview, déléguée à Listing Lifecycle | PublishListing / orchestrateur | à créer |
| autorisation de revue/publication | IdentityAccess | contrat dédié Publication Review | à créer |
| projection publique | Public Projection | PublicListingProjectionUpdater | réutilisé |
| Search | Search | projection publique | inchangé |
| reports/cases | ModerationReports | Moderation Runtime | hors périmètre |

## Autorité IAM

Aucun rôle existant ne porte explicitement la responsabilité de première publication. Le rôle `moderator` ne peut pas être étendu par interprétation.

La Foundation requiert une décision IAM explicite et traçable. Le catalogue candidat est :

- rôle dédié : `publication_reviewer` ;
- capacités fermées : `review_publication` et `approve_publication`.

Ces noms constituent la proposition de Discovery ; ils devront être certifiés avant tout binding ou principal de démonstration. Une politique de séparation des acteurs peut ensuite imposer que l’approbateur diffère de l’auteur du Listing et, si décidée, du reviewer initial.

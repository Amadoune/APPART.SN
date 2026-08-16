# Authoring Draft Resume Implementation 01

## Portée

L'implémentation ajoute une reprise productive, read-only et owner-scoped par `listingId` explicite :

`GET /authoring/workspace/{listingId}` → session IAM → Portfolio → Resume Reader → snapshot Workspace.

Le parcours sans identifiant conserve le comportement « nouveau parcours ». Aucun Submit, aucune migration et aucune modification F6/F7, Projection ou Search ne font partie de ce chantier.

## Composition

- `AuthoringDraftResumeReaderV1` et son implémentation déterministe ;
- catalogue fermé `Available`, `NotFoundOrForbidden`, `Incomplete`, `StateConflict`, `Corrupted`, `DependencyUnavailable` ;
- binding singleton nominatif ;
- contrôleur HTTP protégé par la session IAM ;
- bootstrap JSON sûr dans le Workspace ;
- réhydratation frontend sans génération de nouveaux IDs en mode Resume.

## Verdict

L'implémentation est techniquement exécutable. Le verdict global reste **NO GO PROPOSÉ**, car le draft RC2 obligatoire n'est plus présent dans PostgreSQL local et ne peut donc pas être repris ni certifié.

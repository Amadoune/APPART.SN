# Authoring Draft Resume Authority / Blueprint 01

## Décision

« Reprendre un brouillon » signifie reconstruire, sans mutation, une vue de travail à partir d’un `listingId` sélectionné par un utilisateur authentifié. La session fournit exclusivement l’`AccountId`. Le `listingId` n’est qu’un sélecteur : le serveur résout et contrôle l’ownership, le `propertyId`, les versions, le Draft, l’Aggregate, le Workflow, Geography et Media.

La reprise ne crée ni identité, ni preuve Geography, ni événement, ni ledger. Elle s’arrête avant Submit.

## Modèle retenu

1. `GET /api/authoring/portfolio` fournit l’index owner-scoped.
2. Zéro parcours ouvre un nouveau journey ; un ou plusieurs parcours imposent une sélection explicite.
3. `GET /authoring/workspace/{listingId}` est l’entrée canonique de reprise.
4. Un `AuthoringDraftResumeReaderV1` compose les lectures existantes et retourne un snapshot fermé.
5. Le step est dérivé des faits persistés, jamais mémorisé côté navigateur.

## Gaps démontrés

- aucune composition de reprise actuelle ;
- Portfolio sans état Aggregate/Workflow ;
- aucune URL binaire owner-scoped permettant de réafficher une image après reload ;
- aucun proof context Geography persistant — il ne doit pas être inventé.

Ces gaps sont additifs et ne requièrent ni migration, ni modification F6/F7, ni nouvelle identité métier.

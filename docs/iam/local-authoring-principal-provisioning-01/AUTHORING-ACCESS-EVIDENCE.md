# Preuve d’accès Authoring

`GET /authoring/workspace` est atteint après le Login réel et rend le workspace HTTP attendu avec progression en sept étapes.

Le middleware `RequireIdentityAccessSession` a donc reconnu la session émise par IAM et transporté l’AccountId du nouveau principal.

La relecture du compte et des rôles confirme :

- compte actif ;
- une session active ;
- aucun rôle `publication_reviewer`, `moderator`, `moderation_auditor`, `particulier` ou `admin`.

Aucune Property ni aucun Listing n’a été créé. Le smoke check s’arrête à la preuve que l’AccountId de session est l’actor autoritatif que les Controllers Authoring transmettront aux Runtimes owner-scoped.

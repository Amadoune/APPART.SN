# Intégration RegisterAccount

`RegisterAccount` crée un Account non suspendu avec son credential autoritatif via `AccountRegistry`. Le Login résout email ou téléphone, vérifie le credential et crée la session sans consulter les rôles.

Par conséquent, un Account nouvellement enregistré, persistant et authentifiable peut utiliser immédiatement l’Authoring sans `GrantRole`.

La chaîne normative est :

`CredentialHashAuthorityV1 → RegisterAccount → AccountRegistry → Login HTTPS → Session → Authoring`

Les tokens de vérification font partie de l’Aggregate enregistré, mais leur état n’est pas une précondition du Login HTTP actuel.

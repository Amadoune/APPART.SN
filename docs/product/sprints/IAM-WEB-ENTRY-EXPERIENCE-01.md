# IAM Web Entry Experience 01

## Décision de périmètre

Le sprint a été ouvert comme composition Web des capacités IAM et Authoring déjà certifiées. Aucun composant IAM, Runtime, Property Authoring, Media, Listing Lifecycle, Search ou Projection n'a été modifié.

## Parcours audité

| Étape | Surface observée | État |
|---|---|---|
| Accueil | `GET /` | PASS — shell produit HTTP 200 |
| Se connecter | bouton `data-shell-action` sans navigation | MISSING |
| Authentification IAM | `POST /api/identity-access/login` | PRESENT |
| Session IAM | cookie `__Host-appart_session`, `Secure`, `HttpOnly`, `SameSite=Strict` | BLOCKED sur `http://appart.test` |
| Déposer une annonce | bouton `data-shell-action` sans navigation | MISSING |
| Workspace propriétaire | `GET /authoring/workspace`, protégé par `RequireIdentityAccessSession` | PRESENT mais inaccessible sans cookie accepté |
| Property Authoring | API owner-scoped protégée par la session IAM | PRESENT |
| Media Authoring | API owner-scoped protégée par la session IAM | PRESENT |

## Cause bloquante

L'environnement produit local est servi sur `http://appart.test` et `APP_URL` possède cette même valeur. La session IAM certifiée est volontairement émise dans un cookie préfixé `__Host-` avec l'attribut `Secure`. Un navigateur conforme refuse d'établir ce cookie depuis une réponse HTTP non sécurisée. La tentative HTTPS locale échoue avec `ERR_SSL_PROTOCOL_ERROR` : aucune terminaison TLS exploitable n'est disponible.

Modifier ou affaiblir le cookie IAM aurait constitué une modification interdite de la sécurité/session IAM. Une page de connexion seule aurait donc simulé un parcours sans pouvoir produire une session navigateur réelle.

## Limite du sprint

Aucune implémentation partielle n'a été introduite. La réouverture exige une qualification locale HTTPS distincte (certificat local de confiance, VirtualHost TLS et `APP_URL=https://appart.test`) ou une décision d'autorité explicite portant sur une autre stratégie locale compatible avec le modèle de cookie certifié.

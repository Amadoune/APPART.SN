# RC2 — Controlled Browser Local HTTPS Access Qualification 01

Date d'observation : 15 août 2026.

## Nature et périmètre

Qualification de l'environnement d'exécution uniquement. Aucun fichier applicatif, contrat IAM, middleware, route ou composant APPART.SN n'est modifié.

## Preuve avant correction environnementale

- `appart.test` est résolu localement vers `127.0.0.1`.
- Le port TCP 443 accepte les connexions.
- Apache référence `C:/laragon/etc/ssl/appart.test/appart.test.crt` et sa clé dédiée.
- Le certificat serveur porte `O=APPART.TEST Local Development, CN=appart.test`, couvre `*.appart.test` et reste valide jusqu'au 12 novembre 2028.
- Son émetteur est `APPART.TEST Local Development CA`.
- La construction de chaîne locale échouait en `PartialChain` : la CA locale n'était pas disponible dans le contexte de confiance interrogé.
- Chrome système, avec un profil temporaire isolé et sans extension, accédait à `http://appart.test/`.
- Le navigateur contrôlable refusait `https://appart.test/authoring/workspace` avec `net::ERR_BLOCKED_BY_CLIENT`, avant HTTP.

## Correction environnementale ciblée

La CA `C:/laragon/etc/ssl/appart.test/appart-test-ca.crt`, thumbprint `00DA79D093850830BF05AA003CCC2AAF9A709B0E`, a été confirmée/ajoutée exclusivement dans le magasin racine de confiance de l'utilisateur opérateur. Aucun mécanisme HTTPS global n'a été désactivé et aucune option d'ignorance du certificat n'est retenue comme solution.

Un diagnostic ponctuel avec ignorance du certificat a uniquement servi à confirmer que l'application répondait derrière la frontière TLS ; il ne constitue ni la correction ni la preuve de sortie.

## Preuve après correction

Chrome système, lancé avec un nouveau profil temporaire, sans extension et sans option d'ignorance TLS, reçoit le document HTML APPART.SN sur `https://appart.test/`. Cela démontre que DNS, VirtualHost, serveur HTTPS et certificat sont utilisables depuis un navigateur système neuf.

Le navigateur contrôlable continue néanmoins à refuser `https://appart.test/authoring/workspace` avec `net::ERR_BLOCKED_BY_CLIENT`, y compris depuis un nouvel onglet réclamé après la correction de confiance.

## Qualification de la cause

La portée résiduelle est **A — navigateur contrôlable uniquement**.

La première frontière restante est son mécanisme client de contrôle/navigation : il annule la navigation HTTPS locale avant toute réponse applicative. Ni APPART.SN ni IAM ne sont atteints. L'environnement autorisé n'expose pas l'identité de l'extension, de la règle ou du composant interne qui émet ce veto ; attribuer celui-ci plus précisément serait une conclusion sans preuve.

Cause exacte démontrable : **veto client du navigateur contrôlable sur la navigation HTTPS locale, après qualification positive du même endpoint par Chrome système**.

Contrainte restante : le composant interne précis responsable du veto n'est pas observable ni configurable de manière ciblée avec les surfaces disponibles.

## Session et sécurité

- aucun cookie injecté ou copié ;
- aucune session fabriquée ;
- aucun AccountId fourni par le client ;
- aucun reprovisioning IAM ;
- aucun rôle modifié ;
- la session existante n'est pas déclarée valide, car le workspace n'a pas pu être atteint normalement par le navigateur contrôlable.

## Absence de changement produit

Aucune correction produit n'est effectuée. Les seules mutations concernent le magasin de confiance local de l'opérateur et des profils Chrome temporaires externes au workspace.

## Verdict

**NO GO PROPOSÉ — RC2 CONTROLLED BROWSER LOCAL HTTPS ACCESS QUALIFICATION 01**

Le critère de sortie exige que le navigateur contrôlable ouvre réellement le workspace et obtienne une réponse HTTP applicative normale. Ce critère n'est pas satisfait. RC2 Iteration 11 Reopening 01 ne reprend pas et aucune Iteration 12 n'est ouverte.

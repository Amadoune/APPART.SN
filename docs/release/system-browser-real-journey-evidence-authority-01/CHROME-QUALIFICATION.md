# Qualification Chrome système

Configuration admise :

- exécutable Chrome système installé localement ;
- profil RC2 dédié dans un répertoire externe au repository ;
- profil neuf au début de la campagne ;
- aucune extension ;
- aucun proxy ajouté par la campagne ;
- aucune option `--ignore-certificate-errors` ou équivalent ;
- CA `APPART.TEST Local Development CA` approuvée normalement dans le magasin utilisateur ;
- HTTPS `appart.test` validé sans interstitial ;
- DevTools Network, Console et Application utilisables en lecture probatoire.

La qualification précédente démontre que Chrome système neuf reçoit le document APPART.SN sur HTTPS sans bypass après approbation de la CA locale. Le navigateur contrôlable demeure exclu de cette campagne en raison de son veto client certifié.

Toute divergence TLS, extension active ou profil non propre invalide le préflight.

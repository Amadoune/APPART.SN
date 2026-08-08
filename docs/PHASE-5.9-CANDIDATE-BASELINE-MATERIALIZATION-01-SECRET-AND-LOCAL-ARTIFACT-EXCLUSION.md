# Secret and Local Artifact Exclusion

Le scan des candidats `app`, `src`, `tests`, `docs` et des registres n'a trouvé :

- aucune clé privée PEM/OpenSSH ;
- aucun token AWS, Google, GitHub, Stripe ou Slack correspondant aux motifs contrôlés ;
- aucune affectation littérale de mot de passe, clé API ou secret réel ;
- aucun `.env`, certificat, dump, archive, log ou fichier de configuration locale candidat.

Les familles sensibles et locales restent ignorées : `.env*`, `auth.json`, `storage/*.key`, `storage/**`, caches IDE/PHPUnit, `vendor/**`, `node_modules/**` et sorties `public/build/**`.

Verdict : aucun secret observé dans l'arbre candidat ; aucun artefact local n'est inclus.

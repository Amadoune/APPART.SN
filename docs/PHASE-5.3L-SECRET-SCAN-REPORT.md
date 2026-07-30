# Phase 5.3L — Rapport renforcé de recherche de secrets

## Périmètre

Le scan couvre tous les fichiers suivis et non suivis candidats à la baseline,
hors chemins ignorés de dépendances, caches, builds et logs.

## Détections

Les contrôles portent sur :

- en-têtes de clés privées RSA, EC, OpenSSH et DSA ;
- identifiants AWS à haute confiance ;
- jetons GitHub ;
- jetons Slack ;
- jetons de type OpenAI ;
- affectations non vides dont le nom contient `PASSWORD`, `SECRET`, `TOKEN`,
  `PRIVATE_KEY` ou `ACCESS_KEY`.

Résultat : zéro occurrence.

Les fichiers `.env.example` et `.env.postgresql.example` ont été contrôlés
séparément. Les clés de chiffrement, mots de passe et secrets destinés à être
renseignés sont vides. Aucun fichier `.env` actif n'est présent à la racine.

## Chemins ignorés

Les dépendances, caches, vues compilées et logs sont couverts par `.gitignore`
et ne font pas partie de la baseline. Ils ne doivent jamais être ajoutés avec
une option de forçage.

## Outillage

Le poste ne fournit ni Gitleaks, ni TruffleHog, ni Git-secrets, ni
Detect-secrets. Le contrôle a donc utilisé un scan local multi-signatures
fail-closed qui n'affiche jamais la valeur détectée. Aucun téléchargement ou
outil externe n'a été introduit.

## Conclusion

Aucun secret, dump, clé privée ou jeton à haute confiance n'est identifié dans
les candidats à la baseline. La recherche ne remplace pas une rotation de
secrets si une exposition extérieure indépendante venait à être découverte.

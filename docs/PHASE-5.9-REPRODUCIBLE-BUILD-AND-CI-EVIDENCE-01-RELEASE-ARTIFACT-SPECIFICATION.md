# Release Artifact Specification

## État

Spécification candidate uniquement ; aucun artefact Release Candidate n'est prononcé.

## Contenu futur minimal

- code suivi du commit candidat, sans .git, tests de développement optionnels selon politique explicite ;
- vendor issu de composer.lock avec options de production documentées ;
- public/build issu de package-lock.json ;
- manifest Vite ;
- liste et checksums des migrations applicables ;
- métadonnées source, runtime et dépendances ;
- aucun .env, secret, cache local, log, clé ou credential ;
- configuration injectée séparée et inventoriée.

## Format et identité

Le format d'archive, la normalisation des timestamps, l'ordre des entrées, les permissions Unix et l'algorithme de compression ne sont pas définis dans le repository. Ils sont BLOCKED et doivent être fixés avant toute comparaison byte-for-byte.

L'identité future doit être SHA-256 sur les octets de l'archive finale, distincte du hash d'arbre Vite provisoire.


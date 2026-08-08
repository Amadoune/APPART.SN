# Artifact Integrity Evidence

## Artefact provisoire observé

Deux exécutions consécutives de npm run build sur le même worktree ont produit :

- 11 fichiers sous public/build ;
- hash d'arbre SHA-256 identique : 83ca37efedbe8d544baab45149560ecdbeff331cc0fd37467b0384d33a615455 ;
- aucune occurrence détectée pour APP_KEY, DB_PASSWORD, AWS_SECRET, PRIVATE KEY, BEGIN RSA ou password ;
- avertissement Vite identique : package optionnel fontaine absent.

## Limites

Le hash est calculé sur la concaténation ordonnée chemin relatif, SHA-256 fichier et taille. Il ne représente pas une archive normalisée. public/build est ignoré par Git, n'est pas archivé et provient de sources non commitées. Le paquet PHP et les migrations n'y figurent pas.

Statut : PARTIAL, NOT_RELEASE_ARTIFACT.


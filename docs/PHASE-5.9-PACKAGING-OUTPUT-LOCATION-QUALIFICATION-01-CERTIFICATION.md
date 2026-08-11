# Packaging Output Location Qualification 01

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Nature : Procedural Qualification / Evidence Procedure.

Objectif exclusif : démontrer depuis un clone R5 propre que deux packagings indépendants peuvent écrire vers deux destinations temporaires externes au worktree, tout en conservant strictement `git status --porcelain` vide avant, entre et après les exécutions.

Le contrôle de propreté du script reste inchangé. R5 reste immuable, R6 n'est pas ouverte et Evidence 10 est identifiée — non ouverte.

## Preuves terminales

- clone R5 dédié : PASS — `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
- propreté initiale : PASS ;
- destination A : répertoire temporaire externe dédié ;
- Packaging A : PASS — exit 0, 948,614 s, 9 522 fichiers ;
- propreté après A : PASS — `git status --porcelain` vide ;
- destination B : second répertoire temporaire externe distinct ;
- Packaging B : PASS — exit 0, 951,189 s, 9 522 fichiers ;
- propreté après B : PASS — `git status --porcelain` vide ;
- processus PHP/Bash résiduel : aucun ;
- `git diff --check` : PASS ;
- archive A/B : SHA-256 identique `deed895b03aa77a324c5fbb6dc40dc1493ecfa40a7117160ad5453675a53a136` ;
- inventaire d'arbre A/B : SHA-256 identique `c115b1328a56015631962c457c487b959f144059680705d009526bfd57a254fe`.

Les fichiers texte `artifact-sha256.txt` et `tree-root-sha256.txt` diffèrent octet à octet uniquement parce qu'ils consignent les chemins externes A et B distincts. Les empreintes d'archive et d'arbre qu'ils portent concordent.

## Conclusion

Deux packagings indépendants peuvent être produits hors worktree sans modification de R5 et sans affaiblir la gate de propreté. Aucune R6 n'est nécessaire. Ces artefacts de qualification ne sont pas recyclables dans Evidence 10.

## Verdict

GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY

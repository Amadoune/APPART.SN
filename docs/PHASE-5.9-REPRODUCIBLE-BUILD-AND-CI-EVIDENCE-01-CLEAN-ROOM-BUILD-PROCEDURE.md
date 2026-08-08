# Clean-Room Build Procedure

## Procédure future requise

1. partir d'un commit candidat autorisé et d'un clone sans fichiers non suivis ;
2. vérifier le SHA exact et un worktree propre ;
3. utiliser une image/runtime épinglé par digest ;
4. vérifier les hashes des manifests et lockfiles ;
5. installer PHP depuis composer.lock avec options documentées et sans secret ;
6. installer JavaScript avec npm ci ;
7. exécuter le build Vite ;
8. exécuter les gates qualité autorisées ;
9. assembler une archive avec ordre, permissions et timestamps normalisés ;
10. scanner les secrets et fichiers interdits ;
11. calculer SHA-256 de l'archive ;
12. générer et signer le manifeste ;
13. archiver artefact, manifeste et sorties CI avec rétention définie ;
14. répéter depuis un second environnement vierge et comparer les checksums.

## État d'exécution

NOT EXECUTABLE dans le repository actuel : aucun commit candidat propre, aucune image épinglée, aucun workflow CI, aucun script d'assemblage et aucun format d'archive déterministe.

Cette procédure est documentaire ; aucun pipeline ou script n'a été créé pendant le jalon.


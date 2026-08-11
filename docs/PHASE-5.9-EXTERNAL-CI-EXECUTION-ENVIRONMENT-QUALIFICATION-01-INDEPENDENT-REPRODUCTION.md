# External CI — Independent Reproduction Procedure

Après un run CI externe terminalement PASS :

1. désigner un second opérateur ou environnement sans réutiliser le workspace, les dépendances ou les artefacts du run CI ;
2. cloner le tag R5 depuis le repository officiel ;
3. vérifier tag annoté, commit exact et propreté ;
4. restaurer les runtimes et dépendances depuis les locks ;
5. rejouer la chaîne complète en fail-fast ;
6. produire deux packagings externes neufs ;
7. comparer entre eux archive, arbre et manifeste ;
8. comparer le Release Candidate indépendant avec celui du run CI ;
9. conserver identité de l'opérateur/environnement, logs, hashes et verdict terminal.

État : `BLOCKED`, faute de run CI externe et de second environnement désigné.

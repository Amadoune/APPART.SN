# Evidence 06 Identity Audit

Le clone neuf du tag annoté `phase-5.9-baseline-candidate-r4` résout exactement vers `058719f8aa154466056299b8c26bd7d51f944127` et son worktree est propre.

La candidate n'est toutefois pas admise par ses propres contrôles :

- `build/runtime.lock.json` déclare encore `phase-5.9-baseline-candidate-r3` ;
- le workflow écoute et vérifie encore R3 ;
- `tools/release/build-release.sh` exige encore R3.

Le contrôle intégré termine avec exit code 1. R4 contient bien le correctif Packaging, mais pas l'alignement d'identité nécessaire à sa propre exécution.

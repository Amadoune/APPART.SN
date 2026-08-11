# External CI — Trigger Procedure

Procédure conditionnelle, exécutable uniquement après désignation du repository officiel :

1. vérifier l'URL et l'owner autorisés ;
2. vérifier à distance que le tag annoté R5 existe et résout exactement vers `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
3. vérifier que le workflow R5 est visible et GitHub Actions autorisé ;
4. déclencher soit par la publication initiale du tag exact, soit par `workflow_dispatch` explicitement ciblé sur ce tag ;
5. relever immédiatement repository, workflow SHA, run ID, run attempt, event, ref et `GITHUB_SHA` ;
6. refuser tout run dont l'une de ces identités diverge ;
7. attendre un résultat terminal sans réutiliser un run historique.

Cette procédure n'autorise ni push, ni ajout de remote, ni déclenchement pendant le présent jalon.

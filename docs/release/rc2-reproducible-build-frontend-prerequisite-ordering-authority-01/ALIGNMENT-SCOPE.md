# Alignment Scope

Le futur correction gate pourra modifier uniquement :

- l'ordre du workflow Build/CI ;
- les identités successor de `runtime.lock`, workflow et packaging script ;
- les guards Build/CI directement nécessaires ;
- la documentation Release correspondante.

Base future : `6fc2f7944368c65f0cd87dfa68f550faa3461be9`. Tag futur : `appart-sn-release-candidate-rc2-r3`.

Aucun fichier produit, vue, test fonctionnel, migration ou lockfile ne peut être modifié.

# Clean-Room Invariant

Après un fresh checkout, `composer install` et `npm ci`, `public/build/manifest.json` est normalement absent :

- `public/build` n'appartient pas au tree RC2-R2 ;
- le chemin est ignoré par `.gitignore` ;
- `npm ci` installe les outils mais ne lance pas `vite build` ;
- le manifest est un output Vite, pas une source.

Un clean-room conforme ne doit ni copier ni réutiliser ce fichier depuis un autre worktree.

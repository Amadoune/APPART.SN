# Build Evidence

Le prérequis frontend clean-room a restauré les dépendances depuis `package-lock.json`, puis un vrai `npm run build` a produit `public/build/manifest.json`.

- Build Vite : PASS.
- Manifest : JSON valide, 9 entrées.
- Entrées CSS/JS et fichiers référencés : présents.
- Les corrections Pint sont uniquement PHP et ne modifient aucun input Vite.
- Contrôle final : arbre npm conforme, manifest et assets intègres.
- Aucun second build n'était requis.

`public/build` et `node_modules` restent ignorés et non suivis.

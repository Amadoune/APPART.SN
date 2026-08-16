# Historical Environment Dependency Audit

Le worktree applicatif local contient actuellement `public/build/manifest.json`, chemin ignoré par Git. Une campagne lancée dans ce worktree peut donc satisfaire implicitement Vite sans que le manifest appartienne à la candidate.

Cette observation démontre une possibilité de faux PASS environnemental pour l'ordre des prérequis. Elle ne suffit pas à invalider rétroactivement chaque certification historique : seule la campagne RC2-R2 clean-room démontre la divergence actuelle.

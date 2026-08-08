# npm Security Risk Qualification

`npm audit --package-lock-only --json` retourne une vulnérabilité HIGH : `nanoid` 3.3.16, transitive et `dev`, introduite par PostCSS. Advisory : `GHSA-2v37-7h3g-55p8`, CWE-835, boucle potentiellement infinie pour un générateur personnalisé appelé avec une taille zéro ; plage affectée `<3.3.17`.

Impact qualifié : disponibilité du processus de build/outillage si ce chemin API spécifique est exercé. Aucun usage applicatif direct ni exposition Runtime n'est démontré dans cette preuve. Le risque n'est pas implicitement accepté.

Une correction existe, mais nécessite une mise à jour contrôlée du lockfile dans un jalon séparé. `npm audit fix`, `npm update` et toute mutation de dépendance sont interdits ici.

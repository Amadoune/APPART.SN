# Compatibility Report

| Élément | Décision |
|---|---|
| RC2 actuelle | Préservée et immutable |
| Produit / Foundations | Aucun changement autorisé |
| Runtime pins | Préservés |
| Lockfiles | Préservés |
| Guards | Maintenus ou renforcés |
| Preuves externes actuelles | Restent externes, non ajoutées automatiquement |
| Packaging | Séparé et non ouvert |
| External CI | `MISSING / NOT EXECUTED` |
| Production Readiness | `NO GO` indépendant |

Le modèle est compatible avec Git : seul le nom symbolique futur est versionné ; la liaison cryptographique est créée ensuite par le tag annoté.

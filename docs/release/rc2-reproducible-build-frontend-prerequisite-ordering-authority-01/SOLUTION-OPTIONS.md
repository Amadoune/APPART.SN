# Solution Options

| Option | Décision | Motif |
|---|---|---|
| Build frontend réel avant Feature | Retenue | Reproductible, conforme aux vues packagées et sans donnée fictive |
| `withoutVite()` ou bypass de test | Rejetée | Découplerait les tests HTTP de l'intégration frontend qu'ils rendent réellement |
| Manifest synthétique | Interdit | Artifact fictif, non dérivé des sources frontend et trompeur pour la clean-room |

Le coût d'un build anticipé est acceptable et conserve toutes les suites historiques.

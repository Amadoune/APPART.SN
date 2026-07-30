# Historical Redirect End-to-End Analysis

La campagne 3.10E certifie les composants existants sans modifier leur implémentation. Le test de référence part d'une requête Laravel réelle, confirme l'absence de projection Current dans le store de production, puis traverse le qualifier et le resolver PostgreSQL résolus par le conteneur avant de vérifier le HTTP 301 et son `Location` exact.

Le scénario Current rend volontairement indisponibles les deux tables historiques dans une transaction. La réponse reste 200, ce qui démontre qu'aucune capacité historique n'est consultée sur ce chemin.

Les cinq états de qualification et les sept états de résolution sont injectés comme décisions PostgreSQL matérialisées. Aucun composant de chaîne n'est construit manuellement. Les indisponibilités sont provoquées séparément et rendent 503 sans détail SQL.

La certification ne révèle aucun fallback, accès métier, suivi de chaîne ou reconstruction de canonical.

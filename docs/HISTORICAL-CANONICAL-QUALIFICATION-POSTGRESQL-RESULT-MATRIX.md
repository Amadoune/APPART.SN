# Historical Canonical Qualification PostgreSQL Result Matrix

| Données | Résultat |
|---|---|
| aucune ligne exacte | `Unknown` |
| une décision intacte `current` | `Current` |
| une décision intacte `historical` | `Historical` avec `HistoricalCanonical` |
| au moins deux décisions intactes | `Ambiguous`, sans choix |
| checksum, révision, canonical, qualification ou intégrité invalide | `Corrupted` |

Les lectures répétées des mêmes données rendent strictement le même résultat et le même diagnostic.

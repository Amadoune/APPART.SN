# Source Decision

| Option | Déterminisme/replay | Verdict |
|---|---|---|
| A — année de occurredAt | Stable, explicite, déjà porté par la commande | **Retenue et rendue normative par ce Blueprint** |
| B — clock à chaque exécution | Change au retry et à la frontière annuelle | Rejetée |
| C — calendrier métier versionné | Aucun exercice distinct démontré | Rejeté en V1 |
| D — valeur caller | Double source et manipulation possible | Rejetée |

Le caller fournit l'instant métier, jamais l'année. L'autorité effectue seule la conversion UTC et l'extraction.

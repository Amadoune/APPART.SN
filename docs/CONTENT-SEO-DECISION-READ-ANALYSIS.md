# Sprint 3.8C — analyse d’architecture

## Choix : Option B — Source Snapshot

La projection SEO finale est une sortie de politique. Elle ne permet pas de retrouver sans ambiguïté les entrées `ListingSeoSource`, `SearchSeoSource` et `PropertySeoSource` exigées par l’Updater. L’Option A obligerait donc un futur adaptateur à inverser la décision SEO, ce qui est interdit.

Le snapshot conserve exactement les décisions sources déjà établies : les trois sources SEO, leurs révisions, l’historique canonical et l’instant de décision. Il n’inclut ni Geography publique ni Media public, qui conservent leurs owners et leurs sprints dédiés.

Le reader ne fait qu’une lecture ciblée par Listing. Le mapper réhydrate les Value Objects certifiés pour valider structure et checksum ; il n’appelle aucune policy SEO et ne calcule ni canonical, ni robots, ni JSON-LD.

L’écriture est monotone, transactionnelle et concurrente selon le protocole certifié du store Search : version supérieure appliquée, version identique idempotente ou divergente, version inférieure rejetée.

# APPART.TEST LISTING PUBLICATION REGISTRY SYNCHRONIZATION 01 — Transaction / Ownership Decision

## Comparaison

| Option | Propriété | Atomicité | Limite | Décision |
|---|---|---|---|---|
| A. mutation Aggregate par l'orchestrateur | mélange workflow et Aggregate | transaction locale possible | l'orchestrateur ne possède pas les données Aggregate requises | REJECTED |
| B. composition Application workflow + Registry | owner Application explicite possible | transaction locale possible | doit inventer ou recevoir revision, evidence, média et expiration | BLOCKED |
| C. handoff/event vers owner Listing | ownership clair | cohérence éventuelle | événements actuels ne portent pas les données requises ; critère immédiat non garanti | BLOCKED |
| D. mécanisme existant | aucun identifié | n/a | les use cases Aggregate existent mais ne sont pas composés au workflow | MISSING |

## Décision

Aucune option n'est implémentable dans le périmètre sans amendement contractuel. L'option B est la candidate minimale future, à condition qu'une autorité qualifie un command contract complet et sa source d'informations, sans duplication de règles.

L'owner métier demeure Listing Lifecycle. Une future composition ne devra prendre aucune décision de transition : elle devra déléguer aux workflow et use cases certifiés dans une transaction locale unique.

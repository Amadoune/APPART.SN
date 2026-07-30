# Historical Canonical Qualification PostgreSQL Analysis

La tranche 3.10CB persiste exclusivement une décision d'identité Content/SEO. Une ligne contient un identifiant causal, une canonical publique, la qualification `current` ou `historical`, une révision, un checksum et un état d'intégrité. Elle ne contient aucune destination.

Plusieurs lignes peuvent partager une canonical afin de rendre `Ambiguous` observable. La recherche exacte emploie l'index `(canonical, decision_id)`, un ordre stable et `LIMIT 2`. Zéro ligne signifie `Unknown`; une ligne intacte rend Current ou Historical; deux lignes intactes rendent Ambiguous; toute ligne illisible rend Corrupted.

Les décisions intactes imposent une canonical APPART.SN normalisée et une révision positive. L'état `corrupted` conserve explicitement les anomalies historiques sans permettre une qualification implicite.

# Recency Decision

La récence n'influence pas le rang v1.

Sont interdits : `now()` implicite, horloge runtime non persistée, âge calculé au moment du replay et ordre d'arrivée. Une future politique de récence exigera une source temporelle owner-scoped, versionnée et reproductible.

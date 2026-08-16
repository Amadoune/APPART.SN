# Semantics

En V1, `BusinessYear` représente **l'année civile UTC de la décision métier Property**. Il ne représente ni exercice fiscal, ni campagne commerciale, ni année de publication.

Cette sémantique correspond à son unique usage observé : fournir à `PropertyTypePolicy` la borne annuelle qui interdit un ConstructionYear futur au moment de Register ou Update.

Le Value Object reste un entier 1800–9999. Il n'est pas persisté comme fait de l'Aggregate et ne remplace pas `occurredAt`.

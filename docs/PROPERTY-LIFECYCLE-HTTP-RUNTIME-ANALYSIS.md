# Property Lifecycle HTTP Runtime Analysis

Le Runtime HTTP expose la capacité 4.2I par un adaptateur unique. Le Form Request valide exclusivement la forme de l'identité, de l'action, de la version et des deux instants. Le contrôleur dépend uniquement de `PropertyLifecycleEventOrchestrator` et traduit son résultat fermé.

Le workflow, le store, PostgreSQL, l'Outbox, le Worker, le Consumer, le routeur et les Projections restent invisibles depuis HTTP. Aucun état n'est lu et aucune transition ou identité événementielle n'est construite par cette couche.

Les instants canoniques reçus sont transmis sans normalisation aux Value Objects 4.2E. Aucune horloge ni valeur implicite n'est utilisée.

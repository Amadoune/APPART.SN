# Media Item Lifecycle Workflow Decision Table

Une décision `Allowed` contient exactement une transition et aucun diagnostic. Une décision `Denied` contient exactement un diagnostic et aucune transition.

Le workflow ne décide jamais du remplacement du média principal. Cette responsabilité demeure exclusivement dans `MediaCollection` et sera portée par un contrat certifié lors du gate 4.6C-R1.

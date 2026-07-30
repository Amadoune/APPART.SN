# Lead Lifecycle Runtime Orchestration Specification

L'orchestrateur exécute uniquement `read → version → workflow → append`. Pour un rejeu potentiel (`currentVersion = expectedVersion + 1`), il inspecte le dernier append certifié et ne rappelle pas le workflow. Une égalité d'action, d'acteur et d'instant produit `AlreadyApplied`; une divergence limitée au contexte produit `ContextDivergence`; toute autre divergence est un conflit.

Le graphe Laravel est paresseux et unique. Aucune lecture ou transaction n'est exécutée au bootstrap. Les catalogues d'éligibilité, événements, Outbox et HTTP sont hors périmètre.

Runtime Health expose l'orchestrateur comme 33e capacité. L'inspection vérifie uniquement le binding et sa constructibilité ; elle n'exécute aucune commande.

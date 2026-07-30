# Professional Status Persistence Analysis

La fondation 4.5B matérialise le workflow 4.5A sans décider de transition. Le journal PostgreSQL propriétaire est append-only et ordonné exclusivement par une version métier strictement positive.

L'initialisation écrit la version 1 en état `Active`. Elle ne constitue ni une transition ni un événement. Les seules écritures ultérieures admises sont `Active → Suspend → Suspended` et `Suspended → Reactivate → Active`.

Le repository ne dépend ni de l'Aggregate `Professional`, ni des établissements, mandats, catalogues d'éligibilité ou composants Runtime.

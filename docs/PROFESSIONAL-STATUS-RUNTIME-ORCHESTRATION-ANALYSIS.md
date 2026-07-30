# Professional Status Runtime Orchestration Analysis

L'orchestrateur coordonne exclusivement le workflow, le store contextuel, l'inspecteur et la politique de rejeu certifiés.

Le chemin nominal est `read → contrôle de version → workflow → append`. Le chemin de rejeu est `read → inspectLatest → replayPolicy`; il n'appelle jamais le workflow et ne reconstruit aucune transition.

Le contexte — acteur, instant UTC et version attendue — provient intégralement de l'appelant. Aucune horloge, identité ou version implicite n'est utilisée.

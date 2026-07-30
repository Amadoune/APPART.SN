# Professional Status Workflow Analysis

Le workflow de statut représente exclusivement la suspension et la réactivation d'un professionnel déjà enregistré. Il ne lit jamais l'Aggregate `Professional` et ne reprend aucune règle relative aux établissements, mandats ou preuves d'éligibilité.

L'enregistrement demeure une création explicite hors workflow. `initialState()` retourne donc `Active` sans produire de transition ni d'événement implicite.

Le workflow est une fonction pure de `(state, action)`. Il ne reçoit ni acteur, ni instant, ni version, ni dépendance extérieure.

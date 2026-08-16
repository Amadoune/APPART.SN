# Immutability Model

Modèle retenu : **A — immuable après première publication**.

Comme le path dépend uniquement du ListingId immuable, tout recalcul avant ou après publication rend la même valeur. Une republication, un catch-up ou un rebuild ne crée aucune nouvelle canonical.

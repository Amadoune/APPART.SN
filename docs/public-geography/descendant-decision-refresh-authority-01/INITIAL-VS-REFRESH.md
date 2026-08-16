# Initial versus refresh

Initial : ListingPublished → materialize(ListingId) → resolve terminal → representation V2 → writer.

Refresh : Geography mutation → affected terminal pages → rematerializeTerminal(PlaceId) → même representation engine → même writer.

Seule l'entrée diffère; aucune logique de représentation n'est dupliquée.

# Listing Publication Event Routing Contract

`ListingPublicationEventRouter::route` reçoit un `ListingPublicationEvent` restauré et retourne un `ListingPublicationEventRoutingResult` fermé.

Le routeur ne décide aucune transition, ne modifie aucun événement et ne déclenche implicitement ni Projection ni HTTP. Une future implémentation de production devra transférer réellement l'événement vers un propriétaire durable avant de retourner `Routed`.

Cette fondation ne fournit ni Fake de production, ni Null Object, ni fallback, ni Consumer.

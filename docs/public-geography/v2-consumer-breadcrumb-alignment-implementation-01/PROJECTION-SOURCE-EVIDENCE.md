# Projection source evidence

`CertifiedPublicListingProjectionSource` reconnaît une décision V2 Available, conserve `locality`, transforme les items en `(placeId,type,label)` et transporte la version autoritative. Il ne lit ni Search pour enrichir Geography, ni règle de slug ou d’URL.

Une décision V2 Unavailable ne produit aucune source publique; une décision corrompue reste fail-closed. Le chemin V1 existant est inchangé.

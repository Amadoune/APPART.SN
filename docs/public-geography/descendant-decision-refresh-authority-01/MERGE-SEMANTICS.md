# Merge semantics

Le source Place merged devient disabled et conserve son identité historique. Toute décision dont le vecteur contient la source est actualisée en V2 `Unavailable`, reason=`place_merged`, avec targetPlaceId informatif.

Aucune identité terminale n'est remplacée par la target, aucun breadcrumb n'est redirigé et aucune décision target n'est créée. Le traitement des Listings historiques relève de leurs owners.

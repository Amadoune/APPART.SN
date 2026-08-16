# Cache boundary

Politique minimale V1 : `Cache-Control: no-store`. Elle garantit que retrait, dépublication ou indisponibilité prennent effet sans invalidation CDN/cache partagée.

ETag peut refléter le checksum pour intégrité/diagnostic, mais aucune optimisation 304 ou cache immutable n'est requise. Une politique cache plus agressive est hors périmètre.

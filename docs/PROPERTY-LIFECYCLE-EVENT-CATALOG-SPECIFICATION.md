# Property Lifecycle Event Catalog Specification

Le catalogue reçoit exclusivement une transition déjà certifiée. Il ne consulte jamais le workflow et retourne exactement un événement ordonné.

Sept faits métier couvrent les douze transitions : activation, archivage, début de maintenance, fin de maintenance, indisponibilité, restauration de disponibilité et déclassement. Un type partagé conserve toujours la même action et la même sémantique, quel que soit l'état source autorisé.

Toute transition absente de la matrice lève `UnsupportedPropertyLifecycleEventTransition`. Il n'existe ni mapping implicite, ni événement générique, ni `PropertyLifecycleInitialized`.

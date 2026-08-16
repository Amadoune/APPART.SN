# Duplication Audit

Les 55 identités `consumerId:eventType:version` sont uniques. Les 55 couples `eventType@version` sont également uniques.

Le constructeur du registre rejette en outre toute duplication de clé. `place.lifecycle.renamed@1` ne figure pas dans les trois cas `PlaceLifecycleEventType` (`enabled`, `disabled`, `merged`) et utilise un consumer distinct. Aucun alias ni double binding fonctionnel n'est démontré.

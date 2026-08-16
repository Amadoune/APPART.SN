# Selectability Rules

Une Place est sélectionnable si et seulement si l'Aggregate Geography indique :

- `isEnabled() === true` ;
- `mergedInto() === null` ;
- son type correspond exactement au filtre demandé ;
- son parent correspond exactement au parent demandé, selon les relations existantes.

Une Place disabled ou source d'une fusion n'est pas retournée. La cible enabled d'une fusion peut être retournée sous sa propre identité. Le reader ne redirige jamais silencieusement l'ancien ID vers la cible.

Le Domain ne possède pas d'état `archived`; aucun état de ce nom n'est inventé. Il ne définit pas non plus une désactivation en cascade : les descendants sont évalués individuellement. La validité de création de hiérarchie reste celle de `PlaceType::acceptsParent`.

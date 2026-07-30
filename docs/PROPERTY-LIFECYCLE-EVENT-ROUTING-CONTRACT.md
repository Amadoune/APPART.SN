# Property Lifecycle Event Routing Contract

`PropertyLifecycleEventRouter::route(PropertyLifecycleEvent)` reçoit uniquement l'événement métier restauré et retourne `PropertyLifecycleEventRoutingResult`.

Résultats fermés :

- `Routed` : transfert réel démontré ;
- `Deferred` : destination momentanément indisponible ;
- `RetryableFailure` : transfert tenté et réessayable ;
- `Rejected` : événement non acceptable ou corrompu.

Le port ne connaît ni workflow, HTTP, Projection ou transport concret. Aucune implémentation de production n'appartient à 4.2F.

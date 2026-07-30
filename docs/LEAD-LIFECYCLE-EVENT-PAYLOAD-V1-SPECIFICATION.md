# Lead Lifecycle Event Payload V1 Specification

Le payload V1 contient, dans cet ordre : `eventId`, `aggregateType`, `leadId`, `transition`, `previousState`, `currentState`, `action`, `version`, `occurredVersion`.

`aggregateType` vaut toujours `LeadLifecycle`. `version` vaut `1`. `occurredVersion` est la version strictement positive résultant de l'append certifié et vaut au minimum `2`.

Le payload ne contient aucune donnée de contact, aucun `ListingId`, aucun `AdvertiserId`, aucune preuve d'éligibilité, aucun consentement, aucun sujet et aucun contenu libre.

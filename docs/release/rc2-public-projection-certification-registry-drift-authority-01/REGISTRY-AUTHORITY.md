# Registry Authority

Owner : Public Projection delivery runtime.

- Catalogue runtime : `PublicProjectionDeliveryConsumerRegistry`.
- Modèle d'entrée : `PublicProjectionDeliveryConsumerRegistration`.
- Composition autoritative : singleton de `PublicProjectionRuntimeServiceProvider`.
- Identité : `consumerId:eventType:payloadVersion`.
- Consumer par défaut : `public-projection` depuis `config/public_projection.php`.
- Résolution : type + version vers consumer et mode.
- Garde : le constructeur rejette toute clé dupliquée.

Ce registre n'est ni une migration, ni un read model, ni un simple manifeste documentaire : il est la map productive de dispatch de l'outbox Public Projection.

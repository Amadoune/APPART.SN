# Notifications Event Foundation

Le catalogue Event V1 traduit mécaniquement les résultats des trois Readers publics Notifications. `NotificationEventFactory` est la seule fabrique ; elle produit exactement un `NotificationEventV1` par lecture Preference, Template ou Channel.

Aucune décision, agrégation, transport, routing ou persistance d'événement n'est introduit.

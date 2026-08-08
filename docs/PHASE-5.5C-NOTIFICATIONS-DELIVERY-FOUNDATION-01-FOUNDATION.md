# Notifications Delivery Foundation

Chaque `NotificationEventV1` produit exactement une `NotificationDeliveryV1` via une factory pure. La Delivery propage le type Event, le statut et `observedAt`, sans décision, résolution ou source supplémentaire.

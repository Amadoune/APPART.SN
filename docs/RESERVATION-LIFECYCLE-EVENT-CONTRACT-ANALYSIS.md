# Reservation Lifecycle Event Contract Analysis

## Décision

Le sprint 4.3E introduit un contrat applicatif autonome sous `ReservationLifecycleEvent`. Il transforme exclusivement une transition déjà certifiée par le workflow 4.3A en un fait métier descriptif. Il ne décide, ne persiste, ne publie et ne transporte rien.

Le catalogue contient onze associations explicites et bijectives. Une transition absente est refusée par `UnsupportedReservationLifecycleEventTransition`; aucun événement générique et aucune branche implicite ne sont admis.

## Frontières

- Le workflow demeure propriétaire des transitions.
- `ReservationId` fournit l'identité applicative existante.
- `occurredVersion` provient explicitement de la version du journal à laquelle la transition prend effet.
- `version` désigne la version du payload, fixée à `1` pour V1.
- `eventId` est dérivé par SHA-256 des données normatives; aucune horloge ni identité aléatoire n'intervient.
- Le catalogue et le serializer ne sont liés ni au Runtime Laravel, ni à l'orchestrateur, ni à un transport.

## Invariants

Un événement contient une transition cohérente avec ses états et son action. Sa version de payload, ses métadonnées et son identité doivent concorder. La représentation JSON impose un ordre fixe et les options JSON normatives `JSON_THROW_ON_ERROR`, `JSON_UNESCAPED_SLASHES` et `JSON_UNESCAPED_UNICODE`.

L'initialisation du journal n'est pas une transition 4.3A et ne produit donc aucun événement implicite.

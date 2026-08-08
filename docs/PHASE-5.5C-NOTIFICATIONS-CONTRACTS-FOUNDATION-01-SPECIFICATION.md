# Notifications Public Read Contracts V1

## Statut

`PHASE-5.5C-NOTIFICATIONS-CONTRACTS-FOUNDATION-01` est ouvert à certification.
La capacité et l'owner unique sont `Notifications`.

## Frontières publiques

| Interface | Entrées | Sortie |
|---|---|---|
| `NotificationPreferenceReaderV1` | `NotificationSubjectKey`, `NotificationObservedAt` | `NotificationPreferenceResultV1` |
| `NotificationTemplateReaderV1` | `NotificationSubjectKey`, `NotificationObservedAt` | `NotificationTemplateResultV1` |
| `NotificationChannelReaderV1` | `NotificationSubjectKey`, `NotificationObservedAt` | `NotificationChannelResultV1` |

Les interfaces sont read-only. `NotificationSubjectKey` est opaque et public.
`NotificationObservedAt` est explicite, immutable, normalisé en UTC et canonique à
la microseconde. Aucune horloge implicite n'est fournie.

Chaque résultat V1 ne contient que son statut fermé. Aucun modèle, canal concret,
identité interne, endpoint Identity, diagnostic, contenu source ou PII n'est exposé.

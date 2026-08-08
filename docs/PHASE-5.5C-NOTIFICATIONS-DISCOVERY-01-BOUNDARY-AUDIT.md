# Notifications — Boundary Audit

## Owner recommandé

`Notifications` est l'owner unique de la capacité. La capacité réagit à des faits
métier confirmés ; un échec de notification ne remet jamais en cause la transition
du domaine source.

## Frontières

- les domaines sources décident leurs faits et exposent de futurs intents ou
  événements publics minimaux ;
- `Notifications` décide l'éligibilité locale, la préférence applicable, le modèle,
  le canal, l'identité d'émission, le retry et la suppression ;
- les transports externes remettent un message déjà décidé et ne deviennent
  jamais autorités métier.

Aucun domaine source n'écrit dans Notifications et Notifications n'écrit dans
aucun domaine source. Aucun cycle ni transaction distribuée n'est admis.

## Hors périmètre

Email/SMS/push concrets, fournisseur externe, HTTP, Event matérialisé, Delivery,
Outbox, Consumer, Routing, analytics, campagne marketing et implémentation.

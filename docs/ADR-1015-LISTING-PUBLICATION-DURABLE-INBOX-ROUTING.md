# ADR-1015 — Listing Publication Durable Inbox Routing

## Statut

Accepté pour proposition de certification 4.1EBR.

## Contexte

Le Worker Outbox doit transférer les événements vers une destination réelle avant acquittement. Aucun bus certifié ni handler métier de production n'existe encore.

## Décision

Adopter une Inbox PostgreSQL durable unique. Un dispatch mémoire est rejeté car il ne prouve pas la livraison. Un registre de handlers est reporté faute de responsabilités métier autorisées.

## Conséquences

`Routed` signifie insertion durable ou idempotence strictement confirmée. Le traitement de l'Inbox devient une capacité future distincte. Aucun événement n'est perdu ou marqué traité par la seule réception.

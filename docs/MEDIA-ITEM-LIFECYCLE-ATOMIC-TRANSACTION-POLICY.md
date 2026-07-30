# Media Item Lifecycle Atomic Transaction Policy

`PostgreSqlAggregateOutboxTransaction` fournit l'unique frontière transactionnelle. Elle englobe les écritures du journal Lifecycle, du contexte V1 et de l'Outbox `media`.

Garanties :

- aucun commit partiel ;
- rollback intégral en cas d'échec d'inspection, de construction ou d'écriture Outbox ;
- transactions imbriquées interdites ;
- rejeu identique conservant exactement une transition, un contexte et un message ;
- concurrence sérialisée par les verrous certifiés des stores ;
- identités `eventId` et `messageId` entièrement déterministes.

Aucune compensation métier n'est utilisée.

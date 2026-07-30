# Phase 4.7E — Event Payload V1

Le payload contient uniquement l'identité événementielle, le type d'agrégat, l'identité Lifecycle, la transition exacte, ses états, son action, la version du payload et la version causale.

Les métadonnées contiennent le type, la version, l'acteur, `occurredAt` et `recordedAt`, tous explicites.

L'identité SHA-256 dépend du type, de la version, de l'identité Lifecycle, de la transition exacte et de la version causale.

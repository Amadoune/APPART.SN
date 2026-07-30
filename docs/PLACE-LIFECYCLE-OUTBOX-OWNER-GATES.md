# Place Lifecycle Outbox Owner — Gates du futur 4.8J

## J1 — Ownership

Le mapping direct et inverse `Geography ↔ geography` doit être unique.

## J2 — Migration additive

La migration 040 crée uniquement les quatre structures génériques dans
`geography`; son rollback ne touche ni 038 ni 039.

## J3 — Compatibilité générique

Writer, Reader et mapper existants doivent fonctionner sans spécialisation.

## J4 — Catalogue

Les trois types `place.lifecycle.*` possèdent une entrée unique et restaurable.

## J5 — Worker

Le Worker générique reçoit exactement trois registrations Place Lifecycle.
Aucun Worker dédié n'est permis.

## J6 — Isolation et régression

Les neuf owners historiques restent inchangés et isolés. PostgreSQL,
Architecture, Runtime Health et suite complète doivent être verts.

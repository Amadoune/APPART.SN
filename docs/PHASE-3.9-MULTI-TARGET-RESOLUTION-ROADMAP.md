# Phase 3.9 — Multi-target Resolution

## 3.9A — Property-to-Listings Resolution Foundation

État : implémenté, en attente de certification.

Livrables : port paginé, diagnostic typé, adaptateur PostgreSQL, checkpoint lié à la Property,
index composite, tests Unit/Architecture/PostgreSQL et documentation de rollback.

## Après 3.9A

## 3.9B — Multi-target Delivery Strategy Foundation

État : implémenté, en attente de certification. L'Option B, résolution multi-cibles paginée au sein
de la consommation du message source, est retenue. Le Consumer et son contrat historique restent
inchangés jusqu'au sprint d'évolution dédié.

Les reprises 3.7E, 3.7F, 3.6F, 3.6G et 3.6C.8 restent fermées.

## 3.9C — Multi-target Consumer Integration

État : implémenté, en attente de certification. Le Consumer orchestre les pages 3.9B, conserve le
mono-cible 3.6C.6, arrête au premier échec et s'appuie exclusivement sur la redelivery Outbox pour
la reprise. Aucun fan-out ni stockage de progression n'est introduit.

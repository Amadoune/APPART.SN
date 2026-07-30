# Phase 4.7H-R1 — Administrative Action Lifecycle Outbox Owner Schema Analysis

## Audit

Avant 4.7H-R1, `AdministrationAudit` n'était pas reconnu par le résolveur Outbox et le schéma `administration_audit` ne possédait aucune des quatre structures génériques Outbox.

Une migration additive est donc nécessaire. Le Writer et le Reader génériques existants sont réutilisés sans spécialisation.

## Décision

Mapping canonique :

```text
AdministrationAudit ↔ administration_audit
```

Le résolveur reconnaît désormais neuf owners. La résolution directe détermine le schéma d'écriture ; la résolution inverse restaure l'owner exact pendant la lecture.

## Frontière

Ce sprint ajoute uniquement :

* le mapping d'owner ;
* les quatre structures compatibles ;
* les preuves d'isolation, de coexistence et de rollback.

Aucun catalogue Delivery, mapper spécialisé, Consumer, Worker ou intégrateur atomique n'est introduit.

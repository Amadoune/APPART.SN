# Phase 5.1J — Outbox Concurrency Reservation

## Statut

**RÉSOLUE — preuve historique conservée.**

La campagne PostgreSQL finale a observé une violation `23505` sur
`event_outbox_messages_event_id_key` pendant deux publications identiques.

Le test ciblé 5.1H a ensuite été exécuté en boucle. Les trois premières
exécutions ont passé ; la quatrième a reproduit la même violation. Le défaut
n'est donc ni un timeout ni un incident externe.

## Frontière

Le défaut appartenait à l'Outbox IAM certifiée en 5.1H. Aucune correction
implicite n'a été réalisée pendant 5.1J.

## Amendement requis

```text
A-5.1-IAM-OUTBOX-CONCURRENCY-01
→ GO CERTIFIÉ
→ FERMÉ
→ RÉSERVE LEVÉE
```

Objet minimal :

- rendre convergents les conflits simultanés sur `message_id` et `event_id` ;
- préserver le schéma owner-scoped et l'absence de FK ;
- ne modifier aucun contrat Event V1 ;
- conserver les résultats `Applied`, `AlreadyApplied`, `DivergentMessage` ;
- démontrer la non-duplication par campagne concurrente répétée ;
- recertifier 5.1H puis représenter 5.1J.

L'amendement a restauré la convergence, avec 20/20 campagnes concurrentes et
une campagne PostgreSQL globale terminale de 583 tests / 2 510 assertions PASS.
La représentation de 5.1J a ensuite été autorisée.

Le flaky Reservation Lifecycle observé dans la campagne historique reste hors
périmètre IAM et n'est pas retenu dans les preuves terminales.

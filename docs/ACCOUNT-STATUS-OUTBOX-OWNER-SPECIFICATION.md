# Account Status Outbox Owner — Normative Specification

```text
Module owner    : IdentityAccess
Schema owner    : identity_access
Aggregate type  : AccountStatus
Event namespace : account.status.*
Event types     : account.status.suspended
                  account.status.reactivated
Future migration: 043
```

Les tables futures devront suivre exclusivement la convention :

```text
identity_access.public_projection_outbox_messages
identity_access.public_projection_outbox_deliveries
identity_access.public_projection_outbox_cursors
identity_access.public_projection_outbox_replays
```

Writer, Reader, Mapper et Worker resteront génériques. Aucun composant
IdentityAccess spécialisé n'est autorisé.

## Gates avant 4.9J

1. J1 — owner et schéma uniques : SATISFAIT.
2. J2 — payload générique et checksum : SATISFAIT.
3. J3 — extension unique du catalogue Delivery : OUVERT.
4. J4 — restauration unique dans le mapper générique : OUVERT.
5. J5 — compatibilité normative du Consumer avec le Worker générique :
   BLOQUANT.

Le gate J5 exige un amendement contractuel préalable ou une décision
équivalente certifiée. R1 n'autorise aucune adaptation.

# Phase 5.1H — Atomic Event Integration

`IdentityAccessAtomicEventOrchestrator` enveloppe l'orchestrateur 5.1F sans en modifier le contrat. La callback transactionnelle exécute dans cet ordre :

1. mutations des stores owners ;
2. validation du couple opération/type d'événement ;
3. construction du message 5.1G ;
4. routing 5.1G ;
5. écriture du message et de toutes ses destinations ;
6. écriture du journal d'intent 5.1F ;
7. commit unique.

Toute incompatibilité, divergence, erreur de routing ou erreur Outbox remonte dans la callback et produit `RolledBack`. Aucun événement n'est publié pour une opération métier `Rejected`.

| Opération | Événements autorisés |
|---|---|
| ContactChange | email_changed, phone_changed |
| ProfileMutation | name_changed |
| AccountClosure | closure_requested, closed |
| Reopen | reopened |
| Authentication, Session, PasswordRecovery, ClaimSwap | aucun événement public V1 |

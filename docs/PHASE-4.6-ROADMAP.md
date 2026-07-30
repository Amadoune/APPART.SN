# Phase 4.6 — Media Item Lifecycle Roadmap

1. **4.6A-Discovery** — sélection et blueprint contractuel.
2. **4.6A** — Workflow Foundation : trois états, trois actions, deux transitions et sept refus fermés — GO proposé.
3. **4.6B** — Persistence Foundation : journal append-only 031, déterminisme, idempotence et concurrence, sans contexte de collection — GO proposé.
4. **4.6C** — Runtime Composition : bindings paresseux, alias unique et Runtime Health à 42 capacités — GO proposé.
5. **4.6C-R1** — Collection Transition Context Contract : contexte V1 immuable, décision propriétaire fermée concernant le média principal, acteur, instant et versions explicites — GO proposé.
6. **4.6C-R2** — Contextual Persistence Foundation : migration additive 032, atomicité avec le journal 031, rejeu, concurrence et inspection exacte — GO proposé.
7. **4.6D** — Runtime Orchestration : chemin nominal, rejeu par inspection exacte, huit résultats fermés et Runtime Health à 43 capacités — GO proposé.
8. **4.6E** — Event Contract Foundation : deux faits terminaux bijectifs, payload V1 confidentiel et identité SHA-256 canonique — GO proposé.
9. **4.6F** — Event Transport Foundation : payload opaque, enveloppe V1, checksum SHA-256 et routeur à résultat fermé — GO proposé.
10. **4.6G** — Event Routing Foundation : Inbox durable 033, idempotence stricte, rollback et concurrence multiprocessus — GO proposé.
11. **4.6G-R1** — Delivery Consumption and Runtime Composition : matrice fermée, bindings paresseux et Runtime Health à 45 capacités — GO proposé.
12. **4.6H-R1** — Outbox Owner Schema Foundation : réutilisation certifiée de l'owner historique `Media → media`, sans migration redondante — GO proposé.
13. **4.6H** — Outbox Compatibility : deux événements au catalogue, restauration opaque, Consumer et registre génériques à 45 couples — GO proposé.
14. **4.6I** — Atomic Event Integration : journal, contexte, inspection exacte et Outbox dans une transaction unique — GO proposé.
15. **4.6J** — HTTP Runtime : contexte V1 explicite, validation stricte, huit résultats fermés et délégation atomique unique — GO proposé.

Chaque étape possède un gate GO indépendant. Toute incompatibilité déclenche un amendement versionné préalable ; aucune adaptation opportuniste n'est autorisée.

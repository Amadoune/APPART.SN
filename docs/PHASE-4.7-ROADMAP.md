# Phase 4.7 — Administrative Action Lifecycle Roadmap

1. **4.7A-Discovery** — sélection, blueprint, dépendances, risques et gates.
2. **4.7A-R1** — Administrative Action Decision Context Contract : motif présent, décision four-eyes, auteurs/acteurs et diagnostics techniques.
3. **4.7A** — Workflow Foundation : cinq états, quatre actions, quatre transitions candidates et refus fermés.
4. **4.7B-R1** — Historical Persistence Coexistence Contract : statut du registre historique et stratégie additive.
5. **4.7B** — Persistence Foundation : journal append-only, mapper mécanique, versionnement, idempotence et concurrence.
6. **4.7C** — Runtime Composition : bindings paresseux et décision explicite Runtime Health (**GO certifié**).
7. **4.7C-R1** — Transition Execution Context and Replay Contract : acteur, instant, version, identités de décision et inspection exacte (**GO certifié**).
8. **4.7C-R2** — Contextual Persistence Foundation : persistance additive et atomicité avec le journal (**GO certifié**).
9. **4.7D** — Runtime Orchestration : chemin nominal et rejeu exact sans reconstruction (**GO certifié**).
10. **4.7E** — Event Contract Foundation : faits bijectifs, payload V1 minimal et politique de confidentialité (**GO certifié**).
11. **4.7F** — Event Transport Foundation : payload opaque, enveloppe V1 et routeur à résultats fermés (**GO certifié**).
12. **4.7G** — Event Routing Foundation : Inbox durable, idempotence, rollback et concurrence (**GO certifié**).
13. **4.7G-R1** — Delivery Consumption and Runtime Composition : matrice fermée et graphe Laravel paresseux (**GO certifié**).
14. **4.7H-R1** — Outbox Owner Schema Foundation : mapping `AdministrationAudit → administration_audit` et migration additive 037 (**GO certifié**).
15. **4.7H** — Outbox Compatibility : catalogue, mapper, Consumer et Worker génériques (**GO certifié**).
16. **4.7I** — Atomic Event Integration : journal, contexte et Outbox dans une transaction unique (**GO certifié**).
17. **4.7J** — HTTP Runtime : endpoint unique, validation stricte et délégation atomique (**GO certifié**).

Chaque étape possède un gate GO indépendant. Les sprints `R1/R2` sont obligatoires et ne peuvent être absorbés implicitement par le sprint suivant.

## Statut final

La Phase **4.7 — Administrative Action Lifecycle** est **GO FINAL**, terminée
de bout en bout et gelée.

Baseline de clôture :

- suite complète : **2 463/2 463**, 47 544 assertions ;
- PostgreSQL : **521/521**, 2 201 assertions ;
- Architecture : **500/500**, 40 309 assertions ;
- Runtime Health : **Healthy**, 50 capacités ;
- endpoint HTTP : présent et unique ;
- migrations 034 à 037 : certifiées et gelées.

Toute évolution future exige un amendement versionné préalable. La prochaine
capacité ne peut être ouverte que par une nouvelle Discovery/Blueprint ou une
décision explicite de la roadmap globale.

# Phase 4.8 — Place Lifecycle Roadmap

1. **4.8A — Discovery / Blueprint** : sélection, owner, frontière, états,
   transitions, dépendances, risques et gates — **GO CERTIFIÉ**.
2. **4.8A-R1 — Place Merge Context Contract** : preuve immuable de cible,
   versions source/cible, état, type, pays, acteur, instant et identité
   d'intention — **GO CERTIFIÉ et gelé**.
3. **4.8A-R2 — Workflow Decision Boundary Amendment** : propriété normative
   des décisions entre Workflow, Inspection, Orchestration et Persistance —
   **GO CERTIFIÉ et fermé**.
4. **4.8B — Workflow Foundation** : trois états, trois actions, quatre
   transitions appliquées et refus fermés — **GO CERTIFIÉ et fermé**.
5. **4.8C — Persistence Foundation** : journal append-only, coexistence avec
   toute persistance historique, idempotence, concurrence source/cible et
   rollback total — **GO CERTIFIÉ et fermé**.
6. **4.8D — Runtime Composition** : bindings paresseux, alias unique et
   extension additive de Runtime Health — **GO CERTIFIÉ et fermé**.
7. **4.8A-R3 — Replay Inspection and Target Evidence Boundary Amendment** :
   séparation normative entre inspection de rejeu et qualification de cible —
   **GO CERTIFIÉ et fermé**.
8. **4.8A-R4 — Replay Attempt Identity Amendment** : identité complète d'une
   tentative et autorité de classification exacte — **GO CERTIFIÉ et fermé**.
9. **4.8E — Runtime Orchestration** : chemin nominal, inspection exacte,
   rejeu déterministe et résultats fermés — **GO CERTIFIÉ et fermé**.
10. **4.8F — Event Contract** : faits bijectifs `PlaceDisabled`,
   `PlaceEnabled`, `PlaceMerged`, payload V1 minimal et confidentialité —
   **GO CERTIFIÉ et fermé**.
11. **4.8G — Event Transport** : enveloppe versionnée, payload opaque,
   checksum et restauration exacte — **GO CERTIFIÉ et fermé**.
12. **4.8H — Event Routing** : Inbox durable, idempotence, concurrence et
   rollback — **GO CERTIFIÉ et fermé**.
13. **4.8I — Delivery Consumption** : politique ack/retry/quarantaine et
    composition Runtime certifiées avant Consumer — **GO CERTIFIÉ et fermé**.
14. **4.8J-R1 — Outbox Owner** : audit et résolution de l'owner Geography,
    stratégie additive sans collision — **GO CERTIFIÉ et fermé**.
15. **4.8J-R2 — Generic Delivery Contract Compatibility Amendment** :
    résolution de la compatibilité des ports Payload et Consumer —
    **GO CERTIFIÉ et fermé**.
16. **4.8J-R3 — Generic Delivery Output Type Compatibility Amendment** :
    résolution des sorties checksum et résultat de consommation —
    **GO CERTIFIÉ et fermé**.
17. **4.8J — Outbox Compatibility** : catalogue, mapper, restauration,
    Consumer et Worker génériques compatibles — **GO CERTIFIÉ et fermé**.
18. **4.8K — Atomic Event Integration** : journal, contexte de fusion et
    Outbox dans une transaction unique — **GO CERTIFIÉ et fermé**.
19. **4.8L — HTTP Runtime** : endpoint unitaire, validation stricte,
    résultats fermés et délégation atomique unique — **GO CERTIFIÉ et fermé**.
20. **4.8 Final Certification** : audit complet, GO FINAL et gel versionné de
    Place Lifecycle — **GO FINAL, capacité gelée**.

Chaque étape possède un gate GO / NO GO indépendant. L'étape suivante reste
interdite tant que la précédente n'est pas certifiée GO.

Tous les jalons et amendements 4.8 sont certifiés et fermés. La Phase 4.8 est
**GO FINAL** et la capacité Place Lifecycle est gelée. Aucun nouveau jalon 4.8
n'est autorisé sans amendement versionné préalable.

## Séquence normative

```text
4.8A Discovery / Blueprint
    ↓
4.8A-R1 Place Merge Context
    ↓
4.8A-R2 Workflow Decision Boundary Amendment
    ↓
Workflow Foundation
    ↓
Persistence Foundation
    ↓
Runtime Composition
    ↓
4.8A-R3 Replay Inspection and Target Evidence Boundary Amendment
    ↓
4.8A-R4 Replay Attempt Identity Amendment
    ↓
Runtime Orchestration
    ↓
Event Contract
    ↓
Event Transport
    ↓
Event Routing
    ↓
Delivery Consumption
    ↓
Outbox Owner
    ↓
Generic Delivery Contract Compatibility Amendment
    ↓
Generic Delivery Output Type Compatibility Amendment
    ↓
Outbox Compatibility
    ↓
Atomic Event Integration
    ↓
HTTP Runtime
    ↓
GO FINAL
    ↓
Gel de la capacité
```

## Règle d'amendement

Toute incompatibilité avec une fondation certifiée, toute modification de la
machine approuvée ou toute extension de périmètre exige un amendement
versionné, audité et certifié avant reprise. Aucune adaptation opportuniste
n'est autorisée.

# Roadmap Phase 4 — Business Capabilities

## 4.1 — Listing Publication

- 4.1A — Workflow Foundation : dix états, quinze actions, vingt-sept transitions et diagnostics fermés — certifié GO.
- 4.1B — Persistence Foundation : journal PostgreSQL append-only, versionné, déterministe et concurrent — certifié GO.
- 4.1C — Runtime Composition : workflow, store et adaptateur PostgreSQL composés paresseusement dans Laravel, avec extension Runtime Health — certifié GO.
- 4.1D — Runtime Orchestration : lecture → décision → persistance, sans événement — certifié GO.
- 4.1EA — Event Contract Foundation : catalogue fermé, payload V1, métadonnées explicites, sérialisation canonique et matrice des vingt-sept transitions — certifié GO.
- 4.1EBA — Event Transport Foundation : adaptateur technique unique, identités séparées et contrat de routage sans acquittement silencieux — certifié GO.
- 4.1EBR — Event Routing Foundation : Inbox PostgreSQL durable, routeur de production unique et preuve de transfert avant `Routed` — certifié GO.
- 4.1EB — Outbox Compatibility : catalogue étendu, restauration PostgreSQL, Consumer de transport et vingt enregistrements Worker — certifié GO.
- 4.1E — Event Integration : atomicité workflow → store → Outbox, métadonnées explicites et rollback complet — certifié GO.
- 4.1F — HTTP Runtime : adaptateur de transition, validation de transport et mapping fermé des résultats — certifié GO.

## 4.2 — Property Lifecycle

- 4.2A — Workflow Foundation : six états, huit actions, douze transitions et diagnostics fermés — certifié GO.
- 4.2B — Persistence Foundation : journal PostgreSQL append-only, versionné, contraint et déterministe — certifié GO.
- 4.2C — Runtime Composition : workflow et store composés paresseusement avec extension Runtime Health — certifié GO.
- 4.2D — Runtime Orchestration : coordination déterministe lecture → décision → persistance — certifié GO.
- 4.2E — Event Contract Foundation : sept faits métier, payload V1 et matrice exhaustive des douze transitions — certifié GO.
- 4.2F — Event Transport Foundation : adaptateur opaque, intégrité byte-for-byte et port de routage fermé — certifié GO.
- 4.2G — Event Routing Foundation : Inbox PostgreSQL durable, idempotence stricte et routeur de production — certifié GO.
- 4.2H — Outbox Compatibility : catalogue étendu, round-trip PostgreSQL, Consumer de transport et vingt-sept inscriptions Worker — certifié GO.
- 4.2I — Event Integration : atomicité workflow → journal → événement → Outbox et rollback complet — certifié GO.
- 4.2J — HTTP Runtime : endpoint de transition unique, validation stricte et mapping fermé des résultats — certifié GO.

## 4.3 — Reservation Lifecycle

- 4.3A — Workflow Foundation : huit états, huit actions, onze transitions et diagnostics fermés — certifié GO.
- 4.3B — Persistence Foundation : journal PostgreSQL append-only, versionné, idempotent et concurrent — certifié GO.
- 4.3C — Runtime Composition : workflow et store composés paresseusement avec extension Runtime Health — certifié GO.
- 4.3D — Runtime Orchestration : lecture, contrôle de version, décision et persistance sans événement — certifié GO.
- 4.3E — Event Contract Foundation : onze événements, payload V1, identité déterministe, sérialisation canonique et matrice bijective — certifié GO.
- 4.3F — Event Transport Foundation : payload Delivery opaque, enveloppe V1, identités séparées, sérialisation canonique et port de routage pur — certifié GO.
- 4.3F-R1 — Routing Outcome Contract Amendment : résultat fermé à quatre statuts et signature du port amendée sans implémentation — certifié GO.
- 4.3G — Event Routing Foundation : Inbox PostgreSQL opaque, routeur déterministe, idempotence par `messageId` et résultats fermés — certifié GO.
- 4.3G-R1 — Delivery Consumption and Runtime Composition Amendment : matrice fermée, graphe Laravel paresseux et extension Runtime Health explicite — certifié GO.
- 4.3H-R1 — Outbox Owner Schema Foundation : owner `ReservationLifecycle`, migration additive 021 et lecture isolée par owner — certifié GO.
- 4.3H — Outbox Compatibility : catalogue, restauration PostgreSQL, Consumer de transport et trente-huit inscriptions Worker — certifié GO.
- 4.3I — Atomic Event Integration : transaction journal + événement + Outbox, rollback complet et production des onze événements — certifié GO.
- 4.3J — HTTP Runtime : endpoint unique, validation de transport et mapping fermé des sept résultats — certifié GO.

## Règles de Phase 4

Les capacités métier réutilisent les fondations certifiées des Phases 2 et 3. Chaque sprint conserve les contrats et fondations précédemment certifiés et n'introduit que la couche explicitement autorisée.

## 4.4 — Next Business Capability

- 4.4A-Discovery — Next Business Capability Discovery and Contract Blueprint : **Lead Lifecycle retenu**, dépendances et roadmap préventive documentées — certifié GO.
- 4.4A — Lead Lifecycle Workflow Foundation : quatre états, quatre actions, quatre transitions, diagnostics fermés et seize décisions déterministes — certifié GO.
- 4.4B — Lead Lifecycle Persistence Foundation : journal PostgreSQL append-only, mapper déterministe, versionnement, idempotence et concurrence — certifié GO.
- 4.4C — Lead Lifecycle Runtime Composition : workflow, mapper et store PostgreSQL composés paresseusement ; Runtime Health à 29 capacités — certifié GO.
- 4.4C-R1 — Lead Eligibility Contract Reconciliation : stratégie de réutilisation des ports, évidences et preuve Domain historiques — certifié GO.
- 4.4C-R2 — Lead Eligibility Decision Model Foundation : propriétaires uniques, matrices fermées, relation normative et révision explicite — certifié GO.
- 4.4C-S2 — Lead Eligibility Source Data Foundation : journal PostgreSQL de décisions cohérentes, port de matérialisation et Runtime Health à 30 capacités — certifié GO.
- 4.4C-S1 — Lead Eligibility Source Foundation : adaptateurs mécaniques des ports historiques et Runtime Health à 32 capacités — certifié GO.
- 4.4D-R1 — Lead Transition Context and Eligibility Policy Foundation : preuve limitée à la création, contexte d'audit explicite et port contextuel versionné — candidat à certification.
- 4.4D-R2 — Lead Contextual Persistence Foundation : évolution additive requise avant la reprise de l'orchestration 4.4D.

## Phase 4.4 — Lead Lifecycle

Les fondations Workflow, Persistence, Runtime Composition et Runtime Orchestration sont certifiées. Le Sprint 4.4E formalise exclusivement le contrat événementiel avant toute fondation de transport.
### Sprint 4.4F — Event Transport Foundation

La fondation contractuelle de transport Lead est implémentée sans routeur concret ni persistance. Elle conserve l'événement 4.4E opaque et prépare la future fondation de routage.

## 4.5 — Professional Status Lifecycle

- 4.5A-Discovery — Next Business Capability Discovery and Contract Blueprint : **Professional Status Lifecycle retenu**, propriété du module `Professionals` ; blueprint, gates et roadmap documentés — certifié GO.
- 4.5A — Professional Status Lifecycle Workflow Foundation : deux états, deux transitions, quatre refus et décisions fermées déterministes — certifié GO.
- 4.5B — Professional Status Lifecycle Persistence Foundation : journal append-only PostgreSQL, versionnement, idempotence, rollback et concurrence — certifié GO.
- 4.5C — Professional Status Lifecycle Runtime Composition : workflow et store composés paresseusement, Runtime Health étendu à 37 capacités — GO proposé.
- 4.5C-R1 — Professional Status Transition Context Contract Foundation : contexte explicite, inspection exacte et politique de rejeu fermée — GO proposé.
- 4.5C-R2 — Professional Status Contextual Persistence Foundation : persistance additive, inspection durable et atomicité avec le journal 027 — GO proposé.
- 4.5C-R3 — Professional Status Replay Policy Contract Amendment : politique applicable directement à l'action demandée sans reconstruction — GO proposé.
- 4.5D — Professional Status Runtime Orchestration : coordination déterministe nominale et rejeu exact sans reconstruction — GO proposé.
- 4.5E — Professional Status Event Contract Foundation : deux événements fermés, payload V1 minimal, identité SHA-256 et JSON canonique — GO proposé.
- 4.5F — Professional Status Event Transport Foundation : enveloppe opaque V1, identités séparées et routage fermé — GO proposé.
- 4.5G — Professional Status Event Routing Foundation : Inbox PostgreSQL durable, idempotence et concurrence — GO proposé.
- 4.5G-R1 — Professional Status Delivery Consumption and Runtime Composition : politique fermée, graphe Laravel paresseux et Runtime Health — GO proposé.
- 4.5H-R1 — Professional Status Outbox Owner Schema Foundation : owner additif `Professionals → professionals`, Writer/Reader génériques et isolation — GO proposé.
- 4.5H — Professional Status Outbox Compatibility : catalogue Delivery, mapper, Consumer et deux inscriptions Worker génériques — GO proposé.
- 4.5I — Professional Status Atomic Event Integration : journal contextuel, inspection exacte, événement et Outbox dans une transaction — GO proposé.
- 4.5J — Professional Status HTTP Runtime : endpoint unique, validation stricte et mapping fermé vers l'intégrateur atomique — GO proposé.

## 4.6 — Media Item Lifecycle

- 4.6A-Discovery — Next Business Capability Discovery and Contract Blueprint : **Media Item Lifecycle retenu**, propriété du module `Media` ; remplacement du média principal identifié comme gate contractuel préalable — GO proposé.
- 4.6A — Media Item Lifecycle Workflow Foundation : trois états, deux transitions terminales et décisions pures `(state, action)` — GO proposé.
- 4.6B — Media Item Lifecycle Persistence Foundation : journal append-only 031, mapper SHA-256, idempotence et concurrence — GO proposé.
- 4.6C — Media Item Lifecycle Runtime Composition : workflow et store composés paresseusement, Runtime Health à 42 capacités — GO proposé.
- 4.6C-R1 — Collection Transition Context Contract : contexte V1, décision de remplacement exclusivement fournie par `MediaCollection`, acteur, instant et versions explicites — GO proposé.
- 4.6C-R2 — Contextual Persistence Foundation : journal contextuel 032 additif, append atomique avec 031 et inspection exacte — GO proposé.
- 4.6D — Media Item Lifecycle Runtime Orchestration : chemin nominal et rejeu exact sans reconstruction, Runtime Health à 43 capacités — GO proposé.
- 4.6E — Media Item Lifecycle Event Contract Foundation : deux événements terminaux, payload V1 confidentiel et sérialisation canonique — GO proposé.
- 4.6F — Media Item Lifecycle Event Transport Foundation : événement opaque byte-for-byte, enveloppe V1 et routage contractuel fermé — GO proposé.
- 4.6G — Media Item Lifecycle Event Routing Foundation : Inbox PostgreSQL 033, routeur durable, idempotence et concurrence — GO proposé.
- 4.6G-R1 — Media Item Lifecycle Delivery Consumption and Runtime Composition : politique fermée, graphe paresseux et Runtime Health à 45 capacités — GO proposé.
- 4.6H-R1 — Media Item Lifecycle Outbox Owner Schema Foundation : owner historique `Media ↔ media`, Writer/Reader génériques et isolation certifiée — GO proposé.
- 4.6H — Media Item Lifecycle Outbox Compatibility : catalogue, mapper, Consumer et registre Worker génériques, round-trip byte-for-byte — GO proposé.
- 4.6I — Media Item Lifecycle Atomic Event Integration : journal, contexte et Outbox atomiques, inspection exacte et concurrence — GO proposé.
- 4.6J — Media Item Lifecycle HTTP Runtime : endpoint unique, contexte de collection explicite et délégation atomique — GO proposé.

## 4.7 — Administrative Action Lifecycle

- 4.7A-Discovery — Next Business Capability Discovery and Contract Blueprint : **GO certifié** ; `Administrative Action Lifecycle` retenu, propriété de `AdministrationAudit`.
- 4.7A-R1 — Administrative Action Decision Context Contract : **GO certifié** ; preuve du motif, dispositions four-eyes et acteurs explicites.
- 4.7A — Administrative Action Lifecycle Workflow Foundation : **GO certifié** ; cinq états, quatre actions, quatre transitions et décisions pures.
- 4.7B-R1 — Historical Persistence Coexistence Contract : **GO certifié** ; autorités par opération et absence de fallback.
- 4.7B-R2 — Historical Mirror Mutation and Enrollment Canonicalization Contract : **GO certifié** ; payloads exacts et canonicalisation.
- 4.7B — Administrative Action Lifecycle Persistence Foundation : **GO certifié** ; journal 034, miroir atomique, idempotence et concurrence.
- 4.7C — Administrative Action Lifecycle Runtime Composition : GO certifié ; graphe paresseux et Runtime Health à 47 capacités.
- 4.7C-R1 — Transition Execution Context and Replay Contract : GO certifié ; contexte V1 et rejeu par inspection exacte.
- 4.7C-R2 — Contextual Persistence Foundation : GO certifié ; stockage 035 et atomicité journal + contexte + miroir.
- 4.7D — Runtime Orchestration : GO certifié ; chemins nominal et rejeu exact séparés.
- 4.7E — Event Contract Foundation : GO certifié ; quatre faits bijectifs, payload V1 minimal et confidentialité stricte.
- 4.7F — Event Transport Foundation : GO certifié ; payload opaque, enveloppe V1 et résultats de routage fermés.
- 4.7G — Event Routing Foundation : GO certifié ; Inbox 036, routage durable, idempotence et concurrence.
- 4.7G-R1 — Delivery Consumption and Runtime Composition : GO certifié ; matrice fermée, bindings paresseux et Runtime Health à 50 capacités.
- 4.7H-R1 — Outbox Owner Schema Foundation : **GO certifié** ; mapping owner, migration 037 et isolation des neuf owners.
- 4.7H — Outbox Compatibility : **GO certifié** ; catalogue, mapper, Consumer et registre Worker génériques.
- 4.7I — Atomic Event Integration : **GO certifié** ; journal, contexte, miroir et Outbox dans une transaction unique.
- 4.7J — HTTP Runtime : **GO certifié** ; endpoint unique et délégation exclusive à l'intégrateur atomique.
- **Phase 4.7 : GO FINAL, terminée et gelée** — baseline 2 463 tests, 47 544 assertions, Runtime Health Healthy à 50 capacités.

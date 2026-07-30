# Phase 4.4 — Lead Lifecycle Roadmap

Le discovery **4.4A-Discovery** est certifié GO. Le Sprint **4.4A** formalise les quatre états, quatre actions, quatre transitions et la table exhaustive des seize décisions. Après sa certification, la séquence est :

1. **4.4A — Lead Lifecycle Workflow Foundation** : quatre états, quatre actions et quatre transitions — certifié GO.
2. **4.4B — Lead Lifecycle Persistence Foundation** : journal `contacts_leads`, mapper SHA-256, repository concurrent et rollback — certifié GO.
3. **4.4C — Lead Lifecycle Runtime Composition** : workflow et store composés paresseusement, Runtime Health porté à 29 capacités — certifié GO.
4. **4.4C-R1 — Lead Eligibility Contract Reconciliation** : réutilisation sans amendement des ports et preuves historiques ; aucun contrat concurrent — certifié GO.
5. **4.4C-R2 — Lead Eligibility Decision Model Foundation** : propriétaires uniques, matrices fermées, relation normative, révision et blueprint de matérialisation — certifié GO.
6. **4.4C-S2 — Lead Eligibility Source Data Foundation** : journal 023, matérialisation versionnée, idempotente et concurrente ; Runtime Health à 30 capacités — certifié GO.
7. **4.4C-S1 — Lead Eligibility Source Foundation** : adaptateurs mécaniques des ports historiques, reader partagé et Runtime Health à 32 capacités — certifié GO.
8. **4.4D-R1 — Lead Transition Context and Eligibility Policy Foundation** : éligibilité limitée à la création, contexte explicite et port contextuel versionné — candidat à certification.
9. **4.4D-R2 — Lead Contextual Persistence Foundation** : évolution PostgreSQL additive du journal et implémentation du port contextuel, requise avant orchestration.
10. **4.4D-R3 — Lead Contextual Replay Inspection Contract Amendment** : inspection typée et additive du dernier append — candidat à certification.
11. **4.4D — Lead Lifecycle Runtime Orchestration** : workflow → store contextuel, sans relecture d'éligibilité.
11. **4.4E — Lead Lifecycle Event Contract Foundation** : catalogue, payload minimal, métadonnées, versionnement, matrice exhaustive et politique de confidentialité.
10. **4.4F — Lead Lifecycle Event Transport Foundation** : payload Delivery opaque, enveloppe, identités séparées et port routeur retournant déjà un résultat fermé.
11. **4.4G — Lead Lifecycle Event Routing Foundation** : Inbox durable, routeur réel, idempotence et divergence.
12. **4.4G-R1 — Lead Delivery Consumption and Runtime Composition** : matrice d'acquittement, bindings du routeur/Inbox et décision Runtime Health.
13. **4.4H-R1 — Lead Outbox Owner Schema Foundation** : mapping `ContactsLeads → contacts_leads`, structures génériques additives, Writer/Reader et isolation.
14. **4.4H — Lead Lifecycle Outbox Compatibility** : catalogue Delivery, mapper, Consumer et inscriptions Worker.
15. **4.4I — Lead Lifecycle Atomic Event Integration** : journal contextuel + événement + Outbox dans la transaction existante — certifié GO.
16. **4.4J — Lead Lifecycle HTTP Runtime** : adaptateur HTTP déterministe vers l'intégrateur atomique — certifié GO.
16. **4.4J — Lead Lifecycle HTTP Runtime** : endpoint unique, validation de transport et mapping fermé.

## Gates préventifs

- 4.4C-S2 interdit tant que propriétaires, matrices, relation, révision et port de matérialisation ne sont pas certifiés en 4.4C-R2.
- 4.4C-S1 interdit toute création de ports ou preuves homonymes et attend la certification des données sources 4.4C-S2.
- 4.4D interdit tant que les deux sources d'éligibilité existantes ne sont pas certifiées en production.
- 4.4D reste interdit jusqu'à la certification de la persistance contextuelle additive 4.4D-R2.
- 4.4G interdit si le port 4.4F ne retourne pas un résultat fermé.
- 4.4H interdit tant que politique de consommation, composition du routeur et owner Outbox ne sont pas certifiés.
- 4.4I interdit tant que l'Outbox générique ne réalise pas un round-trip Lead exact.
- aucune migration historique ne sera modifiée.

## Sprint 4.4E — Lead Lifecycle Event Contract Foundation

Statut : implémenté, en attente de certification.

Le sprint définit le catalogue fermé, le payload V1, les métadonnées explicites, l'identité SHA-256, la sérialisation canonique et la politique de confidentialité. Aucun transport, routage, stockage événementiel, Outbox ou HTTP n'est introduit.
## Sprint 4.4F — Lead Lifecycle Event Transport Foundation

Statut : implémenté, en attente de certification.

Le sprint encapsule opaque­ment l'événement certifié, sépare `eventId` et `messageId`, définit l'enveloppe Delivery V1 et ferme le port et les résultats de routage. Aucune infrastructure d'exécution n'est introduite.
## Sprint 4.4G — Lead Lifecycle Event Routing Foundation

Statut : implémenté, en attente de certification.

La migration additive 025, l'Inbox propriétaire, le repository et le routeur durable sont introduits sans Outbox, Consumer, Worker, HTTP ni binding Runtime.
## Sprint 4.4G-R1 — Lead Delivery Consumption and Runtime Composition

Statut : implémenté, en attente de certification.

La matrice de consommation est fermée et le graphe de routage est composé paresseusement. Runtime Health est explicitement étendu de 33 à 35 capacités.
## Sprint 4.4H-R1 — Lead Outbox Owner Schema Foundation

Statut : implémenté, en attente de certification.

L'owner additif `ContactsLeads → contacts_leads` et la migration 026 rendent le Writer et le Reader génériques compatibles sans catalogue, Consumer, Worker ou production événementielle.
## Sprint 4.4H — Lead Lifecycle Outbox Compatibility

Statut : implémenté, en attente de certification.

Le catalogue, le mapper, le Consumer et les trois inscriptions Worker génériques sont étendus sans production événementielle ni intégration atomique.

# Phase 5.3 — Moderation & Reports

## Discovery / Blueprint

**Statut du document :** proposition de baseline documentaire

**Décision sollicitée :** `GO` ou `NO GO` du Discovery / Blueprint

**Nature :** architecture et gouvernance exclusivement

**Implémentation autorisée par ce document :** aucune

---

## 1. Objet

La Phase 5.3 construit la capacité **Moderation & Reports** : réception d'un
signalement, qualification, instruction contradictoire, décision, transmission
contrôlée d'une commande au domaine propriétaire de la cible, suivi du résultat
et clôture du dossier.

La capacité doit garantir :

- une autorité métier unique sur le dossier de modération ;
- la séparation des responsabilités entre signalement, instruction et décision ;
- une politique des quatre yeux démontrable ;
- une traçabilité append-only ;
- une protection stricte des identités, preuves et motifs sensibles ;
- une action sur les domaines cibles uniquement par leurs frontières publiques ;
- l'absence de transaction ACID, FK ou écriture SQL cross-domain ;
- des opérations idempotentes, rejouables et déterministes ;
- une disponibilité fail-closed.

La Phase 5.3 ne devient propriétaire ni des contenus modérés ni de leur
lifecycle. Elle décide et demande une action ; seul le domaine cible peut
appliquer cette action.

---

## 2. État réel du dépôt et règle de reprise

Le dépôt contient déjà un héritage `ModerationReports` comprenant notamment :

- l'Aggregate candidat `ModerationCase` ;
- des use cases de création, validation, constat, décision et clôture ;
- un contrat candidat `ModerationCaseRegistry` ;
- un accès candidat `ListingCatalog` ;
- des événements historiques :
  `ReportCreated`, `ReportValidated`, `FindingRecorded`,
  `DecisionIssued` et `ModerationCaseClosed`.

Ces éléments ne valent pas certification de Phase 5.3. Ils constituent une
matière de reprise soumise aux règles suivantes :

1. aucun contrat, événement ou accès historique n'est réputé public ou
   versionné par simple existence ;
2. aucun accès concret à un catalogue, repository, Aggregate ou store d'un
   autre domaine ne peut être conservé comme frontière cross-domain ;
3. Contracts Foundation devra choisir explicitement entre conservation,
   adaptation additive ou remplacement de chaque élément candidat ;
4. une seule autorité événementielle sera retenue avant toute exposition ;
5. aucun comportement historique ne pourra modifier implicitement une capacité
   gelée.

---

## 3. Objectifs fonctionnels

### 3.1 Signalement

- enregistrer un signalement authentifié concernant une cible supportée ;
- produire un accusé public homogène, sans confirmer l'existence ou l'état
  interne de la cible ;
- rendre les soumissions idempotentes ;
- détecter les divergences d'intent ;
- conserver le contenu sensible dans l'enclave propriétaire ;
- appliquer une politique anti-abus et de limitation des tentatives.

Le signalement anonyme est hors périmètre initial. Son introduction exigerait
une politique dédiée d'identité protégée, de preuve et d'anti-abus.

### 3.2 Qualification et file de travail

- qualifier l'admissibilité et la complétude du signalement ;
- accepter, rejeter ou demander un complément selon des résultats fermés ;
- construire une file de travail propriétaire, déterministe et reconstructible ;
- attribuer le travail par lease bornée ;
- empêcher qu'une projection de file devienne autorité métier.

### 3.3 Instruction et constats

- enregistrer des constats append-only ;
- référencer les preuves par identifiants opaques ;
- conserver les versions de politique utilisées ;
- interdire la suppression silencieuse d'une preuve ou d'un constat ;
- distinguer diagnostic interne et réponse publique.

### 3.4 Décision

- produire une décision fermée et versionnée ;
- imposer la séparation entre reporter, validateur, auteur du constat et
  décideur selon la politique certifiée ;
- gérer la supersession explicite d'une décision avant clôture ;
- refuser toute décision ambiguë ou insuffisamment instruite ;
- maintenir un historique inaltérable des décisions.

### 3.5 Handoff vers le domaine cible

- traduire une décision en demande d'action versionnée ;
- adresser cette demande exclusivement à la frontière publique du domaine
  propriétaire ;
- suivre `Requested`, `Accepted`, `Applied`, `Rejected`,
  `AlreadyApplied`, `Failed` et `Quarantined` ;
- tolérer le retry, le replay et les réponses hors ordre ;
- ne jamais considérer la décision de modération comme une mutation directe de
  Listing, Media, Account ou Professional Profile.

### 3.6 Clôture

- clôturer uniquement lorsque la politique de clôture est satisfaite ;
- conserver l'intégralité de la piste de décision ;
- rendre la clôture terminale dans le périmètre initial ;
- traiter une révision ultérieure par une procédure versionnée distincte.

Un workflow complet d'appel contentieux ou légal est hors périmètre de
Phase 5.3.

---

## 4. Périmètre et hors périmètre

### 4.1 Inclus

- dossiers de modération ;
- signalements authentifiés ;
- qualification et instruction ;
- constats et références opaques de preuves ;
- décisions et supersession contrôlée ;
- politique des quatre yeux ;
- file et portefeuille de travail des modérateurs ;
- handoff de commandes vers les owners cibles ;
- suivi technique et métier des résultats ;
- événements minimisés ;
- Outbox propriétaire ;
- HTTP public de signalement et HTTP privé de modération ;
- audit par consommation d'une frontière publique existante ;
- certification et gel final.

### 4.2 Exclus

- mutation directe des Aggregates Listing, Property, Media, Account ou
  Professional ;
- création de nouveaux états dans leurs lifecycles ;
- analyse antivirus, transcodage ou classification technique des médias ;
- définition des rôles IAM ou des habilitations administratives ;
- administration générale de la plateforme ;
- notification utilisateur multicanale ;
- moteur de recherche ou projection publique ;
- facturation, fraude paiement et litiges financiers ;
- modération automatisée ou décision par modèle ML ;
- effacement légal, anonymisation ou crypto-erasure ;
- conservation du blob de preuve hors d'un owner expressément certifié ;
- appel juridique complet et gestion contentieuse.

---

## 5. Ownership cible

| Responsabilité | Owner unique | Autorité |
|---|---|---|
| Dossier, signalements, constats et décisions | `ModerationReports.ModerationCase` | Aggregate Owner |
| Catalogue d'événements de modération | `ModerationReports` | Event Owner |
| File et portefeuille de travail | `ModerationReports.ModerationQueue` | Projection Owner |
| Intents et commandes de modération | `ModerationReports` | Operation Owner |
| Messages sortants de modération | `ModerationReports.Outbox` | Outbox Owner |
| API publique et privée de modération | `ModerationReports.HTTP` | HTTP Owner |
| État réel de la cible | domaine cible concerné | Aggregate Owner externe |
| Application de la sanction sur la cible | domaine cible concerné | Command Owner externe |
| Identité et habilitation | Identity & Access | Authority externe |
| Journal d'audit transverse | Administration Audit | Audit Owner externe |

`ModerationQueue`, l'Outbox et HTTP ne prennent aucune décision métier.

---

## 6. Modèle métier cible

### 6.1 Cycle du dossier

```text
Open
  → UnderReview
  → Decided
  → Closed
```

- `Open` : dossier créé et signalement enregistré.
- `UnderReview` : au moins un signalement est pris en charge ou un constat est
  enregistré.
- `Decided` : une décision courante valide existe.
- `Closed` : état terminal ; les écritures métier sont refusées.

La représentation exacte de ces états devra être arrêtée par Contracts
Foundation après confrontation avec l'Aggregate historique.

### 6.2 Invariants minimaux

- `CaseId` et association à la cible sont immuables ;
- chaque `ReportId`, `FindingId`, `DecisionId` et `IntentId` est unique ;
- les versions sont monotones ;
- les temps métier ne régressent pas ;
- un reporter ne valide pas son propre signalement ;
- une personne impliquée dans le signalement ne rend pas seule la décision ;
- l'auteur du constat ne rend pas la décision fondée sur ce constat ;
- une décision exige au moins un constat admissible ;
- une supersession référence explicitement la décision remplacée ;
- aucune écriture n'est acceptée après clôture ;
- aucune action cible n'est réputée appliquée sans résultat du target owner ;
- les retries ne créent ni nouvelle décision ni nouvelle sanction.

### 6.3 Cibles initiales

Le modèle prévoit les catégories `Listing`, `Media`, `Account` et
`ProfessionalProfile`. Une catégorie n'est activée techniquement que si :

1. un contrat public de lecture minimal existe ;
2. un contrat public de commande ou de handoff existe ;
3. les résultats fermés et l'idempotence sont certifiés ;
4. aucune capacité gelée n'est modifiée.

Une cible sans frontières certifiées reste fail-closed et indisponible.

---

## 7. Frontières publiques

Les noms ci-dessous sont des noms normatifs candidats. Leur création technique
relève de Contracts Foundation.

### 7.1 Frontières exposées

| Frontière candidate | Type | Consommateurs | Garantie |
|---|---|---|---|
| `SubmitModerationReportV1` | Command | HTTP public authentifié | réponse anti-énumération, idempotence |
| `ReadOwnModerationReportV1` | Query | reporter authentifié | vue minimale, sans diagnostic |
| `ReadModerationQueueV1` | Query | modérateur habilité | projection seulement |
| `ModerationCaseOperationsV1` | Commands | Runtime privé | résultats fermés, optimistic locking |
| `ReadModerationCaseV1` | Query | modérateur habilité | vue de travail filtrée |
| `ModerationDecisionReadV1` | Query | audit/intégrations autorisées | décision courante minimale |

### 7.2 Frontières consommées

| Besoin | Owner attendu | Règle |
|---|---|---|
| Compte authentifié et disponible | IAM | contrat public gelé uniquement |
| Habilitation modérateur/décideur | IAM/Administration owner | décision publique versionnée et fail-closed |
| Existence et identité minimale de la cible | target owner | reader public versionné |
| Application d'une action sur la cible | target owner | command gateway public versionné |
| Écriture d'audit transverse | Administration Audit | contrat public ou événement certifié |

Une absence de frontière publique ne peut être compensée par SQL, Repository,
Aggregate, Event replay, HTTP interne ou classe Application concrète.

### 7.3 Audits de frontière conditionnels

Discovery identifie les gates suivants, sans les ouvrir :

| Identifiant candidat | Objet | Bloquant avant |
|---|---|---|
| `A-5.3-IAM-MODERATOR-AUTHORIZATION-01` | vérifier l'existence d'une décision publique d'habilitation | Runtime et HTTP privé |
| `A-5.3-MODERATION-TARGET-READ-01` | vérifier les readers publics des cibles activées | Runtime |
| `A-5.3-MODERATION-COMMAND-HANDOFF-01` | vérifier les commandes publiques des target owners | orchestration et Outbox |
| `A-5.3-AUDIT-APPEND-BOUNDARY-01` | vérifier la frontière publique d'Administration Audit | Delivery |

Contracts Foundation devra conclure pour chacun : `frontière certifiée
réutilisable`, `fonctionnalité retirée` ou `amendement versionné requis`.

---

## 8. Architecture cible

```text
HTTP public / HTTP modérateur
              │
              ▼
      Contrats Application V1
              │
              ▼
   Moderation Runtime / Orchestrator
       │          │          │
       ▼          ▼          ▼
ModerationCase  Queue     Operation journal
  Persistence  Projection     │
       │                       ▼
       └──────── transaction locale ────────┐
                                             ▼
                                  Moderation Outbox
                                             │
                                  Transport / Routing
                                             │
                                             ▼
                              Target public command gateway
                                             │
                                             ▼
                                    Target owner result
```

### 8.1 Couches

- **Domain** : Aggregate, policies, Value Objects, décisions et invariants.
- **Application** : contrats publics, Commands, Queries, Results,
  orchestration et ports.
- **Infrastructure** : PostgreSQL, mappers, stores, Outbox, transport,
  consumers et adapters de frontières publiques.
- **Runtime** : composition owner-scoped, bindings lazy/singleton,
  disponibilité fail-closed et diagnostics internes.
- **HTTP** : authentification, autorisation, validation, mapping et sécurité,
  sans logique métier.

### 8.2 Transactions

- transaction locale au schéma propriétaire ;
- savepoints pour participation à une transaction englobante autorisée ;
- aucune transaction ACID avec un target owner ;
- mutation métier, journal d'intent et Outbox atomiques dans l'owner
  ModerationReports ;
- résultat cible intégré de façon idempotente dans une transaction locale
  ultérieure.

### 8.3 Persistance documentaire cible

Le schéma propriétaire pourra contenir, après certification :

- snapshot de dossier ;
- signalements et historique de qualification ;
- constats et références opaques ;
- décisions et supersessions ;
- journal d'intents ;
- projection de file et checkpoints ;
- commandes sortantes, destinations et résultats.

Toute migration sera additive, postérieure à la baseline gelée au moment du
sprint Persistence, avec rollback complet, sans FK cross-domain ni cascade.

---

## 9. Dépendances autorisées et interdites

| Source | Cible | Autorisée | Condition |
|---|---|---:|---|
| Moderation HTTP | contrats Application 5.3 | Oui | aucune logique métier |
| Runtime 5.3 | Persistence 5.3 | Oui | ports owner-scoped |
| Runtime 5.3 | IAM | Oui | contrat public certifié |
| Runtime 5.3 | target readers | Oui | contrat public versionné |
| Outbox 5.3 | target command gateway | Oui | transport certifié |
| Administration Audit | événements 5.3 | Oui | payload minimal certifié |
| Queue 5.3 | événements/états 5.3 | Oui | projection reconstructible |
| Application | Infrastructure | Non | inversion de dépendance |
| Moderation | Aggregate/repository cible | Non | frontière owner violée |
| Moderation | SQL cible | Non | écriture/lecture cross-domain |
| Moderation | Provider ou route cible | Non | détail Runtime |
| Moderation | événement historique non certifié | Non | contrat absent |
| Moderation | transaction ACID target | Non | ownership distinct |
| Base 5.3 | FK vers autre domaine | Non | couplage cross-domain |
| Modérateur | mutation directe de cible | Non | target owner exclusif |

---

## 10. Matrice des contrats

| Autorité | Opération | Nature | Résultats fermés minimaux | Idempotence / concurrence |
|---|---|---|---|---|
| Report | Submit | Command | `Applied`, `AlreadyApplied`, `DivergentIntent`, `TargetUnavailable`, `Rejected` | `intentId + checksum`; verrou cible/dossier |
| Report | Validate | Command | `Applied`, `AlreadyApplied`, `DivergentIntent`, `ForbiddenActor`, `VersionConflict`, `Closed` | intent + version attendue |
| Finding | Record | Command | mêmes résultats + `InsufficientEvidence` | intent + version attendue |
| Decision | Issue | Command | mêmes résultats + `FourEyesViolation`, `InvalidSupersession` | intent + version attendue |
| Case | Close | Command | `Applied`, `AlreadyApplied`, `NotClosable`, `VersionConflict` | intent + version attendue |
| Queue | Claim | Command | `Claimed`, `AlreadyClaimed`, `Unavailable`, `Empty` | lease bornée, skip locked |
| Queue | Read | Query | `Available`, `Empty`, `DependencyUnavailable` | checkpoint monotone |
| Own report | Read | Query | `Visible`, `NotVisible`, `Unavailable` | réponse anti-énumération |
| Target | Read eligibility | Query externe | `Eligible`, `Ineligible`, `Missing`, `Corrupted`, `DependencyUnavailable` | fail-closed |
| Target | Apply action | Command externe | `Applied`, `AlreadyApplied`, `Rejected`, `DivergentIntent`, `DependencyUnavailable` | commandId + checksum |
| Audit | Append | Command externe | `Applied`, `AlreadyApplied`, `Rejected`, `DependencyUnavailable` | recordId déterministe |

Les catalogues exacts seront fermés pendant Contracts Foundation. Aucun résultat
technique brut, exception PDO ou diagnostic interne ne devra traverser ces
contrats.

---

## 11. Catalogue événementiel candidat

Le catalogue public doit rester minimal. Les événements candidats sont :

| Événement candidat | Producteur | Consommateurs attendus |
|---|---|---|
| `moderation.report.submitted.v1` | ModerationReports | queue, métriques |
| `moderation.report.validated.v1` | ModerationReports | queue, audit |
| `moderation.decision.issued.v1` | ModerationReports | audit, handoff |
| `moderation.case.closed.v1` | ModerationReports | audit, projections |
| `moderation.target-action.completed.v1` | ModerationReports | audit, queue |

La qualification détaillée et les constats restent internes sauf besoin
contractuel démontré.

Les payloads publics excluent :

- texte libre du signalement ;
- preuve brute ou URI signée ;
- email, téléphone, adresse ou nom ;
- identité publique du reporter ;
- identité de session ;
- token, cookie, hash de credential ;
- diagnostic SQL ou stack trace.

Ils peuvent contenir des identifiants opaques, type de cible, type de décision,
version de politique, horodatage, `eventId` et checksum déterministes.

---

## 12. Runtime, HTTP et sécurité

### 12.1 Runtime

- providers owner-scoped ;
- bindings uniques, lazy et singleton ;
- aucune modification implicite du catalogue Runtime Health gelé ;
- disponibilité composée et fail-closed ;
- diagnostics fermés sans PII ni secret ;
- lecteurs externes résolus exclusivement par leurs contrats publics ;
- aucune mutation lors d'un health check.

### 12.2 HTTP

Surface candidate :

- soumission authentifiée d'un signalement ;
- lecture minimale de son propre signalement ;
- lecture/claim de la file privée ;
- validation, constat, décision et clôture privées ;
- consultation privée d'un dossier filtré.

Garanties :

- session IAM obligatoire ;
- auto-scope, sans identité d'autorité fournie par le client ;
- habilitation publique certifiée pour chaque opération privée ;
- `Idempotency-Key` UUID pour toute mutation ;
- refus des champs inconnus ;
- rate limiting par empreinte HMAC sans PII ;
- réponses `no-store`, `nosniff` et diagnostics internes supprimés ;
- uniformité des réponses empêchant l'énumération ;
- ETag/version attendue pour les mutations concurrentes si retenu au contrat.

---

## 13. Confidentialité, rétention et audit

- signalements, motifs détaillés et preuves sont confidentiels ;
- les vues reporter, modérateur et audit sont distinctes ;
- les références de preuves sont opaques et leur stockage physique reste chez
  l'owner certifié ;
- les révisions, décisions et traces d'intent sont append-only ;
- la rétention est définie avant Persistence Foundation ;
- aucune purge physique n'est autorisée sans politique légale versionnée ;
- l'observabilité utilise des identifiants techniques et métriques agrégées ;
- Administration Audit reste l'unique autorité du journal transverse ;
- l'historique interne du dossier demeure une preuve métier, pas un second
  système d'audit transverse.

---

## 14. Risques et traitements

| Risque | Gravité | Traitement obligatoire |
|---|---:|---|
| Frontière IAM d'habilitation absente | Critique | audit/amendement avant Runtime |
| Commande publique cible absente | Critique | target désactivée ou amendement |
| Accès direct à un domaine gelé | Critique | test Architecture bloquant |
| Décideur impliqué dans l'instruction | Critique | règle des quatre yeux atomique |
| Fuite de PII dans événements/logs | Critique | schémas fermés et tests négatifs |
| Double sanction sous concurrence | Critique | intent/checksum + unicité + locks |
| Décision appliquée mais résultat perdu | Élevée | Outbox/inbox, replay et réconciliation |
| Cible modifiée pendant l'instruction | Élevée | snapshot de référence opaque et revalidation |
| File affamée ou lease perdue | Élevée | lease bornée, reprise, métriques |
| Événements historiques concurrents | Élevée | catalogue V1 unique avant Event Foundation |
| Preuve supprimée ou devenue inaccessible | Élevée | référence immutable et politique de rétention |
| Appel légal non couvert | Moyenne | hors périmètre explicite et amendement futur |

---

## 15. Séquence des sprints

### 15.1 Séquence historique du Blueprint

Le tableau ci-dessous est conservé comme trace normative initiale. Ses trois
derniers identifiants prospectifs sont remplacés, sans réécriture historique,
par l'alignement `A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01` présenté en 15.2.

| Jalon | Objet | Critères GO | Motifs NO GO |
|---|---|---|---|
| **5.3A Discovery / Blueprint** | owners, frontières, modèle, risques et roadmap | périmètre exhaustif, owners uniques, dépendances explicites, gates identifiés | autorité dupliquée, frontière gelée implicite, cible sans owner |
| **5.3B Contracts Foundation** | Commands, Queries, Results, ports, événements candidats, HTTP/Runtime documentaires | catalogues fermés, quatre yeux formalisés, idempotence et confidentialité démontrées | résultat ouvert, PII exposée, dépendance concrète, cycle contractuel |
| **5.3C Boundary Gates** | audits/amendements strictement nécessaires | IAM, target readers, command gateways et audit disposent de frontières certifiées ou fonctions retirées | frontière manquante, contournement proposé, amendement non certifié |
| **5.3D Persistence Foundation** | stores, mappers, snapshots, revisions, queue et migrations locales | migrations additives, rollback, optimistic locking, concurrence PostgreSQL, aucune FK cross-domain | écriture externe, cascade, mapping non bijectif, preuve PG absente |
| **5.3E Runtime & Queue Foundation** | composition, readers/writers, availability, leases et diagnostics | bindings uniques, fail-closed, queue reconstructible, health conforme | provider ambigu, décision dans projection, accès interne externe |
| **5.3F Orchestration & Four-Eyes** | opérations atomiques locales et politiques d'acteurs | huit scénarios positifs/négatifs, rollback, savepoints, sérialisation et idempotence | auto-validation, double décision, transaction cross-domain |
| **5.3G Event / Transport / Routing / Delivery** | Event V1 minimal, transport, consumers, retry/replay/quarantaine | catalogue fermé, checksums, payload sans PII, routing déterministe | doublon d'autorité, événement excessif, replay non sûr |
| **5.3H Outbox & Command Handoff** | atomicité décision/intents/messages et résultats cibles | non-perte, non-duplication, convergence concurrente, target gateway public | mutation cible directe, Outbox partagée, atomicité cross-domain |
| **5.3I HTTP & Security** | APIs publique/privée, autorisation et anti-abus | auto-scope, quatre yeux, validation stricte, rate limit, mappings fermés | rôle fourni par client, diagnostics exposés, frontière IAM absente |
| **5.3J Operational & Audit Certification** | réconciliation, audit, runbooks et observabilité | audit complet, alertes, reprise, rétention et incidents documentés | action orpheline, audit incomplet, runbook non reproductible |
| **5.3K Final Certification & Freeze** | consolidation, registres, baseline et gel | toutes campagnes terminales vertes, aucune réserve bloquante, docs alignées | campagne non terminale, amendement ouvert, divergence documentaire |

Aucun jalon n'est ouvert automatiquement par ce Blueprint. Chaque passage exige
une décision d'autorité explicite.

### 15.2 Séquence normative alignée

La certification effective de 5.3I comme Command Handoff Integration / Listing
est conservée. La renumérotation suivante est uniquement prospective :

| Jalon | Objet | Statut |
|---|---|---|
| **5.3I Command Handoff Integration / Listing** | premier handoff inter-owner limité à Listing | GO CERTIFIÉ, FERMÉ |
| **5.3J HTTP & Security** | APIs publique/privée, autorisation et anti-abus | GO CERTIFIÉ, FERMÉ |
| **5.3K Operational & Audit Certification** | réconciliation, audit, runbooks et observabilité | GO CERTIFIÉ, FERMÉ |
| **5.3L Final Certification & Freeze** | consolidation, registres, baseline et gel | GO CERTIFIÉ, OUVERTE |

Cette séquence n'ouvre aucun jalon. Après certification de l'amendement
d'alignement, 5.3J devient seulement le prochain jalon autorisable par décision
explicite d'autorité.

---

## 16. Stratégie de certification

### 16.1 Preuves par jalon technique

- Unit ciblés sur tous les résultats fermés et invariants ;
- Architecture ciblée et complète ;
- PostgreSQL 18.x ciblé puis campagne complète terminale ;
- Feature pour Runtime, Delivery et HTTP selon le jalon ;
- tests de concurrence répétés pour décisions, claims et Outbox ;
- tests de rollback et savepoints ;
- PHPStan à zéro erreur ;
- Pint PASS ;
- `git diff --check` PASS.

Une erreur d'environnement n'est ni PASS ni FAIL fonctionnel. Une campagne sans
résultat terminal ne peut soutenir un GO.

### 16.2 Scénarios normatifs end-to-end

1. soumission idempotente d'un signalement ;
2. divergence du même intent détectée ;
3. validation refusée au reporter ;
4. constat puis décision avec acteurs distincts ;
5. décision concurrente convergeant vers un résultat unique ;
6. mutation métier et message Outbox atomiques ;
7. handoff appliqué par un target owner via contrat public ;
8. retry/replay sans double sanction ;
9. résultat cible hors ordre intégré sans corruption ;
10. clôture après action et audit complets ;
11. dépendance indisponible conduisant à un refus fail-closed ;
12. absence de PII/secrets dans transport, logs et réponses publiques.

### 16.3 Certification finale

Le GO FINAL exige :

- tous les jalons certifiés et fermés ;
- aucun amendement bloquant ouvert ;
- campagne Architecture complète terminale ;
- campagnes Unit et Feature complètes terminales ;
- campagne PostgreSQL complète terminale ;
- campagnes concurrentes répétées sans échec ;
- PHPStan, Pint et `git diff --check` conformes ;
- runbooks retry, replay, quarantaine et réconciliation ;
- matrice finale des compatibilités ;
- documentation racine et registres alignés ;
- identifiants de gel attribués par l'autorité ;
- migrations de la tranche enregistrées et gelées.

---

## 17. Compatibilité avec la baseline gelée

Phase 5.3 consomme les capacités gelées uniquement par leurs contrats publics.
Elle ne modifie notamment pas :

- Listing Publication et Property Lifecycle ;
- Media Lifecycle et Media Ingestion ;
- Public Listing Projection ;
- Runtime Health historique ;
- Delivery/Outbox historiques ;
- Identity & Access ;
- Property & Listing Authoring ;
- Professional Profile ;
- leurs migrations, événements, Runtime et HTTP certifiés.

Toute extension nécessaire d'une de ces capacités impose un amendement versionné
préalable. Une migration 5.3 ne peut qu'être additive après la dernière migration
gelée connue lors de Persistence Foundation.

---

## 18. Décision proposée

### Recommandation

**Phase 5.3 — Moderation & Reports — Discovery / Blueprint : GO PROPOSÉ**

Justification :

- la capacité et son owner métier sont identifiés ;
- les responsabilités des domaines cibles restent intactes ;
- la politique des quatre yeux est structurante et vérifiable ;
- les lectures et écritures cross-domain sont interdites ;
- les frontières potentiellement manquantes sont identifiées avant
  implémentation ;
- le train de certification est ordonné jusqu'au GO FINAL ;
- aucune implémentation, migration, interface, événement, Provider, route ou test
  n'est introduit par ce jalon.

En cas de GO d'autorité, le seul jalon suivant proposé est :

`Phase 5.3B — Contracts Foundation`

Les audits de frontière restent identifiés mais non ouverts jusqu'à la décision
issue de Contracts Foundation.

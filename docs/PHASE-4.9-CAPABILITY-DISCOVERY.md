# Phase 4.9A — Discovery / Blueprint

## 1. Décision enregistrée

La capacité retenue est **Account Status Lifecycle**, sous la propriété
exclusive du bounded context `IdentityAccess`.

Verdict officiel :

```text
Documentation 4.9A
→ RECEVABLE

4.9A Discovery / Blueprint
→ GO CERTIFIÉ

Développement
→ autorisé exclusivement par jalon certifié

4.9A-R1
→ GO CERTIFIÉ
→ FERMÉ

4.9B
→ GO CERTIFIÉ
→ FERMÉ

4.9C-R1
→ GO CERTIFIÉ
→ FERMÉ

4.9C
→ GO CERTIFIÉ
→ FERMÉ

4.9D
→ SUSPENDU AVANT IMPLÉMENTATION
```

Le GO conditionnel est levé. Le Sprint 4.9A est fermé et le seul jalon ouvert
La reprise de `4.9D — Account Status Runtime Composition` exige une source
Runtime certifiée pour `AccountRegistry`.

## 2. Sélection objective

Échelle : 1 faible, 5 fort. Pour « risque », 5 signifie un risque faible.

| Candidat | Frontière | États prouvés | Owner | Valeur | Dépendances | Risque maîtrisable | Total / 30 |
|---|---:|---:|---:|---:|---:|---:|---:|
| **Account Status Lifecycle** | 4 | 4 | 5 | 5 | 4 | 3 | **25** |
| Moderation Case Lifecycle | 3 | 3 | 5 | 5 | 2 | 2 | 20 |
| Professional Establishment Lifecycle | 3 | 3 | 4 | 3 | 3 | 3 | 19 |
| Professional Mandate Lifecycle | 3 | 3 | 3 | 4 | 2 | 2 | 17 |
| Payment Lifecycle | 2 | 5 | 4 | 4 | 1 | 1 | 17 |

`Account` prouve déjà une identité, une version optimiste, un état initial
actif, les actions `suspend` et `reactivate`, les faits correspondants et un
port propriétaire `AccountRegistry`. Le candidat offre donc la meilleure
valeur incrémentale restante.

Les autres candidats sont différés : Moderation reste un processus composite;
Establishment et Mandate exigent encore une clarification d'autorité; Payment
présente une frontière transactionnelle et fournisseur trop large. Ce
classement ne crée aucun ordre d'implémentation futur.

## 3. Owner et autorité

| Élément | Décision |
|---|---|
| Bounded context | `IdentityAccess` |
| Owner architectural | module `IdentityAccess` |
| Owner métier fonctionnel | Responsable Identité et Accès |
| Titulaire nominatif | Amadoune GUEYE |
| Décisionnaire du statut | Owner métier `IdentityAccess` uniquement |
| Consommateurs | authentification, autorisation, administration et projections, selon contrats futurs |
| Interdiction | un consommateur ne reconstruit ni ne réécrit le statut |

## 4. Périmètre fonctionnel

### Inclus

- représenter la disponibilité administrative d'un compte;
- suspendre un compte actif;
- réactiver un compte suspendu;
- décider à partir d'un état courant et d'une intention explicites;
- distinguer transition appliquée, état déjà atteint, conflit et rejeu;
- préserver identité, version, acteur, instant et identité d'intention.

### Exclus

- inscription et suppression d'un compte;
- authentification et émission/révocation de sessions;
- changement de mot de passe ou de credentials;
- vérification email/téléphone;
- attribution ou révocation de rôles;
- octroi ou retrait de consentements;
- autorisation de l'acteur;
- notification, UI d'administration et traitement en masse;
- modification de `Place Lifecycle` ou d'une autre capacité gelée.

L'inscription établit l'état initial mais reste hors Workflow. Les effets
éventuels d'une suspension sur rôles ou sessions doivent être attribués par
amendement; ils ne sont pas implicitement inclus dans le lifecycle.

## 5. États, actions et transitions pressentis

États fermés :

| État | Définition | Terminal |
|---|---|---:|
| `Active` | compte administrativement disponible | non |
| `Suspended` | compte administrativement suspendu | non |

État initial après inscription : `Active`.

Actions fermées : `Suspend`, `Reactivate`.

| État courant | Action | Résultat pressenti | État cible |
|---|---|---|---|
| `Active` | `Suspend` | `Applied` | `Suspended` |
| `Suspended` | `Reactivate` | `Applied` | `Active` |
| `Active` | `Reactivate` | `AlreadyInState` | — |
| `Suspended` | `Suspend` | `AlreadyInState` | — |

Des refus fermés de type `AccountMissing`, `VersionConflict`,
`InvalidContext` et `ReplayConflict` sont pressentis. Leur propriétaire et
leur libellé ne deviennent contractuels qu'après les gates correspondants.

## 6. Invariants

1. Un compte possède exactement un statut parmi `Active` et `Suspended`.
2. Seules `Suspend` et `Reactivate` modifient ce statut.
3. Une transition appliquée incrémente exactement une version.
4. L'instant métier ne peut précéder le dernier changement durable connu.
5. Une intention de rejeu identique ne produit pas une seconde transition.
6. Une intention différente ne peut être classée comme déjà appliquée.
7. Le Workflow futur ne lit ni Registry, ni projection, ni Runtime.
8. Credentials, vérifications, rôles, consentements et sessions ne font pas
   partie de l'état du lifecycle.
9. Aucun effet sur ces sous-domaines n'est déduit de la seule transition de
   statut sans responsabilité certifiée.
10. Les consommateurs aval observent des faits; ils ne décident pas le statut.

## 7. Contexte de décision pressenti

Une future décision pure devra recevoir explicitement :

- identité du compte;
- état courant observé;
- version attendue et version observée;
- action demandée;
- acteur;
- instant métier;
- identifiant d'intention/idempotence;
- preuve d'inspection de rejeu, séparée de la décision métier.

La forme versionnée exacte relève de `4.9A-R1`. Aucun contrat n'est créé par ce
Blueprint.

## 8. Frontières

| Couche future | Responsabilité exclusive |
|---|---|
| Workflow | validité état/action et transition pure |
| Inspection | présence et intégrité d'une tentative antérieure |
| Orchestration | séquencement sans redécision |
| Persistance | existence courante, concurrence, append et atomicité |
| Runtime | composition paresseuse seulement |
| Event/Delivery | propagation de faits déjà décidés |
| HTTP | validation transport et délégation unique |

## 9. Registre des décisions certifiées

1. **Owner** : Amadoune GUEYE, Responsable Identité et Accès.
2. **États fermés** : `Active` et `Suspended` uniquement.
3. **Effets de suspension** : aucune révocation implicite des rôles et aucune
   invalidation implicite des sessions; les owners Role Assignment et
   Authentication / Session restent seuls décisionnaires.
4. **Orthogonalité** : Credentials, Verification et Consent restent
   indépendants du statut et ne sont jamais mutés par son Workflow.
5. **Consommateurs** : observation facts-only, sans commande, reconstruction,
   modification ni publication d'un nouveau statut.
6. **Gel** : Phase 4.8 intégralement gelée; migrations 038, 039 et 040
   strictement inchangées.

Le registre des préconditions est clôturé.

## 10. Critères GO / NO GO

GO si les six conditions sont validées, l'Owner est unique, les frontières
sont non ambiguës, la roadmap est acceptée et aucun artefact technique n'est
introduit.

NO GO si le statut demeure couplé sans propriétaire aux rôles, sessions,
credentials, vérifications ou consentements; si une autre capacité peut
réécrire le statut; ou si une fondation gelée doit être modifiée sans
amendement versionné.

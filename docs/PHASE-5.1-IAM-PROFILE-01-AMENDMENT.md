# A-5.1-IAM-PROFILE-01 — Profile Frozen Boundary Amendment

## 1. Statut et objet

```text
A-5.1-IAM-01
→ NO GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-PROFILE-01
→ OUVERT
```

Cet amendement recherche un modèle de profil modifiable sans muter `Account`,
`AccountRegistry`, Historical Account, Snapshot V1, les migrations 041–043,
Account Status Event V1, son Runtime, son HTTP ou son Outbox.

Il est exclusivement documentaire.

## 2. Décision d'architecture proposée

Le modèle compatible est un **User Profile additif**, identifié par
`AccountId`, distinct de l'Aggregate historique `Account`.

Principes :

- Snapshot V1 reste la source d'amorçage historique et n'est jamais réécrit
  pour une mutation Profile ;
- après enrôlement, User Profile devient l'autorité canonique pour le nom,
  l'email et le téléphone présentés aux nouvelles capacités 5.x ;
- `Account` reste l'autorité gelée de credential, vérifications historiques,
  rôles, consentements et status historique ;
- une Identity Claim Registry additive garantit les unicités email/téléphone
  en important d'abord toutes les claims de migration 042 ;
- un changement d'email/téléphone est `pending` jusqu'à vérification ;
- l'ancienne claim n'est libérée qu'à l'activation atomique de la nouvelle ;
- les consommateurs 4.9 restent inchangés ; les nouveaux consommateurs
  utilisent un port Profile V1 explicite.

Le mot « additif » ne permet aucune écriture SQL directe dans les tables 042.

## 3. Modèle conceptuel

| Concept | Responsabilité |
|---|---|
| UserProfile | nom affiché, coordonnées canoniques, version et historique |
| IdentityClaim | réservation normalisée unique d'un email ou téléphone |
| ProfileRevision | preuve append-only de chaque changement |
| PendingContactChange | challenge, expiration, tentative et cible |
| Profile Event V1 futur | annonce une modification activée, sans secret/token |

Ces concepts sont des décisions de blueprint pour une phase future, pas des
composants créés par l'amendement.

## 4. Règles de mutation

### Nom

- mutable sans vérification de possession ;
- validation, longueur et normalisation versionnées ;
- révision append-only obligatoire ;
- ne change jamais `Account::name()`.

### Email

- nouvelle claim réservée avant envoi du challenge ;
- activation seulement après preuve email ;
- anti-takeover : session fraîche ou réauthentification, notification de
  l'ancienne adresse, délai/révocation selon politique ;
- unicité globale sur claims historiques et courantes ;
- aucune réutilisation de `VerificationToken` Snapshot V1.

### Téléphone

- mêmes règles de réservation et d'activation ;
- vérification du nouveau numéro obligatoire ;
- normalisation E.164 et protection SIM-swap/rate limit à cadrer ;
- aucune mutation de `Account::phone()`.

## 5. Events, Runtime, HTTP et Outbox

Les futurs Profile events sont un catalogue distinct. Ils ne sont ni ajoutés
à `AccountStatusEventType` ni sérialisés par Account Status Transport.

La future capacité possède :

- son propre HTTP owner et ses propres routes ;
- sa propre composition additive ;
- son propre Outbox owner physique/logique ou un amendement séparé avant toute
  utilisation de l'Outbox générique gelée ;
- aucun nouveau requirement dans le catalogue Runtime Health 58 sans phase
  Runtime dédiée.

## 6. Verdict proposé

Le profil complet est réalisable sans mutation implicite des capacités gelées
si le modèle additif ci-dessus est rendu normatif.

```text
A-5.1-IAM-PROFILE-01
→ GO PROPOSÉ
```

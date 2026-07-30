# A-5.1-IAM-01 — Identity & Access / Account Frozen Boundary Amendment

## 1. Statut

```text
Phase 5.0B
→ GO CERTIFIÉ
→ FERMÉE

A-5.1-IAM-01
→ OUVERT

Phase 5.1
→ FERMÉE
```

Le présent dossier est un audit documentaire. Il ne modifie aucun Aggregate,
Repository, Event, contrat, migration, Runtime, Outbox, HTTP, Provider ou test.

## 2. Question d'amendement

Les fonctions restantes d'Identity & Access peuvent-elles être développées
dans une tranche additive sans modifier :

- `Account` et ses invariants ;
- `AccountRegistry` ;
- Historical Account Snapshot V1 et son repository ;
- Account Status Lifecycle ;
- les événements Account Status V1 ;
- les migrations 041, 042 et 043 ;
- le Runtime Health à 58 capacités ;
- Delivery, Outbox et HTTP Account Status ?

## 3. Réponse synthétique

**Partiellement.**

Huit fonctions sont compatibles avec les capacités gelées sous réserve de
rester dans une nouvelle tranche additive :

- authentification ;
- connexion ;
- récupération de compte ;
- changement de mot de passe ;
- rôles ;
- consentements ;
- vérifications email/téléphone ;
- sessions.

Deux fonctions ne sont pas compatibles dans leur acception produit complète :

- profil utilisateur modifiable ;
- fermeture de compte.

Elles exigent des amendements supplémentaires ciblés avant l'ouverture de
Phase 5.1.

## 4. Découpage imposé

### Tranche additive compatible

Une future tranche IAM peut :

- résoudre un identifiant de connexion par un nouveau port de lecture interne,
  sans étendre `AccountRegistry` ;
- vérifier un secret dans un adapter spécialisé sans exposer le hash ;
- consulter `Account::isSuspended()` et les vérifications existantes ;
- appeler les use cases gelés de mot de passe, rôles, consentements et
  vérifications ;
- posséder un store de sessions et de challenges de récupération séparé ;
- exposer de nouvelles routes sous un HTTP owner IAM distinct, sans modifier
  les deux routes Account Status ;
- avoir sa propre composition additive, sans modifier le catalogue Runtime
  Health gelé ni le Provider Account Status.

Ces autorisations sont des conclusions d'architecture, pas une autorisation de
code avant le GO de Phase 5.1.

### Amendements complémentaires obligatoires

1. **A-5.1-IAM-PROFILE-01** : politique et modèle de mutation du nom, email et
   téléphone, unicités, revérification, événements, persistence et compatibilité
   historique.
2. **A-5.1-IAM-CLOSURE-01** : état de fermeture, réactivation éventuelle,
   anonymisation, rétention, sessions, rôles/consentements, références
   cross-domain et effet sur Account Status.

## 5. Périmètre protégé

| Frontière | Garantie maintenue |
|---|---|
| Account Status Workflow | seulement Active ↔ Suspended |
| Account Status Event V1 | seulement suspended/reactivated |
| Account Status HTTP | routes suspend/reactivate inchangées |
| AccountRegistry | `find(AccountId)`, `add`, `save` inchangés |
| Historical Snapshot V1 | identité, credential, verifications, roles, consents et version inchangés |
| migration 041 | journal Account Status inchangé |
| migration 042 | schéma Historical Account inchangé |
| migration 043 | ownership Outbox IdentityAccess inchangé |
| Runtime/Delivery/Outbox | catalogue, consumers et serializers inchangés |
| projection | aucune projection Account nouvelle supposée par le contrat gelé |

## 6. Verdict proposé

Les critères techniques de l'audit sont satisfaits, mais le résultat binaire
imposé par l'autorité ne permet pas d'ouvrir 5.1 tant que deux fonctionnalités
requièrent encore un amendement.

```text
A-5.1-IAM-01
→ NO GO PROPOSÉ POUR L'OUVERTURE IMMÉDIATE DE 5.1

Prochaine porte proposée
→ A-5.1-IAM-PROFILE-01
→ A-5.1-IAM-CLOSURE-01

Phase 5.1
→ RESTE FERMÉE
```

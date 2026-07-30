# A-5.1-IAM-01 — Decision

## 1. Critères

| Critère | Résultat |
|---|---|
| toutes les frontières gelées identifiées | SATISFAIT |
| impacts potentiels documentés | SATISFAIT |
| compatibilités démontrées | SATISFAIT |
| incompatibilités démontrées | SATISFAIT |
| amendements supplémentaires identifiés | SATISFAIT |
| aucun changement implicite nécessaire pour les fonctions compatibles | SATISFAIT |
| toutes les fonctions réalisables sans amendement supplémentaire | NON SATISFAIT |

## 2. Décisions fonctionnelles

```text
Authentification             → COMPATIBLE
Connexion                    → COMPATIBLE
Récupération de compte       → COMPATIBLE
Changement de mot de passe   → COMPATIBLE
Profil utilisateur modifiable→ AMENDEMENT SUPPLÉMENTAIRE
Fermeture de compte          → AMENDEMENT SUPPLÉMENTAIRE
Rôles                        → COMPATIBLE
Consentements                → COMPATIBLE
Vérifications email/téléphone→ COMPATIBLE
Sessions                     → COMPATIBLE
```

## 3. Décision binaire proposée

L'autorité a imposé deux issues seulement. Puisque deux fonctionnalités du
périmètre ne sont pas compatibles avec l'Aggregate et la persistence gelés,
l'ouverture immédiate de Phase 5.1 ne peut pas être proposée GO.

```text
A-5.1-IAM-01
→ AUDIT COMPLET
→ NO GO PROPOSÉ

Phase 5.1
→ RESTE FERMÉE

Amendements complémentaires proposés
→ A-5.1-IAM-PROFILE-01
→ A-5.1-IAM-CLOSURE-01
```

## 4. Condition de réexamen

A-5.1-IAM-01 pourra être clos après décision d'autorité. Phase 5.1 ne devient
ouvrable qu'après certification des deux amendements complémentaires ou après
une décision produit versionnée retirant explicitement la mutation de profil
et la fermeture du périmètre 5.1.

Cette seconde voie serait un changement de périmètre normatif et ne peut être
déduite du présent audit.

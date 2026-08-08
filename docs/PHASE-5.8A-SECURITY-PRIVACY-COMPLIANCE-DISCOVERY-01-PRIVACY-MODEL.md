# Privacy Model — Discovery 5.8A

## Principes

- finalité explicite et limitation d'usage ;
- minimisation des données et des accès ;
- exactitude et possibilité de rectification ;
- conservation limitée et destruction vérifiable ;
- confidentialité dès la conception et par défaut ;
- traçabilité des accès, exports et suppressions ;
- séparation entre identité technique, identifiant métier et PII.

## Cycle de vie candidat

| Étape | Qualification requise |
|---|---|
| collecte | finalité, base applicable, notice, champs strictement nécessaires |
| utilisation | owner, rôles autorisés, compatibilité avec la finalité |
| partage | destinataire, minimisation, accord et traçabilité |
| conservation | durée par catégorie, événement déclencheur, éventuel gel légal |
| export | authentification forte, périmètre, format, chiffrement et preuve de remise |
| suppression | portée primaire, copies, index, caches et sauvegardes selon politique |
| destruction | méthode adaptée au support et preuve sans reproduction des données |

## PII et demandes

Un inventaire futur devra relier chaque catégorie de PII à son owner métier, sa finalité, ses destinataires, sa durée, ses mesures de protection et ses mécanismes d'accès, rectification, export et suppression. Aucun export ou effacement cross-domain ne peut être décidé unilatéralement par `SecurityCompliance`.

Les conflits entre suppression, preuve, fraude, sécurité et gel légal doivent produire une décision traçable, limitée et validée par l'autorité compétente.

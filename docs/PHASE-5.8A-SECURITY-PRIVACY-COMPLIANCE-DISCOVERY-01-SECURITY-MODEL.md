# Security Model — Discovery 5.8A

## Objectifs

Le modèle candidat couvre confidentialité, intégrité et disponibilité, avec séparation des responsabilités, moindre privilège, défense en profondeur, défaillance fermée et traçabilité vérifiable.

## Contrôles candidats

| Domaine | Politique à qualifier | Preuve attendue ultérieure |
|---|---|---|
| Secrets | stockage hors code et logs, accès nominatif, portée minimale | inventaire, contrôle d'accès, preuve d'absence de secret embarqué |
| Rotation | périodicité fondée sur le risque et rotation immédiate sur incident | historique de rotation sans valeur secrète |
| Chiffrement | algorithmes et paramètres approuvés, données en transit et au repos selon classification | configuration versionnée et inventaire des usages |
| Signature | séparation signature/chiffrement, vérification avant confiance | identité de clé, version et résultat de vérification |
| Hachage | fonction adaptée à l'usage ; mots de passe distincts des checksums d'intégrité | paramètres, version et finalité |
| Audit immuable | append-only, horodatage UTC, intégrité et accès restreint | chaîne de preuve et contrôles d'altération |
| Journalisation | minimisation, redaction, corrélation sans PII superflue | schéma de log et tests de non-divulgation futurs |
| Incidents | détection, qualification, confinement, éradication, reprise et retour d'expérience | chronologie, décisions et clôture |
| Disponibilité | sauvegarde, reprise, objectifs mesurables et tests périodiques futurs | résultats de restauration et exercices |

## Classification candidate

Quatre niveaux sont retenus pour qualification ultérieure : Public, Internal, Confidential et Restricted. Les secrets, clés privées, tokens actifs et PII à risque élevé relèvent de `Restricted` par défaut.

Aucun algorithme, fournisseur KMS, durée de rotation ou objectif de reprise n'est imposé par ce Discovery ; ces choix nécessitent inventaire, analyse de menace et décision certifiée.

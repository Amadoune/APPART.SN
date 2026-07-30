# Phase 5.1H — PostgreSQL Certification

Les scénarios ciblés démontrent :

1. commit commun du profil, de l'intent, du message et de la destination ;
2. replay sans duplication ;
3. événement incompatible avec rollback de toutes les écritures ;
4. claim exclusif ;
5. retry différé puis reprise ;
6. livraison terminale sans perte ;
7. deux publications concurrentes convergeant vers un message et une destination.

Environnement obligatoire : PostgreSQL 18.x, sans fallback SQLite.

Résultat ciblé : **4 tests, 26 assertions, PASS**.

Résultat global terminal : **582 tests, 2 504 assertions, PASS**.

Une première campagne globale a correctement détecté une FK locale dans le
schéma 054. La FK a été retirée afin de préserver la règle certifiée « zéro FK
dans `identity_access_completion` ». Seule la campagne complète réexécutée
jusqu'au résultat terminal PASS constitue la preuve de certification.

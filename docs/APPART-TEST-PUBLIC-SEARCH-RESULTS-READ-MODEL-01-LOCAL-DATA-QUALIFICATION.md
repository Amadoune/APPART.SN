# APPART.TEST Public Search Results Read Model 01 — Local Data Qualification

## État observé

La base locale ne contient actuellement aucune génération publique active ni projection publique courante. L'endpoint retourne donc légitimement `empty` en HTTP 200.

## Option A — pipeline certifié

Option recommandée. Produire Listing, Search/SEO et décisions publiques par les capacités existantes, puis laisser le pipeline Public Projection construire et activer la génération. Cette voie préserve intégralement les invariants et l'autorité des owners.

## Option B — jeu de démonstration local

Possible uniquement après autorisation explicite. Il devrait être isolé, réversible, non exécuté en production et utiliser les writers/contracts certifiés. Aucun Seeder, migration ou SQL manuel ne doit écrire directement la projection.

## Décision du jalon

Aucune donnée n'est créée. L'Option A est qualifiée comme voie normative ; l'Option B reste `NON AUTORISÉE`.

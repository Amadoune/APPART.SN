# Legacy Migration & Reconciliation Discovery — Certification Note

Le Discovery recommande `LegacyMigration` comme owner unique de coordination temporaire. Il ne possède aucune décision métier cible : chaque nouvel owner valide les données qui lui sont proposées et reste la seule autorité après acceptation.

Le Blueprint qualifie l'inventaire candidat, la cartographie des domaines, les transformations autorisées/interdites, les vagues de migration, les rapprochements fonctionnel/volumétrique/référentiel/temporel, l'intégrité des identifiants, la quarantaine, la reprise, le rollback et le cutover.

La volumétrie réelle, les sources physiques, les seuils et plusieurs arbitrages métier demeurent à mesurer ou à décider. Ils constituent des gates explicites et ne sont pas remplacés par des hypothèses.

Ce jalon ne crée aucun code, contrat, Provider, Runtime, Persistence, HTTP, Event, Delivery, Outbox, Transport, Routing, Consumer, SQL, migration ou test. Il ne modifie aucune capacité gelée. Les seules validations autorisées sont la cohérence documentaire et `git diff --check`.

Sous réserve de la décision d'autorité, le Discovery / Blueprint est proposé GO. Aucune Foundation 5.7 n'est ouverte par cette proposition.

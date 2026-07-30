# PostgreSQL Strategy

## 1. Objet et décision

PostgreSQL 18.x est la plateforme de persistance initiale validée. Ce document fixe son usage architectural sans créer de base, schéma, table, migration ou SQL.

## 2. Topologie initiale

Une instance logique principale porte le monolithe modulaire. Les domaines sont isolés par ownership, conventions et droits ; ils ne sont pas distribués en bases indépendantes au démarrage. Une réplication éventuelle sert les lectures tolérant le retard, jamais une décision nécessitant l’état courant.

## 3. Ownership

Chaque structure persistante possède un module propriétaire unique. Seul son adaptateur écrit ses données. Les jointures et écritures directes inter-modules sont interdites. Les échanges passent par contrats publics, événements ou projections approuvées.

L’Outbox appartient logiquement au module producteur. Les marqueurs des consommateurs appartiennent au consommateur.

## 4. Intégrité

PostgreSQL protège les identités, références uniques, relations internes à l’Aggregate, valeurs obligatoires et versions attendues. Les contraintes techniques doublent les garanties de concurrence et d’unicité ; elles ne remplacent pas les invariants du Domaine.

Une suppression physique n’est autorisée que par une politique explicite. Les états historiques, preuves, retraits, archives et réservations durables respectent leurs politiques métier.

## 5. Transactions et concurrence

La Transaction Policy et l’Unit of Work sont obligatoires. Les versions d’Aggregates protègent les écritures perdues. Les opérations combinées avec réservation et Outbox sont atomiques. Aucun appel externe ne se déroule dans une transaction.

## 6. Performance

Les index futurs découlent de requêtes documentées et mesurées. Toute lecture critique possède un budget, un plan d’observation et un jeu de données représentatif. Les requêtes non bornées et le chargement incomplet d’Aggregate sont interdits.

Les projections de lecture absorbent les besoins qui ne doivent pas alourdir les écritures. Une optimisation ne contourne jamais l’ownership d’un module.

## 7. Sécurité

- identités d’accès distinctes par environnement et responsabilité ;
- moindre privilège et interdiction d’accès public direct ;
- secrets hors dépôt et rotation auditée ;
- connexions protégées ;
- journaux sans paramètres sensibles ;
- accès d’exploitation nominatifs, temporaires et tracés ;
- données de production interdites dans les tests hors procédure d’anonymisation validée.

## 8. Sauvegarde et restauration

Les sauvegardes couvrent états, réservations, Outbox et marqueurs de consommateurs comme un ensemble cohérent. Elles sont chiffrées, contrôlées et restaurées régulièrement dans un environnement isolé.

Les objectifs initiaux de production sont : perte maximale de cinq minutes, restauration du service en moins de soixante minutes, conservation opérationnelle de trente-cinq jours et exercice de restauration trimestriel. Une conservation légale ou métier plus longue appartient aux données concernées, pas aux sauvegardes opérationnelles. Aucun lancement n’est autorisé sans exercice concluant, mesure réelle des deux objectifs et rapprochement des volumes, versions et événements.

## 9. Haute disponibilité et maintenance

La disponibilité cible détermine la réplication et le basculement ; aucune complexité n’est ajoutée sans objectif validé. Les mises à jour mineures sont régulières, les changements majeurs répétés hors production, avec retour documenté. Les connexions, verrous, transactions longues, stockage, réplication et échecs Outbox sont surveillés.

## 10. Critères d’acceptation

- PostgreSQL 18.x confirmé ;
- ownership par module ;
- aucune écriture inter-module ;
- versions, unicités et transactions protégées ;
- Outbox atomique ;
- moindre privilège ;
- sauvegarde et restauration testées avant production ;
- mesures de performance et d’exploitation définies ;
- aucun SQL ou schéma imposé par ce document.

# ADR-1002 — Plateforme initiale de persistance

## Statut de la décision

- **Projet :** APPART.SN REBUILD 2026
- **Sprint :** 7 — Document 2
- **Date de décision :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Décision principale :** PostgreSQL 18.x
- **Runtime et framework :** PHP 8.5.x et Laravel 13.x, conformément à ADR-1001
- **Périmètre :** plateforme relationnelle de persistance initiale
- **Hors périmètre :** création de la base, schéma, structures de données, migrations exécutables, modèles, topologie et fournisseur

## Sources temporelles

Les compatibilités et cycles de support cités ont été vérifiés sur les sources officielles le 16 juillet 2026. Ils seront revérifiés avant toute création effective.

---

# 1. Objet

Cet ADR décide la plateforme relationnelle qui servira de source durable aux données métier courantes d’APPART.SN REBUILD.

Il compare PostgreSQL, MySQL et MariaDB, puis définit les politiques conceptuelles de transaction, intégrité, concurrence, sauvegarde, restauration, évolution et sécurité. Il ne crée aucune base, aucun schéma ni aucune structure de données.

---

# 2. Contexte

La Foundation Conceptuelle a établi treize domaines, vingt Aggregate Roots retenus et des frontières de cohérence explicites. ADR-1000 exige un moteur transactionnel mature. ADR-1001 retient PHP 8.5.x et Laravel 13.x. Le projet Laravel n’existe pas encore.

La persistance doit servir les décisions métier sans devenir leur propriétaire. Elle devra notamment soutenir :

- l’unicité de l’état officiel d’une annonce ;
- les transitions atomiques et auditables ;
- les rôles, Mandats et validations à quatre yeux ;
- l’ownership des annonces et médias ;
- l’unicité des URL de référence ;
- l’absence de double effet financier ;
- les preuves de modération et d’audit ;
- les données historiques et correspondances Legacy ;
- les lectures reconstruites pour Recherche, SEO et statistiques.

Le moteur est une capacité technique derrière les domaines. Il ne dicte ni les Aggregates, ni les modules, ni le vocabulaire métier.

---

# 3. Exigences métier à protéger

## 3.1 Aggregate Roots

Chaque Root possède sa propre frontière de décision. La persistance doit permettre d’enregistrer une décision complète sans obliger plusieurs Roots indépendantes à former une transaction globale.

## 3.2 Cycle de vie des annonces

- exactement un état officiel courant ;
- transition compatible avec l’état observé ;
- acteur, motif et date obligatoires ;
- publication impossible par paiement, SEO ou Recherche ;
- suspension et retrait prioritaires sur l’exposition.

## 3.3 Permissions et quatre yeux

- rôle et portée valides ;
- auteur distinct de l’approbateur lorsque requis ;
- action sensible attribuable ;
- Super Administrateur soumis aux mêmes invariants.

## 3.4 Médias

- propriétaire métier obligatoire ;
- au plus une image principale par galerie ;
- retrait public distinct de conservation probatoire ;
- média interdit jamais considéré public.

## 3.5 Monétisation

- commande, paiement et offre séparés ;
- une confirmation financière ne produit qu’un effet ;
- rapprochement et remboursement traçables ;
- aucun fait financier ne publie une annonce.

## 3.6 SEO

- une seule URL de référence par ressource publique ;
- URL historiques conservées et redirections sans boucle ;
- aucune annonce non publiée indexable ;
- pages et projections reconstruisibles à partir des sources.

## 3.7 Migration Legacy

- provenance conservée ;
- décisions Conserver, Nettoyer, Fusionner, Archiver ou Supprimer justifiées ;
- correspondances historiques explicites ;
- zéro écart inexpliqué ;
- domaine temporaire sans dépendance du produit courant.

---

# 4. Contraintes de persistance

1. Une seule plateforme relationnelle principale au démarrage.
2. Transactions locales aux frontières d’Aggregate, sauf justification formelle.
3. Intégrité protégée à plusieurs niveaux : Domaine, cas d’usage et plateforme.
4. Aucune lecture dérivée utilisée comme source de vérité.
5. Aucune relation technique ne doit abolir une frontière métier.
6. Les références inter-Aggregates restent des identités, non des graphes modifiables.
7. Les données critiques doivent être sauvegardables et restaurables avec preuve métier.
8. La concurrence ne doit produire ni double publication, ni double paiement, ni approbation contradictoire.
9. Les évolutions doivent être révisables, observables et compatibles avec le fonctionnement du produit.
10. Les données historiques ne sont jamais corrigées silencieusement.
11. Les valeurs structurées ne doivent pas devenir un refuge pour contourner les contraintes métier.
12. Recherche spécialisée, cache et analytique restent des capacités séparées et facultatives.
13. Les environnements doivent utiliser la même famille et la même branche majeure.
14. Le choix doit rester exploitable par une équipe raisonnable, sans topologie distribuée prématurée.

---

# 5. Critères de sélection

| Critère | Importance | Question décisive |
|---|---:|---|
| Intégrité relationnelle | Critique | Le moteur permet-il d’exprimer et vérifier les invariants structurels ? |
| Transactions | Critique | Les décisions atomiques et retours sont-ils fiables et observables ? |
| Concurrence | Critique | Les transitions, rôles et paiements concurrents restent-ils cohérents ? |
| Contraintes expressives | Critique | Unicités, références, vérifications et contraintes temporelles sont-elles maîtrisables ? |
| Indexation | Élevée | Les lectures critiques disposent-elles d’index adaptés et observables ? |
| Données JSON | Moyenne | Les attributs réellement variables restent-ils validables et indexables ? |
| Recherche initiale | Moyenne | Le moteur peut-il couvrir les besoins simples avant une capacité spécialisée ? |
| Observabilité | Élevée | Verrous, requêtes, plans, sessions et saturation sont-ils compréhensibles ? |
| Sauvegarde et restauration | Critique | Une restauration cohérente et à un instant choisi est-elle possible et testable ? |
| Réplication | Élevée | Disponibilité et lectures secondaires sont-elles possibles sans changer la vérité ? |
| Laravel 13 | Critique | Le support est-il officiel et courant ? |
| Exploitabilité | Élevée | Compétences, hébergement, mises à niveau et diagnostic sont-ils accessibles ? |
| Coût total | Élevée | Exploitation, formation, sauvegarde, supervision et sortie sont-elles soutenables ? |
| Maturité et support | Critique | La branche est-elle stable et maintenue pendant la roadmap ? |
| Réversibilité | Élevée | Les données peuvent-elles être exportées et reprises sans dépendance propriétaire ? |

## 5.1 Règles de jugement

- une différence de syntaxe ne vaut pas avantage métier en elle-même ;
- une fonctionnalité avancée ne compte que si elle protège une exigence réelle ;
- la popularité ne remplace ni le support ni l’intégrité ;
- le coût de compétence est évalué, mais ne justifie pas une faiblesse structurelle durable ;
- la compatibilité Laravel signifie intégration officielle, pas dépendance du Domaine.

---

# 6. Comparaison argumentée

## 6.1 Synthèse

| Axe | PostgreSQL | MySQL avec InnoDB | MariaDB avec moteur transactionnel | Avantage pour APPART.SN |
|---|---|---|---|---|
| Intégrité | Très forte et expressive | Forte | Forte, avec configuration à maîtriser | PostgreSQL |
| Concurrence | MVCC, plusieurs niveaux dont sérialisable, verrous explicites | MVCC InnoDB, niveaux et verrous matures | MVCC InnoDB, niveaux et verrous matures | Léger avantage PostgreSQL |
| Transactions | Robustes, y compris de nombreuses évolutions structurelles transactionnelles | Robustes avec InnoDB ; certaines opérations provoquent un engagement implicite | Robustes avec moteur adapté ; plusieurs opérations structurelles provoquent un engagement implicite | PostgreSQL |
| Contraintes | Unicité, références, vérifications, exclusion, report possible de certaines vérifications | Unicité, références et vérifications modernes | Unicité, références et vérifications, désactivation possible de certains contrôles à gouverner | PostgreSQL |
| Indexation | B-tree, Hash, GiST, SP-GiST, GIN, BRIN, partiels et expressions | B-tree, full-text, spatial, fonctionnel selon cas | B-tree, full-text, spatial, virtuel et vectoriel selon branche | PostgreSQL |
| JSON | JSON et JSONB natifs, indexation riche | Type JSON binaire natif et fonctions nombreuses | Alias JSON fondé sur LONGTEXT avec validation | PostgreSQL / MySQL |
| Recherche | Recherche textuelle intégrée, index GIN/GiST, trigrammes via extension approuvée | Full-text intégré | Full-text et capacités récentes | PostgreSQL |
| Observabilité | Vues statistiques, activité, verrous, plans et extensions reconnues | Performance Schema et vues d’administration matures | Vues, statistiques et outils matures | Équivalent, styles différents |
| Sauvegarde | Logique, physique, archivage continu et restauration à un instant choisi | Logique, physique selon édition/outils, journaux binaires et restauration à un instant choisi | Logique, physique et journaux binaires selon outils | Équivalent sous politique rigoureuse |
| Réplication | Physique et logique intégrées, réplication en continu | Asynchrone, semi-synchrone, GTID, groupes selon besoin | Asynchrone, multi-source et Galera selon besoin | Équivalent selon topologie |
| Laravel 13 | Pilote officiellement documenté | Pilote officiellement documenté | Généralement via compatibilité MySQL, à qualifier précisément | PostgreSQL / MySQL |
| Coût logiciel | Communautaire, licence permissive | Community disponible ; certaines capacités/outils commerciaux | Communautaire GPL, offres commerciales possibles | Tous viables |
| Coût d’exploitation | Compétence PostgreSQL et discipline d’entretien requises ; nombreuses offres gérées | Compétences très répandues ; coût variable selon édition et outils | Compétences répandues ; coût variable selon niveau communautaire ou commercial | À confirmer par chiffrage, sans écart éliminatoire établi |
| Maturité | Très élevée | Très élevée | Très élevée | Équivalent |

## 6.2 PostgreSQL

### Intégrité et contraintes

PostgreSQL propose une intégrité relationnelle riche et cohérente : unicités, références, vérifications, exclusions et contraintes pouvant, dans certains cas, être différées jusqu’à la fin d’une transaction. Ces capacités sont particulièrement adaptées aux identités uniques, URL de référence, périodes et décisions concurrentes.

La plateforme ne remplace pas le Domaine. Elle constitue une seconde ligne de défense contre les états impossibles et les doubles effets.

Référence : [PostgreSQL 18 — Constraints](https://www.postgresql.org/docs/18/ddl-constraints.html).

### Concurrence et transactions

Le modèle MVCC, les niveaux Read Committed, Repeatable Read et Serializable, les verrous de ligne et la détection des blocages permettent de choisir une stratégie proportionnée. Le niveau sérialisable peut signaler un conflit à rejouer au lieu d’accepter une anomalie.

Référence : [PostgreSQL 18 — Concurrency Control](https://www.postgresql.org/docs/18/mvcc.html).

### Indexation, JSON et recherche

PostgreSQL fournit plusieurs familles d’index, y compris les index partiels, sur expressions et GIN adaptés à certains contenus structurés. JSONB offre stockage normalisé, opérateurs et indexation. La recherche textuelle intégrée peut répondre aux besoins initiaux sans décider qu’un moteur spécialisé est inutile à terme.

Références : [Index Types](https://www.postgresql.org/docs/18/indexes-types.html), [Partial Indexes](https://www.postgresql.org/docs/18/indexes-partial.html), [JSON Types](https://www.postgresql.org/docs/18/datatype-json.html), [Full Text Search](https://www.postgresql.org/docs/18/textsearch.html).

### Exploitation

PostgreSQL dispose de sauvegardes logiques et physiques, d’archivage continu, de restauration à un instant choisi, de réplication physique et logique et de vues d’observation. Ces capacités sont matures mais exigent une vraie discipline opérationnelle.

Références : [Backup and Restore](https://www.postgresql.org/docs/18/backup.html), [High Availability and Replication](https://www.postgresql.org/docs/18/high-availability.html), [Monitoring](https://www.postgresql.org/docs/18/monitoring.html).

### Cycle de support

PostgreSQL maintient chaque version majeure pendant cinq ans. La branche 18, publiée le 25 septembre 2025, est annoncée jusqu’au 14 novembre 2030. Le projet recommande l’usage de la dernière révision corrective de la branche.

Référence : [PostgreSQL — Versioning Policy](https://www.postgresql.org/support/versioning/).

### Limites

- compétences spécifiques nécessaires pour exploitation, indexation et réglage ;
- erreurs de sérialisation et blocages à traiter explicitement ;
- certaines évolutions structurelles peuvent rester coûteuses ou bloquantes ;
- richesse des fonctionnalités pouvant encourager des dépendances propriétaires inutiles ;
- recherche intégrée ne remplaçant pas automatiquement une capacité spécialisée à forte échelle.

## 6.3 MySQL

### Intégrité et transactions

MySQL avec InnoDB offre transactions, MVCC, références, unicités et vérifications modernes. Il est tout à fait capable de supporter une place de marché transactionnelle. La discipline impose toutefois de n’utiliser que le moteur transactionnel approuvé et de connaître les opérations provoquant un engagement implicite.

Références : [MySQL 8.4 — Transactions](https://dev.mysql.com/doc/refman/8.4/en/commit.html), [InnoDB](https://dev.mysql.com/doc/refman/8.4/en/innodb-storage-engine.html).

### JSON, indexation et recherche

MySQL possède un type JSON natif, des fonctions riches, des mécanismes d’indexation indirecte ou fonctionnelle, ainsi que la recherche full-text. Ces capacités sont solides, mais l’expression de certains invariants avancés est moins directe que dans PostgreSQL.

Référence : [MySQL 8.4 — JSON](https://dev.mysql.com/doc/refman/8.4/en/json.html).

### Sauvegarde, restauration et réplication

Les sauvegardes logiques, journaux binaires, récupération à un instant choisi, réplication asynchrone, semi-synchrone et GTID sont matures. Certaines capacités de sauvegarde physique ou d’exploitation avancée dépendent de l’outil ou de l’édition retenue, ce qui doit entrer dans le coût total.

Références : [Backup Methods](https://dev.mysql.com/doc/refman/8.4/en/backup-methods.html), [Replication](https://dev.mysql.com/doc/refman/8.4/en/replication.html).

### Compatibilité et maturité

Laravel documente officiellement MySQL. MySQL dispose d’un écosystème et de compétences très larges. La branche 8.4 est une branche LTS officielle, ce qui constitue un avantage opérationnel réel.

### Limites pour ce projet

- certaines opérations structurelles ne s’intègrent pas à une transaction réversible ;
- coexistence possible de moteurs aux propriétés différentes, à interdire par politique ;
- contraintes avancées et indexation conditionnelle moins naturelles pour certains invariants ;
- coûts et disponibilité de certains outils variant selon l’édition.

### Conclusion

Alternative excellente et pleinement viable, classée deuxième. Elle n’est pas rejetée pour faiblesse générale, mais PostgreSQL s’aligne mieux sur les contraintes expressives et les usages JSON/indexation envisagés.

## 6.4 MariaDB

### Intégrité et transactions

MariaDB avec un moteur transactionnel adapté fournit transactions, unicités, références et contraintes CHECK. La plateforme est mature et largement exploitée. Sa politique permet toutefois de désactiver certains contrôles de vérification ; une telle possibilité devrait être strictement interdite en usage courant.

Références : [MariaDB — Transactions](https://mariadb.com/docs/server/reference/sql-statements/transactions), [Constraints](https://mariadb.com/docs/server/reference/sql-statements/data-definition/constraint).

### JSON et compatibilité

MariaDB expose JSON comme alias de LONGTEXT avec validation, contrairement au stockage JSON binaire natif de PostgreSQL JSONB et MySQL JSON. Cette approche reste fonctionnelle mais offre un modèle différent d’indexation, de stockage et d’interopérabilité.

Référence : [MariaDB — JSON Data Type](https://mariadb.com/docs/server/reference/data-types/string-data-types/json).

### Sauvegarde, réplication et support

MariaDB propose sauvegarde, journaux, réplication classique, multi-source et options de haute disponibilité. MariaDB 11.8 est une branche LTS publiée en juin 2025 ; les binaires communautaires sont annoncés jusqu’en juin 2028, avec des durées commerciales plus longues selon l’offre.

Références : [MariaDB — Maintenance Policy](https://mariadb.org/about/), [Replication Overview](https://mariadb.com/docs/server/ha-and-performance/standard-replication/replication-overview).

### Compatibilité Laravel

Laravel s’appuie couramment sur le pilote MySQL pour MariaDB, mais les différences de versions, fonctionnalités et comportements doivent être qualifiées. Cette compatibilité est moins explicite dans la documentation d’installation Laravel que PostgreSQL et MySQL nommément cités.

### Limites pour ce projet

- sémantique JSON différente ;
- divergence progressive avec MySQL à ne pas sous-estimer ;
- vérification plus précise requise pour chaque capacité Laravel ou package ;
- fenêtre communautaire de MariaDB 11.8 plus courte que celle de PostgreSQL 18, sauf offre ou politique différente ;
- certaines opérations structurelles provoquent un engagement implicite.

### Conclusion

Plateforme mature et viable, classée troisième pour ce contexte. Elle ne présente pas un avantage décisif sur PostgreSQL pour les invariants, JSON, support communautaire et compatibilité documentaire recherchés.

## 6.5 Résultat pondéré

PostgreSQL arrive en tête sur les critères critiques d’intégrité expressive, concurrence, indexation, JSON, support communautaire et alignement avec les frontières. MySQL reste une alternative robuste si l’exploitation ou l’hébergement rendent PostgreSQL disproportionné. MariaDB reste viable mais ne fournit pas ici de bénéfice compensant ses différences JSON et de compatibilité à qualifier.

---

# 7. Compatibilité avec les documents normatifs

| Document | Protection apportée par la décision PostgreSQL | Limite à préserver |
|---|---|---|
| MASTER-BLUEPRINT | source durable cohérente pour comptes, annonces, géographie et administration | la plateforme ne définit pas les fonctionnalités |
| LISTING-LIFECYCLE | transaction locale, état attendu, historique et concurrence contrôlée | le moteur ne décide aucune transition |
| MEDIA-POLICY | ownership, unicité de l’image principale et retraits traçables | aucun contenu média physique décidé ici |
| PERMISSIONS-MATRIX | unicités, références, quatre yeux et audit | les rôles seuls ne remplacent pas les invariants locaux |
| SEO-POLICY | unicité des URL, historique, redirections et lectures reconstruisibles | SEO ne modifie pas Catalogue ou Cycle |
| MIGRATION-RULES | provenance, correspondances, décisions et rapprochements cohérents | le Legacy ne dicte pas le modèle cible |
| ARCHITECTURE-BLUEPRINT | modules, transactions locales et projections séparées | aucun accès direct inter-module |
| DOMAIN-MAPPING | ownership unique et références par identité | aucune relation technique ne transfère l’ownership |
| AGGREGATE-BOUNDARIES | atomicité dans une Root et cohérence différée entre Roots | pas de transaction globale « Annonce complète » |
| ADR-1000 | moteur mature, intègre, observable et réversible | topologie et structure restent ouvertes |
| ADR-1001 | compatibilité PHP 8.5/Laravel 13 | aucune dépendance du Domaine au pilote |

---

# 8. Politique de transactions

## 8.1 Frontière par défaut

Une transaction métier correspond par défaut à une intention envers une seule Aggregate Root. Elle inclut la vérification de l’état attendu, la modification cohérente, l’historique requis et la préparation du fait métier produit.

## 8.2 Inter-Aggregates

- aucune transaction globale par défaut ;
- une référence externe est revalidée si sa validité actuelle est critique ;
- les conséquences sur d’autres Roots utilisent des intentions explicites ou des événements ;
- un échec secondaire produit reprise, compensation métier ou alerte, jamais une réécriture silencieuse ;
- toute transaction couvrant plusieurs Roots exige la preuve qu’elles partagent réellement le même invariant immédiat et un ADR si la frontière normative est affectée.

## 8.3 Durée

- transactions courtes ;
- aucune attente utilisateur ou appel externe pendant une transaction ;
- aucun traitement média, notification ou recherche inclus ;
- verrouillage limité aux données nécessaires ;
- seuils de durée surveillés et alertés.

## 8.4 Échec et reprise

- tout conflit attendu dispose d’une stratégie de nouvelle tentative bornée ou d’un refus explicite ;
- les opérations à effet unique utilisent une identité métier stable ;
- un retour annule la décision complète ou n’en annule aucune partie ;
- les événements ne deviennent visibles qu’avec la décision qu’ils décrivent, selon le mécanisme futur choisi.

---

# 9. Politique d’intégrité

## 9.1 Défense en profondeur

1. Le Domaine protège la règle et son sens.
2. Le cas d’usage protège l’ordre et l’autorisation.
3. PostgreSQL protège les invariants structurels qu’il peut exprimer sans dupliquer la décision métier.
4. Les tests prouvent les comportements nominaux, refus et concurrences.
5. L’observabilité détecte les dérives et échecs.

## 9.2 Invariants prioritaires

- identité unique des Roots ;
- références obligatoires valides lorsque la politique l’exige ;
- unicité de l’état courant par Annonce ;
- unicité d’une image principale par Galerie ;
- unicité d’une URL de référence active ;
- distinction auteur-approbateur ;
- unicité d’une confirmation financière ;
- provenance et correspondance historique non ambiguës ;
- valeurs obligatoires et domaines de valeurs valides.

## 9.3 Interdictions

- aucune désactivation générale des protections pour accélérer un import ;
- aucune donnée critique stockée uniquement sous forme de document non contraint ;
- aucun état libre non normatif ;
- aucune correction directe non auditée en production ;
- aucune contrainte technique transformant une projection en propriétaire.

---

# 10. Politique des contraintes

- toute contrainte possède un invariant métier ou technique identifié ;
- son nom futur révèle le domaine et la règle protégée ;
- unicités et références sont définies selon l’ownership, pas selon la commodité de navigation ;
- une contrainte inter-module ne doit pas donner un accès de modification au consommateur ;
- les vérifications complexes restent dans le Domaine si leur sens ne peut être exprimé sans ambiguïté ;
- les contraintes reportées jusqu’à la fin d’une transaction sont réservées aux cas justifiés ;
- les suppressions en cascade sont refusées par défaut pour les données métier et historiques ;
- une suppression, un archivage et un retrait public restent trois décisions distinctes ;
- toute relaxation temporaire est interdite en production sauf procédure exceptionnelle, bornée et auditée ;
- les imports Legacy passent par qualification et validation au lieu de contourner les contraintes.

---

# 11. Politique de concurrence

## 11.1 Risques prioritaires

- deux transitions simultanées d’une même annonce ;
- publication et suspension concurrentes ;
- modification du contenu pendant une décision de modération ;
- double attribution ou révocation de rôle ;
- deux images principales ;
- fusion géographique concurrente avec un rattachement ;
- double confirmation ou remboursement financier ;
- deux approbateurs utilisant une décision devenue obsolète ;
- création concurrente d’une URL identique.

## 11.2 Stratégie

- contrôle optimiste par version métier pour les modifications ordinaires ;
- verrou explicite ciblé pour les décisions à forte contention ou effet unique ;
- unicité comme dernière ligne de défense ;
- niveau d’isolation plus strict pour un scénario prouvé, pas globalement par réflexe ;
- traitement explicite des échecs de sérialisation et blocages ;
- ordre de verrouillage stable ;
- aucune boucle de nouvelle tentative infinie ;
- métriques sur conflits, attentes, abandons et blocages.

## 11.3 Priorité métier

Suspension, retrait, révocation et prévention du double paiement priment sur la disponibilité optimiste. En cas d’incertitude, l’exposition ou l’effet financier est refusé prudemment.

---

# 12. Politique de sauvegarde

## 12.1 Principes

- sauvegardes automatiques, chiffrées, surveillées et séparées du serveur principal ;
- combinaison de sauvegardes complètes et d’archivage continu permettant une restauration à un instant choisi ;
- rétention définie selon obligations métier et légales ;
- copies protégées contre altération et suppression par le même compte d’exploitation ;
- inventaire des sauvegardes, propriétaires, emplacements et dates d’expiration ;
- contrôle d’intégrité et restauration régulière, pas seulement succès de copie ;
- inclusion des rôles, paramètres et éléments nécessaires à une restauration cohérente, sans exposer les secrets ;
- réplication jamais considérée comme une sauvegarde.

## 12.2 Classes de données

Comptes, transitions, permissions, preuves, paiements, URL historiques et décisions Legacy exigent une protection renforcée. Les projections reconstruisibles peuvent avoir une stratégie différente si leur reconstruction est prouvée.

## 12.3 Décisions ouvertes

Objectif de perte maximale, fréquence, rétention, localisation, chiffrement, capacité choisie, responsabilités et coût.

---

# 13. Politique de restauration

## 13.1 Objectifs

- restaurer un ensemble cohérent ;
- choisir un instant antérieur à une corruption ou erreur ;
- prouver les invariants avant réouverture ;
- distinguer reprise technique et validation métier ;
- documenter toute perte ou divergence.

## 13.2 Procédure conceptuelle

1. déclarer l’incident et geler les effets aggravants ;
2. choisir le point de restauration selon les preuves ;
3. restaurer dans un environnement isolé ;
4. vérifier intégrité technique ;
5. vérifier états, paiements, permissions, URLs et volumes critiques ;
6. rapprocher les événements externes survenus autour du point choisi ;
7. obtenir la validation métier et technique ;
8. rouvrir progressivement ;
9. conserver les preuves et conduire le retour d’expérience.

## 13.3 Tests

- exercice complet avant mise en production ;
- exercice régulier selon criticité ;
- temps réel comparé à l’objectif ;
- vérification d’un instant choisi ;
- scénario de suppression malveillante et corruption logique ;
- preuve de reconstruction des projections.

Une sauvegarde non restaurée avec succès n’est pas considérée comme fiable.

---

# 14. Politique d’évolution du schéma

## 14.1 Principes

- toute évolution est versionnée, révisée et liée à un besoin ;
- le propriétaire métier valide tout changement d’invariant, d’ownership ou de sens ;
- introduction, transition et retrait sont séparés pour les changements sensibles ;
- compatibilité avec les versions applicatives coexistantes lorsqu’un déploiement progressif l’exige ;
- aucune destruction avant preuve d’inutilité, sauvegarde et fin de la période de retour ;
- les opérations longues sont mesurées sur des volumes représentatifs ;
- verrous et impacts de concurrence sont estimés ;
- les changements sont observables et arrêtables lorsque possible ;
- la restauration technique et le retour applicatif sont distingués ;
- aucune modification manuelle silencieuse.

## 14.2 Protection des frontières

Une évolution ne fusionne pas Annonce, Cycle de vie, Modération, SEO et Recherche. Elle ne crée pas une structure partagée permettant à plusieurs modules de modifier le même concept. Tout déplacement d’ownership exige d’abord un ADR de domaine.

## 14.3 JSON

JSONB est réservé aux attributs réellement variables dont le noyau reste validé. Identités, états, ownership, montants, URL de référence, permissions et relations critiques ne sont pas relégués dans un document JSON pour éviter une évolution structurée.

---

# 15. Politique des données historiques

- les faits historiques sont append-only autant que le métier le permet ;
- une correction ajoute une décision rectificative plutôt que d’effacer la preuve ;
- auteur, date, motif et provenance accompagnent toute correction sensible ;
- état courant et historique sont cohérents sans devenir deux sources concurrentes ;
- les URL historiques survivent au changement de référence ;
- les décisions de modération et approbations conservent la preuve nécessaire ;
- les données financières restent séparées des avantages commerciaux ;
- archivage, retrait public, anonymisation et suppression sont distincts ;
- les durées de conservation sont décidées par domaine avant production ;
- les projections et statistiques ne remplacent jamais les faits historiques.

PostgreSQL facilite cette politique, mais la décision de conserver ou supprimer reste métier et juridique.

---

# 16. Politique de migration Legacy

## 16.1 Isolement

Les données Legacy n’entrent jamais directement dans les sources courantes. Elles passent par Lot Legacy, Dossier de décision Legacy et Rapprochement de reprise.

## 16.2 Règles

- provenance obligatoire ;
- source de vérité désignée par domaine ;
- qualification avant toute acceptation ;
- déduplication explicable ;
- décision Conserver, Nettoyer, Fusionner, Archiver ou Supprimer ;
- validation par le propriétaire cible ;
- volumes rapprochés et écarts justifiés ;
- répétabilité de la reprise ;
- plan de retour testé ;
- aucun contournement des contraintes de la cible ;
- correspondances historiques conservées sans devenir identités courantes ;
- aucun accès du produit courant aux sources Legacy.

## 16.3 PostgreSQL et Legacy

Les capacités de transaction, contraintes, indexation et JSONB peuvent soutenir la qualification et l’acceptation, mais ne déterminent aucune règle de fusion. Une fonction technique de rapprochement ne décide jamais qu’un Compte, Lieu, Professionnel ou Annonce est identique à un autre.

## 16.4 Temporalité

Les espaces et capacités de reprise sont supprimables après zéro écart inexpliqué, validation des propriétaires, expiration de la période de retour et preuve d’absence de dépendance courante.

---

# 17. Politique de performance

## 17.1 Mesure

- budgets par parcours et percentile ;
- données représentatives de Dakar, autres villes, catégories, médias et historiques ;
- plans d’exécution observés ;
- requêtes lentes, verrous, contention, connexions et croissance mesurés ;
- tests après chaque évolution significative ;
- capacité et marge documentées.

## 17.2 Indexation

- chaque index répond à une lecture ou contrainte identifiée ;
- index d’unicité pour les invariants ;
- index composites selon l’ordre réel des critères ;
- index partiels pour des sous-ensembles stables et justifiés ;
- index JSONB seulement sur usages validés ;
- index inutilisés ou redondants revus ;
- coût d’écriture et de maintenance inclus dans la décision.

## 17.3 Recherche

PostgreSQL peut servir la recherche initiale si les mesures le permettent. Cette décision ne supprime pas le module Recherche ni la possibilité d’un moteur spécialisé. Les résultats restent des projections ; la publication est toujours revalidée auprès du Cycle de vie pour les actions critiques.

## 17.4 Connexions et lectures

La gestion des connexions, les répliques de lecture et la mise en cache sont différées jusqu’à mesure. Une lecture sur réplique doit annoncer et respecter sa fraîcheur ; elle n’autorise jamais une décision critique sur un état potentiellement retardé.

---

# 18. Politique de sécurité

## 18.1 Accès

- identités techniques distinctes par environnement et finalité ;
- moindre privilège ;
- aucun compte applicatif propriétaire universel ;
- accès administratif nominatif, temporaire et audité ;
- aucun accès public direct ;
- secrets gérés hors du dépôt et avec rotation ;
- séparation sauvegarde, restauration et exploitation courante.

## 18.2 Chiffrement

- chiffrement des communications ;
- chiffrement des sauvegardes ;
- protection au repos selon l’infrastructure retenue ;
- clés séparées des données et rotation gouvernée ;
- aucune donnée sensible dans les journaux ou plans partagés.

## 18.3 Durcissement

- dernière révision corrective PostgreSQL 18 validée ;
- extensions refusées par défaut ;
- surface réseau minimale ;
- paramètres dangereux ou permissifs interdits ;
- surveillance des échecs d’accès, changements de privilèges et opérations sensibles ;
- correctifs selon la politique ADR-1001 adaptée au moteur ;
- revue de configuration avant ouverture.

## 18.4 Données personnelles

Minimisation, finalité, conservation, accès et anonymisation restent gouvernés par les domaines. Le moteur ne constitue pas une autorisation de collecte.

---

# 19. Alternatives étudiées

## 19.1 MySQL 8.4 LTS

Alternative principale. Maturité, compatibilité Laravel, compétences, JSON natif, InnoDB, réplication et écosystème sont excellents. Elle serait retenue si une contrainte d’hébergement, de compétence ou de coût rendait PostgreSQL disproportionné et si l’écart d’intégrité/indexation était couvert.

Elle n’est pas retenue initialement car PostgreSQL offre une combinaison plus homogène de contraintes avancées, transactions structurelles, JSONB, index partiels et recherche initiale.

## 19.2 MariaDB 11.8 LTS

Alternative viable, libre et mature. Elle n’est pas retenue en raison de la sémantique JSON différente, de la qualification supplémentaire de compatibilité Laravel et d’un avantage moins clair pour les invariants d’APPART.SN.

## 19.3 SQLite

Refusée comme plateforme principale de production. Elle peut avoir un usage d’outil local futur uniquement si les tests critiques continuent d’utiliser PostgreSQL, car ses comportements de concurrence, types et contraintes ne doivent pas masquer des divergences.

## 19.4 Plusieurs moteurs transactionnels

Refusés au démarrage. Ils multiplieraient exploitation, compétences, sauvegardes, cohérences et comportements sans domaine justifiant cette complexité.

## 19.5 Persistance documentaire principale

Refusée. Les états, références, permissions, paiements et URL exigent une intégrité relationnelle forte. JSONB reste complémentaire, jamais substitut au modèle métier central.

## 19.6 Recherche comme source principale

Refusée. Un moteur de recherche est une projection reconstruisible et ne peut porter publication, permission ou paiement.

## 19.7 Réutiliser la base Legacy

Refusé catégoriquement. Cela reproduirait les ambiguïtés, identifiants, contraintes faibles et frontières du Legacy, en contradiction avec tous les documents normatifs.

---

# 20. Décision retenue

## 20.1 Plateforme

**PostgreSQL est retenu comme plateforme relationnelle principale de persistance.**

## 20.2 Branche initiale

**PostgreSQL 18.x est retenu**, avec la dernière révision corrective stable validée au jour de la création effective. La branche 18 est supportée officiellement jusqu’au 14 novembre 2030.

## 20.3 Justification

PostgreSQL obtient le meilleur équilibre pour APPART.SN entre :

- intégrité et contraintes expressives ;
- concurrence et isolation ;
- transactions robustes ;
- indexation relationnelle, partielle et par expression ;
- JSONB validable et indexable ;
- recherche textuelle initiale ;
- observabilité ;
- sauvegarde, restauration à un instant choisi et réplication ;
- compatibilité officielle Laravel ;
- maturité, licence, support communautaire et réversibilité.

Le choix n’est pas dicté par la mode ni par une fonctionnalité isolée. Il protège directement les Aggregates, transactions critiques, historiques, URL et règles de reprise.

## 20.4 Contraintes associées

- une seule plateforme principale au démarrage ;
- Domaine indépendant de PostgreSQL et Laravel ;
- aucune extension approuvée par cet ADR ;
- aucune topologie ou fournisseur approuvé par cet ADR ;
- aucune structure de données approuvée par cet ADR ;
- JSONB complémentaire seulement ;
- dernière révision corrective de la branche après validation ;
- PostgreSQL utilisé aussi dans les tests d’intégration significatifs ;
- nouvel ADR pour changement de plateforme ou de branche majeure.

## 20.5 Repli

MySQL 8.4 LTS est l’alternative de repli à étudier si une preuve opérationnelle démontre que PostgreSQL 18 ne peut être exploité de façon sûre et soutenable. Ce repli exige un amendement formel, une nouvelle comparaison et l’absence de perte d’invariant.

---

# 21. Conséquences

## 21.1 Positives

- plateforme unique et cohérente ;
- forte défense des invariants structurels ;
- meilleures possibilités pour gérer les conflits concurrents ;
- JSONB sans abandon du relationnel ;
- options d’indexation adaptées aux filtres immobiliers ;
- recherche textuelle initiale possible sans décision prématurée sur un moteur séparé ;
- sauvegarde continue, restauration ciblée et réplication matures ;
- support de branche jusqu’en 2030 ;
- intégration officiellement documentée par Laravel.

## 21.2 Coûts

- formation ou recrutement de compétences PostgreSQL si nécessaires ;
- discipline sur plans, index, vacuum, connexions, blocages et statistiques ;
- tests de concurrence et de restauration obligatoires ;
- gestion explicite des échecs de sérialisation ;
- hébergement et observabilité à sélectionner ;
- risque d’utiliser trop de capacités spécifiques sans besoin.

## 21.3 Conséquences architecturales

La plateforme ne fusionne aucun module ni Aggregate. Les contrats des domaines ne contiennent pas de concepts PostgreSQL. Les projections restent séparées. Le module Migration Legacy reste temporaire. La décision ne transforme pas PostgreSQL en moteur SEO ou Recherche souverain.

## 21.4 Conséquences sur le plan J0

Une question bloquante de LARAVEL-PROJECT-PLAN.md est fermée au niveau plateforme et branche. Restent à décider avant création : topologie, fournisseur ou mode d’exploitation, objectifs de sauvegarde/restauration, configuration, secrets, organisation physique et stratégie d’évolution concrète.

---

# 22. Critères d’acceptation

L’ADR est accepté si :

- PostgreSQL, MySQL et MariaDB sont comparés sans caricature ;
- les quinze axes demandés sont explicitement évalués ;
- PostgreSQL 18.x est clairement retenu ;
- le cycle de support officiel est cité et devra être revérifié ;
- MySQL 8.4 LTS est reconnu comme alternative viable ;
- MariaDB 11.8 LTS est évalué selon sa politique officielle ;
- les Aggregate Roots restent les frontières de transactions par défaut ;
- Annonce, Cycle, Modération, SEO et Recherche ne sont pas fusionnés par la persistance ;
- les transitions, permissions, médias, paiements et URL ont des défenses d’intégrité identifiées ;
- la concurrence prévoit version, verrou ciblé, unicité et reprise bornée ;
- réplication et sauvegarde sont explicitement distinctes ;
- toute sauvegarde doit être restaurée et vérifiée ;
- les évolutions protègent fonctionnement, retour et ownership ;
- les données historiques ne sont pas réécrites silencieusement ;
- Migration Legacy reste qualifiée, rapprochée, réversible et temporaire ;
- JSONB ne remplace pas les structures critiques ;
- Recherche reste une projection reconstruisible ;
- sécurité, moindre privilège et séparation des rôles sont imposés ;
- plateforme, branche, topologie, fournisseur, configuration et structure sont clairement distingués ;
- aucune décision métier normative n’est modifiée ;
- les questions ouvertes sont visibles ;
- le document demeure conceptuel et ne crée aucune ressource.

---

# 23. Questions ouvertes

## Bloquantes avant création effective

1. Quelle révision corrective PostgreSQL 18 est la dernière stable et validée au jour J0 ?
2. Quel mode d’exploitation est retenu : géré, administré en interne ou hybride ?
3. Quel fournisseur ou environnement satisfait localisation, support, coût et réversibilité ?
4. Quelle extension PHP et quelle version assurent la connectivité PostgreSQL avec PHP 8.5 ?
5. Laravel 13 et tous les outils J0 sont-ils validés contre PostgreSQL 18 dans l’environnement cible ?
6. Quels objectifs de disponibilité, perte maximale et temps de restauration sont approuvés ?
7. Quelle stratégie de secrets et d’identités techniques est retenue ?
8. Qui possède l’exploitation PostgreSQL, les correctifs et les incidents ?

## Avant le premier domaine

9. Quelles conventions d’identifiants métier sont retenues pour les Roots ?
10. Quelles règles de nommage des contraintes et objets seront adoptées ?
11. Quelle isolation par défaut et quelles exceptions sont autorisées ?
12. Quelle stratégie de version concurrente est commune aux Aggregates ?
13. Comment rendre atomiques la décision et la publication de son événement sans coupler les modules ?
14. Quels tests d’intégration doivent obligatoirement utiliser PostgreSQL réel ?
15. Quels seuils de requête, verrou et transaction déclenchent une alerte ?

## Avant production

16. Quelle fréquence, rétention et localisation des sauvegardes ?
17. Quelle stratégie d’archivage continu et de restauration à un instant choisi ?
18. Quelle topologie de réplication, si elle est justifiée ?
19. Une capacité de regroupement de connexions est-elle nécessaire selon les mesures ?
20. Quelles extensions PostgreSQL sont autorisées et selon quel processus ?
21. Quels indicateurs et outils d’observabilité sont retenus ?
22. Quel exercice complet de restauration autorise l’ouverture publique ?
23. Quels seuils imposent un moteur de Recherche spécialisé ?
24. Quelles durées de conservation s’appliquent par domaine ?
25. Quels critères clôturent et suppriment les capacités Migration Legacy ?

---

# 24. Références vers ADR-1000 et ADR-1001

## 24.1 ADR-1000 — Technical Foundation

Le présent ADR ferme la décision « moteur de base de données » laissée ouverte par ADR-1000 :

- plateforme : PostgreSQL ;
- branche initiale : PostgreSQL 18.x ;
- révision corrective : dernière stable validée au jour J0.

Il applique les sections ADR-1000 relatives à l’intégrité, aux transactions, à la sécurité, aux évolutions applicatives, aux tests, aux performances, à l’observabilité et aux futurs ADR.

## 24.2 ADR-1001 — Runtime and Framework Selection

PostgreSQL est compatible avec le couple PHP 8.5.x et Laravel 13.x retenu par ADR-1001. La connectivité exige une extension PHP compatible, qui reste à vérifier et approuver avant J0. Le Domaine demeure indépendant du framework et du pilote.

## 24.3 Décisions encore ouvertes

Cet ADR ne ferme pas : fournisseur, hébergement, topologie, configuration, extension PHP précise, sauvegarde chiffrée concrète, objectifs de restauration, réplication, regroupement de connexions, extensions PostgreSQL, observabilité, structure de données ou migrations exécutables.

## 24.4 Références officielles

- [PostgreSQL — Versioning Policy](https://www.postgresql.org/support/versioning/)
- [PostgreSQL 18 — Concurrency Control](https://www.postgresql.org/docs/18/mvcc.html)
- [PostgreSQL 18 — Constraints](https://www.postgresql.org/docs/18/ddl-constraints.html)
- [PostgreSQL 18 — JSON Types](https://www.postgresql.org/docs/18/datatype-json.html)
- [PostgreSQL 18 — Index Types](https://www.postgresql.org/docs/18/indexes-types.html)
- [PostgreSQL 18 — Backup and Restore](https://www.postgresql.org/docs/18/backup.html)
- [PostgreSQL 18 — High Availability and Replication](https://www.postgresql.org/docs/18/high-availability.html)
- [MySQL 8.4 — Reference Manual](https://dev.mysql.com/doc/refman/8.4/en/)
- [MariaDB — Maintenance Policy](https://mariadb.org/about/)
- [MariaDB — JSON Data Type](https://mariadb.com/docs/server/reference/data-types/string-data-types/json)
- [Laravel 13 — Installation and Database Drivers](https://laravel.com/docs/13.x/installation)
- [ADR-1000 — Technical Foundation](./ADR-1000-TECHNICAL-FOUNDATION.md)
- [ADR-1001 — Runtime and Framework Selection](./ADR-1001-RUNTIME-AND-FRAMEWORK-SELECTION.md)

---

# Synthèse de la plateforme retenue

**PostgreSQL 18.x est retenu comme plateforme relationnelle principale**, avec la dernière révision corrective stable validée au jour de la création effective.

PostgreSQL est retenu pour son équilibre entre intégrité expressive, concurrence MVCC, transactions, contraintes, indexation, JSONB, recherche textuelle initiale, observabilité, sauvegarde, restauration à un instant choisi, réplication, maturité et compatibilité Laravel. Sa branche 18 est supportée officiellement jusqu’au 14 novembre 2030.

MySQL 8.4 LTS reste l’alternative de repli à étudier si une contrainte opérationnelle démontrée rend PostgreSQL non soutenable. MariaDB 11.8 LTS reste viable mais n’apporte pas ici un avantage suffisant.

# Décisions restant ouvertes

Restent à décider : révision corrective exacte, fournisseur, mode d’exploitation, topologie, objectifs de sauvegarde et restauration, extension PHP, secrets, isolation par défaut, stratégie de concurrence, publication atomique des événements, connexions, extensions PostgreSQL, observabilité, conventions de structure et durées de conservation.

# Confirmation de périmètre

Ce livrable est exclusivement conceptuel et documentaire. Aucun code, projet Laravel, commande Composer, base de données, table, schéma SQL, migration Laravel, modèle Eloquent ou fichier de configuration n’a été créé.

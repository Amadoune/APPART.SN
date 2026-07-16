# ADR-1000 — Fondation technique d’APPART.SN REBUILD

## Statut de la décision

- **Sprint :** 5 — Document 1
- **Date :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Portée :** décisions techniques fondatrices précédant la création du futur projet
- **Décideurs attendus :** Direction produit, responsable technique, sécurité, exploitation et propriétaires métier concernés

## Contexte normatif

La fondation conceptuelle d’APPART.SN REBUILD est validée. Le futur socle technique doit protéger les domaines, Aggregate Roots, permissions, transitions, politiques média, patrimoine SEO et règles de reprise déjà décidés. Il ne peut ni les simplifier silencieusement ni les déplacer vers un outil.

Le présent ADR fixe des principes, des critères de sélection et une gouvernance. Il ne sélectionne pas prématurément les versions, moteurs, fournisseurs ou services dont l’adéquation doit encore être démontrée.

---

# 1. Objet

Cet ADR définit les décisions techniques fondatrices qui guideront la création ultérieure d’APPART.SN REBUILD. Il établit :

- les principes non négociables du futur socle ;
- la méthode de choix et de maintien des technologies ;
- les règles de modularité et de dépendance ;
- les politiques de configuration, secrets, erreurs et journalisation ;
- les exigences de qualité, tests, performance et sécurité ;
- les conditions exigeant de nouvelles décisions formelles.

Il ne décrit aucune réalisation et ne modifie aucune règle métier validée.

---

# 2. Principes techniques

## 2.1 Décisions fermes

1. **Monolithe modulaire initial.** Le produit commence comme une unité opérationnelle unique, structurée selon les treize domaines validés.
2. **Domaine indépendant du socle.** Les règles, Aggregates, Entités, Value Objects, événements et politiques métier ne dépendent pas de Laravel ni d’une capacité externe.
3. **Frontières par autorité métier.** Un module ne modifie jamais directement la source de vérité d’un autre module.
4. **Ports aux frontières significatives.** Paiements, médias, notifications, recherche, identité externe, audit, cache et traitements différés sont accessibles par des besoins exprimés depuis le produit.
5. **Lectures dérivées sans souveraineté.** Recherche, SEO, statistiques et vues administratives restent reconstruisibles et ne décident jamais publication, permission ou paiement.
6. **Cohérence forte ciblée.** Transitions, permissions, paiements, suspensions, retraits et décisions à quatre yeux privilégient l’intégrité immédiate.
7. **Cohérence différée bornée.** Recherche, sitemap, notifications et statistiques disposent d’un budget de fraîcheur, d’une surveillance et d’un comportement prudent en cas de retard.
8. **Sécurité par défaut.** Refus par défaut, moindre privilège, validation de toute entrée et exposition minimale.
9. **Observabilité dès l’origine.** Les parcours critiques sont corrélables, mesurables et auditables sans exposer de secrets.
10. **Réversibilité.** Toute dépendance externe importante possède une stratégie de remplacement, d’indisponibilité et de sortie.
11. **Simplicité proportionnée.** Aucun système distribué, abstraction ou package n’est introduit sans besoin mesuré.
12. **Legacy isolé et temporaire.** Aucune décision courante ne dépend du Legacy ; les capacités de reprise disparaissent après leur clôture officielle.

## 2.2 Hiérarchie de décision

En cas de conflit, l’ordre suivant s’applique :

1. sécurité des personnes, des données et des paiements ;
2. invariants métier et obligations normatives ;
3. intégrité et réversibilité ;
4. disponibilité des parcours critiques ;
5. performance mesurée ;
6. confort de développement ;
7. préférence individuelle ou effet de mode.

---

# 3. Critères de sélection technologique

Toute technologie candidate est évaluée avec une grille documentée.

| Critère | Question de décision | Preuve attendue |
|---|---|---|
| Protection métier | Permet-elle de préserver les frontières et invariants sans contournement ? | Scénarios critiques et analyse de dépendances |
| Support | Dispose-t-elle d’une fenêtre de maintenance compatible avec la roadmap ? | Calendrier officiel et stratégie de mise à niveau |
| Sécurité | Les correctifs, avis et pratiques de durcissement sont-ils fiables ? | Politique de sécurité, délai de correction et audit |
| Stabilité | Le comportement nécessaire est-il éprouvé et prévisible ? | Maturité, compatibilité et historique de versions |
| Intégrité | Protège-t-elle les décisions critiques et la concurrence ? | Cas de transition, paiement, permission et reprise |
| Performance | Répond-elle aux budgets sur un jeu de données représentatif ? | Mesures reproductibles et marges documentées |
| Observabilité | Permet-elle de comprendre erreurs, lenteurs et saturation ? | Signaux disponibles et corrélation des parcours |
| Exploitabilité | L’équipe peut-elle sauvegarder, restaurer, mettre à jour et diagnostiquer ? | Procédures testées et compétences disponibles |
| Réversibilité | Les données et comportements peuvent-ils être repris ailleurs ? | Plan de sortie et formats maîtrisés |
| Écosystème | Les dépendances utiles sont-elles maintenues et sobres ? | État des mainteneurs, compatibilité et alternatives |
| Coût total | Le coût de possession est-il soutenable, y compris humain ? | Estimation sur construction, exploitation et sortie |
| Conformité | Les droits, licences, localisation et obligations sont-ils acceptables ? | Revue juridique et sécurité lorsque nécessaire |

Une technologie n’est jamais choisie uniquement parce qu’elle est populaire, déjà connue ou fournie par défaut.

---

# 4. Politique de versions

## 4.1 Règles communes

- retenir une version stable, officiellement supportée et compatible avec l’horizon de mise en production ;
- éviter une version en fin de support pendant la construction ou peu après le lancement ;
- ne pas adopter une version de prépublication pour un composant critique ;
- documenter la matrice de compatibilité entre langage, socle, extensions, outils de qualité et capacités externes ;
- séparer mises à jour de sécurité, correctifs, évolutions mineures et changements majeurs ;
- appliquer rapidement les correctifs de sécurité après analyse de risque ;
- tester toute mise à niveau contre les invariants et parcours critiques ;
- prévoir une fenêtre régulière de maintenance pour éviter les sauts de versions massifs ;
- enregistrer les versions effectivement approuvées dans un ADR ultérieur avant création du projet.

## 4.2 PHP

**Décision de principe :** utiliser une branche stable activement supportée, compatible avec la version Laravel retenue et avec toutes les extensions strictement nécessaires.

**Critères :** durée de support restante, sécurité, performance mesurée, compatibilité de l’écosystème, disponibilité dans les environnements cibles, capacités de typage et de diagnostic.

**Protection métier :** un langage supporté réduit le risque qu’une faille ou incompatibilité force une modification urgente des règles de publication, paiement ou permission.

**Décision ouverte :** numéro exact de version, après confirmation du calendrier de démarrage et d’exploitation.

## 4.3 Laravel

**Décision de principe :** Laravel soutiendra les interfaces, l’orchestration et les adaptateurs, sans définir le Domaine ni les frontières des Aggregates.

**Critères :** support officiel restant, compatibilité PHP, stabilité, politique de sécurité, capacité à respecter la modularité, qualité des outils de test et d’exploitation, coût de mise à niveau.

**Protection métier :** le découplage empêche qu’une convention du socle remplace l’état officiel d’une annonce, une permission ou une décision de modération.

**Décision ouverte :** version exacte et composants optionnels autorisés.

## 4.4 Base de données

**Décision de principe :** sélectionner un moteur transactionnel mature comme source durable des données métier courantes. Il doit fournir intégrité, concurrence maîtrisée, sauvegarde, restauration et exploitation fiable.

**Critères :** garanties transactionnelles, contraintes d’intégrité, comportement sous concurrence, indexation adaptée, sauvegarde cohérente, restauration à un instant choisi, réplication éventuelle, observabilité, compétences et coût.

**Protection métier :** transitions uniques, paiements sans double effet, ownership, quatre yeux et correspondances historiques exigent des garanties explicites.

**Décision ouverte :** moteur, version, topologie et politique détaillée de sauvegarde.

## 4.5 Cache

**Décision de principe :** le cache accélère une lecture ; il ne constitue jamais la seule preuve d’une permission, d’un paiement, d’un état de publication ou de l’éligibilité d’un contact.

**Critères :** stratégie d’expiration et d’invalidation, comportement en panne, cohérence, isolation, observabilité, coût et simplicité opérationnelle.

**Protection métier :** une valeur périmée ne doit pas rendre publiable, visible ou contactable une annonce qui ne l’est plus.

**Décision ouverte :** besoin réel, capacité retenue, usages autorisés et budgets de fraîcheur.

## 4.6 Recherche

**Décision de principe :** commencer par la solution la plus simple qui satisfait les besoins mesurés. Toute vue de recherche reste dérivée, reconstruisible et sans pouvoir de mutation.

**Critères :** filtres, facettes, pertinence, pagination stable, tolérance aux fautes, géographie, fraîcheur, reconstruction, retrait rapide, volumétrie, exploitabilité et coût.

**Protection métier :** seules les annonces publiées et admissibles apparaissent ; la source d’état demeure le Cycle de vie.

**Décision ouverte :** capacité intégrée ou moteur spécialisé, après prototype de charge et jeu de données représentatif.

## 4.7 Queue

**Décision de principe :** utiliser des traitements différés seulement pour les conséquences qui n’exigent pas une décision immédiate : notifications, variantes média, projections, sitemap, statistiques et tâches de reprise compatibles.

**Critères :** livraison au moins une fois assumée, idempotence, reprise, ordre nécessaire, délai maximal, visibilité des échecs, files d’échec, capacité de drainage et coût opérationnel.

**Protection métier :** une transition, une permission ou une confirmation financière ne dépend jamais uniquement de l’achèvement différé d’une tâche.

**Décision ouverte :** capacité, politique de rétention, priorités et budgets par type de tâche.

## 4.8 Stockage média

**Décision de principe :** choisir une capacité durable et remplaçable respectant ownership, droits, retrait public, conservation, confidentialité et variantes fonctionnelles définis par la Media Policy.

**Critères :** durabilité, contrôle d’accès, suppression et conservation distinctes, intégrité, disponibilité, performance de lecture, traitement des métadonnées, sauvegarde, réversibilité, localisation et coût.

**Protection métier :** un média interdit ou retiré cesse tout usage public ; un original et ses variantes restent rattachables au même propriétaire et à la même décision.

**Décision ouverte :** fournisseur, localisation, classes de conservation et capacité de transformation.

## 4.9 Tests

**Décision de principe :** retenir des outils maintenus permettant des tests rapides du Domaine, des tests d’intégration contrôlés, des tests de parcours critiques et des contrôles de sécurité et performance.

**Critères :** compatibilité des versions, isolation, lisibilité, parallélisation, diagnostic, gestion des données de test, intégration continue et faible dépendance aux détails internes.

**Protection métier :** les dix états, permissions, quatre yeux, médias, SEO, paiements et règles Legacy disposent de preuves automatisées proches de leur autorité.

**Décision ouverte :** outils exacts et seuils de couverture utiles, sans objectif de pourcentage aveugle.

## 4.10 Observabilité

**Décision de principe :** collecter journaux, mesures et traces corrélées selon des standards ouverts ou exportables, avec minimisation des données sensibles.

**Critères :** corrélation, échantillonnage maîtrisé, alertes, rétention, contrôle d’accès, coût, portabilité et capacité de diagnostic de bout en bout.

**Protection métier :** publication indue, retard de retrait, double paiement, échec média et dérive SEO doivent être détectables et attribuables.

**Décision ouverte :** solution, hébergement, rétention et seuils d’alerte.

---

# 5. Principes de modularité

- un module correspond à l’un des treize domaines ou à une capacité externe clairement délimitée ;
- les vingt Aggregate Roots retenus restent sous l’autorité de leur domaine du Domain Mapping ;
- chaque module expose des intentions, lectures et événements explicitement approuvés ;
- l’accès aux données internes d’un autre module est interdit ;
- une dépendance inter-module pointe vers un contrat appartenant au besoin du consommateur ou vers un langage partagé minimal ;
- les cycles de dépendance sont interdits ; une coordination se fait à un niveau explicite sans fusionner les domaines ;
- aucun « Core » universel ne contient de règle d’annonce, compte, média, SEO, paiement ou reprise ;
- le code partagé futur sera limité aux concepts véritablement indépendants des domaines ;
- l’administration utilise les mêmes actions métier que les autres interfaces ;
- les projections de lecture sont séparées des modèles de décision ;
- Migration Legacy est un module temporaire, sans dépendance du produit courant vers lui ;
- toute exception de dépendance exige un ADR.

La modularité protège directement l’interdiction faite au SEO de modifier une annonce, au Paiement de publier, à Recherche de devenir source de vérité et à l’Administration de contourner un invariant.

---

# 6. Organisation physique future des modules

## 6.1 Décision de principe

L’organisation future reflétera les domaines avant les couches techniques globales. Chaque domaine disposera d’un espace clairement identifiable contenant ses concepts métier, ses cas d’usage, ses points d’entrée autorisés et ses adaptateurs propres.

## 6.2 Zones conceptuelles futures

Sans figer de dossiers ni de fichiers, l’organisation distinguera :

- **Domaines métier :** Identité, Professionnels, Catalogue, Cycle de vie, Médias, Géographie, Recherche, Contacts, Modération, Contenus et SEO, Monétisation, Administration et audit, Migration Legacy ;
- **orchestration :** coordination des cas d’usage sans règle métier propriétaire ;
- **interfaces :** public, espace utilisateur, administration et tâches planifiées ;
- **adaptateurs :** capacités externes et mécanismes techniques ;
- **lectures :** projections de recherche, SEO, administration et statistiques ;
- **socle partagé minimal :** primitives réellement transverses et sans connaissance métier.

## 6.3 Décisions différées

Les noms de dossiers, espaces de noms, mécanismes d’enregistrement des modules et limites exactes de chargement ne sont pas décidés ici. Une proposition physique devra être validée par un ADR dédié et démontrer :

- l’absence de dépendance du Domaine vers Laravel ;
- la visibilité des frontières ;
- le contrôle automatique des dépendances interdites ;
- la possibilité de tester un domaine sans capacité externe ;
- la suppression complète du module Migration Legacy après clôture.

---

# 7. Convention de nommage

## 7.1 Langage

- les noms métier suivent le langage partagé validé ;
- les concepts métier peuvent conserver leur terme français lorsqu’une traduction créerait une ambiguïté ;
- la convention finale choisira une langue principale cohérente pour le code futur et évitera les mélanges dans un même concept ;
- les acronymes sont limités aux termes officiellement partagés ;
- les termes Legacy ne nomment jamais un concept courant.

## 7.2 Intentions, faits et lectures

- une intention emploie un verbe d’action précis : Soumettre une annonce, Retirer un média, Confirmer un paiement ;
- un événement emploie un fait au passé : Annonce publiée, Média retiré, Paiement confirmé ;
- une lecture nomme le résultat attendu, pas le moyen de l’obtenir ;
- une politique nomme la décision qu’elle évalue ;
- une erreur métier nomme la règle violée et non une panne technique générique.

## 7.3 Interdictions

Éviter les noms vagues tels que Manager, Helper, Common, Utils, Data, Handler universel ou Core métier. Un nom doit révéler son domaine, sa responsabilité et sa portée.

La convention concrète de casse, suffixes et espaces de noms sera décidée avant création du projet.

---

# 8. Politique des dépendances

## 8.1 Direction

- le Domaine ne dépend que de concepts du langage et de bibliothèques standards explicitement admises ;
- l’orchestration dépend du Domaine et de ports nécessaires aux cas d’usage ;
- les interfaces et adaptateurs dépendent des contrats qu’ils satisfont ;
- Laravel et les packages tiers restent aux frontières du Domaine ;
- un module consommateur ne lit ni n’écrit les données internes d’un module propriétaire ;
- les événements publics sont versionnés conceptuellement et minimisés.

## 8.2 Admission

Chaque nouvelle dépendance doit déclarer : finalité, propriétaire, version, licence, données manipulées, risques, solution de remplacement, maintenance et stratégie de retrait.

## 8.3 Contrôle

La future chaîne de qualité vérifiera automatiquement les cycles, violations de frontières et imports interdits. Toute dérogation sera temporaire, justifiée, datée et assortie d’un plan de suppression.

## 8.4 Indisponibilité

Chaque capacité externe possède un comportement défini : refus prudent, attente, reprise ou fonctionnalité dégradée. Une panne de notification ne défait pas une transition ; une panne de Recherche n’autorise pas une lecture incohérente ; une panne de paiement ne produit pas de confirmation.

---

# 9. Politique de configuration

- distinguer les valeurs déployables, les secrets et les paramètres métier gouvernés ;
- conserver les valeurs non sensibles dans un mécanisme versionné et révisable ;
- injecter les valeurs propres à chaque environnement sans les incorporer aux règles métier ;
- valider au démarrage la présence, le format et la cohérence des valeurs obligatoires ;
- échouer explicitement si une configuration critique manque ou est incohérente ;
- documenter propriétaire, finalité, valeur par défaut sûre et procédure de changement ;
- ne jamais utiliser la configuration technique pour contourner un état, une permission ou une décision de modération ;
- faire évoluer les paramètres métier via Paramètre métier gouverné et les approbations prévues ;
- interdire la modification libre de configuration depuis l’administration ;
- assurer la parité de structure entre environnements, avec valeurs adaptées à chacun.

La solution concrète et les formats restent ouverts jusqu’à la décision sur les environnements et la gestion des secrets.

---

# 10. Gestion des secrets

## 10.1 Principes

- aucun secret dans le code, l’historique de versions, les journaux, les erreurs, les captures ou les données de test ;
- stockage dans une capacité dédiée avec contrôle d’accès, chiffrement, audit et rotation ;
- accès accordé au moindre privilège, par environnement et par finalité ;
- secrets distincts entre développement, validation et production ;
- rotation possible sans modification des règles métier ;
- révocation immédiate en cas de suspicion ;
- absence de valeur réelle dans les postes ou contextes qui n’en ont pas besoin ;
- inventaire avec propriétaire, date de rotation et procédure d’urgence.

## 10.2 Protection métier

La compromission d’un secret de média, paiement, identité ou notification ne doit pas permettre de contourner une permission ou de fabriquer un fait métier accepté sans validation. Les preuves reçues d’un tiers restent contrôlées selon leur domaine propriétaire.

## 10.3 Décisions ouvertes

Capacité retenue, autorité de rotation, durées, accès d’urgence, récupération et intégration aux environnements.

---

# 11. Journalisation

## 11.1 Trois finalités distinctes

1. **Journal technique :** comprendre l’exécution, les erreurs et les performances.
2. **Audit métier :** prouver une action sensible, son auteur, sa décision et son résultat.
3. **Mesures :** observer volumes, durées, taux d’échec et saturation.

Ces finalités ne partagent pas automatiquement les mêmes contenus, droits ou durées de conservation.

## 11.2 Contenu minimal

- horodatage fiable ;
- environnement et composant logique ;
- identifiant de corrélation ;
- type d’opération ;
- résultat et durée ;
- identités métier minimales lorsque justifiées ;
- catégorie d’erreur ou d’alerte.

## 11.3 Données interdites

Secrets, mots de passe, preuves financières complètes, contenus privés, métadonnées personnelles inutiles, médias bruts et informations de contact non nécessaires.

## 11.4 Protection métier

Publication, suspension, retrait, décisions de modération, changements de rôle, quatre yeux, paiement, redirection, fusion géographique et décisions Legacy doivent être corrélables à leur audit métier sans que le journal technique devienne source de vérité.

---

# 12. Gestion des erreurs

## 12.1 Catégories

- violation d’une règle métier ;
- refus d’autorisation ;
- intention invalide ;
- conflit de décision concurrente ;
- ressource absente ou devenue indisponible ;
- dépendance externe indisponible ;
- dépassement de délai ;
- erreur technique inattendue ;
- incohérence de données nécessitant isolation et investigation.

## 12.2 Règles

- une erreur métier est explicable dans le vocabulaire du domaine ;
- une erreur publique ne révèle ni secret, ni détail interne, ni existence d’une ressource non autorisée ;
- une erreur technique possède une corrélation sans exposer sa cause sensible ;
- les opérations pouvant être rejouées définissent leur idempotence ;
- les reprises sont bornées et n’amplifient pas une panne ;
- un échec différé persistant devient visible et traitable ;
- les incohérences critiques privilégient l’arrêt prudent et l’alerte ;
- aucune exception technique ne transforme un refus métier en succès.

## 12.3 Protection métier

Un échec de notification n’annule pas une publication acquise. Un échec d’actualisation de Recherche entraîne une gestion de fraîcheur. Une preuve de paiement ambiguë ne confirme rien. Une erreur de média interdit l’exposition par défaut.

---

# 13. Politique des migrations applicatives

## 13.1 Deux responsabilités distinctes

- les évolutions structurelles du futur produit accompagnent les versions applicatives ;
- la reprise Legacy suit MIGRATION-RULES.md et demeure isolée dans son domaine temporaire.

Elles ne doivent jamais être confondues dans une opération implicite.

## 13.2 Principes pour les évolutions structurelles

- toute évolution est versionnée, révisée, reproductible et liée à une version du produit ;
- elle protège les données existantes et les invariants ;
- elle prévoit compatibilité pendant les déploiements progressifs lorsque nécessaire ;
- les transformations destructrices sont séparées de l’introduction d’une nouvelle structure ;
- sauvegarde, vérification, observabilité et stratégie de retour sont définies avant exécution ;
- une évolution longue ou à fort volume est mesurée sur des données représentatives ;
- aucune correction manuelle silencieuse n’est admise en production ;
- chaque changement d’ownership, d’état ou de contrainte métier exige une validation du propriétaire concerné.

## 13.3 Protection métier

Les dix états, historiques de transition, ownership média, URL historiques, paiements, preuves d’approbation et correspondances Legacy ne peuvent être perdus, réinterprétés ou fusionnés par commodité technique.

## 13.4 Décisions ouvertes

Stratégie concrète de déploiement compatible, politique de retour selon le type d’évolution, seuil définissant une opération longue et responsabilités d’approbation.

---

# 14. Politique de qualité

La qualité future repose sur des contrôles reproductibles :

- formatage cohérent et automatisé ;
- analyse statique avec niveau progressif puis obligatoire ;
- règles de type explicites sur les frontières et concepts critiques ;
- contrôle automatique des dépendances entre modules ;
- détection des dépendances vulnérables ou abandonnées ;
- tests obligatoires selon le risque ;
- absence de secret et de donnée sensible dans les artefacts ;
- seuils de complexité et duplication surveillés ;
- documentation des décisions et changements de comportement ;
- échec de la chaîne de validation sur une violation critique ;
- aucune désactivation permanente d’un contrôle sans décision approuvée.

Les seuils chiffrés et outils seront décidés avant le premier développement. Ils doivent favoriser la lisibilité des règles métier, non produire une conformité purement statistique.

---

# 15. Politique des revues de code

## 15.1 Exigences

- toute modification rejoint la branche principale après revue par au moins une autre personne ;
- un changement sensible exige une personne compétente dans le domaine concerné ;
- sécurité, paiement, permission, état d’annonce, SEO patrimonial et reprise Legacy exigent un niveau de revue renforcé ;
- l’auteur ne peut être l’unique approbateur de son changement ;
- les changements restent petits, cohérents et reliés à une intention ;
- la revue vérifie tests, observabilité, retour, dépendances et documentation ;
- les commentaires critiques sont résolus explicitement ;
- une urgence est revue après coup dans un délai court et produit les actions correctives nécessaires.

## 15.2 Grille de revue

1. Quelle règle métier est protégée ou affectée ?
2. Le propriétaire du concept reste-t-il unique ?
3. Une frontière de module ou d’Aggregate est-elle franchie ?
4. Permissions et quatre yeux sont-ils respectés ?
5. Les erreurs et effets différés sont-ils maîtrisés ?
6. Les journaux minimisent-ils les données sensibles ?
7. Les tests prouvent-ils le comportement, y compris les refus ?
8. La performance et la sécurité ont-elles été évaluées proportionnellement ?
9. Une dépendance tierce ou un ADR supplémentaire est-il nécessaire ?

---

# 16. Politique de tests

## 16.1 Pyramide de preuve

- **Domaine :** majorité des règles, rapides et indépendantes de Laravel et des capacités externes.
- **Cas d’usage :** orchestration, permissions, transactions conceptuelles, erreurs et effets.
- **Intégration :** conformité des adaptateurs, concurrence, persistance, cache, recherche, paiement, média et notification.
- **Contrats :** attentes stables envers les capacités externes et événements inter-domaines.
- **Parcours :** scénarios critiques public, particulier, professionnel et administration.
- **Qualités non fonctionnelles :** sécurité, performance, reprise, résilience et restauration.

## 16.2 Couverture obligatoire par risque

- dix états et toutes les transitions autorisées et refusées ;
- permissions des neuf acteurs, actions interdites et quatre yeux ;
- conformité, propriété, remplacement et retrait des médias ;
- URL historiques, redirections, indexabilité et pages pauvres ;
- exclusion immédiate des annonces non publiées de Recherche et Contacts ;
- unicité des confirmations financières et séparation paiement-publication ;
- qualification, déduplication, rapprochement et retour Legacy ;
- concurrence sur publication, suspension, attribution de rôle et paiement ;
- comportement en indisponibilité des capacités externes ;
- sauvegarde et restauration avec vérification métier.

## 16.3 Données de test

Utiliser des données synthétiques, déterministes et représentatives. Aucune donnée personnelle de production n’est admise sans processus formel d’anonymisation et autorisation exceptionnelle.

## 16.4 Critère de réussite

La couverture est évaluée par scénarios et risques. Un pourcentage ne remplace jamais la preuve des invariants critiques.

---

# 17. Politique de performance

## 17.1 Mesurer avant de complexifier

- établir des budgets par parcours avant optimisation ;
- mesurer avec volumes, distribution géographique et médias représentatifs ;
- instrumenter les percentiles, pas seulement les moyennes ;
- conserver une marge pour les pics et dégradations ;
- comparer toute optimisation à une référence reproductible ;
- refuser cache, parallélisme ou distribution sans goulot mesuré.

## 17.2 Parcours prioritaires

- accueil et navigation mobile ;
- recherche, filtre, tri et pagination ;
- fiche annonce et médias ;
- création et modification d’annonce ;
- soumission et modération ;
- suspension et retrait effectifs ;
- contact d’un annonceur ;
- administration des files critiques ;
- paiement conditionnel ;
- génération des vues SEO et reprise de données.

## 17.3 Budgets à décider

Temps de réponse par percentile, poids média, délai d’actualisation de Recherche, délai de retrait critique, débit des files, délai de traitement média, capacité de reprise, temps de restauration et objectifs de disponibilité.

## 17.4 Protection métier

Une optimisation ne peut contourner une autorisation, utiliser une projection comme vérité, différer une suspension sans limite ou dégrader la qualité média sous le seuil validé.

---

# 18. Politique de sécurité

## 18.1 Principes

- modèle de menace maintenu selon les parcours et données ;
- authentification robuste et récupération contrôlée ;
- autorisation par action, ressource, ownership, mandat et état ;
- refus par défaut et moindre privilège ;
- validation stricte de toute entrée, y compris médias et données Legacy ;
- protection contre abus, automatisation hostile et répétitions ;
- chiffrement approprié en transit et au repos ;
- séparation des environnements et des responsabilités ;
- dépendances corrigées et inventoriées ;
- sauvegardes protégées et restaurations testées ;
- réponse aux incidents avec rôles, preuves et communication ;
- tests de sécurité proportionnés avant lancement et évolutions sensibles.

## 18.2 Données personnelles

- minimisation par finalité ;
- accès tracé et limité ;
- durées de conservation explicites ;
- suppression, anonymisation et preuve distinguées ;
- aucune donnée personnelle dans Recherche, SEO, journaux ou environnements non autorisés au-delà du strict nécessaire.

## 18.3 Médias

- considérer tout média comme non fiable ;
- vérifier type, taille, qualité, droits, métadonnées et contenu ;
- isoler le traitement ;
- empêcher l’usage public avant conformité ;
- rendre le retrait effectif sur tous les usages.

## 18.4 Paiements

- aucune donnée financière sensible inutile ;
- preuve authentique et vérifiée ;
- idempotence ;
- rapprochement ;
- séparation Finance, Commercial et Modération ;
- paiement sans pouvoir de publication.

## 18.5 Administration

L’administration n’est jamais une zone de confiance implicite. Les mêmes autorisations métier s’appliquent, les actions sensibles sont auditées et le Super Administrateur ne contourne pas les règles.

---

# 19. Politique des packages tiers

## 19.1 Principe d’admission

Un package est admis seulement s’il répond à un besoin réel mieux qu’une solution interne simple et sûre. Son adoption exige :

- finalité et périmètre précis ;
- maintenance active et historique de sécurité acceptable ;
- compatibilité avec les versions retenues ;
- licence approuvée ;
- dépendances transitives comprises ;
- taille et surface d’attaque proportionnées ;
- comportement observable et testable ;
- possibilité de remplacement ou retrait ;
- responsable interne identifié.

## 19.2 Interdictions

- package abandonné pour une fonction critique ;
- package qui impose ses concepts au Domaine ;
- package capable d’être installé ou exécuté depuis l’administration ;
- dépendance à une branche non stable pour la production ;
- duplication de plusieurs packages pour la même capacité sans justification ;
- mise à jour automatique non vérifiée d’une dépendance critique.

## 19.3 Suivi

Inventaire, versions approuvées, avis de sécurité, licences, propriétaires, dates de revue et plans de sortie seront maintenus. Les packages inutilisés seront retirés dans une évolution dédiée et vérifiée.

---

# 20. Politique de dette technique

## 20.1 Définition

Est une dette toute divergence consciente entre l’état actuel et la qualité requise : contournement de frontière, test manquant, dépendance vieillissante, duplication, manque d’observabilité, solution provisoire ou décision différée.

## 20.2 Règles

- aucune dette cachée dans un commentaire isolé ;
- chaque dette possède description, cause, risque métier, propriétaire, date, échéance et critère de résolution ;
- aucune dette ne peut affaiblir silencieusement sécurité, paiement, permissions, états, médias interdits ou patrimoine SEO ;
- les exceptions critiques sont corrigées avant livraison ;
- une capacité régulière de réduction est réservée dans la roadmap ;
- la dette est revue à chaque version et avant tout changement majeur ;
- les tendances sont suivies : âge, criticité, volume, récurrence et domaines affectés.

## 20.3 Seuil d’escalade

Une dette devient décision d’architecture si elle touche plusieurs modules, impose une dépendance durable, modifie une qualité transversale, dépasse deux cycles de livraison ou menace un invariant métier.

---

# 21. Critères imposant un ADR futur

Un nouvel ADR est obligatoire avant toute décision qui :

- choisit ou change une version majeure de PHP ou Laravel ;
- sélectionne ou remplace base de données, cache, recherche, Queue, stockage média ou observabilité ;
- fixe l’organisation physique des modules ;
- modifie la direction des dépendances ou autorise une exception ;
- fusionne, divise ou déplace un Aggregate Root ;
- introduit un système distribué ou extrait un module ;
- définit un nouveau format d’événement public ;
- change les limites transactionnelles ;
- choisit la gestion des secrets, l’authentification ou le chiffrement ;
- adopte un package critique ou un service externe sans alternative simple ;
- modifie sauvegarde, restauration, disponibilité ou déploiement ;
- introduit une stratégie de cache pouvant affecter la fraîcheur critique ;
- change le traitement des données personnelles ou leur localisation ;
- rend irréversible une évolution de données ;
- prolonge Migration Legacy au-delà de sa clôture prévue ;
- contredit ou nécessite de réviser un document normatif.

Chaque ADR précisera contexte, décision, alternatives, conséquences, risques, preuves, plan de retour, statut et documents métier protégés.

---

# 22. Roadmap technique

## Phase 0 — Arbitrages préalables

- confirmer calendrier et horizon de support ;
- mesurer volumétrie, croissance, médias, recherche et reprise ;
- classer données et parcours critiques ;
- définir objectifs de disponibilité, restauration, performance et fraîcheur ;
- produire les ADR de versions, persistance, authentification et secrets.

**Sortie :** décisions suffisamment précises pour créer le projet sans hypothèse cachée.

## Phase 1 — Socle minimal

- créer ultérieurement le projet selon les versions approuvées ;
- matérialiser les frontières de modules et contrôles de dépendance ;
- établir qualité, tests, sécurité, journalisation et corrélation ;
- définir environnements et gestion des secrets ;
- prouver qu’un domaine peut être testé sans capacité externe.

**Sortie :** fondation exécutable future, sans fonctionnalité métier contournant les documents normatifs.

## Phase 2 — Invariants P0

- Identité, Géographie, Catalogue, Cycle de vie, Médias et Modération ;
- permissions et quatre yeux ;
- audit des décisions sensibles ;
- preuves automatisées des états et interdictions.

**Sortie :** cœur métier cohérent avant visibilité publique.

## Phase 3 — Lectures et exposition

- Recherche, SEO, navigation publique, Contacts et vues administratives ;
- budgets de fraîcheur et retrait prudent ;
- performance mobile et média ;
- observabilité des parcours de bout en bout.

**Sortie :** exposition publique fidèle aux sources métier.

## Phase 4 — Reprise et validation

- capacités temporaires Legacy ;
- répétitions contrôlées, rapprochements et validation métier ;
- répétition du retour ;
- vérification des URL historiques et données critiques.

**Sortie :** zéro écart inexpliqué et décision formelle de bascule.

## Phase 5 — Capacités conditionnelles et durcissement

- Monétisation si le périmètre est validé ;
- charge, sécurité et résilience ;
- sauvegarde et restauration complètes ;
- préparation opérationnelle et réponse aux incidents ;
- suppression des capacités temporaires après clôture.

**Sortie :** produit exploitable avec risques acceptés et responsabilités établies.

---

# 23. Critères d’acceptation

L’ADR est acceptable si :

- il respecte les neuf documents normatifs et ne change aucune règle métier ;
- monolithe modulaire, Domaine indépendant et frontières d’Aggregates sont confirmés ;
- les décisions fermes sont distinguées des décisions ouvertes ;
- PHP, Laravel, base de données, cache, recherche, Queue, média, tests et observabilité ont des critères explicites ;
- aucun numéro de version ou fournisseur n’est retenu sans preuve et calendrier ;
- chaque politique technique explique sa contribution à la protection du métier ;
- l’organisation physique future reste indicative et exige un ADR dédié ;
- les dépendances inter-modules directes et cycles sont interdits ;
- configuration, secrets, journaux et erreurs minimisent les risques ;
- les évolutions applicatives et la reprise Legacy sont séparées ;
- tests, performance et sécurité ciblent les parcours et invariants critiques ;
- les packages tiers ont une admission, un suivi et une sortie ;
- la dette technique possède propriétaire, risque et échéance ;
- les déclencheurs de futurs ADR sont explicites ;
- la roadmap place les invariants avant les optimisations et capacités conditionnelles ;
- les questions ouvertes sont visibles ;
- le livrable demeure strictement documentaire.

---

# 24. Questions ouvertes

## Priorité 1 — Avant création du projet

1. Quelle date cible de mise en production et quelle durée minimale de support guident PHP et Laravel ?
2. Quel moteur transactionnel satisfait le mieux intégrité, restauration, compétences et coût ?
3. Quelle organisation physique rend les treize domaines visibles et contrôlables sans dépendance du Domaine envers Laravel ?
4. Quelle stratégie d’authentification et de récupération protège particuliers, professionnels et administration ?
5. Quelle capacité gère les secrets, leur rotation et l’accès d’urgence ?
6. Quels environnements sont nécessaires et quelles exigences de parité s’appliquent ?
7. Quels objectifs chiffrés de disponibilité, restauration et perte maximale acceptable sont retenus ?
8. Quels outils et seuils de qualité deviennent bloquants dès le premier changement ?

## Priorité 2 — Avant les modules concernés

9. Les besoins mesurés justifient-ils un moteur de Recherche spécialisé au lancement ?
10. Quels usages de cache sont autorisés et quels budgets de fraîcheur s’appliquent ?
11. Quelle capacité de Queue, quels ordres et quelles politiques de reprise sont nécessaires ?
12. Quelle solution média satisfait droits, retrait, conservation, variantes et réversibilité ?
13. Quelle solution d’observabilité et quelles durées de rétention sont proportionnées ?
14. Quels formats et garanties régissent les événements traversant les frontières ?
15. Quels seuils de charge et de latence déclenchent une évolution de capacité ?
16. Quels contrôles de sécurité externes sont exigés avant lancement ?

## Priorité 3 — Avant exploitation et bascule

17. Quelle politique de sauvegarde et de restauration est testée de bout en bout ?
18. Quelle stratégie de déploiement et de retour protège les évolutions longues ?
19. Quels tableaux de bord, alertes et procédures d’astreinte couvrent les parcours critiques ?
20. Quels seuils imposent une double revue technique ou sécurité ?
21. Quel inventaire de packages et quelle fréquence de revue seront obligatoires ?
22. Quels critères opérationnels autorisent la clôture et la suppression de Migration Legacy ?
23. Le périmètre initial inclut-il Monétisation et paiements ?
24. Quels risques résiduels sont acceptés formellement avant ouverture publique ?

---

# Synthèse des décisions techniques retenues

APPART.SN REBUILD commencera comme un monolithe modulaire orienté domaines. Le Domaine restera indépendant de Laravel et des capacités externes. Les vingt Aggregate Roots validés conserveront leur ownership. Les interactions entre modules passeront par des intentions, lectures et événements explicites ; les accès directs et cycles seront interdits.

Les technologies seront choisies selon support, sécurité, intégrité, performance mesurée, exploitabilité, réversibilité et coût total. Base durable, cache, Recherche, Queue, stockage média et observabilité auront des responsabilités séparées. Cache et projections ne décideront jamais d’une règle métier. Sécurité, tests, corrélation, qualité, revue et gestion de la dette seront présents dès le socle.

# Décisions restant ouvertes

Restent à décider par ADR dédiés : versions exactes de PHP et Laravel, moteur et version de base de données, organisation physique détaillée, authentification, secrets, environnements, cache, Recherche, Queue, stockage média, observabilité, formats d’événements, sauvegarde, restauration, déploiement, budgets de performance et périmètre initial de Monétisation.

# Risques techniques majeurs

- recréer un monolithe procédural malgré le découpage documentaire ;
- laisser Laravel ou un package tiers définir les concepts métier ;
- créer un « Core » universel ou des cycles inter-modules ;
- utiliser cache ou Recherche comme source de vérité ;
- coupler paiement et publication ;
- exposer une annonce suspendue à cause d’une fraîcheur non maîtrisée ;
- disperser permissions et quatre yeux ;
- journaliser des secrets ou données personnelles ;
- accumuler des dépendances vieillissantes ;
- adopter une complexité distribuée avant mesure ;
- rendre une évolution de données irréversible ;
- laisser Migration Legacy devenir permanente.

# Confirmation de périmètre

Ce livrable est exclusivement documentaire. Aucun code, projet Laravel, commande Composer, migration exécutable, API, fichier de configuration ou structure Laravel définitive n’a été créé.

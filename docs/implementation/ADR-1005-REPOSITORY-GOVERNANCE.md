# ADR-1005 — Gouvernance du dépôt

## Statut de la décision

- **Projet :** APPART.SN REBUILD 2026
- **Sprint :** 7 — Document 5
- **Date de décision :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Portée :** gouvernance Git, contributions, revues, intégration continue conceptuelle et qualité
- **Hors périmètre :** création du dépôt, règles GitHub effectives, automatisation CI, code et configuration

## Décision directrice

APPART.SN REBUILD adoptera une gouvernance proche du trunk-based development : une branche principale protégée et toujours intégrable, des branches de travail courtes, une Pull Request obligatoire pour tout changement et des Quality Gates proportionnées au risque.

Aucun changement ne rejoindra la branche principale par accès direct. L’auteur ne sera jamais l’unique approbateur. Les changements sensibles exigeront une revue métier ou sécurité supplémentaire.

---

# 1. Objet

Cet ADR définit :

- la gouvernance Git ;
- la stratégie des branches ;
- les conventions de commit ;
- les règles des Pull Requests ;
- les Quality Gates ;
- les revues obligatoires ;
- la CI conceptuelle ;
- la protection des branches ;
- les règles de fusion ;
- la Definition of Done ;
- la politique de dette technique.

Il ne crée ni dépôt, ni workflow, ni règle de plateforme. Il décrit les contrôles qui devront être matérialisés au Sprint J0.

---

# 2. Principes de gouvernance

1. **Branche principale saine.** Elle doit toujours représenter un état vérifié et potentiellement livrable.
2. **Changement révisé.** Toute modification passe par une Pull Request.
3. **Auteur distinct de l’approbateur.** Aucune auto-fusion d’un changement ordinaire.
4. **Risque proportionné.** Plus un invariant est critique, plus les preuves et approbations sont fortes.
5. **Petits lots.** Les changements courts sont plus faciles à comprendre, tester et retourner.
6. **Traçabilité.** Chaque changement possède une intention, un propriétaire, des preuves et une décision.
7. **Automatisation reproductible.** Les contrôles automatiques sont identiques pour tous les contributeurs.
8. **Aucun contournement silencieux.** Une exception est explicite, temporaire, auditée et revue après urgence.
9. **Frontières protégées.** Les règles physiques et métier sont contrôlées avant fusion.
10. **Sécurité dès le dépôt.** Secrets, dépendances vulnérables et données sensibles bloquent la fusion.
11. **Documentation normative préservée.** Une décision métier ne change pas au détour d’un correctif technique.
12. **Dette visible.** Aucun compromis connu ne disparaît dans un commentaire ou une promesse orale.

---

# 3. Rôles de gouvernance

| Rôle | Responsabilité | Interdiction |
|---|---|---|
| Contributeur | proposer un changement complet et vérifiable | approuver seul son propre changement |
| Auteur | porter la Pull Request, répondre aux remarques et maintenir les preuves | fusionner malgré un Gate rouge |
| Relecteur technique | vérifier conception, lisibilité, tests et exploitation | approuver sans examiner le risque déclaré |
| Propriétaire de domaine | vérifier le langage, les invariants et les frontières métier | modifier silencieusement une autre politique normative |
| Référent sécurité | revoir identité, secrets, permissions, données, dépendances et surfaces sensibles | remplacer le propriétaire métier |
| Référent données | revoir PostgreSQL, concurrence, sauvegarde, restauration et évolutions sensibles | décider seul d’un changement d’ownership |
| Responsable qualité | gouverner Gates, exceptions et tendances | réduire un seuil sans décision |
| Mainteneur du dépôt | administrer protections, accès et fusion autorisée | contourner les approbations métier |
| Responsable de livraison | décider la promotion d’une version vérifiée | déclarer Done un changement incomplet |

Une personne peut exercer plusieurs rôles si la séparation requise reste respectée. Pour les actions à quatre yeux, auteur et approbateur sont toujours deux personnes distinctes.

---

# 4. Stratégie des branches

## 4.1 Branche principale

La branche principale officielle sera `main`.

Elle est :

- protégée ;
- non modifiable directement ;
- à historique maîtrisé ;
- soumise aux Quality Gates ;
- toujours intégrable ;
- la source des versions et livraisons futures.

## 4.2 Branches de travail

Toute modification naît d’une branche courte issue d’une version récente de `main`.

Familles conceptuelles :

- `feature` pour une capacité fonctionnelle ;
- `fix` pour une correction ;
- `security` pour un traitement de sécurité ;
- `docs` pour une évolution documentaire ;
- `refactor` pour une amélioration sans changement intentionnel de comportement ;
- `chore` pour une maintenance bornée ;
- `hotfix` pour une urgence de production future.

Le format exact du nom sera décidé à J0. Il comportera au minimum le type, une référence de travail et un résumé court.

## 4.3 Durée

- cible : moins de trois jours ouvrés ;
- revue obligatoire au-delà de cinq jours ;
- branche considérée longue au-delà de dix jours ;
- une branche longue doit être découpée ou justifiée avec stratégie de synchronisation ;
- aucune branche ne devient un environnement permanent.

## 4.4 Absence de branche `develop`

Aucune branche d’intégration longue `develop` n’est retenue. Elle créerait deux vérités, retarderait les conflits et affaiblirait la garantie que `main` est intégrable.

## 4.5 Branches de livraison

Les branches de livraison permanentes ne sont pas retenues au démarrage. Une branche temporaire de stabilisation ne pourra être créée que si le processus de livraison le justifie, avec propriétaire, durée et règles de report des corrections.

---

# 5. Synchronisation et conflits

- la branche de travail est synchronisée régulièrement avec `main` ;
- les conflits sont résolus par l’auteur avec compréhension des deux intentions ;
- un conflit touchant un invariant ou document normatif implique le propriétaire concerné ;
- aucun conflit n’est résolu en supprimant un test ou une règle sans justification ;
- après synchronisation importante, les Gates sont rejoués ;
- la Pull Request doit refléter exactement le contenu qui sera fusionné ;
- une branche obsolète après changement d’architecture est abandonnée ou réévaluée, jamais fusionnée mécaniquement.

La méthode technique de synchronisation sera fixée à J0. L’histoire ne doit pas être réécrite après début de revue sans signalement clair aux relecteurs.

---

# 6. Conventions de commit

## 6.1 Format

Les commits suivent une convention inspirée de Conventional Commits :

**type, portée, résumé impératif et référence éventuelle.**

Types autorisés conceptuellement :

- `feat` ;
- `fix` ;
- `docs` ;
- `refactor` ;
- `test` ;
- `perf` ;
- `security` ;
- `build` ;
- `ci` ;
- `chore` ;
- `revert`.

Cette liste pourra être ajustée avant J0 sans changer les principes.

## 6.2 Portée

La portée nomme le domaine ou la capacité propriétaire : `geography`, `identity-access`, `listing-lifecycle`, `media`, `repository`, par exemple. `core`, `common` ou `misc` sont refusés comme portées vagues.

## 6.3 Résumé

- court et précis ;
- décrit l’intention, pas seulement le fichier ;
- rédigé dans la langue de contribution retenue à J0 ;
- aucune information sensible ;
- aucun terme Legacy pour un concept courant.

## 6.4 Corps du commit

Requis lorsque le changement n’est pas évident. Il explique :

- pourquoi ;
- règle métier ou ADR protégé ;
- conséquence notable ;
- risque ou incompatibilité ;
- stratégie de retour lorsque nécessaire.

## 6.5 Atomicité

Un commit représente une intention cohérente. Il ne mélange pas fonctionnalité, reformatage massif, montée de dépendance et refactoring sans nécessité.

## 6.6 Commits interdits

- « WIP » fusionné dans `main` ;
- message vide ou générique ;
- secret ou donnée personnelle ;
- désactivation de contrôle sans explication ;
- fichier généré ou binaire non approuvé ;
- correction de production sans trace de décision.

---

# 7. Pull Request obligatoire

## 7.1 Règle

Tout changement de `main`, y compris documentation, dépendances, configuration future, urgence et automatisation, passe par une Pull Request.

## 7.2 Contenu minimal

Chaque Pull Request indique :

- objectif et contexte ;
- domaine ou capacité propriétaire ;
- document normatif ou ADR concerné ;
- type et niveau de risque ;
- comportement avant et après ;
- tests et contrôles exécutés ;
- effets sur permissions, données, sécurité, performance et observabilité ;
- conséquences sur migration, retour et compatibilité ;
- dette créée ou remboursée ;
- captures ou preuves utiles sans donnée sensible ;
- questions restant aux relecteurs.

## 7.3 Taille

- cible indicative : changement compréhensible en moins d’une heure de revue attentive ;
- une Pull Request dépassant 500 lignes modifiées non générées doit justifier son indivisibilité ;
- au-delà de 1 000 lignes, une stratégie de découpage ou revue par étapes est obligatoire ;
- les fichiers générés sont isolés du volume d’analyse ;
- la taille seule ne bloque pas une reprise nécessaire, mais impose davantage de preuves.

## 7.4 Brouillon

Une Pull Request peut être ouverte en brouillon pour obtenir un retour précoce. Elle ne peut être fusionnée tant qu’elle reste en brouillon ou contient une décision non résolue.

## 7.5 Responsabilité de l’auteur

L’auteur réalise d’abord sa propre revue, retire les éléments inutiles, explique les compromis, répond aux commentaires et maintient la branche compatible avec `main`.

---

# 8. Classification du risque

## 8.1 Niveau R0 — documentaire sans décision

Corrections rédactionnelles sans changement de sens.

## 8.2 Niveau R1 — faible

Maintenance locale, test additionnel, refactoring borné ou changement sans impact métier, sécurité ou données.

## 8.3 Niveau R2 — significatif

Nouvelle fonctionnalité, cas d’usage, contrat public, évolution de données réversible, nouvelle projection ou dépendance admise.

## 8.4 Niveau R3 — critique

Concerne au moins un élément suivant :

- état ou transition d’Annonce ;
- authentification, autorisation, rôle, Mandat ou secret ;
- paiement, remboursement ou rapprochement ;
- décision de modération ;
- données personnelles ou export ;
- URL historique, redirection patrimoniale ou indexabilité ;
- ownership ou frontière d’Aggregate ;
- sauvegarde, restauration ou suppression ;
- Migration Legacy et rapprochement ;
- infrastructure de production ou chaîne de livraison ;
- dépendance critique ou correctif de sécurité.

## 8.5 Effet

Le niveau détermine les relecteurs, preuves, Gates et autorisations de fusion. L’auteur propose le niveau ; un relecteur peut l’augmenter. Le réduire exige une justification approuvée.

---

# 9. Règles de revue

## 9.1 Nombre minimal

| Risque | Approbations minimales | Compétences requises |
|---|---:|---|
| R0 | 1 | mainteneur documentaire ou propriétaire concerné |
| R1 | 1 | relecteur technique |
| R2 | 2 | technique et propriétaire de domaine ou capacité |
| R3 | 2 au minimum, 3 lorsque sécurité et métier sont distincts | technique, propriétaire métier et sécurité/données selon le risque |

## 9.2 Propriétaires obligatoires

- Listing Lifecycle pour les états et transitions ;
- Identité et sécurité pour authentification, permissions et secrets ;
- Finance pour les faits financiers ;
- Modération pour décisions et preuves ;
- SEO / Contenu pour patrimoine d’URL et indexabilité ;
- Géographie pour lieux, aliases et fusions ;
- Migration pour reprise Legacy, avec le propriétaire du domaine cible ;
- architecture pour frontières et dépendances ;
- exploitation pour sauvegarde, restauration et disponibilité.

## 9.3 Revue effective

Une approbation signifie que le relecteur a examiné l’intention, le changement, les preuves et les risques. Les approbations automatiques, de convenance ou sans lecture sont interdites.

## 9.4 Nouvelle revue

Une approbation est invalidée si un changement substantiel intervient après elle. Correction rédactionnelle mineure exceptée, les relecteurs doivent voir la version finale.

## 9.5 Désaccord

Un désaccord non résolu bloque la fusion. L’escalade suit : auteur et relecteur, propriétaire de domaine, responsable technique, puis gouvernance appropriée. La position hiérarchique ne remplace pas la preuve.

---

# 10. Quality Gates universels

Tout changement futur devra satisfaire, selon son contenu :

1. format et conventions ;
2. analyse statique ;
3. tests du Domaine ;
4. tests des cas d’usage ;
5. contrôle des dépendances physiques ;
6. détection de secrets ;
7. analyse des dépendances vulnérables et licences ;
8. absence de données personnelles de production ;
9. tests d’intégration concernés ;
10. documentation et changelog lorsque requis ;
11. absence de marqueur temporaire non suivi ;
12. validation de la compatibilité PHP 8.5, Laravel 13 et PostgreSQL 18 selon le niveau ;
13. compilation ou amorçage futur réussi ;
14. contrôles de sécurité applicables ;
15. approbations obligatoires présentes.

Un Gate requis en échec bloque la fusion. Un contrôle « non applicable » doit être explicable, jamais ignoré silencieusement.

---

# 11. Quality Gates métier et architecture

## 11.1 Frontières

- aucun concept Laravel dans le Domaine ;
- aucun accès interne inter-module ;
- aucun cycle de dépendance ;
- aucun concept métier dans `src/Shared` sans admission ;
- aucune dépendance courante vers Migration Legacy ;
- projections sans pouvoir de décision.

## 11.2 Cycle de vie

- transitions autorisées et refusées couvertes ;
- acteur, état source, motif et résultat vérifiés ;
- publication impossible par Paiement, SEO, Recherche ou Administration ;
- suspension et retrait propagés selon le budget de fraîcheur.

## 11.3 Permissions

- refus par défaut ;
- action et ressource évaluées ;
- ownership, Mandat, état et niveau d’assurance vérifiés ;
- quatre yeux lorsque requis ;
- actions interdites de Commercial, Modération, SEO, Finance et Super Administrateur testées.

## 11.4 Données et persistance

- transaction bornée à l’Aggregate ;
- concurrence testée pour l’effet critique ;
- évolution réversible ou plan de retour ;
- intégrité et historique conservés ;
- aucun JSON utilisé pour éviter une structure critique.

## 11.5 SEO et Legacy

- annonce non publiée jamais indexable ;
- URL historique préservée ;
- aucune page pauvre créée ;
- provenance et rapprochement Legacy vérifiables ;
- zéro écart masqué.

---

# 12. CI conceptuelle

## 12.1 Objectif

La CI reproduira les preuves nécessaires à chaque Pull Request et à `main`. Elle ne remplacera ni la revue humaine ni la validation métier.

## 12.2 Étapes conceptuelles

1. validation du contenu du dépôt ;
2. détection de secrets et fichiers interdits ;
3. résolution reproductible des dépendances futures ;
4. format et analyse statique ;
5. tests Domaine et Application ;
6. contrôles d’architecture ;
7. tests d’intégration PostgreSQL et capacités concernées ;
8. tests de contrats ;
9. tests de parcours ciblés ;
10. analyses de sécurité et licences ;
11. contrôles documentaires ;
12. production de rapports et preuves ;
13. Gates supplémentaires de performance ou sécurité selon risque.

## 12.3 Propriétés

- environnement isolé ;
- données synthétiques ;
- secrets de CI dédiés, courts et minimaux ;
- résultats attribuables au contenu exact évalué ;
- aucun secret dans les journaux ou artefacts ;
- délais maîtrisés avec parallélisation sûre ;
- échec explicite, jamais transformé automatiquement en succès ;
- conservation des preuves proportionnée.

## 12.4 Pipeline principal

`main` rejoue les contrôles après fusion. Une divergence entre résultat de Pull Request et `main` bloque toute promotion et ouvre une investigation.

## 12.5 CI indisponible

L’indisponibilité de la CI bloque les fusions ordinaires. Une urgence critique suit la procédure d’exception, avec contrôles manuels équivalents et régularisation immédiate.

---

# 13. Protection des branches

## 13.1 `main`

Protections obligatoires futures :

- interdiction de push direct ;
- Pull Request obligatoire ;
- approbations selon risque ;
- Gates obligatoires ;
- branche à jour avant fusion selon la stratégie retenue ;
- conversations bloquantes résolues ;
- approbations invalidées après changement substantiel ;
- historique non réécrit ;
- suppression interdite ;
- force push interdit ;
- signature des commits ou attestations à décider avant J0 ;
- fusion limitée aux mainteneurs autorisés.

## 13.2 Tags et versions

- tags de version protégés ;
- création par le processus de livraison autorisé ;
- aucune réutilisation ou modification d’un tag publié ;
- lien vers le contenu exact, les notes et les preuves ;
- signature à privilégier selon la capacité choisie.

## 13.3 Accès administrateur à la plateforme

Les administrateurs du dépôt restent soumis aux protections. Le droit technique de contourner n’est pas une autorisation. Toute utilisation d’urgence est alertée, motivée et revue.

---

# 14. Règles de fusion

## 14.1 Méthode retenue

La fusion par **squash** est la méthode par défaut : une Pull Request devient un changement cohérent dans `main`, avec un message conforme et une référence traçable.

## 14.2 Exceptions

Une fusion conservant plusieurs commits peut être autorisée pour :

- montée technique où les étapes ont un intérêt de retour ;
- reprise ou évolution complexe dont les phases sont indépendamment vérifiées ;
- contribution dont l’historique apporte une preuve utile.

L’exception doit être décidée avant fusion. Les commits intermédiaires restent propres et vérifiables.

## 14.3 Préconditions

- Pull Request non brouillon ;
- contenu final revu ;
- Gates verts ;
- approbations valides ;
- conflits absents ;
- dette déclarée ;
- documentation et notes à jour ;
- stratégie de retour connue pour R2/R3 ;
- aucun blocage métier ou sécurité ouvert.

## 14.4 Après fusion

- branche de travail supprimée ;
- résultat sur `main` surveillé ;
- ticket lié mis à jour ;
- anomalie traitée par une nouvelle Pull Request ou un retour formel ;
- aucune correction directe.

---

# 15. Politique de retour

- tout changement R2/R3 définit avant fusion comment revenir à un état sûr ;
- retour applicatif et retour des données sont distingués ;
- une évolution irréversible exige une phase de compatibilité et un ADR si nécessaire ;
- un revert ne supprime pas l’analyse de cause ;
- une vulnérabilité ne doit pas être réintroduite par retour ;
- les URL historiques, paiements, états et preuves ne sont pas perdus ;
- après retour, les projections sont reconstruites ou invalidées selon leur politique ;
- toute urgence de retour est auditée.

---

# 16. Gestion des urgences

## 16.1 Définition

Une urgence est limitée à une vulnérabilité exploitable, une indisponibilité critique, une corruption ou perte de données, une publication indue, un paiement erroné ou une exposition grave.

## 16.2 Procédure

1. déclarer l’incident ;
2. nommer un responsable ;
3. préparer le plus petit changement sûr ;
4. obtenir au moins une revue indépendante compétente ;
5. exécuter tous les contrôles disponibles ;
6. documenter les contrôles différés ;
7. fusionner par la voie protégée si possible ;
8. surveiller ;
9. régulariser les Gates et revues manquants ;
10. conduire un retour d’expérience.

## 16.3 Interdictions

- urgence utilisée pour accélérer une fonctionnalité ;
- désactivation durable d’une protection ;
- commit anonyme ou partagé ;
- secret transmis dans la Pull Request ;
- correction sans test de non-régression après stabilisation.

---

# 17. Definition of Ready

Un changement peut entrer en réalisation lorsque :

- objectif et valeur sont compris ;
- propriétaire métier ou technique identifié ;
- critères d’acceptation vérifiables ;
- documents normatifs concernés connus ;
- dépendances et prérequis disponibles ;
- niveau de risque proposé ;
- questions bloquantes fermées ;
- stratégie de données et sécurité identifiée ;
- observabilité et retour envisagés ;
- taille compatible avec une Pull Request révisable.

Un travail non Ready peut rester en exploration documentaire, mais ne doit pas produire une fonctionnalité à fusionner.

---

# 18. Definition of Done

Un changement est Done uniquement si :

## 18.1 Métier

- critères d’acceptation satisfaits ;
- invariant et propriétaire préservés ;
- parcours nominal, refus et exceptions couverts ;
- permissions et quatre yeux vérifiés ;
- aucun document normatif contredit.

## 18.2 Technique

- code futur lisible et conforme ;
- frontières physiques respectées ;
- tests nécessaires présents et verts ;
- concurrence et idempotence traitées lorsque pertinentes ;
- erreurs et effets différés maîtrisés ;
- dépendances admises et inventoriées ;
- aucune configuration ou capacité inutile.

## 18.3 Sécurité et données

- modèle de menace mis à jour si nécessaire ;
- aucun secret ni donnée réelle introduit ;
- accès minimaux ;
- conservation et suppression respectées ;
- évolution de données et retour vérifiés ;
- vulnérabilités critiques ou élevées non acceptées silencieusement.

## 18.4 Exploitation

- journaux, mesures et alertes proportionnés ;
- comportement en panne défini ;
- performance comparée aux budgets ;
- procédure de retour connue ;
- documentation opérationnelle mise à jour.

## 18.5 Gouvernance

- Pull Request complète ;
- revues obligatoires obtenues ;
- Gates verts ;
- commentaires résolus ;
- changelog et ADR mis à jour si requis ;
- dette enregistrée avec propriétaire et échéance ;
- changement fusionné et vérifié sur `main`.

« Fonctionne sur mon poste » n’est jamais une Definition of Done.

---

# 19. Politique de dette technique

## 19.1 Registre obligatoire

Chaque dette possède :

- identifiant ;
- description et cause ;
- domaine ou capacité ;
- risque métier et technique ;
- criticité ;
- propriétaire ;
- date de création ;
- échéance ;
- solution cible ;
- critère de clôture ;
- liens vers Pull Request, décision et incidents éventuels.

## 19.2 Catégories

- frontière ou architecture ;
- qualité et tests ;
- sécurité ;
- données et persistance ;
- performance ;
- observabilité ;
- dépendance ou version ;
- documentation ;
- exploitation ;
- Legacy temporaire.

## 19.3 Règles

- aucune dette critique de sécurité, paiement, permission ou publication acceptée avant livraison ;
- aucune violation permanente de frontière ;
- une dette créée par une Pull Request est déclarée dans celle-ci ;
- une date sans capacité de traitement n’est pas acceptable ;
- la dette arrivée à échéance bloque une nouvelle fonctionnalité du même domaine, sauf décision contraire motivée ;
- une part planifiée de chaque cycle est réservée à sa réduction ;
- la tendance d’âge, de volume et de criticité est revue à chaque jalon ;
- une dette touchant plusieurs modules ou deux cycles devient candidate à un ADR.

## 19.4 Dettes interdites

- secret connu dans l’historique ;
- vulnérabilité critique non traitée ;
- perte possible d’une URL historique ;
- contournement de quatre yeux ;
- publication par Paiement ou Recherche ;
- dépendance courante vers LegacyMigration ;
- sauvegarde jamais restaurée ;
- test désactivé sans ticket et échéance.

---

# 20. Gouvernance des dépendances

Toute introduction ou montée majeure future précise :

- besoin ;
- alternative sans dépendance ;
- propriétaire ;
- compatibilité PHP 8.5 et Laravel 13 ;
- licence ;
- historique de sécurité ;
- dépendances transitives ;
- surface d’exécution ;
- tests ;
- stratégie de mise à niveau et sortie.

Une Pull Request de dépendance est isolée d’un changement métier autant que possible. Aucun package non approuvé ne peut être introduit indirectement sans revue.

---

# 21. Gouvernance documentaire

- les documents normatifs sont modifiés dans une Pull Request dédiée ou clairement isolée ;
- un changement de décision exige propriétaire et statut ;
- un ADR accepté n’est pas réécrit pour masquer l’historique : il est amendé ou remplacé ;
- les liens et références sont vérifiés ;
- le changelog retrace les livrables ;
- une correction orthographique ne change pas le sens ;
- aucune documentation ne contient secret, accès ou donnée personnelle ;
- la revue documentaire applique les mêmes protections de branche.

---

# 22. Mesures de gouvernance

Les indicateurs servent à améliorer le flux, jamais à classer individuellement les personnes.

Mesures candidates :

- délai entre ouverture et première revue ;
- durée totale d’une Pull Request ;
- taille et âge des branches ;
- taux d’échec des Gates ;
- changements retournés après fusion ;
- défauts échappés par niveau de risque ;
- temps de correction des vulnérabilités ;
- âge et criticité de la dette ;
- taux de revues R3 avec les compétences requises ;
- violations de frontières détectées ;
- changements urgents et causes ;
- temps de restauration des Gates après panne CI.

Les objectifs chiffrés seront fixés après une période de référence. Optimiser une mesure au détriment de la qualité est interdit.

---

# 23. Critères d’acceptation

L’ADR est accepté si :

- `main` est la seule branche principale et reste intégrable ;
- les branches de travail sont courtes ;
- aucune branche `develop` permanente n’est retenue ;
- tout changement exige une Pull Request ;
- les commits sont atomiques, traçables et conventionnels ;
- l’auteur ne s’auto-approuve pas ;
- les risques R0 à R3 déterminent revues et Gates ;
- les propriétaires métier revoient les invariants sensibles ;
- les Gates universels, métier, sécurité et architecture sont définis ;
- la CI conceptuelle utilise des environnements isolés et données synthétiques ;
- l’échec d’un Gate requis bloque la fusion ;
- `main`, tags et versions sont protégés ;
- squash est la méthode de fusion par défaut ;
- les exceptions et urgences restent révisées et auditées ;
- toute évolution R2/R3 dispose d’un retour ;
- Definition of Ready et Definition of Done sont vérifiables ;
- la dette possède propriétaire, risque, échéance et critère de clôture ;
- les dettes critiques interdites sont explicitement listées ;
- les dépendances futures ont une procédure d’admission ;
- la documentation est soumise à la même gouvernance ;
- aucun choix métier validé n’est modifié ;
- aucune configuration de plateforme ou CI n’est créée.

---

# 24. Questions ouvertes

## Bloquantes avant J0

1. Quelle plateforme hébergera le dépôt ?
2. Qui sont les mainteneurs initiaux et propriétaires des protections ?
3. Quel format exact sera retenu pour les noms de branches ?
4. Quelle langue principale sera utilisée pour les commits et Pull Requests ?
5. Quels outils matérialiseront format, analyse, architecture, secrets, sécurité et licences ?
6. Quels Gates sont obligatoires dès le tout premier commit ?
7. Les commits et tags devront-ils être signés dès J0 ?
8. Quel mécanisme associera propriétaires de domaines et chemins physiques ?
9. Quels délais de revue seront des objectifs de service internes ?
10. Où sera tenu le registre de dette technique ?

## Avant le premier développement métier

11. Les seuils de 500 et 1 000 lignes sont-ils adaptés après les premières Pull Requests ?
12. Quel niveau de risque s’applique par défaut à un nouveau cas d’usage ?
13. Quels changements documentaires peuvent rester R0 ?
14. Quel propriétaire tranche les exceptions à `src/Shared` ?
15. Quelle stratégie de synchronisation avec `main` sera imposée ?
16. Quel format de rapport prouve tests de concurrence et performance ?
17. Comment invalider automatiquement les approbations après changement substantiel ?
18. Quelle rétention s’applique aux journaux et artefacts de CI ?

## Avant production

19. Une branche temporaire de stabilisation sera-t-elle nécessaire ?
20. Quel processus produit et protège les tags de version ?
21. Qui peut déclencher une fusion d’urgence ?
22. Quel délai impose la régularisation d’une urgence ?
23. Quels objectifs chiffrés gouvernent vulnérabilités, dette et temps de revue ?
24. Quelle preuve de restauration et de retour est requise pour un changement de données R3 ?
25. Comment mesurer la santé de la gouvernance sans créer d’incitation négative ?

---

# 25. Références aux ADR validés

## ADR-1000

Cet ADR matérialisera au niveau du dépôt les politiques de qualité, revue, tests, packages et dette définies par ADR-1000.

## ADR-1001

Les Gates futurs vérifieront la compatibilité avec PHP 8.5.x et Laravel 13.x, les mises à niveau et les avis de sécurité.

## ADR-1002

Les changements PostgreSQL 18.x exigeront revue de concurrence, intégrité, retour, sauvegarde et restauration proportionnée.

## ADR-1003

Les contrôles d’architecture protégeront `src`, `app`, les treize modules, Shared, les projections et l’isolement de Migration Legacy.

## ADR-1004

La gouvernance interdira secrets dans le dépôt, comptes partagés, autorisations faibles et changements d’identité sans revue de sécurité.

---

# Synthèse des règles

APPART.SN REBUILD utilisera une branche `main` protégée, des branches courtes et une Pull Request pour tout changement. Squash sera la fusion par défaut. Les risques R0 à R3 détermineront le nombre et la compétence des relecteurs. Aucun auteur ne s’auto-approuvera.

Les Quality Gates couvriront format, analyse, tests, architecture, secrets, dépendances, sécurité, documentation et compatibilité. Les changements critiques sur états, permissions, paiements, SEO patrimonial, données et Legacy recevront des revues renforcées.

La Definition of Done inclut métier, technique, sécurité, données, exploitation et gouvernance. Toute dette sera enregistrée avec risque, propriétaire, échéance et critère de clôture.

# Confirmation de périmètre

Ce livrable est exclusivement documentaire. Aucun code, projet Laravel, configuration GitHub, pipeline CI, règle de branche réelle ou autre configuration n’a été créé.

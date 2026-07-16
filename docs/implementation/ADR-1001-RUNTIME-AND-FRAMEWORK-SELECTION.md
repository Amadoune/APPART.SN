# ADR-1001 — Sélection du runtime et du framework

## Statut de la décision

- **Projet :** APPART.SN REBUILD 2026
- **Sprint :** 7 — Document 1
- **Date de décision :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Décision principale :** PHP 8.5.x et Laravel 13.x
- **Périmètre :** runtime PHP et framework Laravel du futur projet
- **Hors périmètre :** création du projet, structure physique, moteur de données, cache, recherche, Queue, média, déploiement et infrastructure

## Sources temporelles

Les calendriers et compatibilités cités sont ceux publiés par les projets officiels et vérifiés le 16 juillet 2026. Ils doivent être revérifiés au moment de la création effective du projet.

---

# 1. Objet

Cet ADR prend la décision officielle sur les branches de PHP et Laravel qui serviront de fondation au premier projet APPART.SN REBUILD.

Il définit également les politiques de support, mise à niveau, rétrocompatibilité, sécurité, extensions et dépendances applicables à ce couple. Il ne crée aucun artefact exécutable et ne décide aucune structure physique.

---

# 2. Contexte

La Foundation v1.0 est validée. Elle impose :

- un monolithe modulaire orienté domaines ;
- un Domaine indépendant de Laravel ;
- vingt Aggregate Roots aux frontières explicites ;
- des lectures dérivées sans autorité métier ;
- une cohérence immédiate pour publication, permissions, paiements, suspensions et retraits ;
- une cohérence différée bornée pour Recherche, SEO et statistiques ;
- une sécurité, une observabilité et des tests présents dès le socle ;
- une zone Migration Legacy temporaire.

ADR-1000 a laissé ouvertes les versions exactes. LARAVEL-PROJECT-PLAN.md interdit la première ligne de code tant que PHP et Laravel ne sont pas officiellement sélectionnés.

Au 16 juillet 2026, PHP maintient quatre branches : 8.2, 8.3, 8.4 et 8.5. Laravel 13 est sorti le 17 mars 2026 et prend en charge PHP 8.3 à 8.5. Laravel 12 reste sous support de sécurité mais approche de la fin de ses corrections ordinaires.

---

# 3. Exigences métier à protéger

Le couple runtime/framework doit protéger les exigences suivantes :

1. **Cycle de vie des annonces :** un seul état officiel, des transitions autorisées, motivées et auditables.
2. **Permissions :** refus par défaut, moindre privilège, ownership, Mandats, séparation des responsabilités et quatre yeux.
3. **Médias :** propriété, conformité, retrait effectif, droits et conservation distincte.
4. **SEO :** aucune annonce non publiée indexable, patrimoine d’URL conservé et pages pauvres refusées.
5. **Recherche :** projection reconstruisible, jamais source de vérité, avec retrait prudent.
6. **Paiements :** fait financier unique, idempotence et absence totale d’effet direct sur la publication.
7. **Administration :** mêmes cas d’usage que le produit, aucune porte dérobée et audit du Super Administrateur.
8. **Migration Legacy :** qualification temporaire, zéro écart inexpliqué et absence de dépendance courante.
9. **Sécurité :** correctifs disponibles pendant la construction et l’exploitation initiale.
10. **Testabilité :** Domaine vérifiable sans Laravel, réseau ou capacité externe.
11. **Maintenabilité :** mises à niveau régulières sans réécriture des règles métier.
12. **Réversibilité :** aucune fonctionnalité non essentielle du framework ne doit emprisonner les concepts du Domaine.

La durée de support n’est donc pas un critère administratif : elle conditionne directement la continuité des protections métier.

---

# 4. Critères de sélection

| Critère | Pondération | Justification |
|---|---:|---|
| Support de sécurité restant | Critique | Le produit ne doit pas naître sur une branche proche de la fin de support |
| Support correctif actif | Critique | La phase de construction doit bénéficier des corrections ordinaires |
| Compatibilité officielle PHP/Laravel | Critique | Aucun couple toléré mais non garanti |
| Stabilité de la branche | Élevée | La fondation doit être publiée et éprouvable, jamais en préversion |
| Protection du Domaine | Critique | Laravel reste aux frontières et ne définit aucun invariant |
| Compatibilité de l’écosystème | Élevée | Tests, analyse, sécurité et exploitation doivent fonctionner ensemble |
| Coût de mise à niveau | Élevée | Le projet doit éviter une première montée majeure pendant sa construction initiale |
| Sécurité par défaut | Critique | Le socle doit recevoir rapidement les correctifs et durcissements |
| Performance mesurable | Moyenne | Important, mais jamais au prix des invariants ou du support |
| Compétences et exploitabilité | Élevée | Diagnostic, installation et mise à jour doivent être maîtrisables |
| Sobriété des dépendances | Élevée | Aucun package ne doit être ajouté pour compenser un choix trop ancien |
| Absence d’effet de mode | Critique | La sélection repose sur support, compatibilité et risques documentés |

## 4.1 Critères éliminatoires

Une version est éliminée si elle est en fin de support, si son support correctif se termine avant la fin prévisible de la construction initiale, si le couple n’est pas officiellement compatible ou si elle impose une mise à niveau majeure immédiate.

---

# 5. Analyse comparative des versions PHP compatibles

## 5.1 Calendrier officiel au 16 juillet 2026

| Branche PHP | Sortie initiale | Support actif jusqu’au | Sécurité jusqu’au | Compatibilité Laravel 13 | Décision |
|---|---|---|---|---|---|
| 8.2 | 8 décembre 2022 | terminé le 31 décembre 2024 | 31 décembre 2026 | Non | Refusée |
| 8.3 | 23 novembre 2023 | terminé le 31 décembre 2025 | 31 décembre 2027 | Oui | Compatible, non retenue |
| 8.4 | 21 novembre 2024 | 31 décembre 2026 | 31 décembre 2028 | Oui | Alternative prudente |
| 8.5 | 20 novembre 2025 | 31 décembre 2027 | 31 décembre 2029 | Oui | Retenue |

Source : [PHP — Supported Versions](https://www.php.net/supported-versions.php).

## 5.2 PHP 8.2

**Avantages :** maturité et large compatibilité historique.

**Limites déterminantes :** support actif terminé, sécurité limitée à fin 2026 et incompatibilité officielle avec Laravel 13.

**Conclusion :** refusée. Commencer avec PHP 8.2 imposerait Laravel 12 ou plus ancien et une montée de version anticipée.

## 5.3 PHP 8.3

**Avantages :** minimum accepté par Laravel 13, branche connue et mature.

**Limites :** support actif déjà terminé ; seuls les correctifs de sécurité restent jusqu’à fin 2027. La construction débuterait donc sans corrections ordinaires du runtime.

**Conclusion :** compatible mais non retenue pour un nouveau produit.

## 5.4 PHP 8.4

**Avantages :** support actif jusqu’à fin 2026, sécurité jusqu’à fin 2028, maturité supérieure à 8.5 et compatibilité Laravel 13.

**Limites :** fenêtre de support actif courte au regard du démarrage en juillet 2026 ; une montée vers 8.5 deviendrait rapidement souhaitable.

**Conclusion :** alternative de repli si une incompatibilité bloquante de l’environnement ou d’une dépendance approuvée est démontrée avant création.

## 5.5 PHP 8.5

**Avantages :** branche stable depuis novembre 2025, support actif jusqu’à fin 2027, sécurité jusqu’à fin 2029, compatibilité officielle Laravel 13 et durée maximale parmi les branches publiées.

**Limites :** maturité plus courte que 8.4 et risque de compatibilité avec certaines dépendances non essentielles.

**Mesure de maîtrise :** n’admettre que des dépendances officiellement compatibles ; exiger une preuve sur l’environnement cible ; replier vers 8.4 uniquement par amendement formel si un blocage critique persiste.

**Conclusion :** retenue. Le gain de support actif et de sécurité protège mieux la durée de construction et d’exploitation initiale.

## 5.6 Niveau de précision retenu

La décision porte sur **PHP 8.5.x**. À la création du projet, la dernière révision corrective stable publiée de la branche 8.5 sera utilisée après vérification des avis de sécurité, de la compatibilité Laravel et de l’environnement. Une révision corrective ancienne ne sera pas figée dans cet ADR.

---

# 6. Analyse comparative des versions Laravel compatibles

## 6.1 Calendrier officiel au 16 juillet 2026

| Branche Laravel | PHP officiellement pris en charge | Sortie | Corrections ordinaires jusqu’au | Sécurité jusqu’au | Décision |
|---|---|---|---|---|---|
| 11 | 8.2 à 8.4 | 12 mars 2024 | terminé le 3 septembre 2025 | terminée le 12 mars 2026 | Refusée |
| 12 | 8.2 à 8.5 | 24 février 2025 | 13 août 2026 | 24 février 2027 | Compatible, non retenue |
| 13 | 8.3 à 8.5 | 17 mars 2026 | troisième trimestre 2027 | 17 mars 2028 | Retenue |

Source : [Laravel 13 — Release Notes and Support Policy](https://laravel.com/docs/13.x/releases).

## 6.2 Laravel 11

La branche n’est plus couverte par les correctifs de sécurité. Elle est éliminée sans autre comparaison.

## 6.3 Laravel 12

**Avantages :** maturité supérieure, compatibilité PHP 8.5 et écosystème largement disponible.

**Limites déterminantes :** fin des corrections ordinaires le 13 août 2026, moins d’un mois après cet ADR, puis fin des correctifs de sécurité en février 2027.

**Conclusion :** non retenue. Un nouveau projet devrait préparer une montée vers Laravel 13 presque immédiatement, sans bénéfice métier.

## 6.4 Laravel 13

**Avantages :** branche stable actuelle, support correctif jusqu’au troisième trimestre 2027, sécurité jusqu’en mars 2028, compatibilité PHP 8.5 et durée de support la plus longue disponible.

**Limites :** branche récente, écosystème tiers à filtrer plus sévèrement et changements majeurs annuels à anticiper.

**Mesure de maîtrise :** socle minimal, packages limités, vérification des composants nécessaires, tests du Domaine indépendants et veille sur les correctifs.

**Conclusion :** retenue. C’est la seule branche Laravel qui offre une fenêtre de corrections ordinaires cohérente avec un projet démarrant au second semestre 2026.

## 6.5 Niveau de précision retenu

La décision porte sur **Laravel 13.x**. La dernière révision corrective stable de la branche 13 sera choisie au jour de création après revue des notes de version et avis de sécurité.

---

# 7. Compatibilité entre PHP et Laravel

## 7.1 Matrice utile à la décision

| Couple | Compatibilité officielle | Support combiné | Évaluation |
|---|---|---|---|
| PHP 8.3 + Laravel 13 | Oui | PHP sans support actif ; Laravel soutenu | Insuffisant pour un nouveau projet |
| PHP 8.4 + Laravel 13 | Oui | PHP actif jusqu’à fin 2026 ; Laravel jusqu’en 2027/2028 | Repli acceptable |
| PHP 8.5 + Laravel 12 | Oui | Laravel proche de la fin des corrections ordinaires | Mauvaise durée de vie |
| PHP 8.5 + Laravel 13 | Oui | PHP actif jusqu’à fin 2027 ; sécurité jusqu’à fin 2029 ; Laravel correctif jusqu’à Q3 2027 et sécurité jusqu’en mars 2028 | Meilleur couple disponible |

Laravel 13 exige au minimum PHP 8.3 et déclare la compatibilité jusqu’à PHP 8.5. Le couple retenu se situe donc dans la plage officielle, sans pari sur une version future.

## 7.2 Fenêtre réellement gouvernante

Pour le framework, la date de sécurité du 17 mars 2028 gouverne la durée maximale sans montée majeure. Pour le runtime, la branche 8.5 reste protégée au-delà. Le projet devra donc préparer Laravel 14 avant l’échéance de Laravel 13, sans être forcé simultanément de changer PHP.

## 7.3 Validation avant création

Le couple sera revérifié contre les pages officielles, l’environnement cible, les extensions obligatoires et les outils de qualité. Toute incompatibilité critique produit un arrêt, pas un ajustement silencieux.

---

# 8. Politique de support

- seules des branches officiellement supportées sont autorisées en production ;
- la phase de construction doit se dérouler autant que possible pendant le support correctif actif ;
- la sécurité seule est une période de transition vers la branche suivante, pas un état normal durable ;
- la date la plus proche entre runtime, framework et dépendance critique gouverne le calendrier de mise à niveau ;
- le responsable technique maintient un calendrier avec début de revue, décision, validation et déploiement de la montée ;
- les environnements utilisent la même branche mineure PHP et la même branche majeure Laravel ;
- les révisions correctives peuvent différer temporairement uniquement pendant une validation contrôlée ;
- aucun support communautaire non officiel ne remplace le support du projet source.

## 8.1 Jalons initiaux

- revue trimestrielle des calendriers ;
- première revue de Laravel 14 dès publication stable et au plus tard neuf mois avant la fin de sécurité de Laravel 13 ;
- décision de sortie de Laravel 13 au plus tard six mois avant le 17 mars 2028 ;
- revue de PHP 8.6 après sa publication stable éventuelle, sans adoption automatique ;
- sortie de PHP 8.5 planifiée avant le 31 décembre 2029, et idéalement pendant que la branche suivante dispose encore d’un support actif confortable.

---

# 9. Politique de mise à niveau

## 9.1 Révisions correctives

- évaluées en continu ;
- correctifs de sécurité prioritaires ;
- validation automatisée complète avant promotion ;
- notes de version et régressions connues examinées ;
- retour vers la révision précédente possible si aucune correction de sécurité critique ne l’interdit.

## 9.2 Évolutions mineures de PHP

Une nouvelle branche mineure PHP exige un ADR, une matrice de compatibilité, une revue des dépréciations, une validation de charge et un passage par tous les environnements.

## 9.3 Évolutions majeures de Laravel

Chaque branche annuelle exige :

1. lecture du guide officiel ;
2. inventaire des changements affectant le projet ;
3. vérification de tous les packages ;
4. tests des frontières, permissions, états, paiements, SEO et reprise ;
5. comparaison de performance et d’observabilité ;
6. stratégie de retour ;
7. ADR d’adoption ;
8. déploiement séparé des grandes évolutions métier.

## 9.4 Rythme

Les petites mises à niveau régulières sont préférées aux sauts tardifs. Une montée technique ne doit pas être mélangée à une modification importante du cycle de vie, des permissions ou des paiements.

---

# 10. Politique de rétrocompatibilité

## 10.1 Domaine

Le Domaine ne dépend pas des garanties de rétrocompatibilité de Laravel. Ses contrats et invariants évoluent selon les documents normatifs et leurs propres décisions.

## 10.2 Framework

Laravel suit le versionnement sémantique pour le framework et ses packages officiels : les changements majeurs peuvent être incompatibles ; les versions mineures et correctives ne doivent pas introduire de rupture intentionnelle. Les arguments nommés des méthodes Laravel ne sont pas couverts par ses garanties ; ils seront évités aux frontières susceptibles d’évoluer.

Source : [Laravel 13 — Versioning Scheme](https://laravel.com/docs/13.x/releases).

## 10.3 Contrats du projet

- les contrats publics entre modules sont minimaux et versionnés conceptuellement ;
- un événement publié n’est pas modifié silencieusement ;
- une évolution additive précède le retrait d’un ancien contrat ;
- les consommateurs sont recensés avant changement ;
- les projections peuvent être reconstruites ;
- aucun contrat Laravel ne devient le langage partagé du Domaine.

## 10.4 Données et URLs

La rétrocompatibilité technique ne permet jamais d’abandonner les URL historiques, états, preuves, identifiants de rapprochement ou obligations de conservation. Leur continuité relève des politiques métier.

---

# 11. Politique de sécurité

- appliquer une révision PHP 8.5 et Laravel 13 recevant les correctifs officiels ;
- surveiller les canaux officiels et l’inventaire des dépendances ;
- séparer annonce d’une vulnérabilité, qualification du risque, correction et vérification ;
- ne jamais reporter un correctif critique pour préserver une commodité de développement ;
- compenser temporairement uniquement par une mesure documentée, limitée et approuvée ;
- interdire les versions de prépublication en production ;
- vérifier signatures ou intégrité des distributions par les moyens officiels disponibles ;
- limiter les fonctionnalités Laravel activées à celles réellement nécessaires ;
- conserver le Domaine indépendant pour réduire l’impact d’une faille du framework ;
- revoir les durcissements introduits par chaque version au lieu de reproduire d’anciens comportements ;
- tester les protections des requêtes, sessions, cookies, erreurs, fichiers et tâches différées avant exposition.

## 11.1 Protection des règles métier

Une faille ou mise à niveau ne doit jamais autoriser une transition invalide, un rôle excessif, une publication par Paiement, un média interdit ou une modification SEO du Catalogue. Les autorisations et invariants restent vérifiés dans leurs propriétaires métier.

---

# 12. Politique des dépendances PHP

## 12.1 Admission

Toute dépendance PHP future doit :

- déclarer une compatibilité officielle avec PHP 8.5 et Laravel 13 lorsqu’elle touche le framework ;
- être stable, maintenue et sous licence approuvée ;
- résoudre un besoin validé ;
- avoir un propriétaire interne ;
- présenter une surface et des dépendances transitives proportionnées ;
- disposer d’une stratégie de remplacement ;
- rester extérieure au Domaine si elle porte un mécanisme de framework ou d’infrastructure.

## 12.2 Contraintes de versions

- branche majeure ou mineure autorisée explicitement ;
- révisions correctives reçues par mise à jour contrôlée ;
- aucune branche de développement, référence flottante ou source non officielle ;
- verrouillage reproductible des versions effectivement validées ;
- examen séparé de tout changement majeur transitif.

## 12.3 Sobriété

Une capacité native sûre est préférée à une dépendance additionnelle lorsque le coût de maintenance reste raisonnable. L’absence de compatibilité PHP 8.5 d’un package non essentiel entraîne son rejet, pas l’abaissement automatique du runtime.

---

# 13. Politique des extensions PHP

## 13.1 Principes

- installer uniquement les extensions nécessaires au socle, aux technologies officiellement choisies et aux besoins mesurés ;
- exiger disponibilité, maintenance et compatibilité PHP 8.5 sur tous les environnements ;
- inventorier version, finalité, propriétaire, configuration sensible et plan de mise à niveau ;
- appliquer le moindre privilège aux extensions exposant réseau, fichiers ou exécution ;
- interdire une extension non maintenue pour une capacité critique ;
- tester le comportement en absence ou défaillance d’une extension optionnelle ;
- conserver la parité entre développement, validation et production.

## 13.2 Base minimale candidate

Les extensions minimales exigées par la documentation de déploiement Laravel seront revérifiées pour Laravel 13 avant création. La documentation officielle Laravel 12 cite notamment Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer et XML ; cette liste constitue un point de contrôle, pas une instruction d’installation pour le présent sprint.

Source de référence à actualiser : [Laravel — Deployment](https://laravel.com/docs/13.x/deployment).

## 13.3 Extensions conditionnelles

Extensions liées à une base de données précise, au cache, aux images, à l’internationalisation ou à l’observabilité restent différées jusqu’aux ADR propriétaires. Aucune extension ne sera activée « au cas où ».

---

# 14. Politique des packages Laravel

- le framework de base est utilisé avec le minimum de packages officiels ou tiers ;
- aucun starter kit d’authentification ou d’interface n’est admis avant la décision du domaine Identité ;
- aucun package de modules ne définit les frontières métier à la place des documents validés ;
- aucun package d’administration ne reçoit un accès universel aux sources métier ;
- aucun package SEO ne peut modifier Catalogue, Cycle de vie ou Géographie ;
- aucun package de paiement ne transforme une confirmation technique en publication ;
- aucun package média ne décide la conformité métier ;
- les packages de débogage ou d’observation ne sont pas exposés en production sans contrôle ;
- seuls les packages compatibles Laravel 13, maintenus, licenciés et réversibles sont candidats ;
- les packages officiels ne sont pas automatiquement approuvés : leur besoin et leur portée restent à démontrer ;
- toute mise à niveau majeure suit une revue d’impact et les tests des règles protégées.

La liste des packages autorisés demeure vide jusqu’à une décision explicite ultérieure.

---

# 15. Politique des versions LTS

## 15.1 Constat

La politique officielle actuelle de Laravel accorde à toutes les versions 18 mois de corrections ordinaires et 2 ans de correctifs de sécurité. La page officielle ne distingue pas Laravel 13 comme une version LTS spéciale.

PHP décrit des branches avec deux ans de support actif puis deux ans de support pour les problèmes de sécurité critiques ; il ne faut pas transformer cette durée en label LTS non officiel.

## 15.2 Décision

- ne pas attendre une hypothétique version LTS ;
- ne pas attribuer le terme LTS à PHP 8.5 ou Laravel 13 sans déclaration officielle ;
- sélectionner selon les dates publiées, pas selon une réputation historique ;
- si une future politique LTS officielle apparaît, l’évaluer par ADR sans migration automatique ;
- budgéter les mises à niveau annuelles Laravel comme une activité normale.

## 15.3 Justification

Attendre une désignation absente ferait démarrer le produit sur une branche plus ancienne et réduirait la fenêtre de support, à l’opposé de l’objectif de sécurité.

---

# 16. Politique de fin de support

## 16.1 Interdiction

Aucune branche PHP ou Laravel arrivée en fin de correctifs de sécurité ne peut servir la production. Aucune nouvelle fonctionnalité ne sera ouverte sur une branche dont la sortie n’est pas planifiée avant cette date.

## 16.2 Seuils d’action

- **12 mois avant la fin de sécurité :** analyse et choix de la cible ;
- **9 mois avant :** prototype et matrice de compatibilité ;
- **6 mois avant :** plan approuvé, capacité réservée et travaux engagés ;
- **3 mois avant :** validation complète et fenêtre de mise en production ;
- **avant échéance :** production sortie de la branche.

## 16.3 Blocage

Si le calendrier dérive, les fonctionnalités non critiques sont gelées au profit de la mise à niveau. Une dérogation après fin de support est interdite sauf arrêt contrôlé du service ; un support commercial tiers ne peut être considéré qu’au titre d’une mesure d’urgence formellement décidée.

---

# 17. Politique de correction de sécurité

## 17.1 Classification

| Niveau | Situation | Délai cible de décision | Traitement |
|---|---|---:|---|
| Critique | exploitation active ou impact majeur probable | immédiat | qualification, mesure de protection, validation et déploiement urgent |
| Élevé | impact sérieux sur une surface utilisée | 24 heures | correction prioritaire et tests ciblés puis complets |
| Moyen | exposition limitée ou conditions difficiles | 5 jours ouvrés | planification proche et suivi |
| Faible | impact faible ou capacité non utilisée | cycle régulier | correction avec justification du calendrier |

Les délais exacts de déploiement seront fixés avec la politique d’exploitation ; aucun délai ne doit excéder la fenêtre de risque acceptée.

## 17.2 Processus

1. recevoir l’avis depuis une source officielle ou fiable ;
2. déterminer versions, extensions, packages et surfaces concernés ;
3. évaluer l’impact sur données et invariants métier ;
4. appliquer une protection temporaire si nécessaire ;
5. tester la révision corrigée ;
6. déployer selon le niveau d’urgence ;
7. vérifier l’absence d’exploitation et la restauration du service ;
8. documenter la décision et les suites.

## 17.3 Interdiction de confort

Un correctif ne peut être refusé uniquement parce qu’un package secondaire n’est pas compatible. Ce package est retiré, remplacé ou isolé, sauf ADR démontrant un risque métier supérieur.

---

# 18. Risques

| Risque | Probabilité | Impact | Maîtrise |
|---|---:|---:|---|
| Laravel 13 encore récent | Moyenne | Élevé | socle minimal, révisions correctives, tests et veille |
| Package non compatible PHP 8.5 | Moyenne | Moyen à élevé | admission tardive, rejet ou remplacement |
| Extension indisponible sur l’environnement cible | Faible à moyenne | Élevé | preuve avant création et alternative PHP 8.4 par amendement |
| Adoption par effet de nouveauté | Faible | Élevé | justification par support et comparaison datée |
| Couplage du Domaine à Laravel | Élevée sans contrôle | Critique | frontières automatisées et tests hors framework |
| Multiplication de packages Laravel | Moyenne | Élevé | liste vide par défaut et admission formelle |
| Mises à jour repoussées | Moyenne | Critique | calendrier, seuils et gel fonctionnel |
| Régression d’une révision corrective | Faible à moyenne | Élevé | validation complète et retour maîtrisé |
| Faux sentiment « LTS » | Moyenne | Élevé | dates officielles comme seule référence |
| Incompatibilité PHP 8.6 future | Sans objet aujourd’hui | Moyen | aucune adoption automatique |
| Fin de sécurité Laravel avant PHP | Certaine | Élevé | préparer Laravel 14 sans changer simultanément PHP |
| Usage de nouveautés non nécessaires | Moyenne | Moyen | n’activer que les capacités justifiées |
| Ancien comportement de sécurité reproduit | Faible à moyenne | Critique | revoir les durcissements et les tests à chaque montée |
| Dépendance globale ou helper historique | Faible | Élevé | aucun code Legacy et inventaire des symboles globaux |

---

# 19. Alternatives étudiées

## 19.1 PHP 8.4 + Laravel 13

Alternative techniquement saine et officiellement compatible. Elle offre plus de maturité mais seulement cinq mois environ de support actif restant à la date de décision. Elle demeure le repli officiel si PHP 8.5 est objectivement impossible sur l’environnement cible.

## 19.2 PHP 8.5 + Laravel 12

Couple compatible mais refusé : Laravel 12 termine ses corrections ordinaires le 13 août 2026 et sa sécurité en février 2027. Le projet serait en montée majeure presque dès sa création.

## 19.3 PHP 8.4 + Laravel 12

Couple mature mais fenêtre Laravel trop courte. Aucun avantage ne compense la dette de mise à niveau immédiate.

## 19.4 Attendre Laravel 14

Refusé. Laravel 14 n’est pas une branche stable disponible à la date de décision. Attendre retarderait sans preuve le projet et remplacerait une décision supportée par une spéculation.

## 19.5 Utiliser un autre framework

Hors décision : la Foundation et le Laravel Project Plan ont déjà retenu Laravel comme socle futur, tout en maintenant le Domaine indépendant. Réouvrir ce choix nécessiterait un ADR de remplacement motivé par un obstacle majeur.

## 19.6 Aucun framework

Refusé au titre du périmètre validé. Le coût de reconstruire les capacités génériques de sécurité, orchestration et exploitation ne protège aucun invariant supplémentaire.

---

# 20. Décision retenue

## 20.1 Runtime

**PHP 8.5.x est retenu.**

La dernière révision corrective stable disponible et validée au jour de la création sera utilisée. La branche 8.5 est retenue pour sa compatibilité officielle, son support actif jusqu’au 31 décembre 2027 et ses correctifs de sécurité jusqu’au 31 décembre 2029.

## 20.2 Framework

**Laravel 13.x est retenu.**

La dernière révision corrective stable disponible et validée au jour de la création sera utilisée. Laravel 13 est retenu parce qu’il est la branche stable disposant de la plus longue fenêtre de corrections et de sécurité compatible avec PHP 8.5.

## 20.3 Repli conditionnel

PHP 8.4.x avec Laravel 13.x est le seul repli préautorisé à l’étude, mais pas à l’usage automatique. Il exige un amendement à cet ADR démontrant un blocage critique et non contournable de PHP 8.5, son impact, sa durée et le plan de retour vers PHP 8.5.

## 20.4 Contraintes associées

- Domaine indépendant de Laravel ;
- aucun package applicatif approuvé par cet ADR ;
- aucune extension conditionnelle approuvée par cet ADR ;
- aucune version de prépublication ;
- aucune baisse vers PHP 8.3 ou Laravel 12 ;
- révision des sources officielles juste avant création ;
- nouvel ADR pour toute autre branche PHP ou majeure Laravel.

---

# 21. Conséquences

## 21.1 Conséquences positives

- durée de support maximale parmi les couples stables disponibles ;
- absence de mise à niveau majeure immédiate au démarrage ;
- possibilité de préparer Laravel 14 tout en conservant PHP 8.5 ;
- accès aux correctifs et durcissements actuels ;
- réduction du besoin de compatibilité historique ;
- fondation plus simple pour un projet sans code Legacy ;
- alignement avec ADR-1000 et le plan J0.

## 21.2 Coûts et contraintes

- contrôle sévère de compatibilité des packages et extensions ;
- compétence nécessaire sur PHP 8.5 et Laravel 13 ;
- surveillance active d’une branche Laravel récente ;
- budget annuel de mise à niveau Laravel ;
- refus possible d’outils encore limités à des branches anciennes ;
- validation de l’environnement cible obligatoire avant création.

## 21.3 Effets sur l’architecture

Aucun changement du Domain Mapping, des Aggregate Boundaries ou des documents métier. Laravel soutient l’orchestration et les adaptateurs ; PHP exécute le produit ; aucun des deux ne devient propriétaire d’un état, d’une permission, d’un média, d’une URL ou d’un paiement.

## 21.4 Effets sur la roadmap

La validation de cet ADR ferme une partie de la porte « première ligne de code ». Restent notamment à décider la base de données, l’organisation physique exacte, l’authentification, les secrets, les environnements et les outils de qualité.

---

# 22. Critères d’acceptation

L’ADR est accepté si :

- PHP 8.5.x et Laravel 13.x sont clairement retenus ;
- les dates de support proviennent des sources officielles et portent une date de vérification ;
- PHP 8.2, 8.3, 8.4 et 8.5 sont comparés ;
- Laravel 11, 12 et 13 sont comparés ;
- la compatibilité PHP/Laravel est explicitement vérifiée ;
- Laravel 12 est refusé en raison de sa fenêtre trop courte, pas par préférence ;
- le choix de PHP 8.5 est motivé par support, sécurité et compatibilité ;
- le repli PHP 8.4 est conditionnel et exige un amendement ;
- les révisions correctives restent mobiles dans leur branche après validation ;
- les politiques de support, mise à niveau, rétrocompatibilité et fin de support sont opérationnelles ;
- les dépendances, extensions et packages sont refusés par défaut jusqu’à admission ;
- aucune désignation LTS non officielle n’est utilisée ;
- les corrections de sécurité ont un processus et des niveaux de priorité ;
- le Domaine reste indépendant de Laravel ;
- aucune règle métier normative n’est modifiée ;
- aucun choix de structure, données ou infrastructure n’est ajouté ;
- les questions restant ouvertes sont visibles ;
- le document demeure un ADR sans création du projet.

---

# 23. Questions ouvertes

## Bloquantes avant création

1. L’environnement cible exécute-t-il PHP 8.5 avec toutes les extensions obligatoires ?
2. Quelle révision corrective PHP 8.5 est la dernière stable et approuvée au jour J0 ?
3. Quelle révision corrective Laravel 13 est la dernière stable et approuvée au jour J0 ?
4. La documentation officielle Laravel 13 modifie-t-elle la liste minimale d’extensions à retenir ?
5. Les outils de test, analyse, formatage, sécurité et architecture choisis sont-ils tous compatibles avec PHP 8.5 et Laravel 13 ?
6. Quelle image ou distribution de runtime sera approuvée pour chaque environnement ?
7. Qui possède la veille PHP, Laravel et dépendances ?
8. Quels délais définitifs de déploiement s’appliquent à chaque niveau de vulnérabilité ?

## À fermer par ADR ultérieur

9. Quel moteur et quelle version de base de données seront retenus ?
10. Quelle organisation physique matérialisera les domaines sans couplage Laravel ?
11. Quelle stratégie d’authentification et de sessions sera utilisée ?
12. Quelle capacité gérera les secrets et leur rotation ?
13. Quels packages Laravel, s’il y en a, seront admis à J0 ?
14. Quelles extensions conditionnelles seront nécessaires pour médias, cache et observabilité ?
15. Quel calendrier cible sera retenu pour l’évaluation de Laravel 14 ?
16. Quel niveau de compatibilité descendante le projet garantit-il à ses propres événements inter-domaines ?

---

# 24. Références vers ADR-1000

## 24.1 Décisions fermées par le présent ADR

ADR-1000, section 4, laissait ouverts les numéros exacts de PHP et Laravel. Le présent ADR ferme ces décisions au niveau des branches :

- PHP 8.5.x ;
- Laravel 13.x ;
- dernière révision corrective stable validée au jour de création.

## 24.2 Principes ADR-1000 protégés

- section 2 : monolithe modulaire, Domaine indépendant, sécurité et réversibilité ;
- section 3 : support, stabilité, intégrité, exploitabilité et coût total ;
- section 4 : politique de versions ;
- section 5 : modularité ;
- section 8 : dépendances dirigées ;
- section 10 : secrets ;
- section 14 : qualité ;
- section 16 : tests ;
- section 18 : sécurité ;
- section 19 : packages tiers ;
- section 21 : futurs ADR ;
- section 22 : roadmap technique.

## 24.3 Décisions ADR-1000 encore ouvertes

Le présent ADR ne ferme pas : base de données, cache, Recherche, Queue, stockage média, observabilité, authentification, gestion des secrets, environnements, organisation physique, formats d’événements, sauvegarde, restauration ou déploiement.

## 24.4 Références officielles

- [PHP — Supported Versions](https://www.php.net/supported-versions.php)
- [PHP — PHP 8.5 Release Announcement](https://www.php.net/releases/8_5_0.php)
- [Laravel 13 — Release Notes and Support Policy](https://laravel.com/docs/13.x/releases)
- [Laravel 13 — Upgrade Guide](https://laravel.com/docs/13.x/upgrade)
- [Laravel 13 — Deployment](https://laravel.com/docs/13.x/deployment)
- [ADR-1000 — Technical Foundation](./ADR-1000-TECHNICAL-FOUNDATION.md)
- [Laravel Project Plan](./LARAVEL-PROJECT-PLAN.md)

---

# Synthèse des versions retenues

**Runtime : PHP 8.5.x.** La dernière révision corrective stable validée au jour de création sera utilisée.

**Framework : Laravel 13.x.** La dernière révision corrective stable validée au jour de création sera utilisée.

Ce couple est retenu parce qu’il est officiellement compatible, stable, dispose de la meilleure fenêtre de support disponible et évite une montée majeure immédiate. PHP 8.4 avec Laravel 13 reste uniquement une possibilité de repli soumise à amendement si PHP 8.5 est objectivement impossible sur l’environnement cible.

# Décisions restant ouvertes

Restent à confirmer avant J0 : révisions correctives exactes, environnement PHP 8.5, extensions minimales, outils de qualité compatibles, propriétaire de veille et délais définitifs de correction. Base de données, structure physique, authentification, secrets, cache, Recherche, Queue, médias et observabilité relèvent d’ADR ultérieurs.

# Confirmation de périmètre

Ce livrable est exclusivement documentaire. Aucun code, projet Laravel, commande Composer, migration, API, fichier de configuration ou structure physique n’a été créé.

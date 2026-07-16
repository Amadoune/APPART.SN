# APPART.SN REBUILD 2026 — Laravel Project Plan

## Statut du document

- **Sprint :** 6
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition de plan soumise à validation
- **Nature :** préparation documentaire du premier projet Laravel
- **Autorité :** documents normatifs de la Foundation v1.0

## Décision directrice

Le futur projet sera créé comme un monolithe modulaire. Les treize domaines seront visibles dès J0 sous forme d’enveloppes vides, mais leur implémentation sera ouverte l’une après l’autre par des portes explicites. La présence physique future d’un module ne lui accordera aucun droit d’accéder aux données ou règles d’un autre.

Ce document décrit ce qui devra être fait lors d’un sprint ultérieur. Il ne réalise aucune de ces opérations.

---

# 1. Nom officiel du projet

## 1.1 Nom produit

Le nom officiel du produit reste **APPART.SN**.

## 1.2 Nom du programme de reconstruction

Le programme et le dépôt portent le nom **APPART.SN REBUILD**.

## 1.3 Identifiant technique futur

L’identifiant technique canonique sera **appart-sn-rebuild** lorsqu’un identifiant en minuscules est requis. Le répertoire de travail reste **APPART-REBUILD**.

## 1.4 Règles de nommage

- aucune mention « Legacy », « v2 », « new » ou année dans le nom courant du produit ;
- aucun nom historique de boutique ou de module Legacy ;
- le nom APPART.SN reste celui présenté aux utilisateurs ;
- l’identifiant de reconstruction sert uniquement pendant la construction et l’exploitation interne ;
- tout changement de nom officiel exigera une décision distincte avant la création du dépôt applicatif.

---

# 2. Arborescence physique générale future

## 2.1 Principe

L’arborescence future suivra les domaines métier avant les couches techniques globales. Elle distinguera clairement le produit, les modules, les lectures dérivées, les adaptateurs, les interfaces, les tests et la documentation.

## 2.2 Vue générale prévue

| Zone future | Responsabilité | Contenu admis | Contenu interdit |
|---|---|---|---|
| Racine du projet | Gouvernance et point d’entrée du produit | fichiers standards nécessaires, documentation et règles de qualité | règle métier isolée ou secret |
| Application Laravel | Amorçage et intégration du socle | coordination technique minimale | ownership d’un domaine |
| Modules | Treize domaines fonctionnels | Domaine, cas d’usage, contrats publics et adaptateurs propres | accès interne à un autre module |
| Interfaces | Intentions publiques, utilisateur, administration et tâches planifiées | traduction d’entrée et présentation | décision métier |
| Lectures | Recherche, SEO, administration et statistiques | projections reconstruisibles | source de vérité |
| Capacités externes | Paiement, média, notification, recherche, audit et autres adaptateurs | traduction vers des ports approuvés | règle métier propriétaire |
| Socle partagé minimal | Primitives réellement indépendantes | identifiants, temps, corrélation et résultats génériques approuvés | Annonce, Compte, Média, SEO ou Paiement |
| Tests | Preuves par niveau et domaine | scénarios métier, intégration, contrats, parcours et qualités | données personnelles réelles |
| Documentation | Normes, ADR, décisions et guides | documents validés et historiques de décision | secrets ou instructions non révisées |
| Migration Legacy temporaire | Qualification et rapprochement historiques | capacités temporaires autorisées | dépendance du produit courant |

## 2.3 Organisation interne conceptuelle d’un module

Chaque module futur distinguera, sans imposer ici de noms de dossiers définitifs :

1. le **Domaine**, indépendant de Laravel ;
2. les **cas d’usage**, responsables de l’orchestration ;
3. les **contrats publics**, seuls points d’interaction autorisés ;
4. les **adaptateurs**, qui satisfont les besoins externes ;
5. les **lectures propres**, lorsqu’elles sont nécessaires ;
6. les **tests**, organisés selon le niveau de preuve.

## 2.4 Décisions non prises

Les chemins exacts, espaces de noms, suffixes, mécanismes d’enregistrement et conventions de chargement devront être confirmés avant la première ligne de code. Le présent plan fixe les zones et directions de dépendance, pas une structure Laravel définitive.

---

# 3. Modules créés dès J0

## 3.1 Principe J0

J0 est le jour de création contrôlée du futur projet. À J0, une enveloppe identifiable sera réservée pour chacun des treize domaines afin d’empêcher la croissance d’un espace applicatif indifférencié. Ces enveloppes seront vides de fonctionnalité.

## 3.2 Enveloppes de domaines J0

1. Géographie
2. Identité et accès
3. Administration et audit
4. Professionnels
5. Catalogue immobilier
6. Médias
7. Cycle de vie des annonces
8. Modération et signalements
9. Recherche et découverte
10. Contenus et SEO
11. Contacts et leads
12. Migration Legacy
13. Monétisation et paiements

## 3.3 Capacités transverses préparées à J0

Seront également identifiées, sans logique fonctionnelle :

- orchestration applicative ;
- interfaces publique, utilisateur et administrative ;
- lectures et projections ;
- capacités externes ;
- socle partagé minimal ;
- qualité et tests ;
- observabilité et audit technique ;
- documentation des décisions.

## 3.4 Pourquoi créer toutes les enveloppes

- matérialiser immédiatement les frontières validées ;
- empêcher Catalogue ou Administration de devenir des espaces universels ;
- rendre visibles les dépendances interdites ;
- réserver à Migration Legacy une zone temporaire supprimable ;
- permettre les contrôles automatiques de structure avant les fonctionnalités ;
- éviter que les premiers développements déterminent accidentellement toute l’architecture.

La création d’une enveloppe ne signifie ni activation, ni priorité, ni autorisation d’implémenter.

---

# 4. Modules restant vides

## 4.1 À la fin de J0

Les treize modules métier restent vides de fonctionnalité. J0 établit seulement le socle, les frontières, les règles de dépendance et la capacité de test.

## 4.2 Pendant l’implémentation du premier domaine

Lorsque Géographie sera ouvert, resteront vides :

- Identité et accès ;
- Administration et audit ;
- Professionnels ;
- Catalogue immobilier ;
- Médias ;
- Cycle de vie des annonces ;
- Modération et signalements ;
- Recherche et découverte ;
- Contenus et SEO ;
- Contacts et leads ;
- Migration Legacy ;
- Monétisation et paiements.

## 4.3 Pendant l’implémentation du deuxième domaine

Lorsque Identité et accès sera ouvert, Géographie sera déjà accepté. Tous les autres modules restent vides, notamment Catalogue et Administration, afin d’éviter l’apparition prématurée de parcours transverses.

## 4.4 Pendant l’implémentation du troisième domaine

Lorsque Administration et audit sera ouvert, seuls Géographie et Identité pourront être utilisés par références approuvées. Les dix autres modules resteront vides.

## 4.5 Modules différés par nature

- **Recherche et découverte** reste vide jusqu’à l’existence de sources métier publiables.
- **Contenus et SEO** reste vide jusqu’à stabilisation de Géographie, Catalogue et Cycle de vie.
- **Contacts et leads** reste vide jusqu’à la preuve d’une annonce Publiée et revalidable.
- **Migration Legacy** reste vide jusqu’à stabilisation des modèles cibles nécessaires au premier lot de reprise.
- **Monétisation et paiements** reste vide tant que son périmètre initial n’est pas validé par un ADR dédié.

---

# 5. Ordre exact de création

## 5.1 Séquence de création du futur projet

1. **Valider les préconditions documentaires.** Fermer les questions bloquantes sur versions, nommage, structure, secrets et environnements.
2. **Créer le dépôt applicatif.** Établir identité, branche principale, règles de contribution et protections.
3. **Créer le projet Laravel minimal.** Utiliser uniquement les versions approuvées dans les futurs ADR.
4. **Établir le socle de qualité.** Formatage, analyse, tests, contrôle des dépendances et détection de secrets.
5. **Établir les environnements.** Configuration validée, secrets séparés et valeurs sûres.
6. **Établir l’observabilité minimale.** Corrélation, journal technique et erreurs sans données sensibles.
7. **Matérialiser les zones générales.** Modules, interfaces, lectures, adaptateurs, partagé minimal, tests et documentation.
8. **Créer les treize enveloppes de domaines.** Toutes vides et protégées par les mêmes règles.
9. **Activer les contrôles de frontières.** Interdire cycles, accès internes et dépendances du Domaine envers Laravel.
10. **Créer une preuve structurelle minimale.** Démontrer qu’un domaine vide est testable indépendamment du socle.
11. **Effectuer la revue J0.** Vérifier sécurité, structure, reproductibilité et absence de fonctionnalité implicite.
12. **Ouvrir Géographie.** Première autorisation d’implémentation métier, après acceptation J0.

## 5.2 Règle d’arrêt

Une étape non acceptée bloque toutes les suivantes. Aucun contournement temporaire n’est admis sur versions, secrets, frontières, tests ou protections du dépôt.

---

# 6. Modules interdits avant leurs prérequis

| Module à ouvrir | Prérequis obligatoires | Modules ou capacités interdits tant que le prérequis manque |
|---|---|---|
| Géographie | socle J0 accepté, référentiel et arbitrages géographiques validés | tout autre domaine métier |
| Identité et accès | Géographie acceptée au niveau requis, stratégie d’authentification et secrets décidée | Professionnels, Catalogue et administration fonctionnelle |
| Administration et audit | Identité acceptée, actions sensibles et quatre yeux définis | toute interface administrative métier |
| Professionnels | Identité, Mandats et audit disponibles | portefeuille public et monétisation professionnelle |
| Catalogue immobilier | Identité, Géographie, permissions de propriété et audit disponibles | publication, Recherche, SEO et Contacts |
| Médias | propriétaires Catalogue/Professionnels identifiables, politique média testable | exposition publique de médias et publication complète |
| Cycle de vie des annonces | Catalogue minimal, acteur autorisé, audit et graphe des dix états prêts | toute annonce Publiée |
| Modération et signalements | Catalogue, Médias, Cycle de vie et séparation des rôles disponibles | publication après contrôle et traitement de signalements réels |
| Recherche et découverte | Catalogue, Cycle, Géographie, Médias et Professionnels stables ; retrait prudent défini | moteur spécialisé et index public |
| Contenus et SEO | Géographie, Catalogue, Cycle et patrimoine d’URL prêts ; règles d’indexation testables | sitemap public et pages indexables dynamiques |
| Contacts et leads | état Publiée revalidable, Identité et finalités de conservation décidées | collecte de contacts réels |
| Migration Legacy | domaine cible stable, règles de qualification et retour validées | import de tout lot historique |
| Monétisation et paiements | ADR de périmètre, Finance, sécurité et rapprochement approuvés | toute offre payante ou collecte financière |

## 6.1 Interdictions absolues

- Recherche avant Cycle de vie ne doit pas exister comme source d’état.
- SEO avant Patrimoine d’URL ne doit pas générer d’URL historiques improvisées.
- Contacts avant publication revalidable ne doit collecter aucune donnée personnelle.
- Paiement avant séparation Finance/Commercial ne doit pas être ouvert.
- Migration Legacy avant stabilité des cibles ne doit pas dicter leur forme.
- Administration avant permissions et audit ne doit pas devenir une porte dérobée.

---

# 7. Composants Laravel utilisés immédiatement

## 7.1 Composants du socle autorisés à J0

Sous réserve des ADR de versions, les capacités natives suivantes seront utilisées immédiatement et seulement dans leur responsabilité technique :

- amorçage standard du projet ;
- conteneur d’injection de dépendances ;
- gestion des environnements et de la configuration non sensible ;
- journalisation technique et corrélation minimale ;
- gestion centralisée des erreurs ;
- validation des entrées aux frontières ;
- routage minimal nécessaire aux vérifications de fonctionnement, sans parcours métier ;
- middleware pour corrélation, sécurité générique et contexte de requête ;
- commandes de console nécessaires à la vérification et à l’exploitation future ;
- ordonnanceur seulement pour les vérifications techniques indispensables, sans tâche métier J0 ;
- infrastructure native de tests ;
- événements internes strictement techniques lorsque nécessaires à l’amorçage.

## 7.2 Règles d’usage immédiat

- aucun composant Laravel n’entre dans le Domaine ;
- aucun composant natif ne définit une permission ou un état métier ;
- aucune fonctionnalité générée par défaut n’est conservée sans besoin validé ;
- les mécanismes activés à J0 doivent être couverts par une vérification minimale ;
- toute capacité non indispensable reste désactivée ou inutilisée.

---

# 8. Composants volontairement différés

## 8.1 Jusqu’au domaine propriétaire

- authentification fonctionnelle, différée jusqu’à Identité et accès ;
- persistance métier, différée jusqu’au premier Aggregate validé ;
- notifications, différées jusqu’au premier événement métier qui les justifie ;
- traitement différé, différé jusqu’à un effet non atomique validé ;
- cache applicatif, différé jusqu’à un problème de lecture mesuré ;
- stockage média, différé jusqu’au module Médias et à son ADR ;
- recherche spécialisée, différée jusqu’aux critères et volumes mesurés ;
- courrier, messagerie et canaux de contact, différés jusqu’aux finalités approuvées ;
- génération de sitemap, différée jusqu’au module Contenus et SEO ;
- paiement, différé jusqu’à Monétisation et à son ADR spécifique ;
- tâches Legacy, différées jusqu’à stabilité du premier domaine cible.

## 8.2 Capacités volontairement absentes de J0

- kit d’interface ou d’authentification préfabriqué ;
- diffusion temps réel ;
- interface publique complète ;
- interface administrative métier ;
- modèle de recherche ;
- cache métier ;
- file métier ;
- galerie ou transformation média ;
- intégration financière ;
- extension tierce non approuvée.

## 8.3 Principe de déclenchement

Une capacité différée n’est ouverte que lorsqu’un besoin métier, un propriétaire, un risque, un test d’acceptation, un comportement en panne et un ADR éventuel sont établis.

---

# 9. Premier domaine implémenté : Géographie

## 9.1 Décision

**Géographie sera le premier domaine métier implémenté.**

## 9.2 Justification

- il est classé P0 préalable dans l’Architecture Blueprint ;
- Catalogue ne peut référencer une ville ou un quartier fiable sans lui ;
- Recherche et SEO dépendent d’une hiérarchie validée ;
- il possède des invariants riches mais une surface d’acteurs limitée, adaptée à la preuve des frontières ;
- il permet de tester Root, Entités, Value Objects, événements et références sans ouvrir prématurément les comptes ou annonces ;
- il empêche la reprise des deux référentiels Legacy comme vérité.

## 9.3 Périmètre initial

Lieu géographique, identité officielle, type, nom, rattachement, alias, validation, renommage, fusion et désactivation. Aucune page SEO, annonce, recherche ou donnée Legacy active n’entre dans ce premier périmètre.

## 9.4 Critère de clôture

Le domaine est accepté lorsque ses invariants, permissions administratives minimales, audit, erreurs, événements et tests sont prouvés sans dépendance à un autre domaine métier.

---

# 10. Deuxième domaine implémenté : Identité et accès

## 10.1 Décision

**Identité et accès sera le deuxième domaine.**

## 10.2 Justification

- toutes les actions ultérieures exigent un acteur identifiable ;
- ownership, rôles, consentements et Mandats doivent précéder Professionnels et Catalogue ;
- les neuf acteurs de la Permissions Matrix nécessitent une base cohérente ;
- la stratégie d’authentification peut être décidée sans déplacer les règles dans Laravel ;
- l’absence de ce domaine favoriserait des identités provisoires difficiles à supprimer.

## 10.3 Périmètre initial

Compte, identité vérifiée, profil personnel minimal, consentement, attribution de rôle et récupération d’accès. Mandat de représentation est préparé mais son activation complète attend Professionnels.

## 10.4 Critère de clôture

Le domaine est accepté lorsque refus par défaut, moindre privilège, récupération, suspension, audit de rôle, protection des secrets et tests des acteurs sont démontrés.

---

# 11. Troisième domaine implémenté : Administration et audit

## 11.1 Décision

**Administration et audit sera le troisième domaine.**

## 11.2 Justification

- les domaines suivants introduisent des décisions sensibles ;
- quatre yeux, traçabilité et paramètres gouvernés doivent précéder publication, modération et paiement ;
- l’administration doit utiliser les mêmes cas d’usage, jamais un accès direct ;
- bâtir ce domaine tôt empêche la création ultérieure d’une zone interne omnipotente ;
- les preuves des changements de Géographie et Identité deviennent immédiatement vérifiables.

## 11.3 Périmètre initial

Demande d’approbation, séparation auteur-approbateur, trace d’audit métier minimale, consultation autorisée et Paramètre métier gouverné. Aucune administration Catalogue, SEO ou Finance n’est créée avant son domaine propriétaire.

## 11.4 Critère de clôture

Le domaine est accepté lorsque le Super Administrateur reste soumis aux règles, les actions sensibles sont attribuables, les secrets sont exclus des traces et aucune mutation directe inter-module n’est possible.

---

# 12. Ordre complet de l’implémentation

## 12.1 Séquence officielle

| Rang | Domaine ou étape | Résultat exigé avant le rang suivant |
|---:|---|---|
| 0 | Socle J0 | projet minimal, frontières, qualité, sécurité, tests et observabilité acceptés |
| 1 | Géographie | référentiel officiel et événements de lieu fiables |
| 2 | Identité et accès | acteurs, rôles, consentements et accès cohérents |
| 3 | Administration et audit | quatre yeux, audit et paramètres gouvernés disponibles |
| 4 | Professionnels | organisations, vérifications et Mandats opérationnels |
| 5 | Catalogue immobilier | Annonce et contenu complets sans état public incorporé |
| 6 | Médias | Galerie, ownership, conformité et retrait maîtrisés |
| 7 | Cycle de vie des annonces | dix états et transitions protégés, aucune publication implicite |
| 8 | Modération et signalements | dossiers, preuves, décisions et recours séparés du Cycle |
| 9 | Recherche et découverte | projection reconstruisible, retrait prudent et fraîcheur bornée |
| 10 | Contenus et SEO | indexabilité, patrimoine d’URL, pages et sitemap gouvernés |
| 11 | Contacts et leads | contact uniquement pour annonce publiée, finalité et conservation validées |
| 12 | Migration Legacy | reprise temporaire après stabilité des domaines cibles |
| 13 | Monétisation et paiements | capacité conditionnelle, seulement après ADR et validation du périmètre |

## 12.2 Nuance sur Cycle de vie et Modération

Le Cycle de vie est construit avant Modération pour établir l’unique graphe d’états. Les transitions nécessitant une décision humaine restent fermées jusqu’à Modération. Modération produit ensuite une décision ; Cycle de vie demeure seul propriétaire de la transition.

## 12.3 Nuance sur Recherche et SEO

Recherche précède SEO pour prouver les projections et budgets de fraîcheur sur des sources publiables. SEO reste cependant propriétaire de l’indexabilité, des URL et des pages. Une facette de Recherche ne devient jamais automatiquement une page SEO.

## 12.4 Nuance sur Migration Legacy

Le module temporaire peut être préparé par domaine cible après stabilisation de celui-ci, mais aucune reprise globale ne commence avant l’acceptation des domaines P0 nécessaires et du plan de retour. Les données historiques ne modifient jamais l’ordre de construction.

## 12.5 Parallélisme

Le parallélisme entre domaines est interdit jusqu’à clôture des trois premiers domaines. Ensuite, il ne peut être autorisé que pour des modules sans dépendance mutuelle, avec propriétaires distincts et contrats déjà acceptés. L’ordre des portes reste inchangé même si des travaux documentaires sont menés en parallèle.

---

# 13. Critères permettant d’ouvrir la première ligne de code

La première ligne de code ne peut être ouverte que lorsque tous les critères suivants sont validés :

## 13.1 Décisions fermées

- nom officiel, identifiant et emplacement du projet confirmés ;
- version exacte de PHP approuvée ;
- version exacte de Laravel approuvée ;
- calendrier de support compatible avec la mise en production ;
- stratégie initiale de base de données décidée ;
- organisation physique des modules validée par ADR ;
- convention de nommage concrète validée ;
- stratégie d’authentification et gestion des secrets décidées ;
- environnements, sauvegarde initiale et responsabilités définis ;
- outils et seuils de qualité approuvés.

## 13.2 Gouvernance prête

- responsable technique et propriétaires des trois premiers domaines nommés ;
- règles de branche, revue, validation et urgence approuvées ;
- modèle d’ADR et registre de dette disponibles ;
- critères de terminaison par domaine acceptés ;
- processus de décision en cas de conflit entre domaines défini.

## 13.3 Sécurité et conformité prêtes

- classification des données réalisée ;
- modèle de menace initial revu ;
- aucune donnée Legacy ou de production nécessaire au démarrage ;
- politique de secrets, accès et rotation opérationnelle ;
- licences et conditions d’usage du socle approuvées ;
- stratégie de signalement et correction des vulnérabilités définie.

## 13.4 Preuve documentaire

- les dix documents normatifs sont accessibles dans leur version validée ;
- aucune contradiction bloquante non arbitrée ;
- traçabilité entre le plan J0 et ADR-1000 établie ;
- décision explicite « Go J0 » enregistrée par les responsables autorisés.

Si un seul critère manque, la création du projet reste interdite.

---

# 14. Contrôles avant le premier commit

## 14.1 Périmètre et propreté

- dépôt créé au bon emplacement et avec le bon nom ;
- uniquement le socle minimal approuvé ;
- aucun exemple, fonctionnalité, compte, page ou donnée de démonstration inutile ;
- aucun fichier temporaire, sauvegarde locale ou artefact d’éditeur ;
- aucune copie du Legacy ;
- historique initial lisible et attribuable.

## 14.2 Sécurité

- absence de secrets, jetons, mots de passe, clés ou coordonnées réelles ;
- valeurs locales non sensibles et valeurs de production absentes ;
- permissions de fichiers et accès au dépôt revus ;
- dépendances officielles vérifiées et inventoriées ;
- avis de sécurité critiques absents ou décision de blocage appliquée ;
- aucune sortie d’erreur exposant des détails sensibles.

## 14.3 Architecture

- treize enveloppes présentes et vides de fonctionnalité ;
- Domaine indépendant de Laravel ;
- aucune dépendance circulaire ;
- aucun accès interne inter-module ;
- partagé minimal sans concept métier ;
- Migration Legacy isolée et supprimable ;
- aucune interface administrative privilégiée.

## 14.4 Qualité

- amorçage reproductible à partir des instructions validées ;
- contrôles de formatage, analyse et tests exécutables ;
- contrôle des frontières activé ;
- détection de secrets activée ;
- première vérification du socle réussie ;
- conventions de nommage appliquées ;
- documentation J0 et décision de version présentes.

## 14.5 Exploitation

- journalisation minimale sans donnée sensible ;
- corrélation d’une vérification de fonctionnement ;
- comportement explicite en configuration manquante ;
- environnements séparés ;
- propriétaire de chaque alerte initiale identifié ;
- procédure de retour du premier changement connue.

## 14.6 Revue humaine

Le premier commit exige une double revue : une revue technique des frontières et une revue sécurité/configuration. Aucun auteur ne s’auto-approuve.

---

# 15. Risques à éviter dès la création du dépôt

| Risque | Effet précoce | Prévention J0 | Signal d’alerte |
|---|---|---|---|
| Recréer un monolithe procédural | règles dispersées dans l’application générale | enveloppes de domaines et contrôles de dépendance | premier cas d’usage hors module |
| Laisser Laravel définir le Domaine | couplage des invariants au socle | Domaine indépendant et testable seul | concept métier dépendant du framework |
| Créer un Core universel | ownership dilué | partagé minimal soumis à revue | Annonce ou Compte dans le partagé |
| Générer trop de fonctionnalités | surface inutile et vulnérable | socle minimal, suppression des exemples | interface ou authentification non demandée |
| Choisir des versions par défaut | obsolescence avant lancement | ADR de versions avant J0 | version non reliée au support |
| Ajouter des packages prématurés | dette, surface d’attaque et concepts imposés | zéro ajout sans admission formelle | dépendance sans propriétaire |
| Commencer par Catalogue | géographie et identité provisoires | Géographie puis Identité obligatoires | texte libre pour lieux ou annonceurs |
| Administration omnipotente | contournement des cas d’usage | Administration troisième, après Identité et audit | accès direct aux sources métier |
| Recherche comme vérité | états incohérents | ouverture après Cycle et sources stables | publication décidée depuis une projection |
| SEO couplé au Catalogue | règles métier imposées par visibilité | module séparé et événements | SEO modifie une Annonce |
| Paiement lié à publication | contournement de Modération | Monétisation dernière et conditionnelle | statut payé déclenche Publiée |
| Legacy copié dans le projet | contamination de la conception | zone temporaire vide et isolée | nom ou statut Legacy dans un domaine courant |
| Secrets dans le dépôt | compromission immédiate | gestion dédiée et détection avant commit | valeur sensible dans un fichier suivi |
| Données réelles dans les tests | violation de confidentialité | données synthétiques | coordonnées ou médias de production |
| Absence d’observabilité | erreurs impossibles à attribuer | corrélation et journaux J0 | échec sans identifiant de suivi |
| Dette « temporaire » non tracée | exceptions permanentes | registre, propriétaire et échéance | contournement sans date de retrait |
| Dépôt sans protections | changements non revus | branche principale protégée | commit direct non approuvé |

---

# 16. Critères d’acceptation

Le Laravel Project Plan est acceptable si :

- le nom produit, le nom du programme, l’identifiant technique et le répertoire sont distingués ;
- l’arborescence physique générale est suffisamment précise sans être créée ni figée au niveau des fichiers ;
- les treize enveloppes J0 sont listées ;
- leur état vide initial et leurs conditions d’ouverture sont explicites ;
- l’ordre exact de création du futur projet comporte une porte d’arrêt à chaque étape ;
- les dépendances bloquantes entre modules sont documentées ;
- les composants Laravel immédiats sont limités au socle nécessaire ;
- les capacités fonctionnelles et externes prématurées sont différées ;
- Géographie, Identité et accès, puis Administration et audit sont confirmés comme trois premiers domaines ;
- l’ordre complet des treize domaines est justifié ;
- les transitions de publication restent fermées avant Modération ;
- Recherche, SEO, Contacts, Migration Legacy et Monétisation ne peuvent être ouverts prématurément ;
- les critères « Go J0 » sont vérifiables et unanimement requis ;
- le premier commit exige contrôles de sécurité, architecture, qualité et exploitation ;
- les risques précoces possèdent prévention et signal d’alerte ;
- aucune décision métier normative n’est modifiée ;
- aucune décision technique encore ouverte dans ADR-1000 n’est supposée résolue ;
- le document demeure un plan sans création du projet.

---

# 17. Questions ouvertes

## Bloquantes avant la première ligne de code

1. Les versions exactes de PHP et Laravel retenues satisfont-elles l’horizon de support ?
2. Quel moteur de base de données est retenu pour le socle initial ?
3. Quelle organisation exacte de dossiers et d’espaces de noms matérialise les modules ?
4. Quelle langue principale et quelles conventions de casse et suffixes s’appliquent au futur code ?
5. Quelle stratégie d’authentification est retenue sans coupler le Domaine à Laravel ?
6. Quelle capacité gère les secrets, leur rotation et les accès d’urgence ?
7. Quels environnements sont créés dès J0 ?
8. Quels outils et seuils de formatage, analyse, architecture, sécurité et tests bloquent un commit ?
9. Où sera hébergé le dépôt et quelles règles de protection seront appliquées ?
10. Qui détient la décision « Go J0 » et qui réalise les deux revues du premier commit ?

## À fermer avant le domaine concerné

11. Quelle hiérarchie géographique officielle ouvre le domaine Géographie ?
12. Quels rôles et mécanismes de récupération ouvrent Identité et accès ?
13. Quelles actions imposent quatre yeux dès Administration et audit ?
14. Le Mandat devient-il pleinement actif avec Professionnels ou dans une étape dédiée ?
15. Quels changements d’Annonce sont substantiels et renvoient vers Modération ?
16. Quelle limite de Galerie et quel minimum média s’appliquent par catégorie ?
17. Quel budget de fraîcheur bloque l’ouverture de Recherche et de SEO ?
18. Quels canaux et durées de conservation ouvrent Contacts et leads ?
19. Quels domaines cibles doivent être complets avant le premier lot Legacy ?
20. Monétisation et paiements appartient-il au périmètre de la première mise en production ?

## Organisation et gouvernance

21. Quel niveau de parallélisme peut être autorisé après les trois premiers domaines ?
22. Qui arbitre une demande d’exception de dépendance inter-module ?
23. Quelle définition de « domaine terminé » inclut exploitation, sécurité et documentation ?
24. Quel mécanisme garantit la suppression effective de Migration Legacy après clôture ?

---

# Résumé de l’ordre d’implémentation

Après le socle J0, l’ordre officiel est :

1. Géographie ;
2. Identité et accès ;
3. Administration et audit ;
4. Professionnels ;
5. Catalogue immobilier ;
6. Médias ;
7. Cycle de vie des annonces ;
8. Modération et signalements ;
9. Recherche et découverte ;
10. Contenus et SEO ;
11. Contacts et leads ;
12. Migration Legacy ;
13. Monétisation et paiements, sous condition d’un ADR et d’une validation de périmètre.

# Résumé des modules J0

Les treize enveloppes de domaines seront créées à J0 pour rendre les frontières visibles. Elles resteront toutes vides de fonctionnalité. Géographie sera la première et seule enveloppe ouverte après acceptation complète du socle J0.

# Résumé des risques

Les risques prioritaires sont le monolithe procédural, le couplage du Domaine à Laravel, le Core universel, les versions ou packages choisis par défaut, l’administration omnipotente, Recherche utilisée comme vérité, le couplage paiement-publication, la contamination Legacy, les secrets ou données réelles dans le dépôt et l’absence de protections ou d’observabilité dès le premier commit.

# Confirmation de périmètre

Ce livrable est exclusivement préparatoire et documentaire. Aucun code, projet Laravel, commande Composer, contrôleur, modèle, migration, API, package, fichier de configuration ou élément d’implémentation n’a été créé. Aucun document normatif existant n’a été modifié.

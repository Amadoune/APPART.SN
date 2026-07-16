# APPART.SN REBUILD 2026 — Architecture Blueprint

## Statut du document

- **Sprint :** 4 — Document 1
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition d'architecture soumise à validation
- **Nature :** architecture logicielle conceptuelle
- **Hors périmètre :** réalisation, structure physique définitive, contrats d'interface détaillés et choix de versions ou de fournisseurs

## Décision directrice proposée

APPART.SN REBUILD doit adopter un **monolithe modulaire orienté domaines fonctionnels, utilisant des principes de ports et adaptateurs de manière pragmatique**.

Cette décision signifie :

- un seul produit déployable au départ, afin de limiter le coût opérationnel ;
- des frontières métier explicites entre domaines ;
- un domaine indépendant du socle Laravel envisagé ;
- des cas d'usage coordonnés dans une couche Application ;
- des interfaces publiques et administratives qui déclenchent les mêmes actions métier ;
- des dépendances externes placées derrière des ports définis par les besoins du produit ;
- des modèles de lecture séparés pour la recherche, le SEO, l'administration et les statistiques ;
- une zone de migration temporaire qui ne devient jamais une dépendance permanente du produit.

Cette approche combine une discipline forte sur les règles critiques avec une modularité proportionnée. Elle évite à la fois le monolithe procédural Legacy et une distribution prématurée en services indépendants.

---

# 1. Objet et périmètre du Blueprint

## 1.1 Objet

Ce Blueprint traduit les politiques métier validées en contraintes d'architecture logicielle. Il définit les domaines, frontières, couches logiques, directions de dépendance, modes d'interaction, garanties transverses, responsabilités de test et risques.

Il prépare une future réalisation avec Laravel sans créer le projet et sans rendre le domaine dépendant de Laravel.

## 1.2 Périmètre couvert

- architecture logique du produit public et de l'administration ;
- domaines fonctionnels ;
- cycle de vie des annonces ;
- médias ;
- permissions ;
- SEO ;
- recherche et lectures ;
- paiements conditionnels ;
- migration Legacy ;
- sécurité, performance, observabilité, audit et tests.

## 1.3 Hors périmètre

- version de PHP ou Laravel ;
- moteur de persistance ;
- moteur de recherche ;
- stratégie de cache détaillée ;
- traitement asynchrone concret ;
- support physique des médias ;
- infrastructure et déploiement ;
- contrats détaillés d'échange ;
- noms définitifs de classes ou fichiers ;
- structure physique définitive des modules.

## 1.4 Autorité normative

Ce document ne modifie aucune règle métier. En cas d'écart, les documents normatifs cités à la section 2 prévalent. Une contradiction découverte doit produire une question ou une décision d'architecture ultérieure, jamais une réinterprétation silencieuse.

---

# 2. Sources normatives

| Document | Autorité principale sur l'architecture |
|---|---|
| `MASTER-BLUEPRINT.md` | Vision, domaines, modules, parcours, administration et roadmap |
| `LISTING-LIFECYCLE.md` | Dix états, transitions, acteurs, effets et cas exceptionnels des annonces |
| `MEDIA-POLICY.md` | Propriété, qualité, modération, cycle de vie et usages des médias |
| `PERMISSIONS-MATRIX.md` | Actions métier, acteurs, séparation des responsabilités et quatre yeux |
| `SEO-POLICY.md` | Éligibilité SEO, URL, pages, redirections, sitemaps et migration SEO |
| `MIGRATION-RULES.md` | Qualification, décisions par domaine, rapprochement, validation et retour |

## 2.1 Règle de lecture

- une règle métier devient un invariant du domaine ou une politique explicitement appliquée ;
- une permission devient une autorisation portant sur une action métier, jamais sur un écran ;
- un effet SEO devient une réaction à un état métier, pas une transition métier ;
- une lecture optimisée ne devient jamais une source de vérité ;
- une exigence de migration reste confinée à la zone Migration ;
- une question ouverte reste ouverte jusqu'à décision formelle.

## 2.2 Traçabilité normative

Chaque décision d'architecture future devra indiquer la règle normative qu'elle protège, les alternatives examinées et les conséquences. Les décisions incompatibles avec les documents métier sont interdites tant que ces documents ne sont pas officiellement révisés.

---

# 3. Principes d'architecture

## 3.1 Séparation des responsabilités

L'architecture distingue :

- **Domaine** : règles, états, invariants et décisions métier ;
- **Application** : orchestration des cas d'usage et transactions métier ;
- **Interface** : réception d'une intention et présentation d'un résultat ;
- **Infrastructure** : interaction avec les capacités externes ;
- **Lecture et recherche** : vues optimisées sans pouvoir décisionnel ;
- **Administration** : interface interne utilisant les mêmes cas d'usage ;
- **Migration** : traduction temporaire et contrôlée du Legacy.

## 3.2 Dépendances dirigées vers le métier

Les couches externes peuvent dépendre des concepts métier. Le métier ne dépend ni de l'interface, ni de l'administration, ni de Laravel, ni d'un moteur de persistance, de recherche, de cache ou de messages.

Les contrats nécessaires aux capacités externes sont exprimés du point de vue du besoin métier ou applicatif, puis satisfaits par l'extérieur.

## 3.3 Refus du monolithe procédural Legacy

Sont explicitement refusés :

- un fichier central cumulant configuration, règles, accès aux données et rendu ;
- des pages qui portent directement les règles de publication ;
- des décisions métier dispersées dans plusieurs contrôleurs ;
- des requêtes directes comme définition d'une règle ;
- des dépendances globales implicites ;
- des extensions exécutables installables depuis l'administration ;
- des états textuels librement manipulables ;
- des comportements différents entre public et administration.

## 3.4 Modularité pragmatique

Les modules correspondent à des responsabilités métier stables et non à chaque entité ou écran. La discipline des frontières est forte ; la distribution physique et opérationnelle reste simple au départ.

## 3.5 Responsabilités explicites

Chaque règle possède un domaine propriétaire. Chaque donnée a une source de vérité. Chaque interaction inter-domaines a un sens et une direction documentés.

## 3.6 Sécurité par défaut

- refus par défaut ;
- validation de toute intention ;
- autorisation par action et ressource ;
- finalité explicite ;
- surface publique minimale ;
- aucune confiance dans les données Legacy ou externes ;
- audit des actions sensibles.

## 3.7 Traçabilité

Transitions, décisions de modération, autorisations sensibles, paiements, changements éditoriaux, redirections, fusions et actions automatiques produisent des événements d'audit compréhensibles.

## 3.8 Testabilité

Les règles métier critiques doivent pouvoir être vérifiées sans interface, sans réseau et sans dépendance externe. Les cas d'usage sont vérifiables avec des ports contrôlés. Les adaptateurs sont vérifiés séparément.

## 3.9 Évolution progressive

Le produit commence comme monolithe modulaire. Une séparation opérationnelle future ne sera envisagée que si les frontières métier sont stables et qu'un besoin mesuré la justifie.

## 3.10 Indépendance envers Laravel

Le domaine n'utilise aucun concept Laravel. Laravel pourra soutenir l'interface, l'orchestration et les adaptateurs, sans définir les règles du catalogue, du cycle de vie, des permissions ou de la migration.

## 3.11 Cohérence avant disponibilité immédiate

Les états critiques — publication, permission, paiement, suspension et retrait — privilégient la cohérence. Les vues de recherche et statistiques peuvent accepter un léger décalage explicitement borné, mais ne décident jamais d'une action critique.

---

# 4. Style architectural cible

## 4.1 Options étudiées

| Option | Avantages | Limites | Coût et risque |
|---|---|---|---|
| Laravel traditionnel organisé par couches | Convention connue, démarrage simple, faible coût initial | Frontières métier souvent faibles ; règles susceptibles de glisser vers contrôleurs et modèles de persistance | Faible au départ, élevé si le produit devient un nouveau monolithe couplé |
| Architecture modulaire | Responsabilités explicites, équipes et tests mieux isolés, évolution progressive | Nécessite une discipline de dépendances et une gouvernance des contrats | Modéré ; risque de modules de façade sans réelle autonomie |
| Monolithe modulaire | Simplicité opérationnelle avec frontières fortes, transactions locales, extraction future possible | Frontières non garanties par le déploiement ; tentation d'accès directs entre modules | Modéré et proportionné au projet |
| Architecture hexagonale / ports et adaptateurs | Domaine protégé, dépendances externes remplaçables, forte testabilité | Peut devenir cérémonielle si appliquée uniformément à chaque détail | Modéré à élevé ; risque de surarchitecture |
| Architecture orientée domaines fonctionnels | Alignement sur le métier et les propriétaires, langage partagé, règles localisées | Exige de trancher les frontières et d'accepter certains échanges inter-domaines explicites | Modéré ; risque de découpage trop fin ou trop théorique |

## 4.2 Option écartée : Laravel traditionnel seul

Cette option ne fournit pas une garantie suffisante contre les défauts centraux du Legacy : logique dispersée, couplage des interfaces et des règles, divergences entre public et administration et dépendance au mode de persistance.

Elle peut fournir des conventions externes, mais ne doit pas constituer le principe architectural directeur.

## 4.3 Recommandation

Adopter :

1. **un monolithe modulaire** pour la simplicité opérationnelle ;
2. **des modules orientés domaines fonctionnels** pour l'alignement métier ;
3. **des principes hexagonaux sélectifs** sur les frontières externes et les domaines critiques ;
4. **une séparation lectures/écritures pragmatique** pour recherche, SEO, administration et statistiques ;
5. **une zone Migration temporaire** indépendante du produit courant.

## 4.4 Application pragmatique

La discipline hexagonale est obligatoire pour : paiements, médias, notifications, recherche, identité externe, géocodage éventuel, audit et migration.

Elle n'impose pas une abstraction pour toute opération interne. Une abstraction n'est créée que lorsqu'elle protège une frontière réelle, une règle, une dépendance externe ou un besoin de test.

## 4.5 Ce que la recommandation ne décide pas

- aucune structure physique définitive ;
- aucune séparation en services distribués ;
- aucun choix de moteur ;
- aucun protocole d'échange ;
- aucune bibliothèque ;
- aucune convention de nommage concrète.

---

# 5. Domaines fonctionnels à représenter

## 5.1 Tableau de synthèse

| Domaine | Priorité | Statut |
|---|---:|---|
| Identité et accès | P0 | Obligatoire |
| Professionnels | P1 | Obligatoire |
| Catalogue immobilier | P0 | Obligatoire |
| Cycle de vie des annonces | P0 | Obligatoire |
| Médias | P0 | Obligatoire |
| Géographie | P0 | Obligatoire |
| Recherche et découverte | P0 | Obligatoire |
| Contacts et leads | P1 | Obligatoire, portée à confirmer |
| Modération et signalements | P0 | Obligatoire |
| Contenus et SEO | P0 | Obligatoire |
| Monétisation et paiements | P2 | Conditionnel |
| Administration et audit | P0 | Obligatoire |
| Migration Legacy | P0 temporaire | Obligatoire jusqu'à clôture |

## 5.2 Identité et accès

- **Responsabilité :** comptes, authentification, récupération, profils personnels, consentements, rôles et habilitations.
- **Concepts :** Compte, Identité, Profil, Consentement, Rôle, Habilitation, Session courante, Mandat.
- **Normes :** Master Blueprint, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** Administration pour l'attribution approuvée des rôles ; notifications par port ; audit ; Professionnels pour le lien de représentant.
- **Dépendances interdites :** publication directe d'annonce ; décision de paiement ; dépendance au Legacy ; exposition de secrets aux autres domaines.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.3 Professionnels

- **Responsabilité :** identité d'organisation, validation, représentants, profil public et portefeuille.
- **Concepts :** Professionnel, Établissement éventuel, Représentant, Vérification, Profil professionnel, Statut public.
- **Normes :** Master Blueprint, Permissions Matrix, Migration Rules, Media Policy, SEO Policy.
- **Dépendances autorisées :** Identité pour les représentants ; Catalogue pour le portefeuille par références ; Médias ; Contenus et SEO pour l'URL publique ; Audit.
- **Dépendances interdites :** modification de l'état interne d'une annonce ; décision financière ; accès direct aux détails privés d'autres comptes ; reprise d'un modèle boutique Legacy.
- **Priorité :** P1.
- **Statut :** obligatoire.

## 5.4 Catalogue immobilier

- **Responsabilité :** identité du bien proposé, intention, catégorie, prix, caractéristiques, annonceur et contenu de l'annonce.
- **Concepts :** Annonce, Bien annoncé, Intention, Catégorie, Prix, Caractéristique, Disponibilité, Annonceur.
- **Normes :** Master Blueprint, Listing Lifecycle, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** Identité/Professionnels par identité de propriétaire ; Géographie par références validées ; Médias par identité de galerie ; Cycle de vie pour l'état.
- **Dépendances interdites :** décision SEO comme état ; moteur de recherche comme source ; règles de paiement dans l'annonce ; accès aux formes Legacy.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.5 Cycle de vie des annonces

- **Responsabilité :** dix états officiels, transitions, déclencheurs, expirations, renouvellements, retraits et archivage.
- **Concepts :** État d'annonce, Transition, Motif, Décision de modération, Échéance, Renouvellement, Archivage.
- **Normes :** Listing Lifecycle et Permissions Matrix.
- **Dépendances autorisées :** Catalogue pour l'annonce ; Modération pour les décisions ; Identité pour l'acteur ; Audit ; ports de notification ; événements vers SEO et Recherche.
- **Dépendances interdites :** changement d'état depuis une interface, un paiement, une page SEO ou un modèle de lecture ; état libre non officiel.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.6 Médias

- **Responsabilité :** propriété, conformité, galerie, image principale, ordre, variantes fonctionnelles, retrait, droits et archivage.
- **Concepts :** Média, Propriétaire média, Usage, Galerie, Image principale, Ordre, Validation, Droit, Variante, Retrait.
- **Normes :** Media Policy, Listing Lifecycle, Migration Rules.
- **Dépendances autorisées :** Catalogue, Professionnels et Contenus par références de propriétaire ; Modération ; Audit ; capacité externe derrière port.
- **Dépendances interdites :** média sans propriétaire ; décision de publication d'annonce ; exposition du support physique au domaine ; logique Legacy permanente.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.7 Géographie

- **Responsabilité :** référentiel officiel, hiérarchie, villes, quartiers, aliases et règles de rattachement.
- **Concepts :** Ville, Quartier, Niveau géographique validé, Alias, Rattachement, Coordonnée corroborante.
- **Normes :** Master Blueprint, SEO Policy, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** aucune dépendance métier obligatoire pour ses règles internes ; événements vers Catalogue, Recherche et SEO lors d'une évolution validée.
- **Dépendances interdites :** déduction depuis le moteur de recherche ; création par le SEO seul ; dépendance permanente aux deux référentiels Legacy.
- **Priorité :** P0 préalable.
- **Statut :** obligatoire.

## 5.8 Recherche et découverte

- **Responsabilité :** listes, filtres, tri, pagination, compteurs, suggestions et vues géographiques utiles.
- **Concepts :** Critères de recherche, Résultat, Facette, Compteur, Page de résultats, Pertinence, Fraîcheur de lecture.
- **Normes :** Master Blueprint, SEO Policy, Listing Lifecycle.
- **Dépendances autorisées :** projections alimentées depuis Catalogue, Cycle de vie, Géographie, Médias et Professionnels ; politiques SEO pour l'éligibilité des pages.
- **Dépendances interdites :** modification du catalogue ; décision de publication ; source de vérité des états ; règle de paiement.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.9 Contacts et leads

- **Responsabilité :** intentions de contact, téléphone, WhatsApp, message, attribution, consentement et mesure proportionnée.
- **Concepts :** Contact, Canal, Lead, Source, Attribution, Consentement, Signal anti-abus, Période de conservation.
- **Normes :** Master Blueprint, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** Catalogue pour annonce Publiée ; Identité pour acteur lorsque connu ; Professionnels ; Audit et notifications.
- **Dépendances interdites :** maintien d'une annonce Publiée ; collecte sans finalité ; exposition des données à Recherche ou SEO ; reprise illimitée des traces Legacy.
- **Priorité :** P1.
- **Statut :** obligatoire dans une portée minimale ; statistiques avancées conditionnelles.

## 5.10 Modération et signalements

- **Responsabilité :** files, contrôles, motifs, signalements, décisions, recours, suspensions et escalades.
- **Concepts :** Signalement, Dossier de contrôle, Motif, Preuve, Décision, Recours, Conflit d'intérêts.
- **Normes :** Listing Lifecycle, Media Policy, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** Catalogue et Médias en consultation ; Cycle de vie par commandes autorisées ; Identité pour acteurs ; Audit.
- **Dépendances interdites :** modification d'une offre commerciale ; réécriture silencieuse du catalogue ; suppression de preuve ; privilège commercial.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.11 Contenus et SEO

- **Responsabilité :** pages, guides, éligibilité, URL de référence, redirections, maillage, fils d'Ariane, données structurées et sitemaps.
- **Concepts :** Page, Guide, URL historique, URL de référence, Redirection, Décision d'indexation, Sitemap, Règle de maillage.
- **Normes :** SEO Policy, Permissions Matrix, Migration Rules.
- **Dépendances autorisées :** lectures des états de Catalogue, Cycle de vie, Géographie, Professionnels et Médias ; événements métier ; Audit.
- **Dépendances interdites :** modification d'une annonce ; publication d'annonce ; création de géographie ; utilisation de l'index comme source métier.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.12 Monétisation et paiements

- **Responsabilité :** offres, prix, commandes, paiements, rapprochements, remboursements et effets commerciaux autorisés.
- **Concepts :** Offre commerciale, Commande, Paiement, Référence financière, Rapprochement, Remboursement, Droit commercial.
- **Normes :** Master Blueprint, Permissions Matrix, Listing Lifecycle, Migration Rules.
- **Dépendances autorisées :** Identité/Professionnels ; Catalogue pour la cible d'une option ; Finance ; Audit ; fournisseur derrière port.
- **Dépendances interdites :** publication d'annonce ; contournement de modération ; modification par Modérateur ; décision fondée sur un retour d'interface seul.
- **Priorité :** P2.
- **Statut :** conditionnel à la validation commerciale et contractuelle.

## 5.13 Administration et audit

- **Responsabilité :** exposition interne des actions métier, habilitations, quatre yeux, journal, supervision et paramètres métier approuvés.
- **Concepts :** Action administrative, Permission, Validation, Journal d'audit, Paramètre métier, Action de masse.
- **Normes :** Permissions Matrix, Master Blueprint et toutes les politiques pour leurs actions.
- **Dépendances autorisées :** cas d'usage publics des domaines ; lectures administratives ; Audit.
- **Dépendances interdites :** mutation directe des données ; règle métier dupliquée ; édition de code ; plugin exécutable ; contournement du cycle.
- **Priorité :** P0.
- **Statut :** obligatoire.

## 5.14 Migration Legacy

- **Responsabilité :** import temporaire, qualification, décisions, transformation, rapprochement et compatibilité historique.
- **Concepts :** Source Legacy, Donnée candidate, Qualification, Disposition, Correspondance, Registre de décision, Rapprochement, Erreur.
- **Normes :** Migration Rules et toutes les politiques de domaine.
- **Dépendances autorisées :** ports d'entrée explicitement prévus par chaque domaine ; registres d'identifiants et d'URL ; Audit.
- **Dépendances interdites :** modèle Legacy dans le domaine ; accès Legacy depuis le produit courant ; publication sans validation ; dépendance permanente après clôture.
- **Priorité :** P0 temporaire.
- **Statut :** obligatoire jusqu'à clôture formelle, puis retiré du chemin opérationnel.

---

# 6. Frontières des modules

## 6.1 Ce qui appartient à un module

Une responsabilité appartient à un module lorsque celui-ci :

- possède le vocabulaire métier ;
- porte les invariants ;
- décide des transitions ;
- maîtrise la source de vérité ;
- possède le cycle de vie de la ressource ;
- définit les événements qu'il émet ;
- autorise les commandes qui le modifient.

## 6.2 Ce qui reste partagé

Le partage est limité aux concepts réellement universels et sans décision métier propre :

- identité technique d'une action ;
- temps conceptuel ;
- résultat d'une opération ;
- erreurs générales non métier ;
- primitives de pagination ;
- conventions d'audit ;
- types de valeur universels très stables.

Même ces éléments doivent rester petits et sans dépendance vers les domaines.

## 6.3 Ce qui ne doit jamais être partagé

- règles de transition d'annonce ;
- règles de publication ;
- décisions de permission ;
- états d'un domaine ;
- règles de prix et paiement ;
- règles SEO ;
- qualification de migration ;
- requêtes de persistance ;
- modèles propres à une interface ;
- données globales mutables.

## 6.4 Quand créer un nouveau module

Créer un module si plusieurs critères convergent :

- vocabulaire et invariants distincts ;
- propriétaire métier différent ;
- cycle de vie autonome ;
- besoins de lecture spécifiques ;
- dépendance externe forte à isoler ;
- rythme d'évolution différent ;
- nécessité de tests indépendants ;
- potentiel réel d'extraction future.

## 6.5 Quand ne pas créer un module

Ne pas créer un module pour :

- une seule page ;
- une seule opération simple ;
- un regroupement purement technique ;
- une entité sans règle autonome ;
- anticiper une hypothétique équipe future ;
- cacher une frontière encore incomprise ;
- multiplier des couches sans besoin de protection.

## 6.6 Prévenir les dépendances circulaires

- un module ne modifie jamais directement la source de vérité d'un autre ;
- les interactions utilisent commandes autorisées, lectures publiées ou événements ;
- la direction de dépendance est documentée ;
- une interaction bidirectionnelle persistante signale une frontière incorrecte ou un besoin de coordination applicative ;
- les objets internes d'un module ne traversent pas la frontière ;
- une règle couvrant plusieurs domaines est orchestrée par Application, sans déplacer les invariants.

## 6.7 Éviter un « Core » universel

Le partagé ne doit contenir aucune règle d'annonce, utilisateur, SEO, média ou paiement. Tout ajout doit démontrer qu'il est indépendant de tous les domaines, stable et réutilisé sans adaptation.

Un composant partagé qui accumule des conditions métier doit être replacé dans son domaine propriétaire ou remplacé par une coordination explicite.

## 6.8 Accès aux données inter-modules

Un module ne lit ni n'écrit directement la représentation interne d'un autre. Il reçoit :

- une réponse de lecture publiée ;
- un identifiant stable ;
- un événement métier ;
- ou le résultat d'une commande autorisée.

---

# 7. Couches logiques

## 7.1 Domaine

### Peut contenir

- entités et objets-valeurs ;
- invariants ;
- états et transitions ;
- politiques métier pures ;
- services de domaine lorsque la règle n'appartient pas naturellement à une entité ;
- événements métier ;
- erreurs métier explicites.

### Ne doit pas contenir

- concepts Laravel ;
- accès réseau ou persistance ;
- format de page ;
- session d'interface ;
- lecture de configuration externe ;
- logique de migration Legacy ;
- détail de notification ou de média physique.

### Dépendances autorisées

Uniquement vers lui-même et un noyau partagé minimal sans règle métier.

### Tests

Tests rapides et exhaustifs des invariants, transitions, politiques, états limites et erreurs.

## 7.2 Application

### Peut contenir

- cas d'usage ;
- orchestration ;
- validation de la séquence ;
- limites transactionnelles conceptuelles ;
- appel des autorisations ;
- coordination de plusieurs domaines ;
- émission d'événements après succès ;
- ports nécessaires.

### Ne doit pas contenir

- règle métier dupliquée ;
- logique de rendu ;
- accès direct à une capacité externe sans port ;
- requête spécifique à une interface ;
- décision implicite fondée sur un modèle de lecture.

### Dépendances autorisées

Vers Domaine et les ports dont elle exprime le besoin.

### Tests

Cas nominaux, refus, permissions, orchestration, ordre des effets, idempotence conceptuelle et erreurs externes simulées.

## 7.3 Interface

### Peut contenir

- traduction d'une intention utilisateur ;
- validation de forme ;
- présentation ;
- navigation ;
- adaptation des erreurs métier ;
- gestion du contexte de l'acteur.

### Ne doit pas contenir

- changement direct d'état ;
- règle de permission ;
- règle de prix ;
- décision SEO ;
- accès direct aux données internes ;
- logique différente selon public ou administration pour une même action.

### Dépendances autorisées

Vers les cas d'usage Application et les modèles de lecture autorisés.

### Tests

Traduction correcte des intentions, validation de forme, affichage des résultats et refus, sans revalider les invariants déjà couverts.

## 7.4 Infrastructure

### Peut contenir

- adaptateurs de persistance ;
- intégrations d'identité ;
- notifications ;
- capacité média ;
- paiement ;
- recherche ;
- cache ;
- horloge réelle ;
- journalisation technique.

### Ne doit pas contenir

- règle métier ;
- état officiel redéfini ;
- permission ;
- décision de publication ;
- dépendance inverse imposée au Domaine.

### Dépendances autorisées

Vers les ports et types exposés par Application ou Domaine, ainsi que les capacités externes choisies plus tard.

### Tests

Conformité aux ports, erreurs, conversions, sécurité des échanges et comportement avec les dépendances réelles dans un environnement contrôlé.

## 7.5 Lecture et recherche

### Peut contenir

- projections ;
- listes ;
- compteurs ;
- filtres ;
- vues de pages géographiques ;
- vues professionnelles ;
- statistiques ;
- vues administratives.

### Ne doit pas contenir

- invariant ;
- commande de mutation ;
- autorité sur un état ;
- décision de paiement ou permission ;
- donnée non corroborée présentée comme vérité.

### Dépendances autorisées

Vers les événements et sources de vérité publiées par les domaines ; vers les capacités de lecture choisies.

### Tests

Exactitude des projections, fraîcheur attendue, filtres, pagination, compteurs, reconstruction et absence de ressources non publiques.

## 7.6 Administration

### Peut contenir

- contexte d'acteur interne ;
- présentation des files ;
- demandes d'actions métier ;
- collecte de motifs et confirmations ;
- affichage avant/après ;
- lectures d'audit autorisées.

### Ne doit pas contenir

- mutation directe ;
- pouvoir implicite lié à un menu ;
- règle de contournement ;
- édition de code ;
- extension exécutable ;
- logique de domaine spécifique à l'administration.

### Dépendances autorisées

Vers Application, Autorisations et lectures administratives.

### Tests

Permissions par action, double validation, motifs, absence de fuite, cohérence avec l'interface publique et audit.

## 7.7 Migration

### Peut contenir

- lecture des sources Legacy ;
- qualification ;
- normalisation ;
- déduplication ;
- correspondances ;
- registre des décisions ;
- rapprochement ;
- rapports d'erreur.

### Ne doit pas contenir

- règle métier alternative ;
- publication directe ;
- dépendance utilisée par le produit courant ;
- objet Legacy traversant la frontière du domaine ;
- exception non enregistrée.

### Dépendances autorisées

Vers les ports d'import définis et les politiques normatives de chaque domaine.

### Tests

Règles de transformation, répétabilité, rapprochements, rejets, correspondances, erreurs et plan de retour.

---

# 8. Modèle d'interaction

## 8.1 Commandes

Une commande exprime l'intention de modifier une ressource : soumettre une annonce, décider une modération, retirer un média, renouveler, valider un professionnel ou rapprocher un paiement.

Règles :

- nommée selon l'action métier ;
- porte l'acteur, le rôle et la finalité nécessaires ;
- traitée par un seul propriétaire applicatif ;
- vérifie permission et invariant ;
- produit un résultat explicite ;
- ne dépend pas d'un écran particulier.

## 8.2 Requêtes

Une requête lit sans modifier. Elle peut utiliser un modèle de lecture adapté. Elle précise le périmètre et la finalité lorsque les données ne sont pas publiques.

## 8.3 Événements métier

Un événement décrit un fait accompli : annonce publiée, annonce suspendue, média refusé, professionnel validé, paiement rapproché ou URL retirée.

Il permet aux autres domaines de réagir sans modifier la décision source. Un événement n'est pas une commande cachée et ne doit pas contenir plus de données que nécessaire.

## 8.4 Politiques

Une politique exprime une décision qui dépend de plusieurs éléments mais demeure stable et testable : éligibilité au renouvellement, indexabilité, besoin de quatre yeux ou qualification d'une modification substantielle.

Le propriétaire de la politique est explicite. Une politique SEO ne modifie jamais l'état d'une annonce.

## 8.5 Services de domaine

Utilisés seulement lorsqu'une règle pure implique plusieurs concepts du même domaine et n'appartient naturellement à aucun objet unique.

## 8.6 Services applicatifs

Coordonnent cas d'usage, permissions, domaines, ports et audit. Ils ne deviennent pas des conteneurs universels de règles.

## 8.7 Adaptateurs

Traduisent une capacité externe vers un port attendu : identité, paiement, notification, média, recherche ou audit. Ils peuvent être remplacés sans modifier les règles du domaine.

## 8.8 Projections de lecture

Vues dédiées aux listes, filtres, administration, SEO et statistiques. Elles sont reconstruites depuis les sources de vérité et portent une indication de fraîcheur lorsqu'un décalage est possible.

## 8.9 Coordination inter-domaines

La coordination suit l'une de ces formes :

- commande explicite vers le domaine propriétaire ;
- lecture publiée ;
- événement après décision ;
- orchestration Application pour une opération nécessitant plusieurs domaines.

Les accès directs aux représentations internes sont interdits.

---

# 9. Gestion du cycle de vie des annonces

## 9.1 Autorité unique

Le domaine Cycle de vie des annonces est l'unique autorité sur :

- Brouillon ;
- Soumise ;
- En modération ;
- À corriger ;
- Publiée ;
- Suspendue ;
- Expirée ;
- Retirée ;
- Refusée ;
- Archivée.

Les interfaces ne stockent ni ne calculent une transition. Elles demandent une action et présentent le résultat.

## 9.2 Garantie des transitions

Chaque transition est représentée par :

- état de départ ;
- action demandée ;
- acteur et permission ;
- préconditions ;
- état d'arrivée ;
- motif ;
- événement produit ;
- effets attendus.

Toute transition absente du document normatif est refusée.

## 9.3 Permissions

L'autorisation est évaluée avant la transition, avec ressource, propriétaire, rôle, finalité et éventuelle seconde validation. Le Commercial, Finance et SEO ne publient jamais une annonce.

## 9.4 Notifications

Les notifications réagissent à un événement confirmé. Un échec de notification ne remet pas en cause la transition, mais devient observable et peut déclencher une reprise.

## 9.5 Effets SEO

L'événement de changement d'état alimente l'éligibilité SEO. Seule Publiée est éligible. SEO retire ou rétablit ses lectures sans modifier l'annonce.

## 9.6 Effets de recherche

Les projections retirent une annonce dès que l'état public ne l'autorise plus. La source de vérité reste le Cycle de vie ; une projection en retard ne peut autoriser contact ou mutation.

## 9.7 Audit

Chaque transition conserve acteur, rôle, finalité, état avant/après, motif, validation et origine automatique éventuelle.

## 9.8 Expiration

Une politique d'échéance identifie les annonces concernées. La transition Publiée → Expirée est appliquée par le domaine puis annoncée aux projections et notifications.

## 9.9 Renouvellement

Le domaine décide entre renouvellement simple et retour en modération selon les règles normatives. Paiement ou option commerciale ne remplace jamais cette décision.

## 9.10 Archivage

Archivée est terminal. Les lectures, SEO, contacts et médias réagissent à l'événement. La restauration directe est impossible.

## 9.11 Absence de dispersion

Une seule politique de transition est appelée par toutes les interfaces. Aucun contrôleur, page administrative, tâche automatique ou import ne modifie directement l'état.

---

# 10. Gestion des médias

## 10.1 Sous-responsabilités isolées

| Responsabilité | Règle architecturale |
|---|---|
| Propriété métier | Tout média référence un propriétaire de domaine valide |
| Validation | Qualité, format fonctionnel, dimensions et cohérence évalués par politique |
| Modération | Décision distincte, motivée et auditée |
| Image principale | Rôle unique dans une galerie, invariant contrôlé |
| Ordre | Séquence appartenant à la galerie, pas à l'interface |
| Variantes | Représentations du même média, jamais ressources métier autonomes |
| Retrait | Action métier répercutée sur tous les usages publics |
| Archivage | Suit la ressource propriétaire et les durées approuvées |
| Droits | Statut et contestations indépendants de la qualité visuelle |
| Migration | Rattachement par preuve et registre de décision |

## 10.2 Frontière média

Le domaine Média connaît la propriété, le rôle, l'ordre, la conformité et les droits. Il ne connaît pas le support physique retenu plus tard.

## 10.3 Coordination avec Catalogue

Catalogue connaît l'identité de sa galerie et les exigences minimales. Média garantit la conformité. Une annonce ne devient Publiée que si la politique de galerie est satisfaite.

## 10.4 Coordination avec Modération

Modération demande acceptation, remplacement, retrait ou suspension. Média conserve la décision et émet le fait correspondant. Le cycle de vie de l'annonce décide ensuite si une correction ou suspension est requise.

## 10.5 Migration

Les médias Legacy sont convertis en candidats. Aucun candidat ne devient actif sans propriétaire, preuve de rattachement, conformité et décision. Les orphelins restent isolés.

---

# 11. Autorisations

## 11.1 Autorisation par action métier

Chaque cas d'usage déclare l'action qu'il exécute. L'autorisation est évaluée sur :

- acteur réel ;
- rôle utilisé ;
- ressource ;
- propriété ;
- état ;
- finalité ;
- portée ;
- seconde validation éventuelle.

## 11.2 Refus par défaut

Une action non déclarée ou une information manquante produit un refus. L'existence d'un menu, d'une route, d'un rôle large ou d'un accès administratif ne donne aucun droit implicite.

## 11.3 Quatre yeux

Les actions concernées passent par deux décisions distinctes : proposition puis validation indépendante. Une personne cumulant plusieurs rôles ne peut remplir les deux positions.

## 11.4 Séparation des responsabilités

- Commercial prépare, Modération publie ;
- Finance rapproche, Modération ne modifie pas les offres ;
- SEO publie les contenus, jamais les annonces ;
- Super Administrateur supervise sans s'auto-valider ;
- Système applique seulement une règle approuvée.

## 11.5 Journalisation

Le résultat d'autorisation, surtout les refus sensibles et les actions accordées, peut produire une trace avec acteur, rôle, action, ressource, finalité et motif, en limitant les données personnelles.

## 11.6 Cohérence public/administration

Les deux interfaces appellent le même cas d'usage et la même autorisation. L'administration enrichit le contexte et les permissions ; elle ne crée pas une voie parallèle.

---

# 12. SEO

## 12.1 Séparations obligatoires

| Concept | Autorité |
|---|---|
| État métier d'annonce | Cycle de vie des annonces |
| Éligibilité SEO | Contenus et SEO, à partir des faits métier |
| URL de référence | Contenus et SEO avec validation du propriétaire métier |
| Redirection | Contenus et SEO avec registre et équivalence métier |
| Sitemap | Projection des ressources éligibles |
| Page géographique | Géographie pour la vérité ; SEO pour l'éligibilité et le contenu |
| Page éditoriale | Contenus et SEO |
| Registre d'URL | Contenus et SEO, lié aux correspondances de migration |

## 12.2 Flux conceptuel

Un fait métier est produit, par exemple Annonce publiée ou Ville fusionnée. SEO évalue ensuite l'éligibilité, met à jour ses lectures et produit ses propres décisions auditables.

Le flux inverse est interdit : une décision SEO ne publie pas une annonce et ne crée pas une ville.

## 12.3 URL historiques

Le registre conserve source, cible, traitement, justification, propriétaire et statut. Migration l'alimente initialement ; Contenus et SEO en devient le propriétaire durable.

## 12.4 Pages pauvres et facettes

La lecture peut signaler un inventaire insuffisant. La politique SEO décide de l'indexation. La page n'altère ni le catalogue ni la géographie pour atteindre un seuil.

## 12.5 Sitemap

Le sitemap est une projection reconstruisible. Il ne constitue jamais la source de vérité des pages ou annonces.

---

# 13. Recherche et lecture

## 13.1 Pourquoi des modèles distincts

Le modèle d'écriture protège les invariants. Les usages de lecture ont d'autres besoins : filtrer, trier, compter, agréger, paginer et joindre des informations de plusieurs domaines.

Imposer un modèle unique produirait soit des lectures coûteuses, soit un domaine contaminé par les besoins d'affichage.

## 13.2 Modèles nécessaires

- listes d'annonces publiques ;
- filtres et facettes ;
- compteurs par intention, catégorie et géographie ;
- pages Ville et Quartier ;
- profils professionnels et portefeuilles ;
- files et tableaux administratifs ;
- statistiques de leads et d'activité ;
- lectures SEO et sitemap.

## 13.3 Règles

- chaque projection a un propriétaire, une finalité et une fraîcheur attendue ;
- elle est reconstruisible depuis les sources de vérité ;
- elle ne valide aucune transition ;
- elle n'accorde aucune permission ;
- elle ne confirme aucun paiement ;
- elle exclut les annonces non Publiées des vues publiques ;
- elle peut être corrigée sans modifier le domaine.

## 13.4 Fraîcheur critique

Suspension, retrait, expiration, permission et paiement exigent une vérification sur la source autoritative au moment d'une action critique, même si une lecture affiche une information plus ancienne.

---

# 14. Administration

## 14.1 Principe

L'administration est une interface interne sur les mêmes actions métier que le produit. Elle n'est ni un second domaine ni une voie de mutation directe.

## 14.2 Action administrative

Chaque action présente :

- ressource ;
- état actuel ;
- action demandée ;
- permission nécessaire ;
- motif ;
- effets prévus ;
- éventuelle validation à quatre yeux ;
- résultat ;
- état après.

## 14.3 Files et lectures

Les tableaux administratifs utilisent des projections dédiées. Avant toute mutation, le cas d'usage revalide la source de vérité et la permission.

## 14.4 Interdictions

- édition de code ;
- installation de plugins exécutables ;
- modification directe de l'état ;
- publication commerciale d'une annonce ;
- contournement d'un refus ;
- action sensible sans motif ;
- suppression de l'historique ;
- permissions fondées uniquement sur la visibilité d'un écran.

## 14.5 Audit

Les actions sensibles conservent avant/après, acteur, rôle, finalité, motif, validations et résultat. L'administration fournit une lecture autorisée de cet historique sans pouvoir le réécrire.

---

# 15. Migration Legacy

## 15.1 Isolement

Migration est un module temporaire et un chemin d'entrée contrôlé. Il connaît le Legacy ; les domaines courants ne le connaissent pas.

## 15.2 Étapes conceptuelles

1. import temporaire des données candidates ;
2. qualification ;
3. nettoyage ;
4. déduplication ;
5. transformation vers les concepts cibles ;
6. décision Conserver, Nettoyer, Fusionner, Archiver ou Supprimer ;
7. entrée par les ports de domaine ;
8. rapprochement ;
9. validation ;
10. rapport et clôture.

## 15.3 Registre de décision

Chaque exception et règle conserve source, disposition, justification, confiance, impacts, initiateur, valideur et résultat.

## 15.4 Identifiants historiques

Une correspondance explicite associe l'identifiant Legacy à l'identité cible. L'identifiant historique ne devient pas nécessairement l'identifiant métier principal, mais reste disponible pour rapprochement et support.

## 15.5 URL historiques

Les URL et aliases sont transférés au registre SEO avec leur décision. Une URL ne justifie jamais une publication non conforme.

## 15.6 Absence de dépendance permanente

Après clôture :

- aucun cas d'usage courant ne lit le Legacy ;
- aucun objet Legacy ne traverse les domaines ;
- les correspondances nécessaires deviennent un registre historique limité ;
- le module Migration sort du chemin opérationnel ;
- les règles utiles ont été exprimées dans les domaines cibles, pas conservées comme compatibilité implicite.

---

# 16. Sécurité

## 16.1 Authentification

- domaine Identité propriétaire des règles ;
- renouvellement du contexte après connexion ;
- récupération limitée et à usage contrôlé ;
- sessions Legacy invalides ;
- authentification renforcée des acteurs internes ;
- stratégie précise ouverte à décision ultérieure.

## 16.2 Autorisation

- action, ressource, propriété, rôle et finalité ;
- refus par défaut ;
- quatre yeux ;
- revalidation pour action sensible ;
- aucune confiance dans l'interface.

## 16.3 Secrets

- absents du domaine, des contenus et des paramètres métier ;
- accessibles uniquement aux adaptateurs qui en ont besoin ;
- renouvelables ;
- jamais affichés dans l'administration ;
- stratégie de gestion ouverte.

## 16.4 Uploads

- toute entrée média est non fiable ;
- validation avant usage public ;
- aucun contenu exécutable ;
- séparation entre contenu candidat, conforme, retiré et archivé ;
- propriété obligatoire.

## 16.5 Paiements

- événements authentifiés ;
- traitement répétable sans double effet ;
- rapprochement obligatoire ;
- aucun retour d'interface comme preuve unique ;
- séparation Finance/Commercial/Modération ;
- journal complet.

## 16.6 Données personnelles

- minimisation ;
- finalité ;
- durée ;
- accès par besoin ;
- export contrôlé ;
- suppression ou anonymisation selon décision ;
- traces de leads isolées du public et du SEO.

## 16.7 Actions sensibles

Double validation, justification, confirmation, audit et parfois authentification renforcée. Les actions massives suivent le même principe avec contrôle d'impact.

## 16.8 Surface publique minimale

Seuls les parcours nécessaires sont publics. Administration, migration, diagnostics, tâches automatiques et dépendances externes ne constituent jamais des entrées publiques générales.

---

# 17. Performance

## 17.1 Recherche et listes

- modèles de lecture dédiés ;
- filtres fondés sur des attributs fiables ;
- pagination stable ;
- absence de traitement individuel répétitif par résultat ;
- compteurs explicables ;
- budget de fraîcheur défini.

## 17.2 Médias

- variantes adaptées aux usages fonctionnels ;
- image principale immédiatement disponible ;
- chargement progressif de la galerie ;
- qualité conforme sur mobile ;
- retrait propagé à toutes les variantes ;
- capacité physique ouverte à décision ultérieure.

## 17.3 Cache

Le cache accélère une lecture, jamais une décision. Chaque usage précise : clé conceptuelle, durée, invalidation, tolérance de fraîcheur et comportement en cas d'absence.

Permissions, paiements, état de publication et disponibilité au moment d'un contact ne reposent pas uniquement sur un cache.

## 17.4 Pagination

Ordre stable, limites explicites, absence de doublons et comportement défini lorsque le catalogue évolue.

## 17.5 Statistiques

Agrégats produits hors du chemin critique. Les consultations internes sont exclues des vues publiques. Une statistique n'influence pas silencieusement une règle métier.

## 17.6 Pages SEO

Les vues géographiques et thématiques s'appuient sur des projections. Une génération coûteuse ne modifie pas le référentiel ou l'indexabilité.

## 17.7 Traitement asynchrone conceptuel

Peut concerner notifications, variantes média, projections, sitemaps, expiration, statistiques et certaines étapes de migration.

Règles : répétabilité, reprise, observation, ordre lorsque nécessaire, absence de double effet et vérification de l'état actuel avant action.

## 17.8 Fraîcheur des données critiques

Les budgets de fraîcheur sont classés :

- immédiat pour suspension, retrait, permission et paiement ;
- court et borné pour recherche et sitemap ;
- différé acceptable pour statistiques agrégées.

Les seuils chiffrés restent ouverts.

---

# 18. Observabilité et audit

## 18.1 Distinction

- **Observabilité** : comprendre l'état courant, les erreurs, délais et volumes du système ;
- **Audit** : prouver qui a décidé quoi, sur quelle ressource, avec quel motif et quel résultat.

## 18.2 Événements métier importants

| Famille | Événements à observer |
|---|---|
| Annonces | chaque transition, expiration, renouvellement, archivage |
| Modération | prise en charge, correction, publication, refus, suspension, recours |
| Administration | action sensible, quatre yeux, action de masse, export |
| Paiements | initiation, confirmation, échec, rapprochement, remboursement, doublon évité |
| Migration | qualification, rejet, fusion, erreur, écart de volume, validation |
| SEO | URL sans décision, redirection en erreur, soft-404, divergence de sitemap, page pauvre |
| Médias | refus, retrait, droit contesté, orphelin, variante en erreur |
| Automatique | début, fin, volume, reprise, double effet évité |
| Autorisations | refus sensibles, tentatives répétées, conflit de rôles |

## 18.3 Corrélation

Une opération importante reçoit une identité de suivi permettant de relier intention, autorisation, décision, effets, notifications et audit sans exposer de donnée personnelle excessive.

## 18.4 Indicateurs

- délais de modération ;
- stocks par état ;
- échecs et reprises ;
- fraîcheur des projections ;
- écarts de migration ;
- erreurs SEO par famille ;
- refus d'autorisation ;
- paiements non rapprochés ;
- médias rejetés ;
- actions à quatre yeux en attente.

## 18.5 Alertes

Priorité aux anomalies affectant confidentialité, publication indue, paiement, propriété, perte de données, médias interdits et dérive massive SEO.

## 18.6 Audit immuable conceptuellement

Une correction ne réécrit pas une trace : elle produit une nouvelle trace explicative. Les durées et accès seront décidés ultérieurement selon les obligations.

---

# 19. Stratégie de tests

## 19.1 Tests du domaine

- invariants ;
- objets-valeurs ;
- états ;
- transitions ;
- politiques ;
- cas limites ;
- erreurs attendues.

Ils ne dépendent d'aucune capacité externe.

## 19.2 Tests des services applicatifs

- orchestration ;
- séquence ;
- résultats ;
- effets ;
- erreurs de ports ;
- répétabilité ;
- audit demandé.

## 19.3 Tests des autorisations

Matrice complète acteur × action × ressource × état × propriété × finalité, avec refus par défaut, cumul de rôles et quatre yeux.

## 19.4 Tests des transitions

Toutes les transitions autorisées et interdites de `LISTING-LIFECYCLE.md`, y compris expiration, renouvellement, suspension, retrait et archivage.

## 19.5 Tests d'intégration

Conformité des adaptateurs aux ports, gestion des erreurs, conversions, transaction conceptuelle, identité, médias, notifications et paiements conditionnels.

## 19.6 Tests de recherche

- filtres ;
- tri ;
- pagination ;
- compteurs ;
- annonces non publiques exclues ;
- reconstruction ;
- fraîcheur ;
- cohérence géographique.

## 19.7 Tests SEO

- éligibilité par type et état ;
- canonical ;
- redirections ;
- URL historiques ;
- sitemap ;
- pages pauvres ;
- duplications ;
- pagination et facettes ;
- maillage et fils d'Ariane.

## 19.8 Tests de migration

- règles Conserver/Nettoyer/Fusionner/Archiver/Supprimer ;
- répétabilité ;
- correspondances ;
- orphelins ;
- rapprochements ;
- zéro écart inexpliqué ;
- cas ambigus ;
- plan de retour.

## 19.9 Tests de sécurité

- authentification ;
- autorisation ;
- actions sensibles ;
- données personnelles ;
- uploads ;
- secrets absents des sorties ;
- abus et répétitions ;
- séparation des rôles.

## 19.10 Tests de parcours

Parcours complets public, particulier, professionnel, modérateur, commercial, SEO, Finance et Super Administrateur, en couvrant les résultats positifs et refus.

## 19.11 Proportion

La majorité des règles est couverte près du Domaine et de l'Application. Les parcours complets vérifient les intégrations critiques sans devenir l'unique preuve du comportement métier.

---

# 20. Risques architecturaux

| Risque | Probabilité | Impact | Mesure de réduction | Signal d'alerte |
|---|---|---|---|---|
| Recréation d'un monolithe | Élevée | Critique | Frontières, propriétaires, revues de dépendances | Un module lit et écrit partout |
| Module partagé trop large | Élevée | Élevé | Partagé minimal, règle d'admission stricte | « Core » connaît annonces ou utilisateurs |
| Logique métier dans les contrôleurs | Élevée | Critique | Cas d'usage uniques et domaine testé isolément | Comportement différent selon interface |
| Dépendance forte au framework | Moyenne | Élevé | Domaine sans Laravel, ports aux frontières | Règle impossible à tester hors socle |
| Duplication des règles | Élevée | Critique | Propriétaire unique par invariant | Même transition codifiée plusieurs fois |
| Divergence public/administration | Élevée | Critique | Mêmes commandes et autorisations | Admin peut faire une action impossible ailleurs |
| SEO couplé au catalogue | Moyenne | Critique | Éligibilité en réaction aux événements | SEO change directement un état ou lieu |
| Autorisations dispersées | Élevée | Critique | Autorisation par action centralisée conceptuellement | Permissions vérifiées seulement dans l'interface |
| Migration devenue permanente | Moyenne | Élevé | Module temporaire, critères de clôture | Produit courant lit encore le Legacy |
| Recherche utilisée comme source métier | Moyenne | Critique | Projections reconstruisibles, revalidation critique | Une lecture décide publication ou paiement |
| Surarchitecture | Moyenne | Élevé | Abstractions seulement aux frontières réelles | Multiplication d'objets sans règle ni usage |
| Complexité prématurée | Moyenne | Élevé | Monolithe modulaire, décisions différées | Distribution avant charge ou frontière stable |
| Domaine Catalogue trop large | Élevée | Élevé | Séparer Cycle, Média, Géographie et Recherche | Catalogue absorbe toutes les décisions |
| Événements utilisés comme commandes cachées | Moyenne | Élevé | Distinguer fait et intention | Un événement déclenche une mutation non autorisée |
| Cohérence différée mal maîtrisée | Moyenne | Critique | Budgets de fraîcheur et revalidation | Annonce suspendue encore contactable |
| Audit trop verbeux ou sensible | Moyenne | Élevé | Schéma conceptuel minimal et politique de rétention | Secrets ou données personnelles dans les traces |
| Paiement couplé à la publication | Moyenne | Critique | Domaines distincts et coordination explicite | Statut payé publie directement |
| Frontières théoriques non respectées | Élevée | Élevé | Contrôles continus et tests d'architecture futurs | Accès directs inter-modules récurrents |

## 20.1 Revue des risques

Les risques sont revus à chaque décision structurante, avant le démarrage de la réalisation, puis à chaque évolution de module ou dépendance externe.

---

# 21. Décisions encore ouvertes

Les éléments suivants nécessitent des documents de décision ultérieurs :

| Décision | Pourquoi elle reste ouverte | Critères futurs |
|---|---|---|
| Version de PHP | Dépend du calendrier et du support | sécurité, support, compatibilité |
| Version de Laravel | Projet non créé | support, stabilité, besoins du produit |
| Moteur de base de données | Aucun modèle physique défini | intégrité, recherche, exploitation, compétences |
| Moteur de recherche | Besoin à mesurer | filtres, pertinence, volume, fraîcheur, coût |
| Stratégie de cache | Dépend des lectures et objectifs | fraîcheur, invalidation, simplicité |
| Traitement asynchrone | Cas conceptuels identifiés seulement | volume, reprise, observabilité, ordre |
| Stockage média | Politique fonctionnelle définie, capacité ouverte | droits, durabilité, performance, coût |
| Infrastructure | Aucun environnement cible choisi | sécurité, disponibilité, exploitation, budget |
| Déploiement | Dépend de l'infrastructure | réversibilité, contrôle, fréquence, audit |
| Gestion des secrets | Principe défini, solution ouverte | rotation, accès, audit, séparation |
| Stratégie d'authentification | Règles métier définies | sécurité, récupération, expérience, migration |
| Structure physique des modules | Frontières logiques d'abord | lisibilité, dépendances, conventions Laravel |
| Limites transactionnelles | Dépendent du modèle physique | invariants, erreurs, coordination |
| Format des événements | Aucun contrat détaillé aujourd'hui | stabilité, confidentialité, évolution |
| Stratégie d'audit | Événements définis conceptuellement | rétention, accès, volume, preuve |
| Politique de disponibilité | Objectifs chiffrés absents | parcours critiques, budget, support |
| Objectifs de performance | Besoin de mesures | mobile, recherche, images, administration |
| Organisation des tâches planifiées | Concept seulement | fiabilité, reprise, visibilité |
| Extraction future de modules | Non justifiée aujourd'hui | charge, autonomie, frontière stable |

## 21.1 Ordre recommandé des décisions

1. versions de langage et socle ;
2. structure physique des modules ;
3. persistance et limites transactionnelles ;
4. authentification et secrets ;
5. recherche et cache ;
6. médias ;
7. asynchrone et audit ;
8. infrastructure et déploiement.

Cet ordre pourra évoluer selon les arbitrages métier restants, mais aucune décision ne doit introduire une dépendance du Domaine envers Laravel.

---

# 22. Critères d'acceptation

## 22.1 Respect normatif

- les six documents métier sont cités et respectés ;
- aucune règle n'est modifiée ou réinterprétée ;
- les questions ouvertes restent visibles ;
- le cycle de vie, les médias, permissions, SEO et migration sont traduits en contraintes d'architecture.

## 22.2 Style cible

- comparaison des options réalisée ;
- monolithe modulaire orienté domaines recommandé et justifié ;
- principes de ports et adaptateurs appliqués proportionnellement ;
- aucune distribution prématurée imposée ;
- aucune structure physique définitive figée.

## 22.3 Domaines et frontières

- les treize domaines demandés sont définis ;
- responsabilité, concepts, normes, dépendances, priorité et statut sont précisés ;
- les règles de création de module sont compréhensibles ;
- les dépendances circulaires et le « Core » universel sont explicitement refusés ;
- aucun module ne modifie la source de vérité d'un autre.

## 22.4 Couches

- Domaine, Application, Interface, Infrastructure, Lecture, Administration et Migration sont définis ;
- contenus autorisés et interdits explicites ;
- dépendances autorisées explicites ;
- responsabilités de test explicites ;
- le Domaine reste indépendant de Laravel.

## 22.5 Garanties métier

- les dix états et transitions ont une autorité unique ;
- les permissions portent sur des actions ;
- le quatre yeux est représenté ;
- SEO ne publie jamais une annonce ;
- Recherche n'est jamais source de vérité ;
- Média conserve propriété et cycle ;
- Administration utilise les mêmes cas d'usage ;
- Paiement ne contourne pas Modération.

## 22.6 Legacy et migration

- le Legacy est isolé ;
- la migration possède une frontière temporaire ;
- les données candidates entrent par des ports contrôlés ;
- identifiants et URL historiques sont compatibles sans contaminer le Domaine ;
- les rapprochements et registres sont prévus ;
- aucune dépendance permanente au Legacy.

## 22.7 Qualités transverses

- sécurité par défaut ;
- surface publique minimale ;
- événements observables définis ;
- audit des actions sensibles ;
- modèles de lecture distincts ;
- stratégie de tests multi-niveaux ;
- risques documentés avec probabilité, impact, réduction et alerte.

## 22.8 Limites respectées

- aucun code applicatif ;
- aucun projet Laravel ;
- aucun modèle concret ;
- aucune migration de données créée ;
- aucun contrat détaillé d'interface ;
- aucun contrôleur ;
- aucun service applicatif concret ;
- aucun fichier de configuration ;
- aucune modification des documents normatifs.

---

# Synthèse de l'architecture recommandée

APPART.SN REBUILD doit être conçu comme un **monolithe modulaire orienté domaines**, protégé par des **principes de ports et adaptateurs** sur ses frontières importantes. Cette architecture conserve la simplicité d'un produit unique tout en empêchant la reconstitution du monolithe procédural Legacy.

Le Domaine porte les invariants et reste indépendant de Laravel. Application orchestre les cas d'usage. Les interfaces publique et administrative utilisent les mêmes actions métier. Infrastructure satisfait des ports sans imposer ses choix au Domaine. Recherche, SEO, administration et statistiques utilisent des projections reconstruisibles qui ne deviennent jamais sources de vérité. Migration est temporaire et isolée.

# Domaines et frontières retenus

1. Identité et accès ;
2. Professionnels ;
3. Catalogue immobilier ;
4. Cycle de vie des annonces ;
5. Médias ;
6. Géographie ;
7. Recherche et découverte ;
8. Contacts et leads ;
9. Modération et signalements ;
10. Contenus et SEO ;
11. Monétisation et paiements ;
12. Administration et audit ;
13. Migration Legacy.

Les frontières essentielles sont : une source de vérité par domaine, aucune mutation directe inter-module, aucune règle métier dans les interfaces, aucune décision depuis les projections, aucun accès Legacy dans le produit courant et aucun domaine dépendant de Laravel.

# Décisions techniques encore ouvertes

Restent à décider : versions PHP et Laravel, moteur de base de données, moteur de recherche, cache, traitement asynchrone, stockage média, infrastructure, déploiement, secrets, authentification, structure physique des modules, limites transactionnelles, format des événements, audit et objectifs de performance.

# Risques majeurs

Les risques dominants sont la recréation d'un monolithe, l'apparition d'un « Core » universel, la logique métier dans les contrôleurs, le couplage au framework, la duplication des règles, la divergence public/administration, les permissions dispersées, le couplage SEO–Catalogue, la migration permanente, l'usage de Recherche comme source métier et la surarchitecture.

# Confirmation de périmètre

Ce livrable est exclusivement documentaire. Aucun code, projet Laravel, commande Composer, modèle Eloquent, migration SQL, API détaillée, contrôleur, service applicatif concret, fichier de configuration ou dossier applicatif n'a été créé.

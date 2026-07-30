# APPART.SN REBUILD 2026 — MASTER BLUEPRINT

## Statut du document

- **Origine :** Sprint 2 — Master Blueprint
- **Version de gouvernance :** 2.0 — alignement Phase 5.0B
- **Date :** 16 juillet 2026
- **Dernier alignement :** 28 juillet 2026 — GO FINAL 5.2B / Discovery 5.2C
- **Statut :** vision produit conservée ; gouvernance et séquencement remplacés
  par la baseline Phase 5.0
- **Sources autorisées :** MASTER AUDIT validé, sauvegarde de la base Legacy, patrimoine SEO, fonctionnalités recensées et architecture existante
- **Hors périmètre :** choix de framework, code, API, schéma de base de données, infrastructure détaillée et implémentation

## Baseline normative actuelle

Ce document conserve la vision, les publics, les invariants produit et le
périmètre fonctionnel d'origine. Les mentions prospectives (« proposé »,
« futur », « à définir ») décrivent leur contexte historique et ne doivent
plus être utilisées pour déterminer l'état d'implémentation.

Les références normatives actuelles sont, par ordre de priorité :

1. les décisions expresses de certification ;
2. `PHASE-5.0B-BASELINE-ALIGNMENT.md`, les registres 5.0B et les règles de
   certification ;
3. les six livrables certifiés de Phase 5.0A ;
4. les certifications et amendements versionnés des Phases 2 à 4 ;
5. le code, les migrations et les tests pour constater l'implémentation ;
6. le présent Blueprint pour la vision produit.

Identity & Access Completion 5.1A–5.1J est GO CERTIFIÉ.
`A-5.1-IAM-OUTBOX-CONCURRENCY-01` est GO CERTIFIÉ et FERMÉ ; la réserve
concurrente Outbox IAM est levée. Phase 5.1 est GO FINAL CERTIFIÉE, FERMÉE et
GELÉE. F-17 et F-18 sont exécutoires. Phase 5.2A a certifié Discovery,
Contracts, Implementation, Persistence, Runtime, HTTP, Operations et Public
Integration. Phase 5.2A est GO FINAL CERTIFIÉE, FERMÉE et GELÉE. F-19 et
F-20 sont actifs et exécutoires. Phase 5.2B — Media Ingestion est GO FINAL
CERTIFIÉE, FERMÉE et GELÉE ; F-21/F-22 sont actifs. Professional Profile 5.2C
est ouvert exclusivement pour son Discovery / Blueprint.

État consolidé au 28 juillet 2026 :

- les neuf chaînes lifecycle identifiées par 5.0A sont certifiées et gelées ;
- Account Status Lifecycle est fermé depuis le GO FINAL 4.9 ;
- la projection publique, le Runtime Health à 58 capacités et les
  infrastructures de delivery certifiées sont des baselines protégées ;
- 5.0A est `GO CERTIFIÉ` et fermée ;
- 5.2B Atomic Delivery / Outbox Integration Foundation est GO CERTIFIÉE et
  FERMÉE ; les migrations 058–060 sont certifiées et gelées ;
- Phase 5.2B est GO FINAL CERTIFIÉE, FERMÉE et GELÉE ; F-21 et F-22 sont
  actifs et exécutoires ;
- Professional Profile Discovery / Blueprint, Contracts, Persistence et Runtime
  Foundations sont GO CERTIFIÉS et FERMÉS ; HTTP Foundation reste ouverte en
  NO GO CERTIFIÉ ; l’audit
  `A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` est NO GO CERTIFIÉ et FERMÉ ;
  `A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01` est GO CERTIFIÉ et FERMÉ ;
  `A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01` est NO GO CERTIFIÉ et FERMÉ ;
  `A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01` est GO CERTIFIÉ et FERMÉ ;
  HTTP Foundation corrective reste NO GO faute d’implémentations owner et de
  bindings autorisés ; le sprint Owner Read Implementations & Runtime Bindings
  est NO GO CERTIFIÉ et FERMÉ ; Professional Mandate Owner Source Foundation est
  ouverte et en attente de preuve PostgreSQL terminale,
  sans jalon suivant ouvert ;
- les capacités produit encore à construire et leur ordre sont ceux de
  `PHASE-5.0-ROADMAP.md`.

En cas de divergence, ce bloc et les références ci-dessus prévalent sur les
formulations historiques du reste du document.

## Principes directeurs

Ce Blueprint définit le produit à reconstruire, ses responsabilités, ses parcours, ses règles de gouvernance et l'ordre de réalisation. Il ne constitue pas une reproduction technique du Legacy.

Les principes suivants s'appliquent à toutes les décisions futures :

1. **Préserver la valeur, pas l'implémentation.** Les annonces, utilisateurs, professionnels, contenus, médias, signaux commerciaux et actifs SEO sont le patrimoine à conserver. Les scripts, templates, plugins et dépendances du Legacy ne sont pas une architecture cible.
2. **L'immobilier est le domaine unique.** Les fonctions génériques de petites annonces hors immobilier ne font pas partie du nouveau produit sans nouvelle décision métier explicite.
3. **Une responsabilité métier doit avoir un propriétaire clair.** Publication, modération, identité, géographie, contenu, SEO, monétisation et exploitation sont des domaines distincts.
4. **Une donnée possède une source de vérité.** Les doublons historiques de géographie, de comptes professionnels, d'états et de configuration ne doivent pas être reproduits.
5. **La sécurité et la confidentialité sont des conditions d'acceptation.** Elles ne sont ni des options ni des correctifs ultérieurs.
6. **Le SEO est une contrainte de continuité produit.** Les URL historiques, redirections et contenus stratégiques doivent être gouvernés au même niveau que les fonctions métier.
7. **L'administration pilote le produit, pas son code.** Aucun administrateur ne doit installer du code, éditer un fichier exécutable ou déclencher une opération technique dangereuse depuis l'interface.
8. **Toute fonctionnalité doit démontrer sa valeur.** Une fonction sans usage, propriétaire, règle métier ou obligation réglementaire validée n'entre pas automatiquement dans la reconstruction.

---

# 1. Vision

APPART.SN doit devenir la plateforme immobilière sénégalaise de référence pour découvrir, publier et gérer des offres immobilières fiables, avec une expérience adaptée au mobile, aux particuliers, aux professionnels et aux équipes d'exploitation.

La plateforme doit inspirer confiance par :

- la qualité et la fraîcheur des annonces ;
- une recherche compréhensible par ville, quartier, type de bien et intention ;
- des contacts simples avec les annonceurs ;
- une modération traçable ;
- des comptes professionnels clairement identifiés ;
- des contenus locaux utiles ;
- une continuité SEO maîtrisée ;
- une protection réelle des comptes et données personnelles.

La reconstruction doit produire un système explicable, gouvernable et évolutif. Elle ne doit pas reconduire les couplages, écrans génériques, configurations ambiguës ou mécanismes d'extension du Legacy.

## 1.1 Publics cibles

### Chercheur immobilier

Personne recherchant un logement, un terrain, un local ou un investissement, à la location, en location meublée ou à l'achat.

### Annonceur particulier

Propriétaire ou mandataire ponctuel souhaitant publier et gérer une ou plusieurs annonces.

### Professionnel immobilier

Agence, promoteur ou opérateur disposant d'une identité professionnelle, d'un portefeuille d'annonces et de besoins commerciaux mesurables.

### Équipe APPART.SN

Administrateurs, modérateurs et commerciaux chargés de la qualité du catalogue, des comptes professionnels, de la monétisation, des contenus et de l'exploitation.

## 1.2 Proposition de valeur

- **Pour le public :** trouver rapidement des annonces pertinentes et contacter un annonceur identifiable.
- **Pour les particuliers :** publier avec un parcours guidé et suivre clairement le statut de l'annonce.
- **Pour les professionnels :** valoriser un portefeuille et mesurer les contacts reçus.
- **Pour APPART.SN :** administrer le catalogue, les contenus et les offres commerciales avec des règles traçables.

---

# 2. Objectifs

## 2.1 Objectifs produit

1. Unifier la découverte immobilière autour de trois intentions principales : louer, louer meublé et acheter.
2. Rendre le dépôt d'annonce compréhensible, contrôlé et adapté au mobile.
3. Formaliser le cycle de vie complet d'une annonce.
4. Distinguer clairement particuliers et professionnels.
5. Fournir aux professionnels une présence publique et une gestion de portefeuille cohérentes.
6. Faciliter le contact par téléphone, WhatsApp et message, sous réserve des règles de confidentialité.
7. Donner aux équipes une administration sûre et orientée métier.
8. Préserver les actifs SEO qui ont une valeur démontrable.

## 2.2 Objectifs de qualité

- supprimer les ambiguïtés de taxonomie et de géographie ;
- éviter les annonces orphelines, images orphelines et relations implicites ;
- rendre chaque action sensible auditable ;
- garantir un comportement prévisible sur mobile et réseau contraint ;
- assurer l'accessibilité des parcours essentiels ;
- mesurer la qualité du catalogue et les contacts utiles ;
- permettre une migration vérifiable par rapprochement des volumes.

## 2.3 Objectifs commerciaux

- soutenir une offre professionnelle lisible ;
- permettre des options de visibilité seulement si leur définition, durée, prix et effet sont validés ;
- suivre les contacts générés sans créer une collecte disproportionnée ;
- conserver les moyens de paiement correspondant à des contrats et usages réels.

## 2.4 Non-objectifs

- devenir une plateforme généraliste de petites annonces ;
- reproduire les écrans ou fichiers Legacy ;
- conserver toutes les intégrations historiques ;
- rendre l'apparence ou le code modifiables depuis l'administration ;
- définir dans ce document un framework, une API ou une base cible ;
- migrer les données sans qualification préalable.

---

# 3. Architecture fonctionnelle

L'architecture fonctionnelle est organisée en domaines. Ces domaines expriment des responsabilités et non une structure technique imposée.

## 3.1 Domaines cœur

### Identité et accès

Responsabilités : inscription, connexion, récupération de compte, profil, consentements, rôles et contrôle d'accès.

Justification : les comptes particuliers, professionnels et administratifs sont indispensables, mais les mécanismes Legacy de session et de compatibilité des mots de passe ne doivent pas être reproduits.

### Catalogue immobilier

Responsabilités : annonce, caractéristiques du bien, intention, catégorie, prix, disponibilité, statut, annonceur et visibilité.

Justification : il s'agit du cœur de valeur d'APPART.SN.

### Géographie

Responsabilités : pays couvert, régions utiles, villes, quartiers, aliases historiques et rattachements valides.

Justification : la recherche, le dépôt et le SEO dépendent du même référentiel. Les deux modèles géographiques Legacy doivent être fusionnés avant migration.

### Médias

Responsabilités : images d'annonces et de professionnels, ordre, image principale, variantes, contrôle, suppression et traçabilité.

Justification : les médias représentent la majorité du volume physique et constituent un facteur décisif de conversion.

### Recherche et découverte

Responsabilités : recherche, filtres, tri, listes, navigation par intention, ville, quartier et type de bien.

Justification : la découverte doit refléter le catalogue réel et ne pas dépendre de pages SEO artificielles.

### Contacts et leads

Responsabilités : appels, WhatsApp, messages, signalements et mesure des contacts utiles.

Justification : la mise en relation est l'issue commerciale principale, mais la collecte doit être limitée et gouvernée.

### Professionnels

Responsabilités : profil d'agence ou de professionnel, validation, portefeuille public, coordonnées et visibilité.

Justification : le Legacy contient 188 comptes professionnels et un domaine boutique ; la valeur doit être conservée après déduplication.

## 3.2 Domaines de contrôle

### Modération

Responsabilités : files d'attente, contrôle, décision, motif, historique, signalements, suspension et republication.

### Monétisation

Responsabilités : offre commerciale, tarif, option, commande, paiement, rapprochement, activation et remboursement éventuel.

### Contenus et SEO

Responsabilités : pages institutionnelles, guides, métadonnées, URL, redirections, maillage, indexabilité et sitemaps.

### Administration et audit

Responsabilités : habilitations, journal d'activité, configuration métier, supervision et opérations contrôlées.

## 3.3 Règles transverses

- chaque annonce possède un identifiant interne stable et, si nécessaire, un alias public Legacy ;
- chaque annonce appartient à un annonceur identifiable ;
- chaque annonce possède une intention, un type de bien, une localisation et un statut explicites ;
- aucune image ne peut exister sans propriétaire métier ;
- une URL historique ne doit jamais être utilisée comme clé de relation métier ;
- les statuts doivent être finis, documentés et accompagnés de transitions autorisées ;
- une action administrative sensible doit produire un événement d'audit ;
- la configuration métier ne doit pas contenir de code exécutable ;
- les secrets et paramètres techniques ne sont pas du contenu administrable.

---

# 4. Modules

## 4.1 Modules obligatoires pour la première version exploitable

| Module | Périmètre | Justification |
|---|---|---|
| Accueil | Intentions, zones principales, annonces pertinentes, accès dépôt | Point d'entrée public et commercial |
| Recherche | Filtres essentiels, tri, pagination, états vides | Fonction cœur de découverte |
| Fiche annonce | Informations, médias, annonceur, localisation, contact, signalement | Conversion et actif SEO principal |
| Dépôt d'annonce | Saisie guidée, localisation, caractéristiques, médias, récapitulatif | Acquisition du catalogue |
| Gestion des annonces | Liste, consultation du statut, modification autorisée, retrait, renouvellement | Autonomie annonceur |
| Comptes | Inscription, connexion, récupération, profil, fermeture | Identité et sécurité |
| Favoris | Ajouter, retirer, consulter | Continuité du parcours de recherche |
| Professionnels | Profil, portefeuille, coordonnées et validation | Offre B2B historique à préserver |
| Contacts | Téléphone, WhatsApp, message, protection anti-abus | Valeur commerciale mesurable |
| Signalements | Motif, contexte, suivi administratif | Qualité et confiance |
| Modération | Files, contrôle, décision, motifs et audit | Sécurité du catalogue |
| Référentiels | Géographie et catégories | Cohérence de tous les parcours |
| Contenus | Pages légales et éditoriales validées | Obligations et SEO |
| SEO opérationnel | URL, redirections, canonical, indexabilité, sitemap | Continuité d'acquisition |
| Administration | Rôles, opérations métier et journal | Exploitation sûre |

## 4.2 Modules conditionnels

| Module | Condition d'entrée | Décision actuelle |
|---|---|---|
| Options premium | Catalogue commercial, durée, effet et prix approuvés | À cadrer avant réalisation |
| Paiement en ligne | Fournisseurs actifs, contrat, rapprochement et règles de remboursement validés | À cadrer |
| Paiement manuel | Processus commercial chèque/virement confirmé | À cadrer |
| Connexion Google | Usage démontré et règles de liaison de compte approuvées | Option ultérieure |
| Connexion Facebook | Usage démontré et conformité validée | Option ultérieure |
| Multilingue | Langues cibles, équipe éditoriale et stratégie SEO validées | Non prioritaire |
| Statistiques professionnelles | Définitions des métriques et rétention approuvées | Après le socle de leads |
| Carte avancée | Qualité des coordonnées et coût du fournisseur validés | Après fiabilisation géographique |
| Publicité | Modèle économique confirmé | Hors socle |

## 4.3 Modules exclus

- automobile ;
- adulte ;
- installation de plugins ;
- mise à jour du produit depuis l'administration ;
- éditeur de fichiers ou de templates ;
- firewall IP administré dans l'application ;
- intégrations de paiement sans preuve d'activité ;
- outils génériques du produit Legacy sans propriétaire métier.

---

# 5. Navigation publique

## 5.1 Navigation principale

La navigation publique doit rester courte et orientée vers les intentions :

1. **Louer**
2. **Location meublée**
3. **Acheter**
4. **Professionnels**
5. **Déposer une annonce**
6. **Connexion / Compte**

Les villes, quartiers et types de bien doivent être accessibles dans la recherche, les pages de résultats et le maillage contextuel, sans surcharger le menu principal.

## 5.2 Parcours de recherche

### Entrées possibles

- intention depuis l'accueil ;
- ville ou quartier ;
- type de bien ;
- lien SEO historique valide ;
- profil professionnel ;
- favori ou lien direct d'annonce.

### Filtres essentiels

- intention ;
- localisation ;
- type de bien ;
- prix minimum et maximum ;
- nombre de pièces ou chambres lorsque pertinent ;
- surface lorsque pertinente ;
- meublé lorsque pertinent.

Les équipements secondaires ne doivent être proposés que s'ils sont suffisamment renseignés pour produire un résultat utile.

### Résultats

Chaque carte doit présenter au minimum : image principale, titre normalisé, prix lisible, localisation, type de bien, caractéristiques principales, statut professionnel éventuel et fraîcheur de l'annonce.

Le tri par défaut doit privilégier pertinence, qualité et fraîcheur selon une règle documentée. Une option payante ne doit jamais masquer son influence.

## 5.3 Fiche annonce

La fiche doit contenir :

- titre et intention ;
- prix et unité applicables ;
- galerie d'images ;
- caractéristiques structurées ;
- description ;
- ville et quartier ;
- disponibilité ;
- identité ou statut de l'annonceur ;
- moyens de contact autorisés ;
- date de publication ou de mise à jour utile ;
- signalement ;
- annonces similaires pertinentes ;
- éléments SEO et données de partage validés.

Les coordonnées exactes sensibles ne doivent pas être publiées par défaut.

## 5.4 États publics

- **Active :** visible et contactable.
- **Indisponible :** retirée des listes ; page éventuellement conservée temporairement selon la politique SEO.
- **Expirée :** non contactable, renouvelable par l'annonceur.
- **Supprimée :** inaccessible ou réponse adaptée selon valeur SEO et obligation de conservation.
- **Suspendue :** non publique.

Le comportement HTTP et SEO de chaque état doit être défini avant la migration.

## 5.5 Pied de page

Le pied de page doit donner accès à :

- à propos ;
- contact ;
- conditions d'utilisation ;
- politique de confidentialité ;
- règles de publication ;
- professionnels ;
- villes et catégories stratégiques validées ;
- gestion des préférences de confidentialité lorsqu'applicable.

---

# 6. Navigation utilisateur

## 6.1 Espace particulier

Navigation recommandée :

- Tableau de bord
- Mes annonces
- Déposer une annonce
- Mes favoris
- Messages ou contacts, si la messagerie est validée
- Mon profil
- Sécurité et confidentialité
- Déconnexion

## 6.2 Tableau de bord

Le tableau de bord doit montrer uniquement des informations actionnables :

- annonces actives, en attente, refusées et expirées ;
- demandes de correction ;
- actions de renouvellement ;
- alertes de compte ;
- options ou paiements en cours, si ces modules sont validés.

## 6.3 Cycle utilisateur d'une annonce

États métier proposés, soumis à validation :

1. brouillon ;
2. soumise ;
3. à corriger ;
4. en modération ;
5. publiée ;
6. suspendue ;
7. expirée ;
8. retirée par l'annonceur ;
9. archivée.

Chaque transition doit préciser : acteur autorisé, motif éventuel, effets publics, notification et trace d'audit.

## 6.4 Espace professionnel

Il reprend le socle utilisateur et ajoute :

- profil public de l'organisation ;
- informations de vérification ;
- équipe et délégation, seulement si le besoin est confirmé ;
- portefeuille d'annonces ;
- suivi agrégé des contacts ;
- offre commerciale et facturation, si validées.

La boutique Legacy devient un **profil professionnel**. Le concept métier est conservé, pas son implémentation.

## 6.5 Règles d'expérience

- le statut d'une annonce doit toujours être accompagné d'une explication ;
- une action destructive demande confirmation ;
- la suppression du compte distingue désactivation, obligations de conservation et anonymisation ;
- aucun mot de passe, secret ou identifiant technique n'est affiché ;
- l'utilisateur peut consulter et modifier ses consentements ;
- les erreurs doivent être compréhensibles et ne pas révéler de détails techniques.

---

# 7. Administration

## 7.1 Espaces administratifs

### Pilotage

Vue synthétique : volumes à traiter, alertes, anomalies de migration ou d'exploitation, paiements à rapprocher et activité récente.

### Annonces et modération

- files par statut ;
- recherche multicritère ;
- consultation complète ;
- validation, refus, correction, suspension et archivage ;
- motifs normalisés et commentaire interne ;
- historique des décisions ;
- gestion contrôlée des médias ;
- traitement des signalements.

### Utilisateurs et professionnels

- recherche et consultation ;
- statut du compte ;
- vérification professionnelle ;
- fusion ou traitement encadré des doublons ;
- suspension ;
- historique des actions ;
- aucune visualisation de secrets d'authentification.

### Référentiels

- catégories immobilières ;
- intentions ;
- types de bien ;
- villes, quartiers et aliases ;
- contrôle des impacts avant modification ou fusion.

### Contenus et SEO

- pages institutionnelles et guides ;
- métadonnées ;
- prévisualisation ;
- statut brouillon/publié ;
- historique des versions ;
- redirections ;
- contrôle de l'indexabilité ;
- état des sitemaps.

### Monétisation

- offres et tarifs approuvés ;
- commandes ;
- paiements ;
- rapprochement ;
- activation d'options ;
- journal des événements financiers.

Cet espace ne doit exister que pour les modèles commerciaux validés.

### Exploitation et audit

- comptes administratifs ;
- rôles et permissions ;
- journal d'audit ;
- paramètres métier explicitement autorisés ;
- aucun éditeur de code, plugin, template ou secret.

## 7.2 Rôles proposés

| Rôle | Responsabilité principale |
|---|---|
| Super administrateur | Habilitations, paramètres métier sensibles, supervision |
| Modérateur | Annonces, médias et signalements |
| Commercial | Professionnels, offres et suivi commercial autorisé |
| Éditeur contenu/SEO | Pages, métadonnées, redirections et sitemaps |
| Finance | Paiements et rapprochement, si module validé |
| Lecture/audit | Consultation sans mutation |

Le cumul de rôles doit être explicite. Les permissions doivent être définies action par action, pas seulement page par page.

## 7.3 Actions sensibles

Sont notamment sensibles : suppression, fusion, suspension, changement de rôle, publication massive, modification d'URL, remboursement, activation manuelle d'une option et export de données.

Elles exigent selon leur criticité :

- authentification renforcée ;
- permission spécifique ;
- justification ;
- confirmation ;
- journal avant/après ;
- validation secondaire pour les opérations les plus graves.

---

# 8. SEO

## 8.1 Objectifs SEO

- préserver l'autorité des URL historiques légitimes ;
- concentrer l'indexation sur des pages utiles et alimentées ;
- éviter les variantes dupliquées ;
- rendre le catalogue compréhensible par intention, géographie et type de bien ;
- maîtriser le cycle SEO des annonces expirées ou retirées.

## 8.2 Patrimoine à préserver

- URL historiques d'annonces ;
- slugs de villes, quartiers et catégories ;
- pages institutionnelles et guides ayant une valeur validée ;
- redirections 301 existantes ;
- métadonnées éditoriales utiles ;
- structure de maillage interne pertinente ;
- segmentation des sitemaps ;
- règles canonical et noindex ayant une justification.

## 8.3 Gouvernance des URL

Un registre versionné doit associer pour chaque URL Legacy :

- URL source ;
- famille ;
- entité ou intention associée ;
- URL cible ;
- statut de migration ;
- réponse attendue ;
- justification ;
- date de validation ;
- propriétaire de la décision.

Aucune règle générique ne doit transformer automatiquement toute URL inconnue en page de résultats valide.

## 8.4 Types de pages indexables

### Indexables par défaut

- accueil ;
- annonce active et de qualité suffisante ;
- page de ville validée ;
- page de quartier validée ;
- catégorie ou intention avec offre suffisante ;
- profil professionnel validé ;
- page éditoriale unique et utile.

### Non indexables par défaut

- connexion, inscription et compte ;
- dépôt et formulaires ;
- résultats avec paramètres arbitraires ;
- combinaisons sans inventaire suffisant ;
- brouillons et annonces en modération ;
- pages techniques ;
- contenus dupliqués.

## 8.5 Seuils de qualité

Avant de rendre indexable une combinaison géographique ou thématique, il faut valider :

- inventaire minimum ;
- contenu distinctif ;
- stabilité de la demande ;
- maillage cohérent ;
- absence de duplication avec une page existante.

Les seuils chiffrés restent une question ouverte à trancher avec les données Search Console et catalogue.

## 8.6 Annonces retirées

La politique doit distinguer :

- retrait temporaire ;
- expiration renouvelable ;
- bien définitivement indisponible ;
- suppression pour non-conformité ;
- suppression légale.

Le choix entre maintien temporaire, redirection, statut 404 ou 410 dépend de la cause et de la valeur SEO. Il ne doit pas être uniforme sans analyse.

## 8.7 Sitemaps et contrôle

- un index de sitemap canonique ;
- segmentation par type de contenu ;
- uniquement des URL canoniques et indexables ;
- date de modification fiable ;
- exclusion immédiate des contenus non publiables ;
- contrôle régulier des divergences entre sitemap et catalogue.

---

# 9. Sécurité

## 9.1 Principes

- moindre privilège ;
- refus par défaut ;
- séparation des responsabilités ;
- validation côté système de toute entrée ;
- traçabilité des actions sensibles ;
- minimisation des données ;
- secrets hors contenu administrable ;
- dépendances et composants non exposés comme ressources publiques ;
- aucun upload ne doit devenir exécutable.

## 9.2 Identité

- mots de passe stockés selon une méthode moderne au moment de l'implémentation ;
- limitation et surveillance des tentatives ;
- renouvellement de session après authentification ;
- récupération de compte limitée dans le temps et à usage unique ;
- invalidation de toutes les sessions Legacy au basculement ;
- authentification renforcée pour les administrateurs ;
- procédure contrôlée de récupération d'un compte administratif.

## 9.3 Autorisations

- un utilisateur ne gère que ses propres ressources, sauf délégation validée ;
- un professionnel ne voit pas les données d'un autre professionnel ;
- une action administrative vérifie une permission précise ;
- les actions de lecture sensibles sont également auditables lorsque nécessaire ;
- aucune autorisation ne dépend uniquement de l'affichage ou du nom d'une page.

## 9.4 Entrées, contenus et médias

- formats, tailles et nombres de fichiers limités ;
- vérification du contenu réel des images ;
- noms de stockage indépendants du nom fourni ;
- suppression des métadonnées sensibles selon politique ;
- contenus textuels neutralisés contre l'exécution ;
- protection contre abus, spam et automatisation ;
- aucun upload de plugin, template ou fichier exécutable.

## 9.5 Paiements

Si le paiement est validé :

- l'état commercial ne découle jamais d'un simple retour navigateur ;
- chaque événement fournisseur est authentifié et traçable ;
- un même événement ne peut produire deux activations ;
- les montants et références sont rapprochés ;
- les secrets sont rotatifs et jamais administrés comme contenu ;
- les accès finance sont séparés des accès de modération.

## 9.6 Données personnelles

Un registre doit couvrir : finalité, base de traitement, données, destinataires, durée, suppression, export, anonymisation et preuve de consentement.

Les IP, user agents et traces de leads Legacy ne doivent être migrés que si leur finalité et leur durée de conservation sont validées.

## 9.7 Préparation à la migration

- identifier et renouveler tous les secrets ;
- contrôler les comptes administratifs ;
- définir la stratégie de mots de passe Legacy ;
- ne reprendre aucun token ou cookie ;
- archiver séparément les données historiques nécessaires ;
- documenter les rejets et anomalies de sécurité des données migrées.

---

# 10. Performance

## 10.1 Objectifs d'expérience

La plateforme doit rester utilisable sur mobile, sur connexion instable et avec des appareils modestes. Les objectifs chiffrés définitifs seront validés lors de la conception technique, mais les critères produit suivants sont obligatoires :

- contenu principal visible rapidement ;
- navigation utilisable sans attendre le chargement de composants secondaires ;
- recherche avec retour clair et prévisible ;
- images adaptées à la taille d'affichage ;
- formulaires résistants aux coupures et erreurs ;
- absence de chargements inutiles sur les pages essentielles.

## 10.2 Catalogue et recherche

- filtres limités à des attributs fiables et utiles ;
- pagination stable ;
- pas de calcul répétitif par annonce dans une liste ;
- totaux et tris cohérents ;
- états vides rapides et explicites ;
- requêtes de pages SEO soumises aux mêmes règles que la recherche publique.

## 10.3 Médias

Le Legacy contient près de 5 000 fichiers d'annonces pour environ 356,6 Mo, dont 51 dépassent 1 Mo et 158 appartiennent à des groupes de contenu dupliqué.

La cible fonctionnelle doit prévoir :

- un original contrôlé par image utile ;
- une image principale explicite ;
- des variantes adaptées aux usages ;
- chargement différé hors première vue ;
- dimensions réservées pour éviter les décalages ;
- politique de remplacement et d'invalidation ;
- déduplication avant migration, sans supprimer sur la seule base du hash lorsque les droits ou propriétaires diffèrent.

## 10.4 Cache et fraîcheur

Les informations pouvant être accélérées doivent posséder une règle de fraîcheur et d'invalidation. Une donnée critique — statut d'annonce, paiement, permission ou disponibilité — ne doit pas rester visible sur la seule foi d'un cache périmé.

## 10.5 Mesure

Les parcours à mesurer prioritairement sont : accueil, résultats, fiche annonce, dépôt, authentification, tableau de bord et files de modération.

Les mesures doivent distinguer au minimum mobile et bureau, et porter sur l'expérience réelle autant que sur les tests contrôlés.

---

# 11. Migration

## 11.1 Stratégie

La migration est une transformation contrôlée, pas une copie de tables ou de fichiers.

Chaque ensemble de données doit passer par :

1. inventaire ;
2. qualification ;
3. normalisation ;
4. déduplication ;
5. validation ;
6. transformation ;
7. chargement futur ;
8. rapprochement ;
9. contrôle métier ;
10. décision sur les rejets.

Ce Blueprint ne définit ni outil, ni schéma, ni mécanisme technique de chargement.

## 11.2 Sources de vérité à établir

| Domaine | Source Legacy candidate | Décision préalable |
|---|---|---|
| Annonces | `pas_annonce` | Normaliser états et relations |
| Utilisateurs | `pas_user` | Dédupliquer et décider la stratégie de mot de passe |
| Professionnels | `pas_compte_pro` | Résoudre doublons et orphelins |
| Médias | `pas_image` + fichiers | Rapprocher lignes et fichiers |
| Catégories | `pas_categorie` | Limiter au domaine immobilier |
| Géographie | deux familles de tables | Désigner un référentiel consolidé |
| Pages | `pas_page` | Valider contenu et valeur |
| SEO | `pas_seo`, sitemaps, routes | Consolider avec les données externes |
| Favoris | `pas_favoris` | Nettoyer les orphelins |
| Paiements | `pas_paiement` et fournisseurs | Définir obligations et périmètre |
| Leads | tables `lead_*` | Définir rétention et finalité |

## 11.3 Données à ne pas charger comme données actives

- tables de sauvegarde datées ;
- tables d'audit temporaires ;
- caches ;
- sessions et tokens ;
- `connect_check` ;
- firewall historique ;
- statistiques personnelles sans finalité validée ;
- configuration technique ou secrets ;
- plugins et définitions de code ;
- données automobile ou adulte ;
- moyens de paiement abandonnés.

## 11.4 Identifiants et compatibilité

- conserver une correspondance entre chaque identifiant Legacy utile et l'entité cible future ;
- conserver les aliases publics nécessaires aux anciennes URL ;
- ne pas utiliser le MD5 Legacy comme nouvel identifiant métier principal ;
- ne pas renuméroter sans table de correspondance vérifiable ;
- journaliser toute fusion de comptes ou professionnels.

## 11.5 Géographie

La migration géographique est un chantier préalable obligatoire :

1. comparer régions, départements, villes et quartiers de toutes les versions ;
2. identifier les doublons orthographiques et aliases ;
3. valider la hiérarchie avec un responsable métier local ;
4. rattacher les annonces par identifiant, libellé et contexte ;
5. isoler les cas ambigus pour contrôle manuel ;
6. préserver les slugs historiques comme aliases SEO.

## 11.6 Comptes et professionnels

- comparer e-mail, identité publique, coordonnées et historique ;
- distinguer doublon certain, doublon probable et comptes distincts ;
- ne jamais fusionner automatiquement un cas ambigu ;
- décider si les utilisateurs doivent réinitialiser leur mot de passe ;
- recréer les comptes administratifs selon une liste approuvée ;
- ne migrer aucune session.

## 11.7 Annonces et médias

- définir les annonces éligibles selon statut, âge, qualité et propriétaire ;
- préserver les annonces nécessaires au traitement SEO, même si elles ne sont pas toutes republiées ;
- rapprocher les 8 326 références d'images avec les 4 998 fichiers physiques observés ;
- identifier fichiers manquants, lignes dupliquées, miniatures et orphelins ;
- contrôler manuellement un échantillon représentatif ;
- conserver l'ordre et l'image principale lorsque fiables.

## 11.8 Rapprochement

Chaque exécution de migration devra produire, par domaine :

- nombre source ;
- nombre accepté ;
- nombre nettoyé ;
- nombre fusionné ;
- nombre rejeté ;
- nombre chargé ;
- nombre en erreur ;
- motifs ;
- résultats des contrôles d'intégrité ;
- validation métier.

## 11.9 Basculement futur

Le plan détaillé devra inclure : répétitions à blanc, gel des écritures, export final, delta, validation des volumes, contrôle des URL, rotation des secrets, invalidation des sessions et plan de retour maîtrisé.

---

# 12. Roadmap

La roadmap décrit des résultats attendus, sans imposer de technologie.

## Phase 0 — Gouvernance et décisions

### Résultats

- validation du Blueprint ;
- propriétaires métier désignés ;
- fonctionnalités obligatoires et conditionnelles arbitrées ;
- moyens de paiement actifs confirmés ;
- politique de données personnelles approuvée ;
- données SEO externes réunies.

### Condition de sortie

Toutes les questions bloquantes de niveau 1 disposent d'une décision écrite.

## Phase 1 — Référentiels et contrats métier

### Résultats

- dictionnaire fonctionnel ;
- cycle de vie d'une annonce ;
- taxonomie immobilière ;
- référentiel géographique consolidé ;
- rôles et permissions ;
- catalogue des URL et redirections ;
- règles de migration par donnée.

### Condition de sortie

Les équipes métier peuvent expliquer chaque entité, état, relation et exception sans référence au code Legacy.

## Phase 2 — Conception des parcours

### Résultats

- parcours publics ;
- parcours particuliers ;
- parcours professionnels ;
- parcours de modération ;
- parcours contenus/SEO ;
- cas d'erreur et accessibilité ;
- critères de mesure.

### Condition de sortie

Chaque fonctionnalité du socle possède un parcours validé et des critères d'acceptation.

## Phase 3 — Préparation de la migration

### Résultats

- inventaire détaillé des données ;
- règles de nettoyage ;
- registre des correspondances ;
- échantillons validés ;
- rapport des anomalies ;
- stratégie de répétition et rapprochement.

### Condition de sortie

Une migration à blanc peut être évaluée objectivement, sans décision improvisée.

## Phase 4 — Réalisation du socle produit

Cette phase est volontairement décrite sans choix technique.

### Ordre fonctionnel recommandé

1. identité et référentiels ;
2. catalogue et médias ;
3. recherche et fiches ;
4. dépôt et espace utilisateur ;
5. modération et administration ;
6. contenus et SEO ;
7. professionnels et leads ;
8. monétisation validée.

### Condition de sortie

Le socle satisfait les critères fonctionnels, de sécurité, de performance, de migration et de continuité SEO.

## Phase 5 — Recette, migration et lancement

### Résultats

- recettes métier et sécurité ;
- validation de performance ;
- migrations à blanc rapprochées ;
- contrôle exhaustif des redirections prioritaires ;
- préparation support et exploitation ;
- lancement contrôlé et suivi renforcé.

## Phase 6 — Améliorations post-lancement

- optimisation de la pertinence ;
- statistiques professionnelles validées ;
- fonctionnalités conditionnelles approuvées ;
- enrichissement éditorial ;
- amélioration continue fondée sur des mesures réelles.

---

# 13. Critères d'acceptation

## 13.1 Blueprint

- les 15 sections demandées sont présentes ;
- chaque module est justifié ;
- les inclusions, exclusions et éléments conditionnels sont explicites ;
- aucune décision de framework, API ou base n'est contenue dans le document ;
- le Blueprint ne reproduit pas l'arborescence Legacy.

## 13.2 Produit public

- un visiteur peut rechercher par intention et localisation ;
- une liste ne présente que des annonces publiables ;
- une fiche fournit les informations essentielles et un contact autorisé ;
- les états indisponibles ont un comportement public et SEO défini ;
- les parcours essentiels sont utilisables sur mobile et accessibles au clavier ;
- les erreurs ne divulguent aucune information technique ou sensible.

## 13.3 Utilisateur

- inscription, connexion et récupération de compte sont compréhensibles ;
- un utilisateur ne peut agir que sur ses ressources ;
- le dépôt peut être repris avant soumission si le parcours le prévoit ;
- le statut et le motif de décision d'une annonce sont visibles ;
- modification, retrait et renouvellement respectent le cycle validé ;
- les favoris ne créent aucune relation orpheline.

## 13.4 Professionnel

- un profil ne devient public qu'après les contrôles requis ;
- son portefeuille ne contient que ses annonces publiables ;
- les doublons Legacy sont résolus ou isolés ;
- les métriques affichées ont une définition et une période explicites.

## 13.5 Administration

- chaque action est soumise à une permission précise ;
- chaque action sensible est auditée ;
- aucun administrateur ne peut charger ou modifier du code ;
- les décisions de modération possèdent un motif ;
- les référentiels ne peuvent être modifiés sans contrôle d'impact ;
- les secrets ne sont jamais affichés comme paramètres métier.

## 13.6 SEO

- chaque URL prioritaire possède une cible validée ;
- aucune chaîne ou boucle de redirection n'est acceptée ;
- les sitemaps ne contiennent que des URL canoniques indexables ;
- les pages de combinaison insuffisantes ne sont pas indexées ;
- les annonces migrées conservent leur continuité publique ou une réponse décidée ;
- les erreurs 404 et soft-404 sont suivies au lancement.

## 13.7 Sécurité et confidentialité

- aucune session Legacy n'est valide après basculement ;
- les comptes administratifs sont recréés et approuvés ;
- les actions sensibles résistent aux requêtes non autorisées et répétées ;
- les uploads ne peuvent produire de contenu exécutable ;
- les données collectées possèdent une finalité et une rétention ;
- les secrets Legacy concernés sont renouvelés ;
- les événements de paiement, s'ils existent, sont authentifiés et idempotents.

## 13.8 Performance

- les budgets chiffrés sont approuvés avant réalisation ;
- les images sont adaptées au contexte d'affichage ;
- les listes ne déclenchent pas de traitement répétitif par élément ;
- les parcours essentiels sont mesurés sur mobile ;
- la fraîcheur des statuts critiques n'est pas compromise par le cache.

## 13.9 Migration

- chaque domaine produit un rapport de rapprochement ;
- aucune donnée active ne provient d'une table de sauvegarde sans décision ;
- aucun média migré n'est sans propriétaire ;
- les cas ambigus sont isolés, jamais résolus silencieusement ;
- les correspondances Legacy sont conservées ;
- les volumes et échantillons sont validés par le métier ;
- le plan de retour et les contrôles de lancement sont approuvés.

---

# 14. Risques

| Risque | Probabilité | Impact | Réponse prévue | Propriétaire à nommer |
|---|---|---|---|---|
| Mauvais référentiel géographique | Élevée | Critique | Consolidation et validation locale avant conception détaillée | Produit/données |
| Perte de trafic SEO | Élevée | Critique | Registre d'URL, données Search Console, tests de redirection | SEO |
| Mauvaise interprétation des statuts d'annonce | Élevée | Critique | Machine à états validée et échantillonnage | Produit/modération |
| Fusion incorrecte de professionnels | Élevée | Élevé | Classes de confiance et contrôle manuel | Commercial/données |
| Images manquantes ou mal rattachées | Élevée | Élevé | Rapprochement base/fichiers et rapport d'anomalies | Données/contenu |
| Reprise de secrets Legacy | Moyenne | Critique | Inventaire, non-migration et rotation | Sécurité/exploitation |
| Reprise de mots de passe non fiables | Élevée | Élevé | Politique de réinitialisation ou transition approuvée | Sécurité/produit |
| Périmètre commercial non stabilisé | Élevée | Élevé | Paiements et premium restent conditionnels | Direction/commercial |
| Reproduction implicite du Legacy | Moyenne | Élevé | Revue de chaque décision contre les principes directeurs | Gouvernance |
| Surproduction de pages SEO | Élevée | Élevé | Seuils de qualité et gouvernance d'indexation | SEO/produit |
| Collecte excessive de données de leads | Moyenne | Élevé | Finalité, minimisation et rétention | Juridique/produit |
| Administration trop permissive | Moyenne | Critique | Permissions par action et audit | Sécurité/opérations |
| Performance mobile insuffisante | Moyenne | Élevé | Budgets, mesure réelle et politique médias | Produit/qualité |
| Données de sauvegarde prises pour source active | Moyenne | Critique | Catalogue de sources et contrôles de provenance | Données |
| Décisions tardives bloquant la réalisation | Élevée | Élevé | Jalons de validation et propriétaires nommés | Direction produit |

## 14.1 Risques bloquants avant réalisation

Les cinq risques suivants interdisent une réalisation maîtrisée tant qu'ils ne sont pas arbitrés :

1. géographie cible ;
2. cycle de vie d'une annonce ;
3. stratégie de continuité SEO ;
4. stratégie d'identité et de mots de passe Legacy ;
5. catalogue commercial et paiements actifs.

---

# 15. Questions ouvertes

## Niveau 1 — Bloquantes

1. Quel référentiel validé définit les villes et quartiers du Sénégal couverts par APPART.SN ?
2. Quels sont exactement les états d'annonce actuels, leurs significations et transitions autorisées ?
3. Quelles annonces expirées ou retirées doivent rester accessibles pour le SEO, et pendant combien de temps ?
4. Les mots de passe Legacy sont-ils repris avec transition contrôlée ou une réinitialisation globale est-elle imposée ?
5. Quels moyens de paiement disposent encore d'un contrat et d'un usage actif ?
6. Quelles options premium sont commercialisées, avec quel effet, quelle durée et quel prix ?
7. Qui arbitre les 48 doublons et 66 orphelins professionnels signalés par l'audit ?
8. Quelles données Search Console, analytics et journaux 404 sont disponibles ?

## Niveau 2 — Nécessaires avant conception détaillée

9. Une annonce peut-elle être déposée sans compte préalable ?
10. Quels types de biens sont officiellement couverts ?
11. Quelles caractéristiques sont obligatoires par type de bien ?
12. Comment les locations meublées et saisonnières sont-elles distinguées ?
13. Faut-il afficher un prix lorsque l'annonceur choisit « sur demande » ?
14. Quelle précision géographique peut être montrée publiquement ?
15. Les professionnels doivent-ils être vérifiés, et avec quelles preuves ?
16. Un professionnel peut-il avoir plusieurs collaborateurs ?
17. La messagerie interne est-elle indispensable ou un formulaire relayé suffit-il ?
18. Quelles notifications sont nécessaires et par quels canaux ?
19. Quels motifs de modération et de signalement doivent être normalisés ?
20. Quelle durée de publication et quelles règles de renouvellement appliquer ?
21. Quels administrateurs actuels doivent conserver un accès et avec quel rôle ?
22. Quelles pages éditoriales et légales sont encore valides ?

## Niveau 3 — Arbitrages d'évolution

23. Le multilingue est-il un objectif réel et, si oui, pour quelles langues ?
24. Une carte interactive apporte-t-elle suffisamment de valeur par rapport à son coût et à la qualité des coordonnées ?
25. Les alertes de recherche font-elles partie d'une version ultérieure ?
26. Les statistiques professionnelles doivent-elles montrer vues, contacts ou seulement des agrégats vérifiables ?
27. La publicité externe reste-t-elle un axe de revenus ?
28. Quels seuils rendent une page ville, quartier ou combinaison suffisamment riche pour l'indexation ?
29. Une application mobile distincte est-elle envisagée, sans que cela modifie le périmètre actuel ?
30. Quelle politique de support et de traitement des litiges doit accompagner le lancement ?

---

# Synthèse du Blueprint

Le nouveau APPART.SN est défini comme une plateforme immobilière spécialisée, centrée sur la qualité du catalogue, la découverte locale, la mise en relation et la confiance. Le patrimoine Legacy utile sera migré après qualification, mais aucune de ses structures techniques n'est retenue comme modèle.

Le socle fonctionnel comprend : identité, annonces, géographie, médias, recherche, fiches, dépôt, comptes, professionnels, contacts, modération, contenus, SEO et administration. La monétisation, les paiements, le multilingue, les cartes avancées et la publicité restent conditionnels à des décisions métier documentées.

## Décisions majeures

1. APPART.SN reste exclusivement immobilier.
2. Le Legacy est une source de règles et de données, jamais un modèle d'implémentation.
3. La géographie doit être consolidée avant toute conception détaillée.
4. Le cycle de vie des annonces doit être formalisé.
5. Les URL et aliases Legacy sont un patrimoine de migration à part entière.
6. La boutique Legacy devient un profil professionnel gouverné.
7. L'administration ne permet aucune modification de code, plugin ou template.
8. Les paiements et options premium ne sont réalisés qu'après validation commerciale.
9. Les sessions, tokens et secrets Legacy ne sont pas migrés.
10. Toute migration doit être rapprochée, justifiée et validée par domaine.

## Confirmation de périmètre

Ce document ne contient aucun code, aucun choix Laravel, aucun choix de framework, aucune définition d'API, aucun schéma de base de données et aucune création de projet applicatif.

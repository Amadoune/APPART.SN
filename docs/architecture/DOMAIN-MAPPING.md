# APPART.SN REBUILD 2026 — Domain Mapping

## Statut du document

- **Sprint :** 4 — Document 2
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition soumise à validation métier
- **Nature :** modèle conceptuel des domaines

---

# 1. Objet

Ce document transforme les treize domaines de l’Architecture Blueprint en une cartographie conceptuelle destinée à préparer les décisions futures. Il attribue les responsabilités, concepts, invariants et interactions sans décrire leur réalisation.

Il ne remplace aucune politique normative. En cas de contradiction, la règle métier du document spécialisé prévaut et l’écart devient une question ouverte. Le Legacy n’est jamais une référence de conception ; il n’est qu’une source candidate à qualifier dans le domaine temporaire Migration Legacy.

---

# 2. Principes de mapping

1. **Propriétaire unique :** tout concept et tout invariant ont un seul domaine propriétaire.
2. **Autorité locale :** seul le propriétaire décide et modifie son concept ; les autres domaines le référencent ou réagissent à un fait publié.
3. **Intention et fait distincts :** une commande exprime une intention adressée au propriétaire ; un événement décrit un fait déjà décidé.
4. **Référence minimale :** un domaine ne copie que les informations indispensables à son usage et ne transforme jamais cette copie en source de vérité.
5. **Agrégat candidat :** un Aggregate Root candidat représente une frontière possible de décision cohérente, pas une structure physique définitive.
6. **Entité candidate :** une Entité candidate possède une identité métier et un cycle propre à l’intérieur de son domaine.
7. **Value Object candidat :** un Value Object candidat exprime une valeur métier définie par son sens et ses règles, sans identité autonome.
8. **Dépendances dirigées :** les dépendances suivent l’autorité métier ; les lectures dérivées ne commandent jamais leur source.
9. **Cohérence proportionnée :** les invariants critiques sont immédiats ; recherche, SEO, notifications et statistiques peuvent être mis à jour avec un décalage maîtrisé.
10. **Migration temporaire :** Migration Legacy traduit et propose ; chaque domaine cible valide et devient seul propriétaire après acceptation.
11. **Permissions transverses :** toute commande sensible est soumise aux droits métier, à la séparation des responsabilités et, lorsque requis, au principe des quatre yeux.
12. **Audit sans pouvoir métier :** la journalisation constate les décisions ; elle ne les autorise ni ne les remplace.

---

# 3. Cartographie des 13 domaines

## 3.1 Identité et accès

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Établir qui agit, gérer le compte, le profil personnel, les consentements, les rôles, les habilitations et les mandats. |
| **Propriétaire métier** | Responsable produit chargé des comptes et de la confiance, avec validation sécurité et protection des données. |
| **Concepts principaux** | Compte, identité, profil personnel, rôle, habilitation, consentement, mandat de représentation, récupération d’accès. |
| **Candidats Aggregate Roots** | Compte ; Mandat de représentation. |
| **Candidats Entités** | Profil personnel, consentement daté, attribution de rôle, moyen de récupération. |
| **Candidats Value Objects** | Identifiant de compte, coordonnées vérifiées, portée d’habilitation, période de mandat, statut de compte. |
| **Événements métier consommés** | Professionnel validé ou suspendu ; compte candidat qualifié par Migration ; décision administrative d’attribution sensible approuvée. |
| **Événements métier produits** | Compte créé, identité vérifiée, consentement accordé ou retiré, rôle attribué ou retiré, mandat activé ou révoqué, compte suspendu ou fermé. |
| **Commandes conceptuelles** | Créer un compte, vérifier une identité, modifier son profil, recueillir ou retirer un consentement, attribuer un rôle, révoquer un mandat, suspendre un compte. |
| **Requêtes conceptuelles** | Consulter son profil, vérifier la capacité d’un acteur à accomplir une action, connaître les représentants actifs d’un professionnel, consulter les consentements applicables. |
| **Politiques** | Refus par défaut, moindre privilège, preuve de consentement, séparation des rôles, récupération d’accès contrôlée, quatre yeux pour les privilèges critiques. |
| **Services de domaine éventuels** | Évaluation d’habilitation lorsque l’action combine rôle, propriété, mandat, état de la ressource et conflit d’intérêts. |
| **Dépendances autorisées** | Administration et audit pour les attributions approuvées ; Professionnels pour le mandat ; Migration Legacy uniquement durant la reprise. |
| **Dépendances interdites** | Décider la publication d’une annonce, une modération ou un paiement ; dépendre du Legacy après clôture ; exposer des secrets aux autres domaines. |
| **Invariants majeurs** | Une action a un acteur identifiable ; un rôle ne vaut que dans sa portée ; un mandat expiré n’autorise rien ; la fermeture d’un compte ne détruit pas les preuves légalement nécessaires. |
| **Risques de conception** | Confondre compte et professionnel, centraliser toutes les règles métier dans les rôles, multiplier les statuts, conserver des consentements ambigus. |

## 3.2 Professionnels

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Porter l’identité d’une organisation, sa vérification, ses représentants, son profil public et son portefeuille référencé. |
| **Propriétaire métier** | Responsable de l’offre professionnelle. |
| **Concepts principaux** | Professionnel, organisation, établissement éventuel, représentant, vérification, profil public, statut professionnel. |
| **Candidats Aggregate Roots** | Professionnel. |
| **Candidats Entités** | Établissement, représentation, vérification, élément de profil public. |
| **Candidats Value Objects** | Identité légale, coordonnées professionnelles, période de validité, statut de vérification, zone d’activité. |
| **Événements métier consommés** | Compte vérifié, mandat activé ou révoqué, média validé ou retiré, annonce publiée ou retirée, donnée professionnelle qualifiée par Migration. |
| **Événements métier produits** | Professionnel créé, vérification acceptée ou refusée, profil rendu public ou retiré, représentant rattaché ou détaché, professionnel suspendu. |
| **Commandes conceptuelles** | Enregistrer un professionnel, demander sa vérification, rattacher un représentant, modifier le profil public, suspendre ou restaurer le statut public. |
| **Requêtes conceptuelles** | Consulter un profil professionnel, connaître son statut de vérification, lister ses représentants autorisés, consulter son portefeuille public. |
| **Politiques** | Preuve d’existence, représentant habilité, informations publiques minimales et exactes, distinction entre identité de l’organisation et comptes personnels. |
| **Services de domaine éventuels** | Qualification d’un dossier professionnel réunissant plusieurs preuves et règles de validité. |
| **Dépendances autorisées** | Identité et accès, Médias, Catalogue par références d’annonces, Contenus et SEO pour la présence publique, Administration et audit. |
| **Dépendances interdites** | Modifier l’état d’une annonce, décider un paiement, accéder aux informations privées d’autres comptes, reproduire une boutique Legacy. |
| **Invariants majeurs** | Un professionnel a une identité métier unique ; seul un représentant actif agit pour lui ; la suspension retire sa présence publique sans réécrire l’historique. |
| **Risques de conception** | Doublons d’organisations, confusion entre agence et établissement, portefeuille copié au lieu d’être référencé, vérification réduite à un simple statut. |

## 3.3 Catalogue immobilier

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Décrire l’offre immobilière : annonceur, bien proposé, intention, catégorie, prix, caractéristiques et contenu descriptif. |
| **Propriétaire métier** | Responsable produit Annonces et Catalogue. |
| **Concepts principaux** | Annonce, bien annoncé, annonceur, intention, catégorie, prix, caractéristiques, description, disponibilité déclarée. |
| **Candidats Aggregate Roots** | Annonce. |
| **Candidats Entités** | Bien annoncé, caractéristique qualifiée, condition tarifaire éventuelle. |
| **Candidats Value Objects** | Identifiant d’annonce, titre, description, prix, surface, nombre de pièces, intention, référence géographique, référence de galerie. |
| **Événements métier consommés** | Compte ou professionnel habilité, lieu validé ou fusionné, galerie conforme, état d’annonce changé, donnée d’annonce qualifiée par Migration. |
| **Événements métier produits** | Annonce créée, contenu modifié, annonceur rattaché, prix changé, caractéristiques complétées, lieu rattaché, annonce déclarée prête à soumettre. |
| **Commandes conceptuelles** | Créer une annonce, modifier son contenu, rattacher un lieu, fixer un prix, déclarer ses caractéristiques, rattacher une galerie, soumettre la version prête. |
| **Requêtes conceptuelles** | Consulter le détail d’une annonce, vérifier sa complétude, obtenir les annonces d’un propriétaire, comparer sa version courante à la version soumise. |
| **Politiques** | Complétude minimale, propriété de l’annonce, vocabulaire contrôlé, prix cohérent avec l’intention, modification limitée selon l’état. |
| **Services de domaine éventuels** | Évaluation de complétude lorsqu’elle combine catégorie, intention, géographie, contenu et présence d’une galerie conforme. |
| **Dépendances autorisées** | Identité et accès ou Professionnels pour l’annonceur ; Géographie ; Médias par référence ; Cycle de vie pour l’état officiel. |
| **Dépendances interdites** | Prendre une décision d’indexation, utiliser Recherche comme vérité, intégrer une règle de paiement, lire les formes Legacy. |
| **Invariants majeurs** | Une annonce a un propriétaire unique à un instant donné ; catégorie et intention sont valides ; le contenu soumis est identifiable ; l’état officiel n’est pas détenu ici. |
| **Risques de conception** | Agrégat trop large, duplication de l’état, confusion entre bien réel et annonce commerciale, catégories utilisées comme champs libres. |

## 3.4 Cycle de vie des annonces

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Décider et tracer les états officiels, transitions, motifs, échéances, retraits, expirations, renouvellements et archivages. |
| **Propriétaire métier** | Responsable produit Annonces, avec la Modération propriétaire des décisions de contrôle. |
| **Concepts principaux** | État d’annonce, transition, motif, échéance, renouvellement, retrait, expiration, archivage. |
| **Candidats Aggregate Roots** | Cycle de vie d’annonce. |
| **Candidats Entités** | Transition datée, échéance, demande de renouvellement. |
| **Candidats Value Objects** | État officiel, motif de transition, identité d’acteur, date d’effet, période de publication. |
| **Événements métier consommés** | Annonce prête à soumettre, décision de modération rendue, échéance atteinte, demande de retrait, demande de renouvellement, compte ou professionnel suspendu. |
| **Événements métier produits** | Annonce soumise, publiée, suspendue, expirée, retirée, refusée, archivée, renouvelée ou remise en examen. |
| **Commandes conceptuelles** | Soumettre, publier après validation, suspendre, retirer, refuser, expirer, renouveler, archiver, restaurer vers un état autorisé. |
| **Requêtes conceptuelles** | Consulter l’état courant, les transitions autorisées, l’historique, la prochaine échéance et les motifs applicables. |
| **Politiques** | Graphe officiel des transitions, acteur autorisé, motif obligatoire, effets de modification substantielle, expiration et renouvellement, aucune publication implicite. |
| **Services de domaine éventuels** | Décision de transition lorsque l’état courant, l’acteur, le motif, la décision de contrôle et l’échéance doivent être combinés. |
| **Dépendances autorisées** | Catalogue, Modération et signalements, Identité et accès, Administration et audit. |
| **Dépendances interdites** | Laisser Recherche, SEO, Paiement ou une simple lecture changer l’état ; inventer un état hors référentiel officiel. |
| **Invariants majeurs** | Une annonce a exactement un état officiel ; toute transition est autorisée et tracée ; une annonce non publiée n’est pas publiquement active ; payer ne publie jamais. |
| **Risques de conception** | Doubler l’état dans Catalogue, mélanger décision de contrôle et transition, transitions automatiques non auditées, restauration ambiguë. |

## 3.5 Médias

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Gouverner la propriété, les droits, la conformité, les galeries, l’image principale, l’ordre, les usages, variantes fonctionnelles, retraits et archives. |
| **Propriétaire métier** | Responsable qualité des annonces et contenus, avec arbitrage Modération pour les contenus litigieux. |
| **Concepts principaux** | Média, propriétaire média, galerie, usage, image principale, ordre, conformité, droit d’utilisation, variante fonctionnelle, retrait. |
| **Candidats Aggregate Roots** | Galerie ; Média autonome pour les usages éditoriaux ou professionnels. |
| **Candidats Entités** | Média de galerie, contrôle de conformité, droit déclaré, remplacement. |
| **Candidats Value Objects** | Type autorisé, format fonctionnel, dimensions, niveau de qualité, position, texte alternatif, empreinte de duplication. |
| **Événements métier consommés** | Annonce créée ou archivée, professionnel créé ou suspendu, page éditoriale créée, signalement reçu, média candidat qualifié par Migration. |
| **Événements métier produits** | Média ajouté, contrôlé, validé, refusé, remplacé, retiré ou archivé ; image principale choisie ; ordre modifié ; doublon détecté. |
| **Commandes conceptuelles** | Ajouter un média, déclarer ses droits, contrôler sa conformité, choisir l’image principale, réordonner, remplacer, retirer, archiver. |
| **Requêtes conceptuelles** | Consulter une galerie, obtenir le média principal, connaître les usages autorisés, la conformité, le texte alternatif et les variantes attendues. |
| **Politiques** | Propriétaire obligatoire, formats et qualité acceptables, interdictions, duplication, métadonnées personnelles, accessibilité, conservation proportionnée. |
| **Services de domaine éventuels** | Évaluation de conformité et de similarité lorsqu’un ensemble de critères doit produire une décision explicable. |
| **Dépendances autorisées** | Catalogue, Professionnels ou Contenus et SEO comme propriétaires ; Modération ; Administration et audit ; Migration temporaire. |
| **Dépendances interdites** | Décider la publication d’une annonce, conserver un média orphelin sans décision, exposer les choix physiques, reprendre la logique Legacy. |
| **Invariants majeurs** | Tout média a un propriétaire et un usage ; une galerie a au plus une image principale ; un média interdit n’est jamais public ; toute suppression respecte preuves et conservation. |
| **Risques de conception** | Propriété polymorphe incontrôlée, duplication entre galerie et annonce, confusion entre retrait public et effacement, automatisation opaque de la modération. |

## 3.6 Géographie

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Détenir le référentiel géographique officiel, sa hiérarchie, les villes, quartiers, aliases, fusions et rattachements valides. |
| **Propriétaire métier** | Responsable Référentiels, avec validation conjointe Produit et SEO pour les impacts publics. |
| **Concepts principaux** | Lieu, ville, quartier, niveau géographique, alias, rattachement, fusion, statut de validité. |
| **Candidats Aggregate Roots** | Lieu géographique ; Référentiel géographique pour les contraintes de hiérarchie globale. |
| **Candidats Entités** | Alias, rattachement hiérarchique, décision de fusion. |
| **Candidats Value Objects** | Nom officiel, nom normalisé, type de lieu, référence géographique, période de validité, indication de position corroborante. |
| **Événements métier consommés** | Lieu candidat qualifié par Migration, demande de correction issue de Modération ou du SEO. |
| **Événements métier produits** | Ville ou quartier validé, lieu renommé, alias ajouté, lieu fusionné, rattachement corrigé, lieu désactivé. |
| **Commandes conceptuelles** | Créer un lieu validé, rattacher un quartier, ajouter un alias, renommer, fusionner, désactiver, corriger une hiérarchie. |
| **Requêtes conceptuelles** | Résoudre un nom vers un lieu officiel, consulter la hiérarchie, lister les quartiers d’une ville, retrouver les aliases et fusions. |
| **Politiques** | Hiérarchie valide, unicité dans un contexte, alias non concurrent, fusion réversible par preuve, aucun lieu créé uniquement pour le référencement. |
| **Services de domaine éventuels** | Résolution et rapprochement d’un lieu ambigu à partir du nom, de la hiérarchie et d’indices corroborants. |
| **Dépendances autorisées** | Migration Legacy pendant la qualification ; Administration et audit pour les décisions sensibles. |
| **Dépendances interdites** | Déduire sa vérité de Recherche, laisser SEO créer seul un lieu, dépendre durablement des référentiels Legacy. |
| **Invariants majeurs** | Un quartier appartient à une hiérarchie valide ; chaque lieu actif a une identité officielle ; toute fusion conserve la correspondance historique. |
| **Risques de conception** | Doublons homonymes, confusion ville/quartier/zone informelle, fusions destructrices, coordonnées considérées à tort comme vérité unique. |

## 3.7 Recherche et découverte

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Permettre la découverte par critères, listes, tri, pagination, facettes, compteurs, suggestions et vues géographiques. |
| **Propriétaire métier** | Responsable Expérience de recherche. |
| **Concepts principaux** | Critères, résultat, facette, compteur, pertinence, tri, page de résultats, suggestion, fraîcheur. |
| **Candidats Aggregate Roots** | Aucun agrégat décisionnel central ; Vue de découverte candidate comme composition de lecture reconstruisible. |
| **Candidats Entités** | Résultat projeté, facette projetée, suggestion qualifiée. |
| **Candidats Value Objects** | Critères de recherche, ordre de tri, fenêtre de pagination, score de pertinence, budget de fraîcheur. |
| **Événements métier consommés** | Annonce ou contenu modifié, état d’annonce changé, lieu évolué, média principal changé, professionnel rendu public ou suspendu. |
| **Événements métier produits** | Vue de recherche actualisée, annonce retirée des résultats, facette recalculée, retard de fraîcheur détecté. |
| **Commandes conceptuelles** | Reconstruire une vue, actualiser un résultat après un fait métier, invalider une vue devenue incorrecte. |
| **Requêtes conceptuelles** | Rechercher, filtrer, trier, paginer, obtenir des facettes, compter, suggérer, consulter la fraîcheur d’un résultat. |
| **Politiques** | Seules les annonces publiées et éligibles sont visibles ; filtres compréhensibles ; pagination stable ; revalidation avant action critique. |
| **Services de domaine éventuels** | Calcul de pertinence et composition de facettes, sans pouvoir sur les sources métier. |
| **Dépendances autorisées** | Catalogue, Cycle de vie, Géographie, Médias, Professionnels ; Contenus et SEO pour l’éligibilité des pages publiques de découverte. |
| **Dépendances interdites** | Modifier une annonce, décider sa publication, devenir source de vérité d’un état, appliquer une décision financière. |
| **Invariants majeurs** | Une annonce non publiée est exclue ; chaque résultat est rattachable à ses sources ; la vue est reconstruisible ; un retard critique entraîne le retrait prudent. |
| **Risques de conception** | Lecture devenue autorité, visibilité d’une annonce suspendue, facettes générant des pages pauvres, pertinence opaque ou biaisée. |

## 3.8 Contacts et leads

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Enregistrer et acheminer une intention de contact légitime, son canal, sa finalité, son consentement, son attribution et sa conservation proportionnée. |
| **Propriétaire métier** | Responsable Acquisition et relation annonceurs, avec gouvernance protection des données. |
| **Concepts principaux** | Intention de contact, lead, canal, destinataire, source, attribution, consentement, signal anti-abus, conservation. |
| **Candidats Aggregate Roots** | Lead. |
| **Candidats Entités** | Tentative de contact, consentement associé, attribution de source, qualification. |
| **Candidats Value Objects** | Canal, coordonnées de réponse, finalité, référence d’annonce, source de contact, période de conservation. |
| **Événements métier consommés** | Annonce publiée, suspendue, expirée ou retirée ; professionnel suspendu ; consentement retiré ; signal d’abus reçu. |
| **Événements métier produits** | Contact demandé, lead transmis, contact refusé, lead qualifié, consentement retiré, lead anonymisé ou arrivé à échéance. |
| **Commandes conceptuelles** | Demander un contact, transmettre au destinataire, qualifier, marquer un abus, retirer un consentement, clôturer ou anonymiser. |
| **Requêtes conceptuelles** | Consulter ses contacts reçus, vérifier l’éligibilité d’une annonce au contact, mesurer les leads selon une finalité autorisée. |
| **Politiques** | Finalité explicite, minimisation, annonce publiquement active, limitation des abus, accès réservé, durée de conservation décidée. |
| **Services de domaine éventuels** | Évaluation anti-abus et attribution lorsqu’elles exigent plusieurs signaux sans modifier les domaines sources. |
| **Dépendances autorisées** | Catalogue et Cycle de vie pour l’éligibilité, Identité et accès, Professionnels, Administration et audit. |
| **Dépendances interdites** | Maintenir une annonce publiée, transmettre des données personnelles à Recherche ou SEO, conserver indéfiniment les traces Legacy. |
| **Invariants majeurs** | Tout lead a une finalité et un destinataire légitimes ; aucun nouveau contact n’est accepté pour une annonce non publiée ; l’accès est justifié et tracé. |
| **Risques de conception** | Portée fonctionnelle non arbitrée, consentement mal défini, doublons multicanaux, statistiques disproportionnées, exposition de données personnelles. |

## 3.9 Modération et signalements

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Recevoir les signalements, constituer les dossiers, évaluer preuves et motifs, rendre les décisions, traiter les recours et conflits d’intérêts. |
| **Propriétaire métier** | Responsable Confiance et Modération. |
| **Concepts principaux** | Signalement, dossier de contrôle, motif, preuve, décision, recours, conflit d’intérêts, escalade. |
| **Candidats Aggregate Roots** | Dossier de modération ; Signalement lorsqu’il possède un traitement autonome. |
| **Candidats Entités** | Preuve, examen, décision, recours, affectation. |
| **Candidats Value Objects** | Motif, niveau de gravité, issue de décision, identité du décideur, délai de recours. |
| **Événements métier consommés** | Annonce soumise, média ajouté ou signalé, signalement déposé, recours demandé, compte ou professionnel suspendu. |
| **Événements métier produits** | Dossier ouvert, contrôle affecté, décision rendue, publication approuvée, refus ou suspension demandé, recours accepté ou rejeté, conflit déclaré. |
| **Commandes conceptuelles** | Déposer un signalement, ouvrir un dossier, affecter un examen, ajouter une preuve, rendre une décision, escalader, demander ou juger un recours. |
| **Requêtes conceptuelles** | Consulter une file autorisée, un dossier, ses preuves, décisions, délais et conflits d’intérêts. |
| **Politiques** | Impartialité, motif explicite, preuve conservée, séparation des responsabilités, quatre yeux pour cas sensibles, droit au recours défini. |
| **Services de domaine éventuels** | Qualification de gravité et résolution de conflits entre plusieurs signalements ou preuves. |
| **Dépendances autorisées** | Catalogue et Médias en consultation ; Cycle de vie par intention explicite ; Identité et accès ; Administration et audit. |
| **Dépendances interdites** | Réécrire silencieusement l’annonce, modifier une offre commerciale, supprimer une preuve, accorder un privilège au Commercial. |
| **Invariants majeurs** | Toute décision a un motif, un décideur habilité et une trace ; l’examinateur n’est pas en conflit ; la décision de modération précède la transition correspondante. |
| **Risques de conception** | Confondre décision et changement d’état, files sans priorité explicite, preuves personnelles surconservées, Super Administrateur utilisé comme exception permanente. |

## 3.10 Contenus et SEO

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Gouverner les pages éditoriales et guides, l’éligibilité à l’indexation, les URL de référence et historiques, redirections, maillage, fils d’Ariane et sitemaps. |
| **Propriétaire métier** | Responsable SEO / Contenu, sous autorité des règles produit. |
| **Concepts principaux** | Page, guide, intention de page, décision d’indexation, URL de référence, URL historique, redirection, maillage, fil d’Ariane, sitemap. |
| **Candidats Aggregate Roots** | Page éditoriale ; Guide ; Patrimoine d’URL. |
| **Candidats Entités** | Version de contenu, redirection, entrée d’URL historique, décision d’éligibilité, lien éditorial. |
| **Candidats Value Objects** | Type de page, statut éditorial, motif de non-indexation, URL de référence, cible de redirection, seuil de qualité. |
| **Événements métier consommés** | Annonce publiée ou sortie de publication, lieu validé ou fusionné, catégorie changée, professionnel rendu public, média principal validé, contenu migré qualifié. |
| **Événements métier produits** | Page publiée ou retirée, indexation autorisée ou refusée, URL de référence attribuée, redirection décidée, sitemap actualisé, page pauvre détectée. |
| **Commandes conceptuelles** | Créer ou réviser une page, publier un contenu, évaluer l’indexabilité, attribuer une URL de référence, enregistrer une URL historique, décider une redirection, retirer une page pauvre. |
| **Requêtes conceptuelles** | Résoudre une URL, consulter l’éligibilité d’une page, obtenir le fil d’Ariane, le maillage, les pages d’un sitemap et les URL historiques. |
| **Politiques** | Une annonce non publiée n’est jamais indexée ; une URL historique est un patrimoine ; aucune page pauvre créée pour le seul référencement ; référence unique et redirection justifiée. |
| **Services de domaine éventuels** | Évaluation de qualité et résolution d’URL lorsque plusieurs signaux normatifs doivent être combinés. |
| **Dépendances autorisées** | Catalogue, Cycle de vie, Géographie, Professionnels et Médias en lecture ou par événements ; Administration et audit ; Migration temporaire. |
| **Dépendances interdites** | Modifier ou publier une annonce, créer un lieu, devenir source de vérité de Recherche, imposer une règle au métier. |
| **Invariants majeurs** | Une ressource publique a au plus une URL de référence ; aucune annonce non publiée n’est indexable ; toute URL historique a une décision ; une page indexable satisfait la qualité minimale. |
| **Risques de conception** | Confusion entre visibilité publique et indexation, redirections en chaîne, duplication des règles géographiques, facettes pauvres, patrimoine historique incomplet. |

## 3.11 Monétisation et paiements

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Gouverner offres, prix, commandes, paiements, rapprochements, remboursements et droits commerciaux sans contrôler la publication. |
| **Propriétaire métier** | Finance pour les décisions financières ; Direction commerciale pour le catalogue d’offres, avec séparation formelle. |
| **Concepts principaux** | Offre commerciale, tarif, commande, paiement, remboursement, rapprochement, droit commercial, bénéficiaire. |
| **Candidats Aggregate Roots** | Offre commerciale ; Commande ; Paiement. |
| **Candidats Entités** | Ligne de commande, tentative de paiement, remboursement, droit accordé, rapprochement. |
| **Candidats Value Objects** | Montant, devise, période tarifaire, statut financier, référence de transaction, motif de remboursement. |
| **Événements métier consommés** | Professionnel ou compte validé, commande demandée, confirmation financière reçue, annulation autorisée, paiement historique qualifié par Migration. |
| **Événements métier produits** | Offre publiée commercialement, commande créée, paiement confirmé ou échoué, remboursement décidé, rapprochement constaté, droit commercial accordé ou expiré. |
| **Commandes conceptuelles** | Créer ou modifier une offre, passer une commande, confirmer un paiement par preuve fiable, rapprocher, rembourser, accorder ou retirer un droit commercial. |
| **Requêtes conceptuelles** | Consulter les offres, une commande, un paiement, les droits actifs, les écarts de rapprochement et l’historique financier autorisé. |
| **Politiques** | Séparation Commercial/Finance, preuve financière fiable, montant immuable après engagement, remboursement motivé, quatre yeux selon seuil, paiement sans effet de publication. |
| **Services de domaine éventuels** | Calcul tarifaire, admissibilité à une offre et rapprochement de preuves financières. |
| **Dépendances autorisées** | Identité et accès, Professionnels, Catalogue uniquement pour la cible d’un avantage, Administration et audit, Migration temporaire. |
| **Dépendances interdites** | Publier ou modérer une annonce, contourner une suspension, laisser Modération modifier une offre, considérer un retour utilisateur comme preuve financière. |
| **Invariants majeurs** | Toute somme est justifiée ; une confirmation est idempotente conceptuellement ; Finance ne publie jamais ; un droit commercial ne vaut pas validation métier. |
| **Risques de conception** | Couplage paiement-publication, statut financier ambigu, doublons de confirmation, séparation Commercial/Finance insuffisante, périmètre P2 prématuré. |

## 3.12 Administration et audit

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Coordonner les actions internes autorisées, la supervision, les validations à quatre yeux, le journal d’audit et les paramètres métier gouvernés. |
| **Propriétaire métier** | Direction des opérations et gouvernance, avec propriétaires spécialisés pour chaque paramètre. |
| **Concepts principaux** | Action administrative, demande d’approbation, approbation, entrée d’audit, consultation sensible, paramètre métier, motif d’exception. |
| **Candidats Aggregate Roots** | Demande d’approbation ; Journal d’une action sensible ; Paramètre métier gouverné. |
| **Candidats Entités** | Approbation, acteur impliqué, preuve d’action, version de paramètre. |
| **Candidats Value Objects** | Type d’action, portée, motif, résultat, horodatage métier, niveau de sensibilité, règle de quatre yeux. |
| **Événements métier consommés** | Actions et décisions sensibles de tous les domaines ; demande de privilège ; anomalie de supervision. |
| **Événements métier produits** | Action auditée, approbation accordée ou refusée, consultation sensible enregistrée, paramètre métier modifié, anomalie signalée. |
| **Commandes conceptuelles** | Demander une action sensible, approuver ou refuser, consulter une trace autorisée, modifier un paramètre avec gouvernance, exporter une preuve autorisée. |
| **Requêtes conceptuelles** | Consulter les actions, approbations en attente, anomalies, historique d’un paramètre et activité d’un acteur selon ses droits. |
| **Politiques** | Traçabilité obligatoire, quatre yeux, auteur distinct de l’approbateur, accès proportionné, conservation et confidentialité, Super Administrateur soumis aux règles. |
| **Services de domaine éventuels** | Évaluation de l’exigence d’approbation selon action, risque, montant, rôle et conflit d’intérêts. |
| **Dépendances autorisées** | Tous les domaines peuvent produire des faits d’audit ; les actions administratives s’adressent toujours au domaine propriétaire. |
| **Dépendances interdites** | Modifier directement un concept d’un autre domaine, dupliquer ses règles, contourner une règle métier, transformer l’audit en autorisation. |
| **Invariants majeurs** | Une action sensible est attribuable ; l’approbateur requis est distinct ; une trace n’est pas altérée silencieusement ; le Super Administrateur reste audité. |
| **Risques de conception** | Domaine omnipotent, copie de toutes les données, journal contenant des secrets, paramètres sans propriétaire, audit trop volumineux ou inexploitable. |

## 3.13 Migration Legacy

| Élément | Définition conceptuelle |
|---|---|
| **Responsabilité** | Qualifier les sources historiques, appliquer les décisions Conserver/Nettoyer/Fusionner/Archiver/Supprimer, rapprocher les volumes et présenter des candidats aux domaines propriétaires. |
| **Propriétaire métier** | Responsable Migration, avec validation obligatoire de chaque propriétaire de domaine. |
| **Concepts principaux** | Source historique, lot, candidat, qualification, correspondance, décision de migration, anomalie, rapprochement, validation, plan de retour. |
| **Candidats Aggregate Roots** | Lot de migration ; Dossier de décision de migration ; Rapprochement de domaine. |
| **Candidats Entités** | Candidat historique, anomalie, correspondance d’identifiants, correspondance d’URL, validation métier. |
| **Candidats Value Objects** | Provenance, niveau de confiance, décision, motif, statut de qualification, écart de volume, identifiant historique. |
| **Événements métier consommés** | Règle de domaine approuvée, lot reçu, validation ou rejet d’un candidat par un domaine cible, anomalie détectée. |
| **Événements métier produits** | Candidat qualifié, doublon proposé, donnée nettoyée conceptuellement, candidat archivé ou exclu, lot rapproché, migration validée ou rejetée. |
| **Commandes conceptuelles** | Enregistrer une source, qualifier un candidat, proposer une fusion, décider archiver ou supprimer, soumettre au domaine cible, rapprocher, valider un lot, déclencher le retour. |
| **Requêtes conceptuelles** | Consulter provenance, décisions, candidats ambigus, correspondances, volumes attendus et acceptés, écarts, validations et capacité de retour. |
| **Politiques** | Source de vérité explicite, aucune confiance implicite, décision justifiée, répétabilité métier, zéro écart inexpliqué, validation par propriétaire, durée de vie limitée. |
| **Services de domaine éventuels** | Rapprochement et proposition de déduplication lorsque plusieurs sources et critères de confiance se contredisent. |
| **Dépendances autorisées** | Tous les domaines cibles uniquement pour soumettre des candidats et recevoir leurs validations ; Administration et audit. |
| **Dépendances interdites** | Devenir source du produit courant, imposer le modèle historique, publier une annonce sans validation, survivre après clôture officielle. |
| **Invariants majeurs** | Toute donnée acceptée garde sa provenance ; chaque décision est justifiée ; aucun écart n’est masqué ; le domaine cible devient seul propriétaire après acceptation. |
| **Risques de conception** | Zone temporaire permanente, contamination du langage courant, fusion irréversible, métriques de rapprochement insuffisantes, URL historiques perdues. |

---

# 4. Interactions entre domaines

## 4.1 Dépendances autorisées

| Domaine demandeur | Domaine propriétaire sollicité | Pourquoi | Mode conceptuel privilégié |
|---|---|---|---|
| Professionnels | Identité et accès | Vérifier représentants et mandats | Référence et vérification explicite |
| Catalogue immobilier | Identité et accès / Professionnels | Identifier l’annonceur habilité | Référence stable |
| Catalogue immobilier | Géographie | Rattacher un lieu officiel | Référence validée |
| Catalogue immobilier | Médias | Associer une galerie conforme | Référence de galerie |
| Cycle de vie | Catalogue immobilier | Vérifier existence et complétude de l’annonce | Décision immédiate avant transition |
| Cycle de vie | Modération et signalements | Appliquer une décision de contrôle | Commande explicite issue d’un fait décidé |
| Recherche et découverte | Catalogue, Cycle, Géographie, Médias, Professionnels | Construire une vue découvrable | Réaction différée à des événements |
| Contacts et leads | Catalogue et Cycle de vie | Vérifier annonce et état public | Revalidation immédiate au contact |
| Modération | Catalogue et Médias | Examiner contenu et preuves | Consultation cohérente |
| Contenus et SEO | Cycle, Catalogue, Géographie, Professionnels, Médias | Décider l’éligibilité et la représentation publique | Réaction aux événements et lectures maîtrisées |
| Monétisation | Identité / Professionnels | Identifier payeur et bénéficiaire | Références stables |
| Administration et audit | Tous les domaines | Coordonner une intention et tracer son résultat | Commande au propriétaire et événement d’audit |
| Migration Legacy | Tous les domaines cibles | Soumettre des candidats qualifiés | Acceptation explicite par propriétaire |

## 4.2 Dépendances absolument interdites

- Recherche et découverte ne modifie jamais Catalogue ou Cycle de vie.
- Contenus et SEO ne publie, ne suspend et ne réécrit jamais une annonce ou un lieu.
- Monétisation et paiements ne publie jamais une annonce et ne contourne jamais Modération.
- Administration et audit ne modifie jamais directement la source de vérité d’un autre domaine.
- Migration Legacy n’est jamais consultée par le produit courant après acceptation des données.
- Identité et accès ne devient pas propriétaire des règles spécialisées sous prétexte qu’elles impliquent un rôle.
- Professionnels ne possède ni les annonces de son portefeuille, ni leurs médias, ni leurs états.
- Médias ne décide jamais de l’état d’une annonce ; il produit une conformité que Cycle et Modération peuvent prendre en compte.
- Modération ne modifie ni les offres commerciales ni le contenu d’annonce en silence.
- Contacts et leads ne maintient jamais artificiellement la visibilité d’une annonce.

## 4.3 Sens général des dépendances

Les domaines de vérité — Identité, Professionnels, Catalogue, Cycle de vie, Médias, Géographie, Modération, Monétisation — produisent des faits. Recherche, SEO, administration de lecture et statistiques les consomment sans acquérir leur autorité. Migration alimente temporairement les domaines de vérité, mais ceux-ci acceptent ou refusent chaque candidat selon leurs propres invariants.

---

# 5. Frontières de cohérence

## 5.1 Cohérence immédiate requise

Doivent être décidés comme une seule opération métier cohérente :

- la transition d’état avec vérification de l’état courant, de l’acteur, du motif et de la décision préalable requise ;
- l’attribution ou la révocation d’un rôle avec sa portée et son approbation éventuelle ;
- l’activation d’un mandat avec le compte, le professionnel, la période et les preuves valides ;
- la désignation d’une image principale avec l’unicité dans la galerie et la conformité du média ;
- la décision de modération avec le dossier, le motif, le décideur et les preuves requises ;
- la confirmation financière avec la commande, le montant, la preuve et la prévention d’un double effet ;
- la fusion d’un lieu avec sa cible officielle, ses aliases et la conservation des correspondances ;
- la décision d’une URL de référence ou d’une redirection avec l’unicité et l’absence de boucle ;
- l’approbation à quatre yeux avec la distinction entre auteur et approbateur ;
- l’acceptation d’un candidat migré avec provenance, décision et validation du propriétaire cible.

## 5.2 Cohérence différée autorisée

Peuvent être actualisés après la décision source, dans un délai défini et surveillé :

- résultats, facettes, compteurs et suggestions de recherche ;
- sitemaps, maillage et vues d’éligibilité SEO ;
- statistiques et tableaux de supervision ;
- notifications non constitutives de la décision ;
- portefeuille public d’un professionnel ;
- variantes fonctionnelles d’un média déjà accepté, si leur absence bloque prudemment l’usage concerné ;
- rapprochements consolidés de migration, tant qu’aucun lot n’est déclaré validé ;
- mesures d’attribution des leads.

La cohérence différée n’autorise jamais une annonce non publiée à rester découvrable ou contactable sans limite. Un budget de fraîcheur, une détection du retard et une stratégie de retrait prudent doivent être définis avant la réalisation.

---

# 6. Ownership

| Concept source de vérité | Propriétaire unique | Utilisateurs autorisés du concept |
|---|---|---|
| Compte, rôle, consentement, mandat | Identité et accès | Tous selon nécessité et droits |
| Organisation et statut professionnel | Professionnels | Catalogue, Recherche, SEO, Paiements |
| Contenu et caractéristiques d’annonce | Catalogue immobilier | Cycle, Recherche, Modération, SEO |
| État et historique de transition | Cycle de vie des annonces | Recherche, Contacts, Modération, SEO, statistiques |
| Média, galerie, conformité média | Médias | Catalogue, Professionnels, Modération, SEO, Recherche |
| Ville, quartier, alias et hiérarchie | Géographie | Catalogue, Recherche, SEO, Migration |
| Résultat, facette et pertinence | Recherche et découverte | Navigation publique et supervision |
| Lead et intention de contact | Contacts et leads | Annonceur destinataire et acteurs internes autorisés |
| Signalement, preuve et décision de modération | Modération et signalements | Cycle de vie et audit selon droits |
| Page, guide, URL, redirection, décision d’indexation | Contenus et SEO | Navigation publique, Recherche, Migration |
| Offre, commande, paiement et droit commercial | Monétisation et paiements | Professionnels, Finance et supervision autorisée |
| Approbation, trace d’audit et paramètre gouverné | Administration et audit | Propriétaires et agents de vérification autorisés |
| Provenance, qualification et correspondance historique | Migration Legacy | Propriétaires cibles jusqu’à clôture |

Une copie de lecture ne transfère jamais l’ownership. Une référence brisée ou une divergence se résout auprès du propriétaire ; elle n’est pas corrigée localement par le consommateur.

---

# 7. Langage partagé

| Terme métier | Sens partagé | Domaine propriétaire |
|---|---|---|
| Compte | Identité permettant à une personne d’agir | Identité et accès |
| Mandat | Autorisation bornée de représenter un professionnel | Identité et accès |
| Professionnel | Organisation validée offrant des biens ou services immobiliers | Professionnels |
| Annonce | Offre immobilière décrite par un annonceur | Catalogue immobilier |
| Bien annoncé | Objet immobilier décrit dans une annonce, sans prétendre modéliser tout le bien réel | Catalogue immobilier |
| Annonceur | Compte ou professionnel propriétaire de l’annonce | Catalogue immobilier |
| État d’annonce | Situation officielle parmi le référentiel validé | Cycle de vie des annonces |
| Transition | Passage autorisé, motivé et tracé entre deux états | Cycle de vie des annonces |
| Galerie | Ensemble ordonné de médias rattaché à un propriétaire métier | Médias |
| Image principale | Unique média représentatif choisi dans une galerie | Médias |
| Ville / Quartier | Lieux officiels dans une hiérarchie validée | Géographie |
| Facette | Regroupement dérivé permettant d’affiner une recherche | Recherche et découverte |
| Lead | Intention de contact enregistrée pour une finalité légitime | Contacts et leads |
| Signalement | Alerte motivée demandant un examen | Modération et signalements |
| Décision de modération | Conclusion motivée d’un dossier de contrôle | Modération et signalements |
| URL de référence | Adresse publique principale reconnue pour une ressource | Contenus et SEO |
| URL historique | Adresse antérieure conservée comme patrimoine | Contenus et SEO |
| Offre commerciale | Proposition tarifée donnant un avantage défini, jamais un droit à publication | Monétisation et paiements |
| Paiement confirmé | Fait financier prouvé et rapprochable | Monétisation et paiements |
| Quatre yeux | Validation d’une action sensible par un second acteur distinct | Administration et audit |
| Trace d’audit | Preuve attribuable d’une action ou décision sensible | Administration et audit |
| Candidat migré | Donnée historique qualifiée mais pas encore acceptée par son propriétaire cible | Migration Legacy |
| Source de vérité | Domaine seul habilité à décider la version officielle d’un concept | Principe partagé, appliqué par chaque propriétaire |

Les termes « publié », « validé », « vérifié », « payé » et « indexable » ne sont jamais synonymes : ils appartiennent à des décisions et domaines différents.

---

# 8. Correspondance avec les documents normatifs

Légende : **Direct** = règles structurantes ; **Appui** = règles applicables à certaines interactions ; **—** = aucune autorité directe identifiée.

| Domaine | Listing Lifecycle | Media Policy | Permissions Matrix | SEO Policy | Migration Rules |
|---|---|---|---|---|---|
| Identité et accès | Appui | — | **Direct** | — | **Direct** |
| Professionnels | Appui | Appui | **Direct** | **Direct** | **Direct** |
| Catalogue immobilier | **Direct** | Appui | **Direct** | Appui | **Direct** |
| Cycle de vie des annonces | **Direct** | Appui | **Direct** | **Direct** | Appui |
| Médias | Appui | **Direct** | **Direct** | Appui | **Direct** |
| Géographie | — | — | **Direct** | **Direct** | **Direct** |
| Recherche et découverte | **Direct** | Appui | Appui | **Direct** | Appui |
| Contacts et leads | **Direct** | — | **Direct** | — | **Direct** |
| Modération et signalements | **Direct** | **Direct** | **Direct** | Appui | **Direct** |
| Contenus et SEO | **Direct** | Appui | **Direct** | **Direct** | **Direct** |
| Monétisation et paiements | — | — | **Direct** | — | **Direct** |
| Administration et audit | **Direct** | **Direct** | **Direct** | **Direct** | **Direct** |
| Migration Legacy | Appui | Appui | Appui | **Direct** | **Direct** |

Le Master Blueprint fixe la vision fonctionnelle de tous les domaines. L’Architecture Blueprint fixe leurs frontières et directions de dépendance. La matrice ci-dessus précise l’autorité des cinq politiques spécialisées demandées, sans réduire l’autorité des deux Blueprints.

---

# 9. Points de vigilance

## 9.1 Concepts encore ambigus

- la liste exacte et la dénomination des dix états officiels doivent rester strictement alignées avec Listing Lifecycle lors de toute décision ultérieure ;
- la distinction entre professionnel, établissement et agence publique demande un arbitrage métier ;
- la notion de « bien annoncé » doit rester minimale tant que le partage d’un même bien entre plusieurs annonces n’est pas décidé ;
- la portée du module Contacts et leads, notamment WhatsApp, appel, message et qualification commerciale, reste à fixer ;
- les seuils de qualité média, de page riche et de quatre yeux ne sont pas tous chiffrés ;
- le propriétaire final des catégories immobilières et intentions doit être confirmé entre Catalogue et gouvernance des référentiels ;
- le traitement d’une modification substantielle d’annonce après publication doit être précisé ;
- les conditions de restauration après suspension, retrait, refus ou archivage nécessitent une matrice définitive ;
- la portée initiale de Monétisation et paiements reste conditionnelle ;
- les durées de conservation des leads, preuves, médias, traces et historiques financiers restent à arbitrer.

## 9.2 Frontières fragiles

- Catalogue / Cycle de vie : contenu et état ne doivent jamais être regroupés par commodité.
- Cycle de vie / Modération : la décision de contrôle et la transition sont deux responsabilités coordonnées.
- Catalogue / Médias : l’annonce référence une galerie mais ne possède pas ses règles de conformité.
- Géographie / SEO : SEO exploite les lieux validés sans en créer.
- Recherche / SEO : une facette de recherche n’est pas automatiquement une page indexable.
- Professionnels / Identité : l’organisation n’est pas un compte et le représentant n’est pas l’organisation.
- Administration / tous domaines : l’administration orchestre et audite sans devenir propriétaire universel.
- Migration / tous domaines : une donnée candidate ne devient courante qu’après acceptation explicite.

## 9.3 Risques de duplication

- état d’annonce recopié dans Catalogue, Recherche, SEO ou Administration comme valeur modifiable ;
- habilitations recodifiées dans chaque domaine au lieu d’une décision d’accès cohérente complétée par les invariants locaux ;
- ville et quartier copiés sous forme de texte libre dans Annonce ou Page ;
- profil professionnel et compte personnel fusionnés ;
- conformité média dupliquée dans Modération ;
- URL calculée séparément par Catalogue, Recherche et SEO ;
- décision financière confondue avec droit commercial ou visibilité ;
- traces d’audit transformées en second historique métier concurrent ;
- correspondances Legacy utilisées comme identifiants courants.

---

# 10. Critères d’acceptation

Le Domain Mapping est acceptable si :

- les treize domaines sont présents et chacun renseigne les seize rubriques demandées ;
- chaque concept majeur possède un propriétaire unique ;
- les Aggregate Roots, Entités et Value Objects restent explicitement des candidats conceptuels ;
- commandes, requêtes et événements sont formulés en langage métier ;
- un événement décrit un fait passé et ne sert pas de commande cachée ;
- les dépendances autorisées sont justifiées et les dépendances interdites sont explicites ;
- Catalogue, Cycle de vie, Modération, Recherche et SEO conservent des autorités distinctes ;
- paiement, rôle administratif ou privilège commercial ne peuvent jamais publier une annonce ;
- Recherche et SEO restent des consommateurs sans pouvoir de mutation sur leurs sources ;
- les frontières de cohérence immédiate et différée sont compréhensibles ;
- toute cohérence différée critique prévoit un retrait prudent et une surveillance de fraîcheur ;
- Migration Legacy reste temporaire et chaque domaine cible valide ses candidats ;
- l’ownership, le langage partagé et la traçabilité normative sont vérifiables ;
- les ambiguïtés sont visibles et ne sont pas transformées en décisions implicites ;
- le document ne décrit aucune structure physique ni aucun mécanisme de réalisation.

---

# 11. Questions ouvertes

1. Les catégories et intentions appartiennent-elles définitivement au Catalogue ou à un référentiel métier transversal gouverné séparément ?
2. Un même bien réel pourra-t-il être relié à plusieurs annonces, et avec quelles preuves d’identité du bien ?
3. Quels changements d’une annonce publiée sont « substantiels » et imposent un retour en modération ?
4. Quelle matrice définitive régit restauration, renouvellement et remise en examen pour chaque état officiel ?
5. Quel service métier possède la validation documentaire des professionnels, et quels délais de renouvellement s’appliquent ?
6. Quelle est la hiérarchie géographique officielle au-delà de Ville et Quartier, et comment traiter les zones informelles reconnues par les usagers ?
7. Quel délai maximal de retrait de Recherche, Contacts et SEO est acceptable après une suspension ou un retrait ?
8. Quel niveau de dégradation média interdit la publication, et lequel autorise une publication avec avertissement ou correction ?
9. Quels canaux de contact sont inclus au lancement, quelles preuves de consentement exigent-ils et qui peut qualifier un lead ?
10. Quels cas de modération imposent quatre yeux, escalade ou impossibilité pour le même acteur de juger un recours ?
11. Quelles facettes peuvent devenir des pages publiques et quel seuil métier minimal les rend utiles ?
12. Monétisation et paiements fait-il partie du premier périmètre, et quels avantages commerciaux sont autorisés sans affecter le classement organique ?
13. Quels seuils déclenchent une double validation financière ou administrative ?
14. Quelles durées de conservation s’appliquent par domaine, notamment aux leads, preuves, médias retirés, paiements et traces d’audit ?
15. Quels critères formels clôturent Migration Legacy et interdisent définitivement toute dépendance résiduelle ?
16. Qui arbitre un conflit entre deux propriétaires de domaine et comment la décision normative est-elle consignée ?

---

# Synthèse du mapping

Le modèle retient treize domaines autonomes reliés par des intentions explicites, des références maîtrisées et des événements métier. Chaque concept a un propriétaire unique. Les domaines de lecture et de visibilité réagissent aux décisions sans devenir sources de vérité. Migration Legacy demeure une frontière temporaire de qualification et non un composant du produit courant.

# Domaines les plus critiques

1. **Catalogue immobilier**, car il porte l’offre sans absorber état, médias, géographie ou SEO.
2. **Cycle de vie des annonces**, car il est l’unique autorité sur les états et transitions.
3. **Modération et signalements**, car ses décisions conditionnent publication, suspension et confiance.
4. **Identité et accès**, car chaque action, propriété et séparation de responsabilités en dépend.
5. **Géographie**, car elle alimente simultanément catalogue, recherche, SEO et migration.
6. **Contenus et SEO** et **Recherche et découverte**, car une cohérence différée mal maîtrisée peut exposer une annonce qui ne doit plus l’être.
7. **Migration Legacy**, car toute contamination des domaines cibles compromettrait la reconstruction.

# Ambiguïtés restantes

Les principaux arbitrages concernent la propriété des catégories, l’identité éventuelle du bien au-delà de l’annonce, les modifications substantielles, les restaurations d’état, la hiérarchie géographique, la portée des leads, les seuils de qualité et de quatre yeux, les durées de conservation, le périmètre de monétisation et les critères de clôture de la migration.

# Confirmation de périmètre

Ce livrable est exclusivement conceptuel et documentaire. Aucun code ni élément de réalisation n’a été créé.

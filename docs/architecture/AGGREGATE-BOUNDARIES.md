# APPART.SN REBUILD 2026 — Aggregate Boundaries

## Statut du document

- **Sprint :** 4 — Document 3
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition soumise à validation métier
- **Nature :** frontières conceptuelles des Aggregates

---

# 1. Objet

Ce document transforme les candidats du Domain Mapping validé en frontières conceptuelles décidées. Il précise les Aggregate Roots retenus, ceux qui demeurent candidats et ceux qui sont refusés, ainsi que les règles de cohérence et d’échange qui les protègent.

Une frontière retenue est une unité de décision métier. Elle ne préjuge d’aucune organisation physique. Le Legacy n’apporte aucune règle de conception ; ses données sont seulement qualifiées par le domaine temporaire **Migration Legacy**, conformément à la terminologie normative.

---

# 2. Principes de découpage des Aggregates

1. Un Aggregate protège un petit ensemble d’invariants qui doivent être vrais immédiatement.
2. Une seule Aggregate Root reçoit les intentions et autorise les changements internes.
3. Une frontière suit une responsabilité métier, pas un écran, un parcours ou une commodité documentaire.
4. Deux concepts ayant des cycles, propriétaires ou rythmes de changement différents restent séparés.
5. Un Aggregate référence un autre Aggregate par son identité métier ; il n’absorbe ni son état interne ni son historique.
6. Un événement traversant une frontière décrit un fait déjà acquis. Il ne donne jamais un accès direct à l’intérieur de la source.
7. Une lecture composée peut réunir plusieurs Aggregates sans devenir elle-même une autorité de décision.
8. La cohérence différée est admise pour les conséquences qui peuvent être retardées, rejouées ou reconstruites.
9. La cohérence immédiate est limitée aux invariants dont la violation rendrait la décision invalide au moment où elle est prise.
10. Une frontière n’est fusionnée ou divisée que sur preuve métier, jamais pour anticiper une préférence de réalisation.
11. Les permissions s’évaluent avant l’intention ; l’Aggregate réévalue ensuite ses propres invariants.
12. L’audit constate le résultat sans devenir propriétaire de la décision.

---

# 3. Règles de cohérence

## 3.1 À l’intérieur d’un Aggregate

- une intention produit soit une décision complète et valide, soit aucun changement ;
- les Entités internes ne sont modifiées qu’au travers de la Root ;
- tous les invariants internes sont vérifiés avant la production d’un événement ;
- une décision concurrente est évaluée contre la version métier la plus récente ;
- les dates, motifs, acteurs et preuves exigés font partie de la décision ;
- aucun événement n’est annoncé si la décision correspondante n’a pas été acquise.

## 3.2 Entre Aggregates

- chaque Aggregate protège seulement ses invariants ;
- une référence externe est considérée comme une identité, jamais comme un objet interne modifiable ;
- le domaine propriétaire est consulté lorsqu’une validité actuelle est indispensable ;
- les conséquences non critiques réagissent aux événements ;
- un échec après la décision source ne l’annule pas silencieusement : il produit une reprise, une compensation métier ou une alerte ;
- aucune chaîne d’Aggregates ne prétend constituer une décision atomique globale par défaut.

## 3.3 Références observées

Un Aggregate peut mémoriser un fait externe nécessaire à sa décision — par exemple l’identité de l’annonceur ou la référence d’une décision de modération — mais cette observation reste attribuée à sa source, datée et non modifiable localement.

---

# 4. Aggregate Roots retenus

## 4.1 Registre de décision

### Retenus

1. Compte
2. Mandat de représentation
3. Professionnel
4. Annonce
5. Cycle de vie d’annonce
6. Galerie
7. Lieu géographique
8. Lead
9. Dossier de modération
10. Page éditoriale
11. Guide
12. Patrimoine d’URL
13. Offre commerciale
14. Commande commerciale
15. Paiement
16. Demande d’approbation
17. Paramètre métier gouverné
18. Lot Legacy
19. Dossier de décision Legacy
20. Rapprochement de reprise

### Restent candidats

- **Média autonome :** candidat pour les médias professionnels ou éditoriaux ayant un cycle indépendant d’une galerie. La politique d’usage partagé doit être arbitrée avant décision.
- **Signalement autonome :** candidat si un signalement doit vivre indépendamment du dossier qui le traite, être regroupé ou réaffecté.
- **Établissement professionnel :** candidat si des établissements acquièrent identité publique, vérification et suspension propres.
- **Remboursement :** candidat si son instruction, ses validations et son cycle deviennent indépendants du Paiement.
- **Droit commercial :** candidat si plusieurs sources, périodes ou révocations rendent son cycle autonome de la Commande.
- **Dossier de vérification professionnelle :** candidat si la preuve, le recours et le renouvellement exigent une frontière distincte du Professionnel.

### Refusés

- **Référentiel géographique global :** refusé comme Aggregate unique ; il rendrait toute évolution d’un lieu dépendante de l’ensemble du territoire.
- **Vue de découverte :** refusée comme Aggregate décisionnel ; elle est reconstruisible et ne possède aucun invariant de vérité métier.
- **Résultat de recherche :** refusé comme Root ; il est une lecture dérivée et périssable.
- **Portefeuille professionnel :** refusé comme Aggregate ; il compose des références d’annonces sans les posséder.
- **Annonce complète globale :** refusée si elle inclut état, galerie, modération, SEO et recherche ; elle réunirait des autorités incompatibles.
- **Journal universel d’audit :** refusé comme Aggregate global ; il concentrerait toutes les actions et deviendrait un second modèle métier.
- **Produit APPART.SN :** refusé comme Root englobante ; aucune cohérence immédiate ne justifie une frontière de cette taille.

## 4.2 Compte

- **Responsabilité :** garantir l’identité d’action, le statut du compte, le profil personnel, les consentements et les attributions de rôle.
- **Propriétaire :** Identité et accès.
- **Invariant principal :** un compte possède un statut cohérent et seules des attributions valides autorisent son acteur.
- **Frontières :** inclut profil, consentements datés, attributions et moyens de récupération ; exclut Professionnel, Mandat, annonces et décisions spécialisées.
- **Taille :** petite à moyenne ; limitée aux décisions propres à une identité personnelle.
- **Cycle de vie :** création, vérification, activation, évolution, suspension, fermeture et conservation probatoire limitée.
- **Événements produits :** Compte créé, Identité vérifiée, Consentement modifié, Rôle attribué ou retiré, Compte suspendu ou fermé.
- **Références autorisées :** identités d’approbation et de preuve ; aucun contenu interne d’un autre Aggregate.
- **Références interdites :** annonces, paiements, dossiers de modération et pages comme composants internes.

## 4.3 Mandat de représentation

- **Responsabilité :** autoriser un Compte à représenter un Professionnel dans une portée et une période déterminées.
- **Propriétaire :** Identité et accès.
- **Invariant principal :** un mandat actif relie un compte admissible, un professionnel existant, une portée explicite et une période valide.
- **Frontières :** inclut accord, portée, dates et révocation ; exclut profils du Compte et du Professionnel.
- **Taille :** petite.
- **Cycle de vie :** demandé, approuvé, actif, expiré ou révoqué.
- **Événements produits :** Mandat demandé, activé, expiré ou révoqué.
- **Références autorisées :** identités de Compte, Professionnel, approbateur et preuves.
- **Références interdites :** composition du Compte ou du Professionnel ; transfert de leurs règles.

## 4.4 Professionnel

- **Responsabilité :** garantir l’identité, le statut, la vérification et la présence publique d’une organisation.
- **Propriétaire :** Professionnels.
- **Invariant principal :** une organisation possède une identité métier unique et un statut public compatible avec sa vérification.
- **Frontières :** inclut identité légale, vérifications et profil public ; exclut comptes représentants, portefeuille, galeries et paiements.
- **Taille :** moyenne.
- **Cycle de vie :** enregistrement, vérification, activation publique, révision, suspension, restauration ou fermeture.
- **Événements produits :** Professionnel enregistré, vérifié, rendu public, suspendu ou restauré.
- **Références autorisées :** identités de Mandats, Galerie professionnelle et URL de référence.
- **Références interdites :** Annonce, Compte, Média ou Paiement comme Entité interne.

## 4.5 Annonce

- **Responsabilité :** garantir la description cohérente d’une offre immobilière et sa propriété.
- **Propriétaire :** Catalogue immobilier.
- **Invariant principal :** une annonce a un annonceur unique, une intention et une catégorie valides, et une version de contenu identifiable.
- **Frontières :** inclut bien annoncé, prix, caractéristiques, description et disponibilité déclarée ; exclut état officiel, galerie, décision de modération, URL et résultat de recherche.
- **Taille :** moyenne ; bornée aux données éditables ensemble par l’annonceur.
- **Cycle de vie :** création du contenu, complétion, soumission d’une version, modifications autorisées et clôture descriptive ; l’état public est ailleurs.
- **Événements produits :** Annonce créée, contenu modifié, prix changé, lieu rattaché, version déclarée prête, modification substantielle constatée.
- **Références autorisées :** identités d’annonceur, de Lieu, de Galerie et du Cycle de vie correspondant.
- **Références interdites :** inclusion des transitions, preuves de modération, paiements, décisions SEO et résultats de recherche.

## 4.6 Cycle de vie d’annonce

- **Responsabilité :** protéger l’unique état officiel et l’historique des transitions autorisées d’une annonce.
- **Propriétaire :** Cycle de vie des annonces.
- **Invariant principal :** une annonce a exactement un état officiel et toute transition est autorisée, motivée, attribuée et datée.
- **Frontières :** inclut état, transitions, échéances et renouvellements ; exclut contenu d’annonce, dossier de contrôle, visibilité SEO et vues de recherche.
- **Taille :** petite à moyenne ; l’historique peut croître mais n’élargit pas son autorité.
- **Cycle de vie :** initialisé avec l’annonce, traverse les dix états normatifs puis se clôt avec la fin irréversible autorisée.
- **Événements produits :** Annonce soumise, publiée, suspendue, expirée, retirée, refusée, archivée, renouvelée ou remise en examen.
- **Références autorisées :** identité d’Annonce, acteur, décision de modération et approbation requise.
- **Références interdites :** composition de l’Annonce, du Dossier de modération, de la page SEO ou de la vue de recherche.

## 4.7 Galerie

- **Responsabilité :** garantir propriété, ordre, conformité d’ensemble et unicité de l’image principale d’un groupe de médias.
- **Propriétaire :** Médias.
- **Invariant principal :** une galerie possède un propriétaire métier, au plus une image principale et uniquement des médias dont l’usage est autorisé.
- **Frontières :** inclut médias de galerie, ordre, conformité, droits déclarés et remplacements ; exclut propriétaire métier détaillé et décision de publication de l’annonce.
- **Taille :** moyenne, avec une limite métier du nombre de médias à définir.
- **Cycle de vie :** créée, alimentée, contrôlée, rendue éligible, corrigée, retirée ou archivée.
- **Événements produits :** Média ajouté, validé, refusé, remplacé ou retiré ; Image principale choisie ; Galerie conforme ou insuffisante.
- **Références autorisées :** identité du propriétaire Annonce, Professionnel ou contenu éditorial ; dossier de contrôle éventuel.
- **Références interdites :** contenu complet du propriétaire, état d’annonce et règles de publication.

## 4.8 Lieu géographique

- **Responsabilité :** garantir l’identité officielle, le type, les aliases et le rattachement hiérarchique d’un lieu.
- **Propriétaire :** Géographie.
- **Invariant principal :** un lieu actif a un nom officiel, un type et un rattachement compatibles avec la hiérarchie reconnue.
- **Frontières :** inclut aliases et décisions propres au lieu ; exclut annonces, pages SEO et enfants comme composants obligatoires.
- **Taille :** petite.
- **Cycle de vie :** proposition, validation, renommage, correction, fusion, désactivation.
- **Événements produits :** Lieu validé, renommé, fusionné, rattaché, corrigé ou désactivé.
- **Références autorisées :** identité du lieu parent, de la cible de fusion et des décisions d’approbation.
- **Références interdites :** composition de toute la hiérarchie ; inclusion des annonces ou pages liées.

## 4.9 Lead

- **Responsabilité :** garantir la finalité, le destinataire, le canal, le consentement et le traitement proportionné d’une intention de contact.
- **Propriétaire :** Contacts et leads.
- **Invariant principal :** tout lead a une finalité légitime, un destinataire autorisé et une référence à une annonce publiquement active lors de sa création.
- **Frontières :** inclut tentatives, consentement associé, attribution et qualification ; exclut Annonce, Compte destinataire et statistiques globales.
- **Taille :** petite.
- **Cycle de vie :** demandé, accepté ou refusé, transmis, qualifié, clôturé, anonymisé ou arrivé à échéance.
- **Événements produits :** Contact demandé, Lead transmis, refusé, qualifié, clôturé ou anonymisé.
- **Références autorisées :** identités d’Annonce, annonceur, demandeur connu et source d’attribution.
- **Références interdites :** contenu complet de l’Annonce, profil complet du demandeur et historique d’autres leads.

## 4.10 Dossier de modération

- **Responsabilité :** garantir l’examen impartial d’un objet signalé ou soumis, les preuves, motifs, décisions, recours et conflits d’intérêts.
- **Propriétaire :** Modération et signalements.
- **Invariant principal :** toute décision est motivée, attribuée à un acteur habilité sans conflit et appuyée par les éléments exigés.
- **Frontières :** inclut signalements rattachés, preuves, examens, décision et recours ; exclut contenu source, transition d’annonce et offre commerciale.
- **Taille :** moyenne ; limitée à un objet principal et une séquence de décision cohérente.
- **Cycle de vie :** ouvert, qualifié, affecté, examiné, décidé, éventuellement contesté, réexaminé puis clôturé.
- **Événements produits :** Dossier ouvert, contrôle affecté, décision rendue, publication approuvée, suspension ou refus demandé, recours jugé.
- **Références autorisées :** identités d’Annonce, Galerie, Média, Compte, Professionnel, Cycle de vie et approbation.
- **Références interdites :** modification interne de ces Aggregates ; composition de leur historique.

## 4.11 Page éditoriale

- **Responsabilité :** garantir le contenu, la qualité, la version et le statut éditorial d’une page autonome.
- **Propriétaire :** Contenus et SEO.
- **Invariant principal :** une page publiée a un responsable, une intention utile, une qualité minimale et des droits médias compatibles.
- **Frontières :** inclut versions, statut éditorial et liens choisis ; exclut Patrimoine d’URL, Lieux, Annonces et Galerie.
- **Taille :** moyenne.
- **Cycle de vie :** brouillon, révision, validation, publication, retrait, remplacement ou archivage.
- **Événements produits :** Page créée, révisée, publiée, retirée, déclarée pauvre ou archivée.
- **Références autorisées :** identités de Galerie, auteur, approbateur, URL de référence et concepts cités.
- **Références interdites :** composition d’Annonce, Lieu ou Guide ; appropriation de leurs règles.

## 4.12 Guide

- **Responsabilité :** garantir un contenu guidé, structuré et durable répondant à une intention éditoriale définie.
- **Propriétaire :** Contenus et SEO.
- **Invariant principal :** un guide publié est utile, original, attribué, actualisable et conforme à sa promesse éditoriale.
- **Frontières :** inclut chapitres et versions cohérentes ; exclut URL, Galerie et pages géographiques.
- **Taille :** moyenne ; ses chapitres ne deviennent pas Roots sans cycle autonome prouvé.
- **Cycle de vie :** proposition, rédaction, revue, publication, actualisation, retrait ou archivage.
- **Événements produits :** Guide créé, publié, actualisé, retiré ou archivé.
- **Références autorisées :** identités de Galerie, URL de référence, auteur et approbateur.
- **Références interdites :** inclusion de pages ou annonces externes comme Entités internes.

## 4.13 Patrimoine d’URL

- **Responsabilité :** garantir l’unicité des URL de référence, la conservation des URL historiques et la validité des redirections.
- **Propriétaire :** Contenus et SEO.
- **Invariant principal :** une ressource publique a au plus une URL de référence et aucune redirection ne forme de boucle ou de chaîne injustifiée.
- **Frontières :** inclut affectations, historiques et décisions de redirection ; exclut contenu et état métier des ressources ciblées.
- **Taille :** moyenne à grande en volume, mais étroite en responsabilité ; la division future est conditionnée à des invariants indépendants.
- **Cycle de vie :** réservation, activation, remplacement, conservation historique, redirection, retrait justifié.
- **Événements produits :** URL attribuée, URL devenue historique, Redirection décidée, conflit ou boucle détecté.
- **Références autorisées :** identité et type de la ressource publique, URL source et cible, décision d’approbation.
- **Références interdites :** contenu de l’Annonce, du Lieu, du Professionnel, de la Page ou du Guide.

## 4.14 Offre commerciale

- **Responsabilité :** garantir la définition, le prix, la période et les avantages d’une proposition commerciale.
- **Propriétaire :** Monétisation et paiements, sous autorité commerciale.
- **Invariant principal :** une offre active possède un prix, une période et des avantages explicites qui ne confèrent jamais un droit de publication.
- **Frontières :** inclut versions tarifaires et conditions ; exclut Commandes, Paiements, Annonces et décisions de Modération.
- **Taille :** petite à moyenne.
- **Cycle de vie :** préparation, approbation, activation, révision, suspension ou retrait commercial.
- **Événements produits :** Offre créée, approuvée, activée, modifiée ou retirée.
- **Références autorisées :** approbations, catégories de bénéficiaires et paramètres tarifaires gouvernés.
- **Références interdites :** clients, annonces et paiements comme composants internes.

## 4.15 Commande commerciale

- **Responsabilité :** garantir l’engagement d’un bénéficiaire envers une version précise d’offre et un montant.
- **Propriétaire :** Monétisation et paiements.
- **Invariant principal :** une commande engagée conserve bénéficiaire, offre, montant et conditions tels qu’acceptés.
- **Frontières :** inclut lignes et décisions d’annulation ; exclut Offre active courante, Paiement et droit de publication.
- **Taille :** petite.
- **Cycle de vie :** initiée, confirmée, en attente de règlement, honorée, annulée ou clôturée.
- **Événements produits :** Commande créée, confirmée, annulée, honorée ou clôturée.
- **Références autorisées :** identités de bénéficiaire, Offre, Paiements associés et approbation éventuelle.
- **Références interdites :** composition de l’Offre ou du Paiement ; état d’Annonce.

## 4.16 Paiement

- **Responsabilité :** garantir l’unicité et la preuve d’un fait financier, ses rapprochements et son issue.
- **Propriétaire :** Monétisation et paiements, sous autorité Finance.
- **Invariant principal :** un même fait financier ne produit qu’un effet, pour un montant et une commande identifiables.
- **Frontières :** inclut tentatives, confirmations, écarts et remboursements tant que ceux-ci ne deviennent pas autonomes ; exclut Commande, Offre et publication.
- **Taille :** petite à moyenne.
- **Cycle de vie :** attendu, initié, confirmé, échoué, contesté, rapproché, remboursé ou clôturé.
- **Événements produits :** Paiement initié, confirmé, échoué, rapproché, contesté ou remboursé.
- **Références autorisées :** identité de Commande, payeur, preuve financière, approbation et remboursement éventuel.
- **Références interdites :** Annonce, Cycle de vie et Dossier de modération comme composants ou effets directs.

## 4.17 Demande d’approbation

- **Responsabilité :** garantir qu’une action sensible exigeant quatre yeux est approuvée ou refusée par un acteur distinct et habilité.
- **Propriétaire :** Administration et audit.
- **Invariant principal :** l’auteur de l’intention ne peut être son approbateur lorsque quatre yeux sont requis.
- **Frontières :** inclut intention, motif, portée, approbations et issue ; exclut la décision finale du domaine sollicité.
- **Taille :** petite.
- **Cycle de vie :** demandée, en attente, approuvée, refusée, expirée ou annulée.
- **Événements produits :** Approbation demandée, accordée, refusée, expirée ou annulée.
- **Références autorisées :** identités d’acteurs, de ressource cible et d’action métier.
- **Références interdites :** contenu interne de la ressource ; mutation directe du domaine cible.

## 4.18 Paramètre métier gouverné

- **Responsabilité :** garantir la valeur, la portée, l’autorité et l’historique de décision d’un paramètre métier partagé.
- **Propriétaire :** Administration et audit pour la gouvernance ; chaque valeur conserve un propriétaire métier nommé.
- **Invariant principal :** une valeur active a un propriétaire, une portée, une période d’effet et les approbations exigées.
- **Frontières :** inclut versions et décisions du paramètre ; exclut les règles intrinsèques de chaque Aggregate.
- **Taille :** petite par paramètre.
- **Cycle de vie :** proposé, approuvé, activé, remplacé, suspendu ou retiré.
- **Événements produits :** Paramètre proposé, approuvé, activé, remplacé ou retiré.
- **Références autorisées :** propriétaire métier, approbations et concepts de portée.
- **Références interdites :** paramètres universels sans propriétaire ; remplacement d’un invariant structurel par une valeur modifiable.

## 4.19 Lot Legacy

- **Responsabilité :** borner un ensemble historique reçu, sa provenance, son niveau de qualification et son état d’avancement.
- **Propriétaire :** Migration Legacy.
- **Invariant principal :** tout candidat du lot possède une provenance et aucune acceptation n’est déclarée sans rapprochement explicable.
- **Frontières :** inclut candidats et anomalies propres au lot ; exclut les concepts courants acceptés par les domaines cibles.
- **Taille :** bornée avant traitement selon un volume métier contrôlable ; jamais l’ensemble du Legacy en une seule frontière.
- **Cycle de vie :** enregistré, qualifié, soumis, partiellement accepté, rapproché, validé, rejeté ou clôturé.
- **Événements produits :** Lot enregistré, qualifié, soumis, rapproché, validé, rejeté ou clôturé.
- **Références autorisées :** identités historiques, Dossiers de décision et Rapprochement de reprise.
- **Références interdites :** composition des Aggregates cibles ; dépendance du produit courant.

## 4.20 Dossier de décision Legacy

- **Responsabilité :** garantir la justification d’une décision Conserver, Nettoyer, Fusionner, Archiver ou Supprimer pour un candidat ou groupe cohérent.
- **Propriétaire :** Migration Legacy jusqu’à validation par le propriétaire cible.
- **Invariant principal :** toute décision est reliée à une provenance, un motif, un niveau de confiance et une validation métier requise.
- **Frontières :** inclut candidats comparés, décision, preuves et validation ; exclut l’Aggregate cible après acceptation.
- **Taille :** petite à moyenne, limitée à une ambiguïté métier cohérente.
- **Cycle de vie :** ouvert, instruit, proposé, validé, refusé, révisé ou clôturé.
- **Événements produits :** Candidat qualifié, fusion proposée, décision validée ou refusée, correspondance acceptée.
- **Références autorisées :** identités de Lot, candidats, domaine cible, valideur et correspondances historiques.
- **Références interdites :** modification d’un Aggregate cible ; règle tirée du modèle Legacy.

## 4.21 Rapprochement de reprise

- **Responsabilité :** garantir l’explication des volumes d’un domaine entre source qualifiée, décisions et acceptations finales.
- **Propriétaire :** Migration Legacy, avec validation du propriétaire métier concerné.
- **Invariant principal :** chaque écart est classé, justifié et validé ; aucun volume ne disparaît silencieusement.
- **Frontières :** inclut périmètre, décomptes métier, écarts, justifications et validations ; exclut le contenu détaillé des Aggregates acceptés.
- **Taille :** moyenne, bornée par domaine, lot et étape de validation.
- **Cycle de vie :** préparé, calculé, analysé, corrigé, validé ou rejeté.
- **Événements produits :** Rapprochement préparé, écart détecté, écart justifié, rapprochement validé ou rejeté.
- **Références autorisées :** Lots, Dossiers de décision, domaine cible et approbateurs.
- **Références interdites :** autoriser lui-même une donnée courante ou masquer un écart sous un total global.

---

# 5. Entités internes

| Aggregate Root | Entités internes retenues | Limite explicite |
|---|---|---|
| Compte | Profil personnel, consentement daté, attribution de rôle, moyen de récupération | Un rôle spécialisé ne devient pas une règle du Compte |
| Professionnel | Vérification, élément de profil public | Représentant référencé, pas incorporé |
| Annonce | Bien annoncé, caractéristique qualifiée, condition tarifaire | État et galerie exclus |
| Cycle de vie d’annonce | Transition datée, échéance, demande de renouvellement | Décision de modération référencée |
| Galerie | Média de galerie, contrôle de conformité, droit déclaré, remplacement | Propriétaire métier externe |
| Lieu géographique | Alias, rattachement, décision de fusion | Parent et cible référencés |
| Lead | Tentative, consentement associé, attribution, qualification | Annonce et personnes externes |
| Dossier de modération | Signalement rattaché, preuve, examen, décision, recours, affectation | Objet contrôlé externe |
| Page éditoriale | Version de contenu, lien éditorial | URL et Galerie externes |
| Guide | Chapitre, version, contribution | URL et Galerie externes |
| Patrimoine d’URL | Affectation, entrée historique, redirection | Ressource publique externe |
| Offre commerciale | Version tarifaire, avantage défini | Commandes externes |
| Commande commerciale | Ligne d’engagement, décision d’annulation | Offre et Paiement externes |
| Paiement | Tentative, rapprochement, remboursement non autonome | Commande externe |
| Demande d’approbation | Approbation, refus, expiration | Action finale externe |
| Paramètre métier gouverné | Version, décision d’activation | Invariants structurels exclus |
| Lot Legacy | Candidat historique, anomalie de lot | Concepts courants exclus |
| Dossier de décision Legacy | Candidat comparé, preuve, validation | Aggregate cible exclu |
| Rapprochement de reprise | Écart, justification, validation | Données détaillées externes |

Une Entité interne ne peut être adressée directement depuis l’extérieur de sa frontière. Si son cycle devient autonome et si d’autres Aggregates doivent la référencer directement, elle devient candidate à une division future.

---

# 6. Value Objects

## 6.1 Partagés avec prudence

- Identifiant métier
- Période d’effet
- Identité d’acteur
- Motif normalisé
- Décision datée
- Référence externe
- Montant et devise
- Coordonnée de contact vérifiée

Le partage porte sur le sens stable, jamais sur une règle spécialisée. Par exemple, un Motif de transition et un Motif de modération restent deux vocabulaires propriétaires distincts.

## 6.2 Propriétaires par frontière

- **Compte :** statut de compte, portée d’habilitation, coordonnées vérifiées.
- **Mandat :** portée et période de mandat.
- **Professionnel :** identité légale, statut de vérification, zone d’activité.
- **Annonce :** titre, description, prix, surface, intention, catégorie, référence géographique.
- **Cycle de vie :** état officiel, motif de transition, date d’effet, période de publication.
- **Galerie :** type de média, dimensions, qualité, position, texte alternatif, empreinte de similarité.
- **Lieu :** nom officiel, nom normalisé, type de lieu, période de validité.
- **Lead :** canal, finalité, source d’attribution, période de conservation.
- **Modération :** motif, gravité, issue, délai de recours.
- **Contenus et SEO :** type de page, statut éditorial, URL de référence, cible de redirection, motif de non-indexation.
- **Monétisation :** montant, période tarifaire, statut financier, référence de transaction.
- **Administration :** type d’action, portée, sensibilité, règle de quatre yeux.
- **Migration Legacy :** provenance, niveau de confiance, décision, écart de volume, identifiant historique.

Un Value Object ne doit pas recevoir artificiellement une identité. S’il acquiert un historique, des décisions propres ou des références externes, son statut doit être réexaminé.

---

# 7. Invariants critiques

1. Une Annonce a un annonceur unique, mais son état n’appartient pas à l’Annonce.
2. Un Cycle de vie possède exactement un état officiel pour l’Annonce référencée.
3. Une publication n’est possible qu’après une transition autorisée et, lorsque requis, une décision de modération favorable.
4. Une décision de modération n’est pas elle-même une transition.
5. Une Galerie a un propriétaire, au plus une image principale et aucun média public interdit.
6. Un Lieu actif appartient à une hiérarchie valide sans absorber toute cette hiérarchie.
7. Un Lead ne naît que pour une annonce publiquement active et une finalité légitime.
8. Une ressource publique a au plus une URL de référence.
9. Une annonce non publiée n’est ni indexable, ni découvrable, ni nouvellement contactable.
10. Une Offre ou un Paiement ne confère jamais la publication.
11. Une action à quatre yeux ne peut être approuvée par son auteur.
12. Un Super Administrateur ne contourne aucun invariant et reste audité.
13. Une donnée Legacy n’entre dans un Aggregate courant qu’après décision justifiée et validation de son propriétaire.
14. Tout écart de reprise est expliqué avant validation.

---

# 8. Références entre Aggregates

## 8.1 Référence par identité

Une référence par identité est obligatoire lorsque le concept référencé :

- possède son propre cycle de vie ;
- relève d’un autre propriétaire ;
- peut évoluer sans invalider automatiquement la source ;
- doit être consulté ou revalidé selon le contexte.

Exemples : Annonce vers Lieu et Galerie ; Cycle de vie vers Annonce et Décision de modération ; Lead vers Annonce ; Paiement vers Commande ; Patrimoine d’URL vers ressource publique.

## 8.2 Composition

La composition est réservée à un concept :

- sans existence métier utile hors de la Root ;
- modifié dans la même décision ;
- soumis aux mêmes règles de conservation ;
- non référencé directement par d’autres Aggregates.

Exemples : Transition dans Cycle de vie, Média de galerie dans Galerie, Preuve dans Dossier de modération, Ligne d’engagement dans Commande.

## 8.3 Dépendance interdite

Sont interdits :

- la référence directe à une Entité interne d’un autre Aggregate ;
- la modification d’un Aggregate depuis l’intérieur d’un autre ;
- la copie d’un état externe utilisée comme autorité modifiable ;
- la navigation implicite à travers une chaîne de références ;
- l’inclusion de toutes les dépendances d’un parcours dans une même frontière ;
- l’utilisation d’une vue de Recherche ou SEO pour décider un état métier.

---

# 9. Cohérence immédiate

La cohérence immédiate s’applique uniquement à l’intérieur d’une Root et à ses Entités :

- Compte avec statut, consentement ou attribution modifiés dans la même décision ;
- Mandat avec portée, période, compte et professionnel admissibles au moment de l’activation ;
- Annonce avec propriétaire, intention, catégorie et version de contenu ;
- Cycle de vie avec état courant, transition, motif, acteur et échéance ;
- Galerie avec ordre, conformité et image principale ;
- Lieu avec type, parent autorisé, aliases et fusion ;
- Lead avec finalité, destinataire et éligibilité de contact au moment de sa création ;
- Dossier de modération avec décideur, conflit d’intérêts, motif et preuves requises ;
- Patrimoine d’URL avec unicité et absence de boucle ;
- Commande et montant engagé ; Paiement et unicité de son effet ;
- Demande d’approbation avec séparation auteur-approbateur ;
- chaque décision Legacy avec provenance et motif ; chaque Rapprochement avec explication des écarts.

Une vérification immédiate d’une référence externe n’agrandit pas la frontière. Elle confirme seulement une précondition actuelle.

---

# 10. Cohérence différée

Peuvent réagir après la décision source, avec délai borné et surveillance :

- portefeuille public après publication ou retrait d’une annonce ;
- Recherche après modification de contenu, état, lieu, média principal ou statut professionnel ;
- décision d’indexation, sitemap, maillage et fils d’Ariane ;
- notifications et statistiques ;
- retrait secondaire des usages d’un média ;
- attribution et agrégations de leads ;
- audit enrichi, à condition que la preuve minimale accompagne immédiatement la décision sensible ;
- rapprochements consolidés avant validation finale d’une reprise.

Pour une suspension ou un retrait, la prudence prévaut : Recherche, SEO et Contacts doivent cesser l’exposition dans le budget de fraîcheur défini. En cas de doute ou de retard, le retrait est préféré à l’exposition.

---

# 11. Transactions métier conceptuelles

Une transaction métier conceptuelle est une intention complète envers une seule Aggregate Root. Elle ne décrit aucun mécanisme de réalisation.

| Transaction conceptuelle | Root décisionnaire | Préconditions externes possibles | Résultat métier |
|---|---|---|---|
| Soumettre une version d’annonce | Annonce | annonceur habilité, lieu valide, galerie admissible | Version prête déclarée |
| Publier après validation | Cycle de vie d’annonce | décision favorable, état compatible | État Publiée acquis |
| Suspendre après signalement grave | Cycle de vie d’annonce | décision ou règle d’urgence autorisée | État Suspendue acquis |
| Choisir l’image principale | Galerie | média conforme présent | Unicité de l’image principale |
| Rendre une décision de contrôle | Dossier de modération | acteur habilité, preuves et absence de conflit | Décision motivée acquise |
| Créer un lead | Lead | annonce publiée et destinataire admissible | Lead légitime créé |
| Affecter une URL de référence | Patrimoine d’URL | ressource publique admissible | URL unique attribuée |
| Confirmer un paiement | Paiement | commande et preuve fiables | Fait financier unique confirmé |
| Approuver une action sensible | Demande d’approbation | second acteur habilité | Approbation acquise |
| Accepter un candidat Legacy | Dossier de décision Legacy | validation du propriétaire cible | Candidat autorisé à entrer dans son domaine cible |

Lorsqu’un parcours traverse plusieurs Roots, chaque décision garde son résultat propre. Une étape ultérieure peut échouer sans réécrire silencieusement les décisions précédentes.

---

# 12. Événements traversant les frontières

## 12.1 Événements structurants

| Événement | Source | Consommateurs principaux | Interdiction |
|---|---|---|---|
| Annonce déclarée prête | Annonce | Cycle de vie, Modération | Ne publie pas |
| Décision de modération rendue | Dossier de modération | Cycle de vie, Audit | Ne change pas directement l’état |
| Annonce publiée | Cycle de vie | Recherche, SEO, Contacts, Professionnels | Ne garantit pas paiement ou classement |
| Annonce suspendue ou retirée | Cycle de vie | Recherche, SEO, Contacts, statistiques | Ne supprime pas le contenu source |
| Galerie conforme ou insuffisante | Galerie | Annonce, Modération, Recherche | Ne publie ni ne suspend seule |
| Lieu fusionné | Lieu | Annonce, Recherche, SEO, Patrimoine d’URL | Ne réécrit pas silencieusement les propriétaires |
| Professionnel suspendu | Professionnel | Identité, Recherche, SEO, Cycle de vie | N’annule pas l’historique financier |
| Paiement confirmé | Paiement | Commande, droits commerciaux, Audit | Ne publie pas une annonce |
| URL de référence modifiée | Patrimoine d’URL | SEO, Navigation, Recherche | Ne modifie pas la ressource métier |
| Lot Legacy validé | Lot Legacy | Propriétaires cibles, Audit | Ne rend pas toutes ses données automatiquement courantes |

## 12.2 Qualité obligatoire d’un événement

Chaque événement traversant une frontière doit exprimer un fait, son identité source, sa date métier, la version de décision utile et les références strictement nécessaires. Il ne transporte ni secret, ni contenu complet par défaut, ni capacité de modification.

---

# 13. Cas particuliers et séparation obligatoire

## 13.1 Pourquoi Annonce, Cycle de vie, Modération, SEO et Recherche restent séparés

La chaîne apparente :

**Annonce → Cycle de vie → Modération → SEO → Recherche**

décrit un parcours de conséquences, pas une unité de cohérence.

### Autorités différentes

- **Annonce** décide ce que l’offre décrit.
- **Cycle de vie** décide dans quel état officiel elle se trouve.
- **Modération** décide si les preuves et règles de contrôle autorisent une issue.
- **SEO** décide si une ressource publique mérite l’indexation et quelle URL la représente.
- **Recherche** compose une vue découvrable à partir de faits déjà décidés.

### Propriétaires différents

L’annonceur peut modifier le contenu dans les limites autorisées. Le Modérateur rend une décision de contrôle. Le responsable SEO gouverne les pages et URL sans modifier l’annonce. Recherche applique une politique de visibilité sans autorité sur l’état. Réunir ces acteurs dans une Root rendrait la séparation des responsabilités impraticable.

### Cycles différents

Une Annonce peut être corrigée plusieurs fois ; son Cycle accumule des transitions ; un Dossier de modération peut être rouvert ou contesté ; une URL historique survit au retrait ; une vue de Recherche peut être reconstruite. Leur naissance, leur durée et leur clôture ne coïncident pas.

### Cohérences différentes

Le contenu et l’état exigent chacun leur cohérence locale. SEO et Recherche acceptent une cohérence différée bornée. Les réunir imposerait une décision globale longue, fragile et inutile, ou affaiblirait les invariants critiques pour accommoder les lectures dérivées.

### Taille et concurrence

Une Root unique grossirait avec chaque image, transition, preuve, URL, facette et statistique. Une correction éditoriale entrerait en concurrence avec une modération, un retrait urgent et une actualisation de recherche. Le risque de blocage métier et de décisions contradictoires deviendrait majeur.

### Sécurité et permissions

Le Commercial ne peut publier ni contourner une modération ; le responsable SEO ne peut modifier une annonce ; Finance ne publie jamais ; le Modérateur ne modifie pas l’offre commerciale. Une Root globale rendrait ces interdictions moins lisibles et favoriserait des privilèges excessifs.

### Conclusion ferme

Ces cinq responsabilités **ne doivent pas devenir un seul Aggregate**. Elles coopèrent par références d’identité, préconditions explicites et événements. Une fusion future n’est recevable que si les responsabilités, propriétaires, cycles et exigences de cohérence deviennent réellement identiques, hypothèse aujourd’hui contraire aux documents normatifs.

## 13.2 Annonce et Cycle de vie

Le contenu peut évoluer sans que chaque correction soit une transition. L’état doit rester protégé contre une modification éditoriale. L’Annonce produit « version prête » ou « modification substantielle » ; le Cycle décide la transition autorisée. Aucun des deux n’incorpore l’autre.

## 13.3 Cycle de vie et Modération

La Modération établit une décision et ses preuves ; le Cycle traduit seulement les conséquences autorisées dans le graphe d’états. Une approbation peut ne pas suffire si l’état a changé entre-temps. Inversement, une suspension d’urgence autorisée doit rester une transition explicite et auditée.

## 13.4 Médias et Annonce

Une Galerie possède ordre, droits et conformité propres. Une Annonce ne garde que son identité. Le remplacement d’un média peut entraîner une nouvelle évaluation sans réécrire le contenu descriptif. Un média litigieux peut être conservé comme preuve alors que l’annonce suit un autre cycle.

## 13.5 Paiements et publication

Commande et Paiement sont séparés de l’Annonce et du Cycle. Un fait financier peut accorder un avantage commercial défini, jamais une validation de contenu, une décision de modération ou une transition vers Publiée. Un remboursement ne retire pas automatiquement une annonce ; il modifie les droits commerciaux selon leur propre politique.

## 13.6 SEO et Recherche

SEO gouverne l’éligibilité des pages, les URL historiques et le patrimoine public. Recherche gouverne pertinence, filtres et fraîcheur de découverte. Une page peut être non indexable tout en servant une navigation utilisateur légitime ; une ressource indexable n’est pas nécessairement un résultat de recherche interne. Leur fusion créerait des pages pauvres à partir de facettes et transformerait la visibilité en décision éditoriale.

## 13.7 Pourquoi Migration Legacy reste temporaire

Migration Legacy existe uniquement pour qualifier, décider et rapprocher des données historiques avant leur acceptation par les domaines cibles. Son langage de provenance, de confiance et de correspondance n’est pas le langage quotidien du produit.

Elle reste temporaire parce que :

- chaque domaine cible devient seul propriétaire après acceptation ;
- le produit courant ne doit jamais consulter le Legacy pour prendre une décision ;
- les identifiants historiques servent à la traçabilité et à la continuité, pas à définir les concepts futurs ;
- ses Roots — Lot Legacy, Dossier de décision Legacy et Rapprochement de reprise — perdent leur raison d’être après validation finale, résolution des écarts et expiration des obligations de preuve ;
- sa permanence créerait une double source de vérité et contaminerait les frontières validées.

Sa clôture exige des volumes rapprochés, zéro écart inexpliqué, des décisions validées, la capacité de retour arrivée à son terme officiel et l’absence démontrée de dépendance du produit courant. Après clôture, seules les preuves historiques dont la conservation est justifiée subsistent sous gouvernance d’audit ; le domaine temporaire n’accepte plus aucune nouvelle intention.

---

# 14. Critères imposant une fusion future

Une fusion ne peut être étudiée que si tous les critères suivants sont durablement vérifiés :

- même propriétaire métier et mêmes acteurs autorisés ;
- mêmes invariants immédiats ;
- cycles de vie inséparables ;
- impossibilité légitime d’accepter une cohérence différée ;
- changements presque toujours conjoints, mesurés sur une période représentative ;
- aucune séparation réglementaire, financière ou de modération ;
- réduction démontrée du risque, sans création d’une Root disproportionnée ;
- validation explicite de tous les documents normatifs concernés.

Une fréquence élevée d’échanges, à elle seule, n’impose jamais une fusion.

---

# 15. Critères imposant une division future

Une Root doit être réexaminée si :

- elle protège plusieurs groupes d’invariants indépendants ;
- ses Entités acquièrent des cycles de vie autonomes ;
- des acteurs différents doivent modifier des parties distinctes ;
- sa taille ou son historique rend les décisions ordinaires disproportionnées ;
- des changements sans rapport entrent régulièrement en concurrence ;
- une partie doit survivre, être conservée ou être supprimée selon une politique différente ;
- d’autres Aggregates doivent référencer directement une Entité interne ;
- les événements produits deviennent trop génériques pour exprimer le fait réel ;
- la plupart des décisions ne concernent qu’une petite partie isolable.

La division conserve un propriétaire clair et n’est acceptée que si les nouveaux invariants de frontière sont explicites.

---

# 16. Risques d’un Aggregate trop gros

- confusion des propriétaires et des permissions ;
- décision globale fragile pour des changements indépendants ;
- concurrence excessive entre modifications ordinaires ;
- chargement conceptuel de données inutiles à l’intention ;
- événements vagues et effets secondaires difficiles à attribuer ;
- conservation uniforme de concepts soumis à des durées différentes ;
- impossibilité de distinguer vérité métier et lecture dérivée ;
- propagation d’un échec secondaire vers une décision critique ;
- tendance à créer une Root universelle autour de l’Annonce.

Le cas le plus dangereux serait une « Annonce complète » englobant contenu, état, médias, modération, SEO, recherche, contacts et paiements.

---

# 17. Risques d’un Aggregate trop petit

- invariants répartis entre plusieurs Roots sans propriétaire effectif ;
- besoin constant de décisions globales pour une action simple ;
- événements utilisés comme commandes cachées ;
- incohérences transitoires sur des règles qui devraient être immédiates ;
- multiplication d’identités sans sens métier ;
- Entités transformées artificiellement en Roots ;
- orchestration excessive pour maintenir un ordre obligatoire ;
- difficulté à expliquer le cycle de vie à un responsable métier.

Une Transition isolée, un Alias isolé, une Ligne de commande ou un Média de galerie ne sont pas des Roots tant qu’ils n’ont ni autonomie ni invariant propre justifiant cette séparation.

---

# 18. Anti-patterns à éviter

1. **Aggregate universel Annonce :** absorbe tout ce qui est affiché sur une fiche.
2. **Aggregate par écran :** confond présentation et décision métier.
3. **Aggregate par document historique :** reproduit la forme Legacy au lieu du langage validé.
4. **Référence vivante :** permet de modifier l’intérieur d’une autre Root.
5. **Copie souveraine :** une projection locale devient une seconde source de vérité.
6. **Événement-ordre :** un fait passé cache une intention non autorisée.
7. **Validation distribuée :** un invariant immédiat dépend de plusieurs décisions partielles.
8. **Root sans invariant :** une lecture ou un regroupement est promu sans responsabilité décisionnelle.
9. **Root géante de référentiel :** chaque lieu dépend de l’ensemble du territoire.
10. **Root minuscule systématique :** chaque nom ou statut devient autonome sans justification.
11. **Audit souverain :** le journal devient capable de modifier le métier.
12. **Paiement déclencheur de publication :** confond droit commercial, conformité et état.
13. **SEO propriétaire du métier :** une exigence de visibilité crée ou modifie une annonce ou un lieu.
14. **Migration Legacy permanente :** maintient une double vérité après la reprise.

---

# 19. Correspondance normative

## 19.1 Domain Mapping

Le présent document décide les candidats du Domain Mapping. Il conserve l’ownership unique, les dépendances autorisées, les invariants et la distinction entre cohérence immédiate et différée. Tout candidat non retenu est explicitement classé candidat ou refusé.

## 19.2 Architecture Blueprint

Les frontières respectent le monolithe modulaire orienté domaines, les dépendances dirigées, les modèles de lecture sans pouvoir décisionnel et la zone Legacy temporaire. Elles restent conceptuelles et indépendantes de tout choix futur de réalisation.

## 19.3 Listing Lifecycle

Cycle de vie d’annonce reste l’unique Root propriétaire des dix états et transitions. Annonce, Modération, SEO, Recherche, Paiement et Administration ne peuvent changer cet état directement.

## 19.4 Media Policy

Galerie protège propriété, conformité, ordre, image principale, droits, retrait et archivage fonctionnel. Le remplacement est une nouvelle décision média ; la publication demeure extérieure.

## 19.5 Permissions Matrix

Les intentions sont autorisées par action métier et ressource. Commercial, Modérateur, SEO, Finance et Super Administrateur conservent les interdictions normatives. Les quatre yeux utilisent Demande d’approbation sans transférer la décision finale hors du domaine propriétaire.

## 19.6 SEO Policy

Page éditoriale, Guide et Patrimoine d’URL sont distincts du Catalogue et du Cycle. Une annonce non publiée n’est jamais indexable. Les URL historiques demeurent un patrimoine, et une facette de Recherche ne devient pas automatiquement une page.

## 19.7 Migration Rules

Lot Legacy, Dossier de décision Legacy et Rapprochement de reprise appliquent Conserver, Nettoyer, Fusionner, Archiver et Supprimer. Les propriétaires cibles valident, les écarts sont expliqués et le domaine est clôturé lorsqu’il n’a plus de fonction temporaire.

---

# 20. Critères d’acceptation

Le document est acceptable si :

- tous les candidats du Domain Mapping sont classés Retenu, Candidat ou Refusé ;
- chaque Root retenue possède responsabilité, propriétaire, invariant principal, frontières, taille, cycle de vie, événements, références autorisées et interdites ;
- les Entités internes et Value Objects sont attribués sans créer d’autorité concurrente ;
- composition, référence par identité et dépendance interdite sont distinguées ;
- la cohérence immédiate reste locale à une Root ;
- la cohérence différée possède un délai borné et une règle de retrait prudent pour les effets critiques ;
- chaque transaction métier conceptuelle a une seule Root décisionnaire ;
- les événements traversant les frontières sont des faits, non des intentions cachées ;
- Annonce, Cycle de vie, Modération, SEO et Recherche sont explicitement séparés et la fusion globale est refusée ;
- Paiement ne publie jamais ; SEO ne modifie jamais une Annonce ; Recherche n’est jamais source de vérité ;
- Migration Legacy est temporaire, ses critères de clôture sont explicites et les domaines cibles restent souverains ;
- les critères futurs de fusion et de division reposent sur des preuves métier ;
- les risques de frontières trop grandes ou trop petites sont documentés ;
- la correspondance avec les huit documents normatifs est explicite ;
- aucune décision ouverte n’est transformée silencieusement en règle définitive ;
- le document demeure exclusivement conceptuel.

---

# 21. Questions ouvertes

1. Média autonome doit-il devenir une Root pour les logos, couvertures et médias éditoriaux partagés ?
2. Un Signalement doit-il conserver un cycle autonome avant et après son rattachement à un Dossier de modération ?
3. Un Établissement professionnel aura-t-il identité publique, vérification et suspension distinctes ?
4. Le Dossier de vérification professionnelle justifie-t-il une frontière propre avec recours et renouvellement ?
5. Quels changements d’Annonce sont substantiels et exigent une nouvelle décision de Modération ?
6. Quelle limite métier borne la taille d’une Galerie selon le type d’annonce ?
7. Le Média de galerie peut-il être référencé directement comme preuve, ou faut-il une référence probatoire distincte ?
8. Quelle hiérarchie de Lieux est officielle et quels rattachements doivent être vérifiés immédiatement ?
9. Le Lead peut-il regrouper plusieurs tentatives et canaux, ou chaque intention multicanale doit-elle rester distincte ?
10. Qui peut juger un recours et quels cas exigent une nouvelle Root ou un nouveau Dossier de modération ?
11. Page éditoriale et Guide ont-ils réellement des cycles suffisamment différents pour conserver deux Roots ?
12. Patrimoine d’URL doit-il rester une Root unique ou être divisé par famille de ressources lorsque les volumes seront connus ?
13. Remboursement ou Droit commercial acquerront-ils un cycle autonome au lancement de la monétisation ?
14. Quels seuils imposent une Demande d’approbation pour Modération, Finance, Géographie et SEO ?
15. Quels paramètres sont réellement gouvernables sans affaiblir les invariants structurels ?
16. Quelle taille de Lot Legacy permet une qualification et un retour maîtrisés ?
17. Quelles preuves historiques subsistent après clôture de Migration Legacy, sous quel propriétaire et pendant combien de temps ?
18. Quel budget maximal de fraîcheur s’applique au retrait de Recherche, SEO et Contacts après changement d’état ?

---

# Synthèse des Aggregates retenus

Vingt Aggregate Roots sont retenus : Compte, Mandat de représentation, Professionnel, Annonce, Cycle de vie d’annonce, Galerie, Lieu géographique, Lead, Dossier de modération, Page éditoriale, Guide, Patrimoine d’URL, Offre commerciale, Commande commerciale, Paiement, Demande d’approbation, Paramètre métier gouverné, Lot Legacy, Dossier de décision Legacy et Rapprochement de reprise.

Six candidats restent à arbitrer : Média autonome, Signalement autonome, Établissement professionnel, Remboursement, Droit commercial et Dossier de vérification professionnelle. Les Roots globales de Recherche, de Géographie, d’Audit, du Produit et de l’Annonce complète sont refusées.

# Frontières les plus sensibles

- Annonce / Cycle de vie : contenu contre état officiel.
- Cycle de vie / Modération : transition contre décision de contrôle.
- Annonce / Galerie : description contre conformité média.
- Géographie / SEO : lieu officiel contre représentation publique.
- SEO / Recherche : indexabilité contre découverte interne.
- Offre / Commande / Paiement : proposition, engagement et fait financier.
- Administration / domaines propriétaires : approbation et audit sans souveraineté métier.
- Migration Legacy / domaines cibles : qualification temporaire contre vérité courante.

# Décisions restant ouvertes

Les arbitrages portent principalement sur l’autonomie des médias et signalements, la notion d’établissement, la vérification professionnelle, les modifications substantielles, les limites de galerie, la hiérarchie géographique, le regroupement multicanal des leads, les recours, l’éventuelle division du Patrimoine d’URL, l’autonomie des remboursements et droits commerciaux, les seuils d’approbation, la taille des lots historiques et les budgets de fraîcheur.

# Confirmation de périmètre

Ce livrable est exclusivement conceptuel et documentaire. Aucun code ni élément de réalisation n’a été créé.

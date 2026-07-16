# APPART.SN REBUILD 2026 — Règles officielles de migration

## Statut du document

- **Sprint :** 3 — Document 5
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition métier soumise à validation
- **Références :** `MASTER-BLUEPRINT.md`, `LISTING-LIFECYCLE.md`, `MEDIA-POLICY.md`, `PERMISSIONS-MATRIX.md`, `SEO-POLICY.md`
- **Nature :** règles métier exclusivement

## Objet

Ce document définit les règles officielles permettant de transformer le patrimoine Legacy en données fiables pour APPART.SN REBUILD.

La migration n'est pas une copie générale. Chaque donnée reçoit une décision explicite parmi :

- **Conserver** : reprendre comme donnée active après validation ;
- **Nettoyer** : corriger ou normaliser avant toute reprise ;
- **Fusionner** : réunir plusieurs représentations d'une même réalité ;
- **Archiver** : préserver hors usage actif pour une finalité et une durée définies ;
- **Supprimer** : ne pas reprendre ou effacer lorsque plus aucune finalité légitime ne justifie la conservation.

Une donnée ambiguë n'est jamais transformée silencieusement. Elle est isolée, documentée et soumise au propriétaire métier compétent.

## Gouvernance

La migration est gouvernée par les propriétaires métier de chaque domaine : identité, professionnels, catalogue, médias, géographie, SEO, finance, modération et contenu.

Le Responsable Migration coordonne les décisions et les rapprochements, sans se substituer aux propriétaires. Le Super Administrateur ne peut valider seul une fusion, une suppression massive ou une exception qu'il a initiée. Les règles de permissions et de double validation restent applicables pendant toute la migration.

---

# 1. Principes de migration

## 1.1 Préserver la valeur, pas les incohérences

Sont recherchés : continuité des comptes légitimes, catalogue utile, historique nécessaire, médias conformes, professionnels réels, patrimoine SEO, contenus exacts et preuves financières obligatoires.

Ne sont pas recherchés : reproduction des états ambigus, doublons, relations orphelines, données mortes, anciens modules hors immobilier, configurations obsolètes ou historiques sans finalité.

## 1.2 Une décision par donnée

Chaque ensemble, puis chaque cas exceptionnel, reçoit une disposition et une justification. L'absence de décision vaut interdiction de migration active.

## 1.3 Traçabilité

Toute transformation doit permettre de connaître :

- l'origine Legacy ;
- la donnée observée ;
- la règle appliquée ;
- la valeur retenue ;
- les valeurs écartées ;
- le motif ;
- l'acteur et le valideur ;
- la date ;
- le niveau de confiance ;
- l'éventuel besoin de contrôle ultérieur.

## 1.4 Aucun orphelin actif

Aucune annonce sans annonceur, aucun média sans propriétaire, aucun favori sans utilisateur et annonce, aucun quartier sans ville, aucun paiement sans contexte et aucun contenu sans propriétaire éditorial ne peut entrer dans le périmètre actif.

## 1.5 Pas d'invention

Une valeur manquante ne doit pas être inventée. Une déduction est admise uniquement lorsqu'une règle métier validée produit un résultat suffisamment certain et traçable. Sinon, le cas est mis en attente ou exclu.

## 1.6 Réversibilité décisionnelle

Les choix de fusion, rejet, archivage et correspondance doivent pouvoir être revus avant le basculement final. Après validation finale, toute correction suit une nouvelle décision documentée.

## 1.7 Protection des personnes

La migration ne justifie pas une conservation plus large que l'usage normal. Données personnelles, traces de connexion, contacts, messages et signalements sont repris uniquement selon une finalité et une durée approuvées.

## 1.8 Sécurité et identité

Les sessions actives, jetons temporaires, secrets, accès historiques et informations d'authentification non fiables ne sont jamais considérés comme patrimoine fonctionnel à reprendre tel quel.

## 1.9 SEO subordonné au métier

Une URL historique est préservée comme patrimoine, mais elle ne peut rendre active une annonce non Publiée, créer une ville inexistante ou maintenir un contenu trompeur.

---

# 2. Sources de vérité

## 2.1 Hiérarchie des sources

La valeur de référence est choisie selon l'ordre suivant :

1. décision métier approuvée ;
2. document officiel du domaine ;
3. donnée active la plus récente et cohérente ;
4. historique d'audit ou preuve datée ;
5. sauvegarde historique utilisée uniquement pour comparaison ;
6. déclaration du propriétaire, lorsqu'elle peut être vérifiée.

Une source plus récente ne prévaut pas automatiquement si elle est moins fiable ou résulte d'une erreur.

## 2.2 Sources par domaine

| Domaine | Source de vérité à établir | Sources de corroboration |
|---|---|---|
| Comptes | identité et contact validés du compte actif | annonces, consentements, échanges de support autorisés |
| Professionnels | organisation validée et propriétaire légitime | portefeuille, coordonnées publiques, preuves professionnelles |
| Annonces | enregistrement actif et état requalifié | médias, annonceur, historique de modération, dates |
| Médias | média réellement présent, lisible et rattaché | galerie historique, annonce, droits, ordre connu |
| Géographie | référentiel consolidé approuvé | versions Legacy, aliases, rattachements d'annonces, validation locale |
| SEO | registre des URL validé | sitemaps, redirections historiques, maillage, données externes |
| Paiements | preuve financière vérifiable | offre, montant, référence, bénéficiaire, date et rapprochement |
| Favoris | relation utilisateur–annonce valide | état du compte et existence de l'annonce |
| Signalements | dossier de signalement et décision de modération | annonce, auteur habilité, historique et motif |
| Contenus | version éditoriale approuvée | historique, date de revue, droits et données externes |
| Paramètres métier | règle officiellement approuvée | historique d'effet et décision du propriétaire métier |

## 2.3 Conflit entre sources

En cas de conflit :

- aucune valeur n'est retenue sur la seule base de sa présence ;
- le niveau de confiance est indiqué ;
- le propriétaire métier arbitre ;
- les données originales restent consultables pendant la phase de décision ;
- une fusion ou correction sensible nécessite une validation indépendante ;
- l'absence d'arbitrage bloque la migration active du cas, pas nécessairement celle du domaine entier.

---

# 3. Qualification des données

## 3.1 Statuts de qualification

| Statut | Définition | Conséquence |
|---|---|---|
| **Qualifiée** | Complète, cohérente, rattachée et conforme | Éligible à Conserver |
| **Qualifiée sous condition** | Utilisable après correction clairement définie | Nettoyer puis revalider |
| **Doublon certain** | Même réalité démontrée | Fusionner selon règle |
| **Doublon probable** | Similarité forte mais non suffisante | Contrôle manuel obligatoire |
| **Orpheline** | Relation ou propriétaire absent | Rattacher avec preuve, sinon Archiver ou Supprimer |
| **Incohérente** | Valeurs contradictoires ou impossibles | Nettoyer ou isoler |
| **Obsolète** | Ne représente plus un état actif mais garde une valeur historique | Archiver |
| **Interdite** | Non conforme, illégale ou hors périmètre | Exclure et traiter selon obligation |
| **Non déterminée** | Preuve insuffisante | Mettre en attente ; jamais migrer activement |

## 3.2 Dimensions de qualification

Chaque donnée est évaluée sur :

- identité ;
- complétude ;
- cohérence ;
- fraîcheur ;
- propriété ;
- relation aux autres domaines ;
- conformité aux politiques ;
- finalité ;
- durée de conservation ;
- valeur opérationnelle ;
- valeur SEO éventuelle ;
- niveau de confiance.

## 3.3 Échantillonnage

Les contrôles portent sur l'ensemble des règles déterminantes et sur des échantillons représentatifs des cas réguliers. Les domaines à risque élevé — professionnels, géographie, annonces, médias, paiements et URL — exigent des contrôles renforcés et des cas limites documentés.

---

# 4. Nettoyage

## 4.1 Opérations métier admises

- normaliser espaces, casse et ponctuation ;
- corriger un encodage manifestement dégradé ;
- normaliser les formats de téléphone, devise, date et prix ;
- rapprocher les valeurs oui/non historiques des valeurs officielles ;
- requalifier un état Legacy vers le cycle officiel ;
- rattacher une catégorie ou localisation lorsque la preuve est suffisante ;
- retirer un contenu interdit ou une information personnelle non nécessaire ;
- marquer une valeur inconnue plutôt que l'inventer ;
- corriger une faute formelle sans changer le sens ;
- isoler les incohérences non résolues.

## 4.2 Opérations interdites

- inventer une adresse, un prix ou une localisation ;
- déclarer Publiée une annonce au statut incertain ;
- attribuer un média sur la seule ressemblance ;
- fusionner des personnes uniquement parce qu'elles partagent un nom ;
- transformer un compte particulier en professionnel sans validation ;
- réécrire un contenu afin de masquer son origine ;
- modifier une preuve financière pour la rendre cohérente ;
- supprimer une anomalie sans conserver sa décision ;
- utiliser le référencement comme justification d'une fausse valeur.

## 4.3 Résultat du nettoyage

Chaque donnée nettoyée conserve la valeur observée, la valeur retenue, la règle, le motif et le niveau de confiance. Une correction ne devient définitive qu'après validation du domaine concerné.

---

# 5. Déduplication

## 5.1 Niveaux de confiance

### Doublon certain

Plusieurs éléments indépendants démontrent la même réalité : identifiant historique cohérent, même propriétaire vérifié, mêmes coordonnées, même bien, même référence financière ou décision antérieure fiable.

### Doublon probable

Les données sont proches mais une ambiguïté demeure. Aucune fusion automatique n'est admise.

### Non-doublon

Des ressemblances existent, mais des éléments déterminants montrent deux réalités distinctes.

## 5.2 Règle de fusion

Avant toute fusion :

1. choisir la ressource de référence ;
2. comparer toutes les valeurs ;
3. définir la règle de priorité par attribut ;
4. préserver les anciens identifiants comme correspondances ;
5. rattacher les ressources dépendantes ;
6. isoler les conflits ;
7. obtenir la validation requise ;
8. produire un rapport avant/après.

## 5.3 Interdictions

- fusion sur un seul critère faible ;
- perte d'un historique utile ;
- déplacement d'une annonce vers un autre propriétaire sans preuve ;
- mélange de paiements de personnes distinctes ;
- fusion géographique sans validation locale ;
- fusion d'URL dont les intentions diffèrent ;
- auto-validation par l'initiateur.

---

# 6. Gestion des comptes

## 6.1 Périmètre

Comptes particuliers, représentants professionnels, identités, coordonnées, consentements, états de compte et historique strictement nécessaire.

## 6.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Comptes actifs ou historiquement nécessaires, identité minimale, coordonnées validées, rattachements légitimes, consentements prouvés | Continuité utilisateur, propriété des annonces et obligations |
| **Nettoyer** | E-mails mal formés, téléphones non normalisés, états ambigus, noms d'affichage, consentements sans format commun | Rendre les comptes compréhensibles et exploitables sans inventer |
| **Fusionner** | Doublons certains appartenant à la même personne | Éviter plusieurs identités et réunir les ressources légitimes |
| **Archiver** | Comptes fermés, bannis, inactifs ou nécessaires à un historique | Préserver support, sécurité ou obligation sans accès actif |
| **Supprimer** | Sessions, jetons temporaires, données sans finalité, comptes de test confirmés, doublons résiduels après fusion | Réduire les risques et ne pas reprendre les artefacts sans valeur métier |

## 6.3 Règles

- aucune session Legacy n'est reprise ;
- les accès administratifs sont recréés selon une liste validée, non migrés comme comptes ordinaires ;
- la stratégie applicable aux mots de passe historiques doit être approuvée avant le basculement ;
- un compte sans moyen fiable de récupération est isolé ;
- une fusion conserve la propriété correcte des annonces, favoris et professionnels ;
- les personnes bannies ne redeviennent pas actives par défaut ;
- les demandes de suppression et obligations de conservation restent applicables.

## 6.4 Validation

Propriétaire : Identité/Produit. Avis requis : Sécurité et Protection des données. Fusion sensible : double validation.

---

# 7. Gestion des professionnels

## 7.1 Périmètre

Organisations, représentants, noms publics, coordonnées, descriptions, logos, portefeuilles, statuts de vérification et historique commercial utile.

Le MASTER AUDIT a identifié des doublons et orphelins professionnels. Ces constats sont des alertes historiques à recalculer, non des décisions finales.

## 7.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Organisations réelles, profils validés, portefeuilles légitimes, identité publique et éléments commerciaux nécessaires | Préserver la valeur B2B et la continuité des annonces |
| **Nettoyer** | Noms, coordonnées, descriptions, visibilité, catégories professionnelles, médias non conformes | Obtenir une identité publique exacte et cohérente |
| **Fusionner** | Doublons certains d'une même organisation ou même établissement selon politique | Réunir portefeuille, historique et URL sans créer plusieurs identités |
| **Archiver** | Organisations fermées, non vérifiées mais historiquement nécessaires, orphelins utiles à une enquête | Conserver la preuve sans publication ni accès actif |
| **Supprimer** | Profils de test, coquilles vides, doublons absorbés, données sans propriétaire ni obligation | Éviter les faux professionnels et données mortes |

## 7.3 Règles

- aucune fusion uniquement sur le nom commercial ;
- le propriétaire légitime doit être identifié ;
- les annonces ne changent pas d'organisation sans preuve ;
- les anciens noms et URL utiles sont conservés comme correspondances ;
- un profil non validé n'est pas publié au terme de la migration ;
- les médias suivent la politique officielle ;
- les engagements financiers ou contractuels sont examinés avant fusion ou archivage.

## 7.4 Validation

Propriétaire : Commercial/Produit. Avis : Modération pour le portefeuille, Finance pour obligations, SEO pour URL. Fusion : quatre yeux.

---

# 8. Gestion des annonces

## 8.1 Périmètre

Identité de l'annonce, annonceur, titre, description, intention, type de bien, prix, caractéristiques, localisation, dates, état, visibilité, options historiques et URL.

## 8.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Annonces éligibles, propriété vérifiée, contenu utile, identifiants et URL historiques, dates fiables | Préserver le catalogue et la continuité SEO |
| **Nettoyer** | États Legacy, prix textuels, booléens, catégories, localisations, téléphones, descriptions et caractéristiques | Aligner chaque annonce sur le cycle et les référentiels officiels |
| **Fusionner** | Doublons certains du même bien, même offre et même annonceur | Éviter la concurrence interne et réunir historique et médias légitimes |
| **Archiver** | Annonces expirées, retirées, refusées ou historiques utiles mais non publiables | Préserver obligations, support, statistiques autorisées et traitement d'URL |
| **Supprimer** | Tests, contenus interdits sans obligation de preuve, doublons absorbés, annonces sans propriétaire ni valeur historique | Ne pas introduire de contenu faux, inutile ou orphelin |

## 8.3 Requalification des états

Chaque état Legacy est rapproché d'un état officiel : Brouillon, Soumise, En modération, À corriger, Publiée, Suspendue, Expirée, Retirée, Refusée ou Archivée.

Une annonce n'est migrée comme Publiée que si :

- son état est démontré ;
- son annonceur est valide ;
- sa localisation et sa catégorie sont valides ;
- son contenu respecte les règles ;
- son offre paraît encore disponible selon le critère approuvé ;
- elle possède le minimum média requis ;
- aucun litige ou signalement bloquant n'est ouvert.

À défaut, elle est migrée dans un état non public approprié ou archivée.

## 8.4 Doublons

Même titre ou même image ne suffisent pas. Sont examinés : propriétaire, bien, localisation, caractéristiques, période, médias, prix et historique. Deux unités réellement distinctes d'un programme ne sont pas fusionnées.

## 8.5 Validation

Propriétaire : Catalogue/Modération. Avis : SEO pour URL, Géographie pour rattachement, Média pour galerie. Publication en masse : quatre yeux.

---

# 9. Gestion des médias

## 9.1 Périmètre

Images d'annonces, professionnels et contenus ; rôle principal ; ordre ; droits ; qualité ; relations ; variantes historiques et éventuelles preuves de litige.

Le MASTER AUDIT a relevé un écart entre les références historiques et les médias effectivement présents, ainsi que des duplications. Chaque relation doit être rapprochée.

## 9.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Images lisibles, conformes, autorisées, rattachées avec confiance, ordre et rôle principal fiables | Préserver la qualité des annonces et contenus |
| **Nettoyer** | Orientation, données personnelles visibles, métadonnées sensibles, ordre, rôle principal, qualité formelle | Respecter la politique média sans falsifier le contenu |
| **Fusionner** | Références exactes du même média pour le même usage légitime | Éviter les répétitions tout en conservant les relations autorisées |
| **Archiver** | Médias de contenus archivés, contestés ou nécessaires comme preuve | Conserver hors usage public selon finalité et durée |
| **Supprimer** | Orphelins non rattachables, contenus interdits, fichiers illisibles, doublons inutiles, médias sans droits | Empêcher publication trompeuse et conservation injustifiée |

## 9.3 Règles

- aucune image n'est rattachée sur le seul nom de fichier ;
- une similarité déclenche un contrôle, pas une fusion automatique ;
- les droits et propriétaires peuvent justifier plusieurs usages d'un même visuel ;
- l'image principale est reprise seulement si elle respecte la politique ;
- une annonce sans média conforme ne devient pas Publiée, sauf exception approuvée ;
- les projections, plans et images d'environnement restent identifiés ;
- un média retiré ne doit plus apparaître dans un ancien usage public.

## 9.4 Validation

Propriétaire : Média/Modération. Avis : Annonceur pour droits, SEO pour médias historiques publics, Protection des données pour contenu sensible.

---

# 10. Gestion de la géographie

## 10.1 Périmètre

Régions utiles, villes, quartiers, anciens niveaux géographiques, noms, orthographes, aliases, rattachements et coordonnées lorsqu'elles sont suffisamment fiables.

Le Legacy contient plusieurs représentations concurrentes. Aucun ensemble n'est déclaré maître sans consolidation.

## 10.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Villes et quartiers validés, hiérarchie approuvée, noms locaux et aliases utiles | Fonder recherche, dépôt et SEO sur une source cohérente |
| **Nettoyer** | Casse, accents, variantes, fautes, rattachements, coordonnées et noms historiques | Éviter les doublons et erreurs de localisation |
| **Fusionner** | Lieux identiques sous plusieurs noms ou générations, après validation locale | Créer une seule référence tout en conservant les aliases |
| **Archiver** | Anciennes divisions, noms obsolètes et cas ambigus utiles à la correspondance | Préserver la migration et les URL sans les rendre actifs |
| **Supprimer** | Tests, entrées impossibles, doublons absorbés sans valeur d'alias, lieux hors périmètre confirmé | Ne pas polluer le référentiel actif |

## 10.3 Règles de rattachement des annonces

- priorité aux identifiants cohérents corroborés par les libellés ;
- contrôle des villes et quartiers homonymes ;
- aucune déduction depuis le seul titre de l'annonce ;
- les coordonnées servent de corroboration, pas de preuve unique ;
- les cas ambigus sont isolés ;
- une annonce non rattachée ne devient pas Publiée ;
- les aliases historiques restent disponibles pour les URL et correspondances.

## 10.4 Validation

Propriétaire : Produit/Géographie. Avis : responsable local, SEO et Modération. Toute fusion utilisée : quatre yeux.

---

# 11. Gestion du SEO

## 11.1 Périmètre

URL historiques, redirections, canonical, indexabilité, sitemaps, contenus de pages, métadonnées éditoriales, aliases, maillage et données externes disponibles.

## 11.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | URL ayant une ressource ou une valeur historique, redirections légitimes, contenus utiles, aliases géographiques | Préserver l'autorité et la continuité des parcours |
| **Nettoyer** | Variantes, accents, doublons, destinations obsolètes, métadonnées incohérentes, maillage cassé | Établir une URL de référence et une intention claire |
| **Fusionner** | URL et pages réellement équivalentes, contenus redondants et aliases d'une même ressource | Éviter la concurrence interne et concentrer la valeur |
| **Archiver** | Registres historiques, anciennes décisions, pages non indexables gardant une valeur de preuve | Conserver la traçabilité sans exposition publique |
| **Supprimer** | Pages pauvres sans valeur, variantes sans usage, redirections trompeuses, contenus dupliqués absorbés | Ne pas reproduire la dette SEO du Legacy |

## 11.3 Règles

- chaque URL historique reçoit une décision ;
- une annonce non Publiée n'est jamais indexée ;
- une page pauvre n'est pas créée pour préserver artificiellement une URL ;
- aucune redirection générale vers l'accueil ;
- une fusion d'URL exige une équivalence d'intention ;
- les URL des annonces conservées restent stables ;
- les données externes corroborent la priorité, sans imposer une règle métier fausse ;
- les sitemaps de migration reflètent uniquement les pages éligibles.

## 11.4 Validation

Propriétaire : SEO / Contenu. Validation croisée avec le propriétaire de la ressource. Changement massif : quatre yeux.

---

# 12. Gestion des paiements historiques

## 12.1 Périmètre

Références de paiement, montants, dates, payeurs, bénéficiaires, offres associées, états, remboursements, rapprochements et preuves nécessaires.

La reprise des paiements dépend des obligations comptables, contractuelles, fiscales, de support et de litige. Un paiement historique ne rend jamais une annonce Publiée.

## 12.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Paiements vérifiés encore nécessaires aux comptes, obligations, litiges ou droits commerciaux actifs | Assurer continuité financière et preuve |
| **Nettoyer** | Devise, format de montant, référence, état et rattachement lorsqu'ils sont corroborés | Permettre un historique compréhensible sans altérer la preuve |
| **Fusionner** | Représentations certaines d'un même événement financier | Éviter le double comptage tout en conservant les sources |
| **Archiver** | Paiements clôturés requis pour conservation mais sans action active | Limiter l'usage tout en respectant les obligations |
| **Supprimer** | Tests confirmés, doublons absorbés, informations accessoires sans finalité après délai | Réduire l'exposition sans effacer une obligation |

## 12.3 Règles

- une preuve financière n'est jamais corrigée pour s'adapter à une annonce ;
- tout conflit de montant, devise ou bénéficiaire est isolé ;
- le rapprochement avec l'offre et le payeur est documenté ;
- les paiements sans contexte restent non actifs jusqu'à décision ;
- les données sensibles sont minimisées ;
- aucune session, secret ou information de paiement non nécessaire n'est reprise ;
- fusion, correction sensible ou suppression suivent les validations Finance.

## 12.4 Validation

Propriétaire : Finance. Avis : Commercial pour l'offre, Produit pour l'effet, Protection des données pour conservation. Décision sensible : quatre yeux.

---

# 13. Gestion des favoris

## 13.1 Périmètre

Relations entre un utilisateur et une annonce qu'il a souhaité retrouver.

## 13.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Favoris dont l'utilisateur et l'annonce sont valides | Préserver la continuité du parcours utilisateur |
| **Nettoyer** | Doublons exacts, relations mal formées et références historiques rattachables | Obtenir une relation unique et fiable |
| **Fusionner** | Favoris identiques issus de comptes fusionnés ou de doublons certains d'annonce | Éviter les répétitions après déduplication |
| **Archiver** | Favoris liés à une annonce archivée lorsque l'historique utilisateur est justifié | Conserver temporairement sans suggérer une disponibilité |
| **Supprimer** | Favoris orphelins, comptes supprimés, annonces inexistantes sans correspondance | Aucune valeur sans les deux propriétaires de la relation |

## 13.3 Règles

- un favori ne rend jamais une annonce visible ;
- une annonce non Publiée est signalée indisponible ou retirée du parcours actif ;
- la fusion de comptes ou annonces ne crée qu'un favori ;
- aucun favori n'est attribué à une autre personne ;
- les favoris ne sont pas conservés au-delà de la finalité approuvée.

## 13.4 Validation

Propriétaire : Produit/Identité. Contrôle renforcé après fusion de comptes et d'annonces.

---

# 14. Gestion des signalements

## 14.1 Périmètre

Signalement, auteur ou contexte autorisé, cible, motif, date, preuve, décision, recours et historique de modération.

## 14.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Signalements ouverts, graves, récents ou nécessaires à une décision, un recours ou une obligation | Assurer sécurité, modération et preuve |
| **Nettoyer** | Motifs, états, liens vers annonces et informations personnelles excessives | Rendre le dossier exploitable et proportionné |
| **Fusionner** | Signalements portant certainement sur le même fait et la même cible | Regrouper l'enquête sans effacer les auteurs ni dates |
| **Archiver** | Dossiers clôturés nécessaires à l'historique, la récidive ou une obligation | Conserver avec accès limité et durée définie |
| **Supprimer** | Spam manifeste, doublons techniques absorbés, données personnelles sans finalité après délai | Réduire les risques et le bruit sans perdre une preuve utile |

## 14.3 Règles

- l'identité du signalant reste protégée ;
- un signalement ne devient pas vrai par sa seule existence ;
- les preuves et décisions restent distinguées ;
- une fusion n'efface pas le nombre de signalants indépendants ;
- les dossiers graves ne sont pas supprimés pendant un litige ;
- les signalements concernant une annonce non migrée peuvent être archivés si leur finalité subsiste ;
- les notes internes ne deviennent pas des données publiques.

## 14.4 Validation

Propriétaire : Modération. Avis : Sécurité ou Juridique selon gravité, Protection des données pour conservation.

---

# 15. Gestion des contenus

## 15.1 Périmètre

Pages éditoriales, guides, menus, éléments de pied de page, textes légaux, modèles de communication et médias éditoriaux.

## 15.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Contenus exacts, utiles, autorisés, stratégiques ou obligatoires | Préserver information, confiance et patrimoine SEO |
| **Nettoyer** | Encodage, orthographe, liens, dates, mentions obsolètes, droits et métadonnées éditoriales | Garantir exactitude et qualité actuelle |
| **Fusionner** | Pages ou guides réellement redondants répondant à la même intention | Éviter duplication et concentrer la valeur |
| **Archiver** | Anciennes versions, contenus obsolètes utiles à la preuve ou à l'historique | Préserver la traçabilité hors publication |
| **Supprimer** | Contenus faux, hors immobilier, sans propriétaire, dupliqués absorbés ou sans droit | Ne pas reprendre la dette éditoriale |

## 15.3 Règles

- chaque contenu possède un propriétaire et une date de revue ;
- une page publique n'est pas automatiquement indexable ;
- un contenu légal est validé par le responsable compétent ;
- les modèles de communication sont revus pour exactitude et ton ;
- aucun contenu actif n'est repris uniquement parce qu'il existait ;
- une fusion conserve les URL historiques selon la politique SEO ;
- les médias suivent la politique média.

## 15.4 Validation

Propriétaire : SEO / Contenu. Avis : Juridique pour légal, Produit pour parcours, métier concerné pour exactitude.

---

# 16. Gestion des paramètres métier

## 16.1 Périmètre

Durées, seuils, motifs, limites, règles de publication, options commerciales, règles éditoriales et autres décisions ajustables officiellement approuvées.

Les secrets, accès individuels et éléments exécutables ne sont pas des paramètres métier.

## 16.2 Matrice de décision

| Disposition | Données concernées | Justification |
|---|---|---|
| **Conserver** | Règles actives explicitement validées et encore applicables | Assurer continuité des opérations légitimes |
| **Nettoyer** | Noms, valeurs, unités, dates d'effet, propriétaires et descriptions | Rendre chaque règle compréhensible et gouvernable |
| **Fusionner** | Paramètres synonymes ou concurrents représentant une même règle | Éliminer les conflits et établir une source unique |
| **Archiver** | Anciennes valeurs nécessaires à l'historique des décisions | Comprendre les effets passés sans les rendre actifs |
| **Supprimer** | Réglages obsolètes, secrets, modules abandonnés, doublons absorbés et valeurs sans propriétaire | Ne pas importer les ambiguïtés ou risques du Legacy |

## 16.3 Règles

- aucun paramètre n'est repris sans propriétaire métier ;
- toute valeur possède une définition et une unité ;
- la date d'effet et le traitement des dossiers en cours sont précisés ;
- un réglage historique ne devient pas une règle officielle par défaut ;
- les règles de paiement et options restent inactives tant que le catalogue commercial n'est pas validé ;
- les valeurs supprimées restent mentionnées dans le registre si elles ont produit des effets ;
- les modifications sensibles suivent quatre yeux.

## 16.4 Validation

Propriétaire : domaine concerné. Mise en vigueur : selon la matrice des permissions, avec validation indépendante pour les effets sensibles.

---

# 17. Données non migrées

Ne sont pas migrées dans le périmètre actif :

- sessions, jetons temporaires et états de connexion ;
- secrets et accès historiques ;
- comptes administratifs non recréés officiellement ;
- anciennes extensions ou mécanismes de personnalisation ;
- données automobile et adulte ;
- caches et résultats temporaires ;
- copies de sauvegarde considérées comme ensembles actifs ;
- journaux sans finalité approuvée ;
- statistiques personnelles trop anciennes ou non justifiées ;
- anciennes protections IP applicatives ;
- moyens de paiement sans contrat ou usage validé ;
- paramètres sans propriétaire ;
- pages pauvres, tests et contenus génériques ;
- images orphelines ou sans droits ;
- relations orphelines ;
- données de démonstration confirmées.

## 17.1 Justification

Ces éléments n'apportent pas de continuité fonctionnelle fiable, présentent un risque de sécurité ou de confidentialité, ou appartiennent à des fonctions exclues du nouveau produit.

## 17.2 Exception

Une donnée non migrée activement peut être archivée si une obligation, un litige, une preuve ou une décision de migration le justifie. L'exception possède un propriétaire, une durée et un accès limité.

---

# 18. Données archivées

## 18.1 Finalités autorisées

- obligation légale ou financière ;
- litige ou preuve ;
- sécurité et récidive ;
- support limité ;
- audit de migration ;
- historique éditorial ou SEO ;
- rapprochement temporaire des anomalies.

## 18.2 Règles

- aucune donnée archivée n'est active ou publique par défaut ;
- l'accès dépend de la finalité ;
- une durée est fixée ;
- une date de revue ou d'effacement est prévue ;
- l'archivage ne remplace pas la suppression lorsqu'aucune finalité ne subsiste ;
- une donnée archivée ne peut être restaurée sans nouvelle qualification ;
- les archives de migration ne deviennent pas une seconde source active.

## 18.3 Registre

Chaque famille archivée indique : contenu, motif, propriétaire, périmètre, accès autorisés, durée et issue finale.

---

# 19. Données supprimées

## 19.1 Conditions

Une donnée est supprimée lorsque :

- aucune finalité légitime ne justifie sa conservation ;
- le délai approuvé est expiré ;
- elle constitue un doublon absorbé sans valeur propre ;
- elle est orpheline et non rattachable ;
- elle est interdite et aucune preuve ne doit être gardée ;
- une demande recevable l'exige ;
- elle provient d'un test confirmé ;
- elle est corrompue et sans valeur de rapprochement.

## 19.2 Contrôles préalables

- vérifier les dépendances ;
- vérifier litiges et obligations ;
- vérifier les URL historiques ;
- vérifier la portée de la suppression ;
- obtenir la validation requise ;
- consigner le motif et les volumes ;
- s'assurer qu'une fusion a bien repris les éléments légitimes.

## 19.3 Suppressions sensibles

Les suppressions massives, professionnelles, financières, géographiques, SEO ou liées à un litige requièrent quatre yeux. Le Super Administrateur ne s'auto-valide pas.

---

# 20. Rapprochement des volumes

## 20.1 Rapport obligatoire par domaine

| Mesure | Définition |
|---|---|
| Source observée | Total de données Legacy dans le périmètre |
| Hors périmètre | Données exclues avant qualification |
| Qualifiées | Données ayant reçu une décision |
| Conservées | Données actives reprises |
| Nettoyées | Données corrigées avant reprise |
| Fusionnées | Sources regroupées et ressources finales résultantes |
| Archivées | Données conservées hors usage actif |
| Supprimées | Données non reprises ou effacées selon décision |
| En attente | Cas sans décision finale |
| En erreur | Cas dont la règle n'a pas produit le résultat attendu |
| Final actif | Total de ressources actives après décisions |

## 20.2 Équation métier

Le total Source observée doit être expliqué par la somme des dispositions, en tenant compte du fait qu'une fusion peut transformer plusieurs sources en une ressource finale. Aucun écart inexpliqué n'est accepté.

## 20.3 Rapprochements croisés

- comptes avec annonces, favoris et professionnels ;
- professionnels avec représentants et portefeuille ;
- annonces avec annonceurs, médias, géographie et URL ;
- médias avec propriétaires et usages ;
- quartiers avec villes ;
- paiements avec payeurs, offres et effets ;
- signalements avec cibles et décisions ;
- contenus avec URL et médias ;
- sitemaps avec pages réellement éligibles.

## 20.4 Tolérance

La tolérance d'écart inexpliqué est zéro. Des rejets sont acceptables lorsqu'ils sont dénombrés et justifiés ; une disparition silencieuse ne l'est pas.

---

# 21. Validation métier

## 21.1 Responsabilités

| Domaine | Validateur principal | Avis requis |
|---|---|---|
| Comptes | Produit/Identité | Sécurité, Protection des données |
| Professionnels | Commercial/Produit | Modération, Finance, SEO |
| Annonces | Catalogue/Modération | Géographie, Média, SEO |
| Médias | Média/Modération | Droits, Protection des données, SEO |
| Géographie | Produit/Géographie | Responsable local, SEO |
| SEO | SEO / Contenu | Propriétaires des ressources |
| Paiements | Finance | Commercial, Produit |
| Favoris | Produit | Identité |
| Signalements | Modération | Sécurité ou Juridique |
| Contenus | SEO / Contenu | Juridique et métiers concernés |
| Paramètres | Propriétaire du domaine | Domaines impactés et SA selon sensibilité |

## 21.2 Formes de validation

- validation des règles ;
- validation des échantillons ;
- validation des cas limites ;
- validation des volumes ;
- validation des rapprochements ;
- validation des rejets ;
- validation finale du domaine.

## 21.3 Refus de validation

Un refus décrit l'anomalie, son impact, le périmètre concerné et la correction attendue. Il bloque le domaine ou le lot concerné. Une échéance ne transforme jamais un refus en accord implicite.

---

# 22. Plan de migration

## Phase 0 — Décisions préalables

- valider les propriétaires ;
- approuver les sources de vérité ;
- arbitrer géographie, états d'annonce, mots de passe, paiements et conservation ;
- définir les seuils et échantillons ;
- ouvrir le registre des décisions.

### Condition de sortie

Aucun domaine critique ne conserve une question bloquante sans propriétaire ni date d'arbitrage.

## Phase 1 — Inventaire et qualification

- compter les sources ;
- classifier les données ;
- identifier doublons, orphelins et incohérences ;
- rapprocher les relations ;
- prioriser les cas à risque.

### Condition de sortie

Tout le périmètre observé possède un statut de qualification.

## Phase 2 — Référentiels

- consolider comptes de référence ;
- valider professionnels ;
- consolider géographie ;
- valider catégories et paramètres ;
- établir le registre des URL.

### Condition de sortie

Les annonces peuvent être rattachées à des propriétaires et référentiels stables.

## Phase 3 — Catalogue et contenus

- requalifier annonces ;
- rapprocher médias ;
- traiter favoris et signalements ;
- valider pages et guides ;
- qualifier les paiements historiques.

### Condition de sortie

Chaque ressource active candidate respecte les politiques officielles.

## Phase 4 — Répétition complète

- appliquer toutes les décisions sur une copie de travail ;
- produire les volumes ;
- contrôler les relations ;
- examiner les échantillons ;
- traiter les erreurs ;
- obtenir les validations par domaine.

### Condition de sortie

Aucun écart inexpliqué et aucun bloquant critique ouvert.

## Phase 5 — Répétitions de confirmation

- recommencer avec une source fraîche ;
- comparer les résultats ;
- vérifier la stabilité des règles ;
- mesurer les nouveaux écarts ;
- figer les décisions de basculement.

### Condition de sortie

Deux répétitions successives produisent des résultats cohérents et approuvés.

## Phase 6 — Basculement

- définir l'heure de référence ;
- limiter les changements Legacy ;
- prendre la source finale ;
- traiter les changements depuis la dernière répétition ;
- effectuer les rapprochements ;
- valider comptes, annonces, médias, URL et paiements prioritaires ;
- obtenir l'autorisation de mise en service.

## Phase 7 — Surveillance renforcée

- suivre erreurs, connexions, annonces, médias, URL, redirections et paiements ;
- comparer les volumes réels ;
- traiter les cas utilisateurs ;
- documenter toute correction ;
- décider la fin de la période renforcée.

---

# 23. Plan de retour

## 23.1 Objectif

Le plan de retour protège la continuité du service si les critères critiques ne sont pas satisfaits après le basculement.

## 23.2 Déclencheurs

- impossibilité généralisée d'accéder aux comptes ;
- perte ou mauvais rattachement significatif d'annonces ;
- exposition d'annonces non Publiées ;
- médias largement manquants ou attribués au mauvais bien ;
- géographie incohérente affectant recherche et SEO ;
- paiements ou droits commerciaux incorrects ;
- redirections prioritaires défaillantes ;
- écart de volume inexpliqué ;
- risque de sécurité ou de confidentialité ;
- absence de capacité à corriger dans le délai approuvé.

## 23.3 Décision

La décision appartient à un groupe désigné comprenant Produit, Opérations, Sécurité et propriétaires des domaines affectés. Elle est distincte de l'équipe ayant initié le basculement lorsque quatre yeux sont requis.

## 23.4 Effets

- suspendre les nouvelles mutations sur la cible ;
- rétablir le service de référence approuvé ;
- préserver les actions intervenues après basculement ;
- informer les équipes et utilisateurs affectés selon le plan de communication ;
- qualifier les écarts avant toute nouvelle tentative ;
- ouvrir un rapport d'incident et de décision.

## 23.5 Limites

Le retour ne doit pas écraser silencieusement les nouvelles annonces, modifications, retraits ou paiements survenus après basculement. Ces événements sont isolés, rapprochés et rejoués seulement après validation métier.

---

# 24. Gestion des erreurs

## 24.1 Classes

| Classe | Exemple | Traitement |
|---|---|---|
| Bloquante | annonce sans propriétaire, exposition privée, paiement faux | Arrêt du domaine ou du basculement |
| Critique | fusion incorrecte, média attribué au mauvais bien, URL prioritaire perdue | Isolement, correction et nouvelle validation |
| Majeure | valeur importante manquante, état ambigu, quartier incorrect | Non-migration active du cas et arbitrage |
| Mineure | forme ou libellé sans impact de sens | Nettoyage tracé |
| Avertissement | donnée ancienne mais cohérente | Revue ou suivi après validation |

## 24.2 Dossier d'erreur

Chaque erreur indique : domaine, source, ressource, règle, résultat attendu, résultat obtenu, gravité, impact, décision, responsable, délai et statut.

## 24.3 Règles

- aucune erreur bloquante n'est ignorée ;
- aucune correction manuelle n'échappe au registre ;
- un cas rejeté reste compté ;
- une règle corrigée entraîne une nouvelle vérification de tout son périmètre ;
- les erreurs récurrentes sont traitées à leur cause ;
- les utilisateurs ne supportent pas les conséquences silencieuses d'une erreur de migration ;
- un paiement ou droit ne fait l'objet d'aucune approximation.

## 24.4 Escalade

Les délais d'escalade et décideurs sont définis avant la répétition complète. L'absence de propriétaire est elle-même une erreur bloquante de gouvernance.

---

# 25. Critères d'acceptation

## 25.1 Gouvernance

- chaque domaine possède un propriétaire et un valideur ;
- les sources de vérité sont approuvées ;
- les cinq dispositions sont utilisées et justifiées ;
- chaque exception figure dans le registre ;
- les décisions sensibles suivent quatre yeux ;
- aucune échéance ne vaut accord implicite.

## 25.2 Qualité

- aucune valeur importante n'est inventée ;
- les données actives sont complètes selon leur domaine ;
- les états d'annonce correspondent au cycle officiel ;
- les formats et référentiels sont cohérents ;
- les cas ambigus sont isolés ;
- les nettoyages sont traçables.

## 25.3 Intégrité des relations

- aucune annonce active sans compte valide ;
- aucun média actif sans propriétaire ;
- aucun favori actif sans utilisateur et annonce ;
- aucun quartier actif sans ville ;
- aucun paiement actif sans contexte vérifié ;
- aucun contenu actif sans propriétaire éditorial ;
- aucune URL active sans ressource ou décision.

## 25.4 Déduplication

- aucun doublon certain non traité dans le périmètre actif ;
- les doublons probables sont contrôlés manuellement ;
- chaque fusion conserve les correspondances ;
- les ressources dépendantes sont rapprochées ;
- aucune fusion sur un seul critère faible ;
- les fusions sensibles sont doublement validées.

## 25.5 Domaines critiques

- comptes actifs récupérables selon la stratégie approuvée ;
- professionnels validés et correctement propriétaires de leur portefeuille ;
- annonces Publiées conformes et disponibles selon le critère retenu ;
- médias conformes et correctement rattachés ;
- géographie consolidée ;
- URL historiques prioritaires traitées ;
- paiements requis rapprochés ;
- contenus actifs revus.

## 25.6 Volumes

- totalité du périmètre expliquée ;
- zéro écart inexpliqué ;
- volumes Conservés, Nettoyés, Fusionnés, Archivés, Supprimés, En attente et En erreur disponibles ;
- rapports croisés cohérents ;
- échantillons validés ;
- résultats stables sur deux répétitions successives.

## 25.7 Protection et sécurité

- aucune session ou jeton Legacy repris ;
- aucun secret historique actif repris ;
- accès administratifs recréés et approuvés ;
- données personnelles limitées aux finalités ;
- litiges et obligations respectés ;
- données interdites hors périmètre public.

## 25.8 SEO

- chaque URL prioritaire possède une décision ;
- aucune annonce non Publiée n'est indexable ;
- aucune redirection trompeuse ;
- pages pauvres et doublons traités ;
- sitemaps cohérents avec les ressources éligibles ;
- aliases géographiques préservés.

## 25.9 Basculement et retour

- critères de lancement signés ;
- déclencheurs de retour approuvés ;
- responsabilités nommées ;
- événements postérieurs au basculement préservés ;
- communication préparée ;
- surveillance renforcée active jusqu'à décision de clôture.

---

# 26. Questions ouvertes

## Niveau 1 — Bloquantes

1. Quelle stratégie est retenue pour les mots de passe historiques ?
2. Quel référentiel géographique final est approuvé ?
3. Quels critères déterminent qu'une annonce Legacy est encore disponible ?
4. Quelle ancienneté maximale autorise une annonce à devenir Publiée ?
5. Quels moyens de paiement et offres sont encore actifs ?
6. Quelles obligations déterminent la conservation des paiements ?
7. Qui arbitre les doublons et orphelins professionnels ?
8. Quelles durées s'appliquent aux données archivées par domaine ?
9. Quelles données externes sont disponibles pour qualifier les URL historiques ?
10. Qui possède le pouvoir final de déclencher un retour ?

## Niveau 2 — Avant répétition complète

11. Quels seuils distinguent modification formelle et changement substantiel d'annonce ?
12. Une annonce sans image conforme peut-elle être conservée non publique pour correction ?
13. Quels critères permettent de rattacher un média historique avec confiance ?
14. Comment traiter les comptes partageant une même adresse électronique historique ?
15. Comment traiter un professionnel avec plusieurs établissements ?
16. Quelle politique s'applique aux favoris liés à des annonces expirées ?
17. Quels signalements historiques restent utiles à la récidive ?
18. Quels contenus légaux exigent une nouvelle validation complète ?
19. Quels paramètres Legacy ont encore un propriétaire métier ?
20. Quel volume définit une suppression ou fusion massive ?
21. Combien d'échantillons par domaine sont nécessaires ?
22. Quel délai maximal est accordé aux arbitrages manuels ?

## Niveau 3 — Avant basculement

23. Quelle période de limitation des changements Legacy est acceptable ?
24. Quels événements sont autorisés pendant cette période ?
25. Quelle durée de surveillance renforcée est retenue ?
26. Quels indicateurs déclenchent une alerte immédiate ?
27. Comment informer les utilisateurs dont les données sont en attente ou archivées ?
28. Comment traiter les nouvelles actions survenues entre la source finale et la mise en service ?
29. Quel niveau de perte SEO déclenche une mesure corrective majeure ?
30. Quand les archives temporaires de rapprochement sont-elles supprimées ?

---

# 27. Registre des décisions de migration

## 27.1 Finalité

Le registre constitue la référence officielle de toutes les décisions de transformation, exception, fusion, archivage et suppression.

## 27.2 Informations obligatoires

| Information | Description |
|---|---|
| Identifiant de décision | Référence unique et stable |
| Domaine | Compte, professionnel, annonce, média, géographie, SEO, paiement, favori, signalement, contenu ou paramètre |
| Périmètre | Ensemble ou ressource concernée |
| Source observée | Origine Legacy et contexte |
| Problème | Ambiguïté, doublon, incohérence, orphelin ou règle générale |
| Disposition | Conserver, Nettoyer, Fusionner, Archiver ou Supprimer |
| Valeur retenue | Résultat métier décidé |
| Justification | Raisonnement et politique applicable |
| Niveau de confiance | Certain, élevé, moyen, faible ou non déterminé |
| Impacts | Ressources liées, droits, SEO, finance et utilisateur |
| Initiateur | Personne et rôle |
| Validateur | Personne et rôle indépendant si requis |
| Date d'effet | Moment où la décision s'applique |
| Réversibilité | Conditions de revue avant basculement |
| Preuves | Éléments ayant fondé la décision |
| Résultat | Appliquée, rejetée, en attente ou à revoir |

## 27.3 Règles

- aucune décision sensible sans justification ;
- aucune modification silencieuse du registre ;
- une correction crée une nouvelle version de décision ;
- les décisions générales et exceptions sont distinguées ;
- une exception ne devient pas une règle générale sans validation ;
- le registre est rapproché des volumes ;
- les décisions d'URL sont liées au registre SEO ;
- les décisions de fusion indiquent toutes les sources et la ressource de référence ;
- les suppressions indiquent le contrôle des obligations ;
- les décisions ouvertes possèdent un propriétaire et une échéance.

## 27.4 Exemple métier abstrait

| Domaine | Problème | Disposition | Justification | Validation |
|---|---|---|---|---|
| Professionnel | Deux profils représentent certainement la même organisation | Fusionner | Identité, coordonnées et portefeuille concordants | Commercial + validation indépendante |
| Média | Image sans annonce identifiable | Archiver temporairement | Rapprochement encore possible pendant la période définie | Média/Modération |
| Annonce | État Legacy contradictoire | Ne pas migrer activement avant décision | Une annonce incertaine ne peut devenir Publiée | Catalogue/Modération |
| Géographie | Deux orthographes du même quartier | Fusionner et conserver l'ancien nom comme alias | Référentiel unique et continuité des URL | Géographie + SEO + quatre yeux |

---

# Résumé des règles de migration

La migration APPART.SN est une transformation gouvernée. Chaque donnée est qualifiée puis reçoit une disposition justifiée : Conserver, Nettoyer, Fusionner, Archiver ou Supprimer. Aucune donnée ambiguë, orpheline, interdite ou sans propriétaire ne rejoint le périmètre actif.

Les comptes, professionnels, annonces, médias, référentiels géographiques, URL, paiements, favoris, signalements, contenus et paramètres suivent leurs propres règles et validations. Les relations entre domaines sont rapprochées afin d'obtenir zéro écart inexpliqué.

La migration est répétée jusqu'à produire des résultats stables. Le basculement exige des validations métier par domaine, un registre de décisions complet et un plan de retour protégeant les actions intervenues après la mise en service.

# Risques majeurs

1. fusion incorrecte de comptes ou professionnels ;
2. annonce attribuée au mauvais propriétaire ;
3. média manquant, volé ou rattaché au mauvais bien ;
4. mauvais référentiel de ville ou quartier ;
5. annonce non Publiée rendue visible ou indexable ;
6. perte d'une URL historique importante ;
7. doublon de paiement ou perte d'une preuve financière ;
8. reprise de données personnelles sans finalité ;
9. suppression massive non maîtrisée ;
10. écart de volume inexpliqué ;
11. règle de transformation instable entre répétitions ;
12. événements postérieurs au basculement perdus lors d'un retour.

# Confirmation de périmètre

Ce document définit exclusivement des règles, décisions, responsabilités, contrôles et validations métier. Aucun code ni mécanisme de réalisation n'a été créé ou défini.

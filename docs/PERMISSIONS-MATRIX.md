# APPART.SN REBUILD 2026 — Matrice officielle des permissions

## Statut du document

- **Sprint :** 3 — Document 3
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition métier soumise à validation
- **Références :** `MASTER-BLUEPRINT.md`, `LISTING-LIFECYCLE.md`, `MEDIA-POLICY.md`
- **Nature :** règles métier exclusivement

## Objet

Ce document définit qui peut accomplir chaque action métier sur les ressources officielles d'APPART.SN. Une permission porte toujours sur une action précise, une ressource précise, un périmètre précis et une finalité légitime.

La présence d'un rôle dans une équipe ou l'accès à un espace de travail ne donne aucun droit implicite. Tout droit non expressément accordé est interdit.

## Légende

### Acteurs

| Code | Acteur |
|---|---|
| **V** | Visiteur |
| **P** | Particulier |
| **PRO** | Professionnel |
| **M** | Modérateur |
| **C** | Commercial |
| **SEO** | Responsable SEO / Contenu |
| **F** | Finance |
| **SA** | Super Administrateur |
| **SYS** | Système |

### Niveaux d'autorisation

| Symbole | Sens |
|---|---|
| **✓** | Autorisé dans le périmètre normal du rôle |
| **C** | Autorisé sous condition métier explicite |
| **4Y** | Autorisé uniquement après validation par une seconde personne habilitée |
| **Auto** | Exécuté uniquement selon une règle automatique approuvée |
| **—** | Interdit ou sans objet |

« Propre » signifie que l'acteur est le propriétaire métier de la ressource. « Public » signifie que la ressource a été rendue visible conformément à son cycle de vie. « Habilité » signifie que l'action fait partie du mandat écrit de l'acteur.

---

# 1. Principes généraux

## 1.1 Refus par défaut

Une action absente de cette matrice est interdite jusqu'à décision formelle. Aucun rôle ne déduit un droit par analogie avec une autre ressource.

## 1.2 Moindre privilège

Chaque acteur reçoit uniquement les droits nécessaires à sa mission actuelle. Un droit temporaire possède un motif, une durée et une fin explicite.

## 1.3 Propriété et habilitation

Le Particulier et le Professionnel agissent uniquement sur leurs propres ressources, sauf mandat validé. Les rôles internes agissent uniquement dans leur domaine de responsabilité.

## 1.4 Une action, une finalité

Consulter pour modérer, consulter pour vendre une offre et consulter pour rapprocher un paiement sont trois finalités distinctes. Une permission accordée pour l'une n'autorise pas les autres.

## 1.5 Cumul explicite

Un même membre de l'équipe peut cumuler plusieurs rôles seulement après validation. Lorsqu'il agit, le rôle et la finalité utilisés doivent être identifiables. Le cumul ne permet jamais de satisfaire seul une double validation.

## 1.6 Règles métier invariantes

- une annonce ne devient Publiée que selon son cycle de vie ;
- une option commerciale ne contourne pas la modération ;
- un paiement ne donne aucun droit de publication ;
- une décision SEO ne change pas l'état métier d'une annonce ;
- une action exceptionnelle reste motivée et auditable ;
- le Super Administrateur ne dispose d'aucune exemption aux règles métier ;
- le Système agit uniquement selon des règles approuvées et ne prend pas de décision discrétionnaire.

## 1.7 Sens des actions

| Action | Définition métier |
|---|---|
| **Créer** | Initier une nouvelle ressource métier |
| **Consulter** | Voir les informations autorisées pour une finalité donnée |
| **Modifier** | Changer le contenu ou les attributs métier sans changer implicitement son statut |
| **Publier** | Rendre visible ou actif dans le périmètre public prévu |
| **Retirer** | Mettre fin volontairement ou administrativement à la visibilité ou à l'usage actif |
| **Suspendre** | Interrompre temporairement pour contrôle, risque ou non-conformité |
| **Supprimer** | Demander ou décider l'effacement selon les règles de conservation |
| **Restaurer** | Remettre en usage une ressource retirée ou supprimée lorsque cela reste permis |
| **Archiver** | Clôturer l'usage opérationnel en conservant l'historique autorisé |
| **Exporter** | Produire une copie d'informations pour une finalité métier approuvée |

La restauration ne permet jamais de rouvrir une ressource dont l'état métier est terminal. Une nouvelle ressource peut être requise.

---

# 2. Acteurs officiels

## 2.1 Visiteur

Personne non authentifiée. Elle consulte uniquement les ressources publiques, effectue une recherche et peut initier un signalement selon les règles anti-abus. Elle ne possède aucun droit de mutation sur le catalogue.

## 2.2 Particulier

Annonceur non professionnel. Il gère son profil, ses brouillons, ses annonces et médias propres dans les limites des politiques officielles. Il soumet une annonce mais ne décide jamais de sa publication.

## 2.3 Professionnel

Organisation ou représentant professionnel validé. Il possède les droits d'un annonceur sur son portefeuille et gère les informations autorisées de son profil professionnel. Son statut ne lui donne aucun privilège de modération.

## 2.4 Modérateur

Responsable de la conformité des annonces, médias et signalements. Il peut publier, demander une correction, suspendre, refuser et clôturer selon le cycle officiel. Il n'agit pas sur les prix, durées ou règles des offres commerciales.

## 2.5 Commercial

Responsable de la relation avec les professionnels et de la préparation des propositions commerciales. Il peut accompagner, qualifier et suivre, mais ne publie jamais une annonce et ne contourne jamais une décision de modération.

## 2.6 Responsable SEO / Contenu

Responsable des pages éditoriales, guides, règles de visibilité organique, sitemaps et continuité des URL. Il ne modifie jamais une annonce, ses médias ou son état métier.

## 2.7 Finance

Responsable du contrôle financier, du rapprochement, des remboursements autorisés et des exports financiers. Finance ne publie aucune ressource publique et n'active pas une annonce.

## 2.8 Super Administrateur

Responsable des habilitations, paramètres métier sensibles, arbitrages exceptionnels et supervision. Il est soumis aux mêmes cycles, motifs, séparations et validations que les autres rôles. Toutes ses actions sensibles sont auditées.

## 2.9 Système

Acteur non humain exécutant exclusivement des règles préalablement validées : échéances, archivages programmés, rappels, génération de sitemaps ou contrôles déterministes. Il ne remplace pas un jugement humain lorsque celui-ci est requis.

---

# 3. Ressources métier

Les ressources officielles couvertes sont :

1. Annonce ;
2. Média ;
3. Profil ;
4. Professionnel ;
5. Paiement ;
6. Offre commerciale ;
7. Signalement ;
8. Ville ;
9. Quartier ;
10. Catégorie ;
11. Page éditoriale ;
12. Guide ;
13. Sitemap ;
14. Paramètres métier.

Les matrices ci-dessous sont exhaustives pour ces ressources. Lorsqu'une action est « sans objet », elle reste interdite plutôt que réinterprétée.

---

# 4. Matrices par ressource

## 4.1 Annonce

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | P ✓ propre ; PRO ✓ propre ; M C ; SA C | M ou SA uniquement pour assistance formellement demandée, sans auto-publication |
| Consulter | V ✓ publique ; P ✓ propre ; PRO ✓ propre ; M ✓ ; C C ; SEO ✓ publique ; F C ; SA ✓ ; SYS C | C limité au suivi professionnel ; F limité à un dossier financier ; les contenus non publics sont réservés aux finalités habilitées |
| Modifier | P ✓ propre ; PRO ✓ propre ; M C ; SA C | Selon l'état ; M limité aux corrections formelles autorisées ; modification substantielle renvoyée en contrôle |
| Publier | M ✓ ; SA C | Décision de modération motivée ; SA suit exactement le même cycle ; C, SEO et F interdits |
| Retirer | P ✓ propre ; PRO ✓ propre ; M C ; SA C ; SYS Auto | Propriétaire : retrait volontaire ; M/SA : motif de conformité ; SYS : échéance ou règle approuvée |
| Suspendre | M ✓ ; SA C ; SYS C | Risque, signalement, droit ou sécurité ; SYS seulement pour un gel automatique approuvé et révisable |
| Supprimer | P C propre ; PRO C propre ; SA 4Y | Demande soumise à conservation, droits et litiges ; suppression immédiate de l'historique interdite |
| Restaurer | M C ; SA 4Y | Seulement depuis un état réactivable ; jamais depuis Archivée ; nouvelle modération si nécessaire |
| Archiver | M C ; SA C ; SYS Auto | Selon le cycle officiel et les délais ; motif obligatoire |
| Exporter | P C propre ; PRO C propre ; M C ; C C ; SEO C public ; F C ; SA C | Finalité explicite, périmètre minimal et traçabilité ; export massif sensible sous double validation |

### Interdictions absolues

- le Commercial ne publie jamais une annonce ;
- le Commercial ne modifie jamais le contenu d'une annonce ;
- le Responsable SEO / Contenu ne modifie jamais une annonce ;
- Finance ne publie, ne modifie et ne suspend jamais une annonce ;
- un paiement ou une offre ne déclenche pas directement la publication ;
- le Super Administrateur ne transforme pas une annonce en Publiée sans décision conforme au cycle.

## 4.2 Média

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | P ✓ propre ; PRO ✓ propre ; M C ; SEO C éditorial ; SA C | Le média possède un propriétaire et un usage ; M seulement pour correction formelle ou assistance autorisée |
| Consulter | V ✓ public ; P ✓ propre ; PRO ✓ propre ; M ✓ ; C C ; SEO ✓ public/éditorial ; F — ; SA ✓ ; SYS C | Les médias non publics sont limités à la modération, aux droits ou au support autorisé |
| Modifier | P ✓ propre ; PRO ✓ propre ; M C ; SEO ✓ éditorial ; SA C | M limité aux corrections formelles prévues ; SEO ne touche jamais aux médias d'annonce |
| Publier | M ✓ média d'annonce avec annonce ; SEO ✓ éditorial ; SA C | Le média d'annonce suit l'état de l'annonce ; publication isolée interdite |
| Retirer | P ✓ propre ; PRO ✓ propre ; M ✓ ; SEO ✓ éditorial ; SA C ; SYS Auto | Le minimum média d'une annonce doit rester respecté ; retrait immédiat en cas de risque |
| Suspendre | M ✓ ; SEO C éditorial ; SA C ; SYS C | Droit contesté, fraude, contenu interdit ou contrôle en cours |
| Supprimer | P C propre ; PRO C propre ; M C ; SEO C éditorial ; SA 4Y | Sous réserve de conservation et litige ; M peut décider le retrait, pas effacer une preuve utile |
| Restaurer | P C propre ; PRO C propre ; M C ; SEO C éditorial ; SA 4Y | Nouveau contrôle obligatoire ; média archivé terminal non restauré automatiquement |
| Archiver | M C ; SEO C éditorial ; SA C ; SYS Auto | Suit la ressource propriétaire et les règles de conservation |
| Exporter | P C propre ; PRO C propre ; M C ; SEO C éditorial ; SA C | Droits d'utilisation et finalité vérifiés ; Finance et Commercial sans droit général |

## 4.3 Profil

Le Profil désigne les informations personnelles et préférences d'un compte individuel. Le profil public d'une organisation relève de la ressource Professionnel.

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | P ✓ propre ; PRO ✓ représentant ; SA C | Une personne crée son profil ; SA uniquement pour procédure assistée approuvée |
| Consulter | P ✓ propre ; PRO ✓ propre ; M C ; C C ; F C ; SA C ; SYS C | Chaque rôle voit seulement les données nécessaires à sa finalité ; V voit uniquement les éléments explicitement publics |
| Modifier | P ✓ propre ; PRO ✓ propre ; SA C | Les attributs contrôlés ou sensibles suivent une vérification ; les rôles internes ne réécrivent pas l'identité sans procédure |
| Publier | P C ; PRO C | Uniquement les éléments dont la visibilité est volontaire et autorisée ; aucun rôle interne ne publie des données personnelles par défaut |
| Retirer | P ✓ propre ; PRO ✓ propre ; SA C | Retrait des éléments publics ou fermeture demandée, sous réserve des obligations |
| Suspendre | M C ; SA C ; SYS C | M seulement si son mandat inclut les abus liés aux annonces ; décision motivée et portée définie |
| Supprimer | P C propre ; PRO C propre ; SA 4Y | Demande de suppression soumise à vérification, obligations et litiges |
| Restaurer | P C propre ; PRO C propre ; SA 4Y | Après contrôle d'identité et si l'effacement définitif n'a pas eu lieu |
| Archiver | SA C ; SYS Auto | Fermeture, inactivité selon règle ou obligation ; accès ensuite limité |
| Exporter | P ✓ propre ; PRO ✓ propre ; SA C | Export par un tiers interne uniquement pour obligation, support formel ou demande recevable |

## 4.4 Professionnel

Cette ressource représente l'organisation professionnelle, son statut de vérification, son identité publique et son portefeuille.

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | PRO ✓ demande ; C C ; SA C | C accompagne ou prépare, mais la validation du statut est distincte |
| Consulter | V ✓ public ; PRO ✓ propre ; M C ; C ✓ ; SEO ✓ public ; F C ; SA ✓ ; SYS C | Accès non public limité à la vérification, modération, relation commerciale ou finance |
| Modifier | PRO ✓ propre ; C C ; SA C | Les données vérifiées nécessitent un nouveau contrôle ; C ne modifie pas seul un statut de vérification |
| Publier | SA C ; rôle de validation professionnelle à confirmer 4Y | C peut proposer, jamais décider seul ; publication après vérification |
| Retirer | PRO C propre ; C C proposition ; SA C ; SYS Auto | Retrait volontaire ou non-conformité ; impacts sur portefeuille évalués |
| Suspendre | M C ; SA C | M limité aux risques liés au catalogue ; suspension de l'organisation sous décision habilitée |
| Supprimer | PRO C propre ; SA 4Y | Obligations commerciales, financières et historiques vérifiées |
| Restaurer | SA 4Y | Nouvelle vérification ; aucune restauration silencieuse |
| Archiver | SA C ; SYS Auto | Clôture après délais et obligations ; C peut demander, pas décider seul |
| Exporter | PRO C propre ; C C ; F C ; SA C | Périmètre limité à la finalité ; données personnelles minimisées |

## 4.5 Paiement

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | P C propre ; PRO C propre ; F C ; SYS Auto | Initiation liée à une offre ou obligation validée ; F peut enregistrer un paiement manuel selon procédure |
| Consulter | P ✓ propre ; PRO ✓ propre ; C C statut limité ; F ✓ ; SA C ; SYS C | M et SEO interdits ; C ne voit que l'information nécessaire au suivi commercial |
| Modifier | F 4Y ; SA 4Y | Correction exceptionnelle, motif et preuve ; montant ou bénéficiaire jamais modifié sans contrôle renforcé |
| Publier | — | Un paiement n'est jamais une ressource publique |
| Retirer | F 4Y ; SA 4Y | Annulation ou invalidation selon règles financières ; historique conservé |
| Suspendre | F ✓ ; SA C ; SYS Auto | Doute, rapprochement impossible, contestation ou risque |
| Supprimer | — | Effacement métier interdit tant que des obligations s'appliquent ; toute demande suit la conservation légale |
| Restaurer | F 4Y ; SA 4Y | Réouverture d'un paiement suspendu ou annulé après preuve |
| Archiver | F C ; SYS Auto | Après clôture et selon la durée applicable |
| Exporter | P C propre ; PRO C propre ; F ✓ ; SA C | Export financier sensible, justifié et tracé ; export massif sous double validation |

### Règle fondamentale

Finance contrôle la réalité financière mais ne publie jamais une annonce, un professionnel, une offre, une page ou un guide. Le statut payé ne vaut ni conformité ni publication.

## 4.6 Offre commerciale

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | C ✓ proposition ; F C avis financier ; SA 4Y validation | Une offre possède cible, prix, durée, effet, conditions et propriétaire métier |
| Consulter | V ✓ publique ; P ✓ applicable ; PRO ✓ applicable ; M C lecture ; C ✓ ; SEO ✓ publique ; F ✓ ; SA ✓ ; SYS C | Les versions en préparation sont réservées aux rôles habilités |
| Modifier | C ✓ proposition ; F C aspects financiers ; SA 4Y validation | Le Modérateur est toujours interdit ; modification active soumise à contrôle d'impact |
| Publier | SA 4Y | C prépare ; F valide les impacts financiers ; aucune publication par C seul ou F seul |
| Retirer | C C proposition ; F C ; SA 4Y ; SYS Auto | Conditions clients, engagements en cours et date d'effet évalués |
| Suspendre | F C risque financier ; SA 4Y | Suspension globale exceptionnelle ; M interdit |
| Supprimer | — | Une offre utilisée n'est pas effacée ; une proposition jamais active peut être abandonnée et archivée |
| Restaurer | SA 4Y | Nouvelle validation complète ; pas de remise en activité silencieuse |
| Archiver | C C ; F C ; SA C ; SYS Auto | Après fin commerciale et obligations |
| Exporter | C ✓ ; F ✓ ; SA C ; SEO C publique | Contenu commercial ou analyse autorisée ; données clients exclues sauf finalité distincte |

### Interdiction absolue

Le Modérateur ne crée, ne modifie, ne publie, ne retire, ne suspend, ne restaure et ne supprime jamais une offre commerciale. Il peut uniquement consulter les règles strictement nécessaires pour comprendre l'effet visible d'une option sur une annonce.

## 4.7 Signalement

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | V ✓ ; P ✓ ; PRO ✓ ; M C ; C C ; SEO C ; F C ; SA C ; SYS C | Motif, cible et bonne foi ; protection anti-abus ; rôles internes signalent sans auto-décider |
| Consulter | Créateur C suivi limité ; P/PRO propre ; M ✓ ; C C ; SEO C ; F C ; SA ✓ ; SYS C | Identité du signalant protégée ; chaque rôle voit seulement ce qui relève de sa mission |
| Modifier | Créateur C avant prise en charge ; M ✓ qualification ; SA C | Le fond initial reste traçable ; C, SEO et F ne requalifient pas hors domaine |
| Publier | — | Un signalement n'est jamais public |
| Retirer | Créateur C ; M C ; SA C | Retrait volontaire ou signalement manifestement abusif ; historique de traitement conservé |
| Suspendre | M ✓ traitement ; SA C ; SYS C | Suspension du traitement, pas effacement du signalement ; la cible suit sa propre règle |
| Supprimer | SA 4Y | Uniquement selon conservation, droit ou contenu illicite ; M ne supprime pas une preuve |
| Restaurer | SA 4Y | Cas exceptionnel après erreur démontrée |
| Archiver | M ✓ ; SA C ; SYS Auto | Après décision et délai de recours |
| Exporter | M C ; SEO C agrégé ; F C financier ; SA C | Données minimisées ; export nominatif exceptionnel et justifié |

## 4.8 Ville

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO C proposition ; SA 4Y | Validation géographique et contrôle des impacts sur annonces et URL |
| Consulter | Tous ✓ selon visibilité ; SYS C | Référentiel public ; informations internes de gouvernance limitées |
| Modifier | SEO C proposition ; SA 4Y | Nom, slug, rattachement et aliases contrôlés ; pas de changement direct par M ou C |
| Publier | SEO ✓ proposition ; SA C validation | Publication éditoriale distincte de la validité du référentiel |
| Retirer | SEO C proposition ; SA 4Y | Aucune annonce ne doit devenir orpheline ; continuité des URL décidée |
| Suspendre | SEO C ; SA C | Masquage temporaire pour anomalie grave ; impact catalogue évalué |
| Supprimer | — | Une ville utilisée n'est pas supprimée ; elle est fusionnée, retirée ou archivée selon décision |
| Restaurer | SEO C ; SA 4Y | Contrôle de cohérence et d'URL |
| Archiver | SEO C ; SA 4Y | Seulement après traitement de toutes les dépendances métier |
| Exporter | SEO ✓ ; M C ; C C ; SA C | Référentiel ou analyse ; aucune donnée personnelle |

## 4.9 Quartier

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO C proposition ; SA 4Y | Ville de rattachement et nom local validés |
| Consulter | Tous ✓ selon visibilité ; SYS C | Référentiel public |
| Modifier | SEO C proposition ; SA 4Y | Aliases, orthographe et rattachement contrôlés ; impact sur annonces et SEO |
| Publier | SEO ✓ proposition ; SA C validation | La page publique exige une qualité suffisante ; le quartier peut exister sans page indexable |
| Retirer | SEO C proposition ; SA 4Y | Fusion ou retrait sans orphelin ; traitement des URL |
| Suspendre | SEO C ; SA C | Anomalie, doublon ou litige de dénomination |
| Supprimer | — | Un quartier utilisé n'est pas effacé |
| Restaurer | SEO C ; SA 4Y | Après contrôle géographique et SEO |
| Archiver | SEO C ; SA 4Y | Toutes les annonces doivent être rattachées ou isolées avant décision |
| Exporter | SEO ✓ ; M C ; C C ; SA C | Référentiel ou analyse validée |

## 4.10 Catégorie

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO C ; M C avis ; SA 4Y | Besoin produit démontré, règles de classement et impacts validés |
| Consulter | Tous ✓ public ; SYS C | Catégories actives publiques ; versions en préparation limitées |
| Modifier | SEO C proposition ; M C avis ; SA 4Y | Aucun changement rétroactif silencieux du sens d'une annonce |
| Publier | SEO ✓ proposition ; SA C validation | Page publique seulement si contenu et inventaire suffisants |
| Retirer | SEO C ; M C avis ; SA 4Y | Reclassement des annonces et continuité SEO obligatoires |
| Suspendre | SEO C ; SA C | Anomalie ou risque de mauvaise classification |
| Supprimer | — | Une catégorie utilisée n'est pas effacée ; fusion ou archivage privilégiés |
| Restaurer | SEO C ; SA 4Y | Revalidation du sens, du classement et des URL |
| Archiver | SEO C ; M C avis ; SA 4Y | Plus aucune annonce active ne doit en dépendre |
| Exporter | SEO ✓ ; M ✓ ; C C ; SA C | Référentiel ou analyse, sans données hors finalité |

## 4.11 Page éditoriale

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO ✓ ; SA C | Auteur, objectif et propriétaire éditorial identifiés |
| Consulter | V ✓ publique ; tous rôles ✓ publique ; SEO ✓ toutes ; SA C toutes | Brouillons limités aux acteurs éditoriaux habilités |
| Modifier | SEO ✓ ; SA C | Historique conservé ; relecture selon sensibilité |
| Publier | SEO ✓ ; SA 4Y si légale ou sensible | Le rôle Finance peut relire une page financière mais ne la publie pas |
| Retirer | SEO ✓ ; SA C | Impacts de navigation et d'URL évalués |
| Suspendre | SEO ✓ ; SA C | Information obsolète, litige, risque ou attente de validation |
| Supprimer | SEO C proposition ; SA 4Y | Conservation et continuité d'URL vérifiées |
| Restaurer | SEO ✓ ; SA C si sensible | Nouvelle revue du contenu et de sa date |
| Archiver | SEO ✓ ; SA C | Page non active, historique conservé selon règle |
| Exporter | SEO ✓ ; SA C | Contenu éditorial ; données associées exclues sauf finalité distincte |

## 4.12 Guide

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO ✓ ; C C contribution ; M C contribution ; SA C | Contributions métier possibles, responsabilité éditoriale unique |
| Consulter | V ✓ publié ; tous rôles ✓ publié ; SEO ✓ tous ; SA C tous | Brouillons réservés aux contributeurs habilités |
| Modifier | SEO ✓ ; C C contribution ; M C contribution ; SA C | C et M proposent dans leur domaine, sans publication autonome |
| Publier | SEO ✓ ; SA 4Y si sensible | Exactitude, sources, date et utilité validées |
| Retirer | SEO ✓ ; SA C | Obsolescence, droit, faible qualité ou stratégie éditoriale |
| Suspendre | SEO ✓ ; SA C | Doute sur exactitude, droit ou actualité |
| Supprimer | SEO C proposition ; SA 4Y | Continuité SEO et conservation évaluées |
| Restaurer | SEO ✓ ; SA C si sensible | Relecture complète obligatoire |
| Archiver | SEO ✓ ; SA C | Guide clôturé mais historique conservé selon règle |
| Exporter | SEO ✓ ; SA C | Contenu et éléments dont les droits le permettent |

## 4.13 Sitemap

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | SEO ✓ définition ; SYS Auto génération ; SA C | Structure et familles validées par SEO |
| Consulter | V ✓ public ; SEO ✓ ; SA ✓ ; SYS C ; autres rôles ✓ public | État de contrôle interne limité aux acteurs concernés |
| Modifier | SEO ✓ règles ; SA C ; SYS Auto contenu | Aucun ajout manuel d'une annonce non Publiée |
| Publier | SEO ✓ validation ; SYS Auto ; SA C | Seulement des URL canoniques éligibles |
| Retirer | SEO ✓ ; SA C ; SYS Auto | Retrait d'un ensemble ou d'une URL selon état métier |
| Suspendre | SEO ✓ ; SA C | Anomalie grave, pollution ou décision de contrôle |
| Supprimer | SEO C ; SA 4Y | Une version active n'est supprimée qu'après remplacement ou retrait approuvé |
| Restaurer | SEO ✓ ; SA C | Nouvelle vérification d'éligibilité |
| Archiver | SEO ✓ ; SYS Auto ; SA C | Historique de contrôle selon durée approuvée |
| Exporter | SEO ✓ ; SA C | Données publiques ou rapports de qualité uniquement |

## 4.14 Paramètres métier

Les paramètres métier sont des règles configurables approuvées : durées de publication, délais, seuils, motifs, limites, conditions commerciales ou règles éditoriales. Ils ne comprennent jamais des secrets, des droits d'accès individuels ou du contenu exécutable.

| Action | Autorisation | Conditions et limites |
|---|---|---|
| Créer | Propriétaire métier C ; SA 4Y | Définition, justification, date d'effet et impacts documentés |
| Consulter | M C ; C C ; SEO C ; F C ; SA ✓ ; SYS C | Chaque rôle consulte uniquement les paramètres de son domaine ; V/P/PRO voient seulement les règles publiques applicables |
| Modifier | Propriétaire métier C ; SA 4Y | Double validation pour tout effet sur publication, prix, droits, conservation ou SEO |
| Publier | SA 4Y | Mise en vigueur distincte de la rédaction ; date d'effet explicite |
| Retirer | Propriétaire métier C ; SA 4Y | Effets sur dossiers en cours déterminés avant retrait |
| Suspendre | SA 4Y | Mesure exceptionnelle et temporaire, avec règle de retour |
| Supprimer | — | Un paramètre ayant produit des effets n'est pas effacé ; il est clôturé et archivé |
| Restaurer | SA 4Y | Nouvelle validation complète et date d'effet |
| Archiver | Propriétaire métier C ; SA C ; SYS Auto | Après fin d'application et conservation de l'historique |
| Exporter | Propriétaire métier C ; SA C | Référentiel métier approuvé, sans informations sensibles étrangères à la finalité |

---

# 5. Permissions spéciales

## 5.1 Soumettre une annonce

P et PRO peuvent soumettre leurs propres annonces. Soumettre n'est pas Publier. M décide selon la modération ; SA peut agir uniquement dans le même cadre.

## 5.2 Demander une correction

M peut placer une annonce À corriger avec motif. SA peut le faire dans le même cadre. C peut transmettre une explication, mais ne change pas l'état et ne décide pas que la correction est suffisante.

## 5.3 Lever une suspension

M peut lever une suspension ordinaire si son habilitation le prévoit et s'il n'est pas à l'origine d'un conflit nécessitant escalade. Une suspension grave, juridique, financière ou liée à un compte exige le rôle propriétaire du risque et éventuellement une double validation.

## 5.4 Fusionner des ressources

La fusion d'un Professionnel, d'une Ville, d'un Quartier ou d'une Catégorie est une action sensible distincte. Elle exige :

- preuve de doublon ou décision de référentiel ;
- analyse des impacts ;
- choix explicite de la ressource de référence ;
- traitement des contenus et historiques ;
- validation à quatre yeux ;
- journal complet.

## 5.5 Modifier une URL ou une identité géographique

SEO propose et contrôle la continuité publique. SA valide les changements à fort impact. M, C et F ne modifient ni slug, ni ville, ni quartier de référence en dehors de leur contribution au diagnostic.

## 5.6 Rembourser

F prépare et justifie. Une seconde personne habilitée valide selon les seuils. SA ne remplace pas la preuve financière et ne peut approuver seul sa propre demande.

## 5.7 Exporter des données sensibles

L'export exige : finalité, périmètre, destinataire, durée d'usage et propriétaire. Un export massif, nominatif, financier ou lié à un litige requiert une double validation. Le droit de consulter n'implique jamais automatiquement le droit d'exporter.

## 5.8 Agir au nom d'un utilisateur

L'assistance peut guider ou préparer une action lorsque le mandat est vérifié. Elle ne doit pas :

- accepter des conditions à la place de l'utilisateur sans mandat recevable ;
- publier au nom de l'utilisateur ;
- masquer l'identité réelle de l'acteur ;
- modifier silencieusement une information substantielle ;
- contourner une suspension ou un refus.

## 5.9 Action de masse

Toute publication, suspension, retrait, archivage, modification de référentiel ou export portant sur un ensemble important de ressources exige contrôle d'impact, aperçu du périmètre, justification, double validation et rapport de résultat.

---

# 6. Séparation des responsabilités

## 6.1 Catalogue et commerce

- M contrôle la conformité des annonces ;
- C gère la relation et prépare les offres ;
- C ne décide jamais de la publication ;
- M ne modifie jamais une offre commerciale ;
- un objectif de vente ne change pas une décision de modération.

## 6.2 Contenu et annonces

- SEO gère pages, guides, catégories publiques, URL et sitemaps ;
- SEO ne modifie jamais une annonce ou un média d'annonce ;
- M gère la conformité de l'annonce mais ne réécrit pas une page éditoriale hors contribution demandée.

## 6.3 Finance et publication

- F confirme les faits financiers ;
- M confirme la conformité d'une annonce ;
- C confirme les conditions commerciales ;
- aucun de ces faits ne remplace les deux autres ;
- F ne publie aucune ressource publique.

## 6.4 Habilitations et exécution

- SA accorde ou retire les habilitations selon une décision approuvée ;
- SA ne doit pas valider seul une action qu'il a initiée lorsque quatre yeux sont requis ;
- le propriétaire métier d'une règle ne dispose pas automatiquement du droit de la mettre en vigueur seul.

## 6.5 Signalement et décision

Un acteur peut signaler une anomalie sans devenir automatiquement décideur. Lorsqu'il est personnellement impliqué, il transmet le dossier à un autre acteur habilité.

---

# 7. Double validation — principe des quatre yeux

## 7.1 Définition

Une action à quatre yeux exige deux personnes distinctes, habilitées et indépendantes dans le dossier. L'initiateur ne peut être son propre valideur, même s'il cumule plusieurs rôles.

## 7.2 Actions obligatoirement concernées

- publication ou modification d'une offre commerciale active ;
- modification d'un prix, d'une durée ou d'un effet commercial déjà applicable ;
- remboursement au-delà du seuil à définir ;
- correction exceptionnelle d'un paiement ;
- export massif ou sensible ;
- fusion de professionnels ;
- fusion, retrait ou archivage d'une ville, d'un quartier ou d'une catégorie utilisée ;
- changement massif d'URL ;
- suppression définitive d'un ensemble de ressources ;
- restauration exceptionnelle après décision grave ;
- changement d'un paramètre affectant publication, conservation, prix, droits ou référencement ;
- action de masse sur les annonces ;
- attribution ou élévation d'un rôle interne sensible ;
- décision sur un dossier où le premier décideur est en conflit d'intérêts.

## 7.3 Validations croisées recommandées

| Action | Préparation | Validation indépendante |
|---|---|---|
| Offre commerciale | C | SA avec avis F lorsque financier |
| Remboursement | F | Second responsable Finance ou SA habilité |
| Fusion de professionnels | C ou SA | Second SA ou responsable métier indépendant |
| Référentiel géographique | SEO | SA après avis métier local |
| Action de masse annonces | M | Second M senior ou SA |
| Export sensible | Rôle demandeur | SA ou responsable de la donnée |
| Paramètre de publication | Propriétaire métier | SA et avis du domaine impacté |

## 7.4 Refus et expiration

Une validation non donnée dans le délai prévu vaut absence d'autorisation, jamais accord implicite. Toute validation possède une portée et une durée ; elle ne peut pas être réutilisée pour une autre action.

---

# 8. Journalisation obligatoire

## 8.1 Actions toujours journalisées

- création, modification de statut, retrait, suspension, suppression, restauration et archivage ;
- publication d'une annonce, page, guide, professionnel, offre ou sitemap ;
- décision de modération ;
- changement d'image principale par l'équipe ;
- modification d'un paiement ;
- remboursement ;
- changement de prix ou d'offre ;
- fusion de ressources ;
- changement de ville, quartier, catégorie ou URL ;
- modification de paramètre métier ;
- attribution, modification ou retrait d'une habilitation ;
- export ;
- action de masse ;
- consultation exceptionnelle de données sensibles ;
- action du Système produisant un effet métier.

## 8.2 Informations minimales

Le journal indique :

- acteur réel ;
- rôle utilisé ;
- action ;
- ressource et périmètre ;
- état ou valeur avant et après lorsque pertinent ;
- date métier ;
- motif ;
- origine de la demande ;
- seconde validation lorsqu'exigée ;
- résultat ;
- éventuelle référence de recours ou d'incident.

## 8.3 Règles

- aucun acteur ne modifie son propre journal ;
- une correction de journal est un nouvel événement explicatif ;
- le journal ne doit pas exposer inutilement des secrets ou données personnelles ;
- le droit d'agir n'implique pas le droit de consulter tous les journaux ;
- les durées de conservation sont définies selon la finalité, la sécurité et les obligations ;
- les actions de SA et SYS sont soumises au même niveau de traçabilité.

---

# 9. Actions interdites

Sont interdites sans exception ordinaire :

1. publier une annonce par C ;
2. contourner une modération par C ;
3. modifier une offre commerciale par M ;
4. modifier une annonce ou un média d'annonce par SEO ;
5. publier une annonce, une offre, une page, un guide ou un professionnel par F ;
6. considérer SA comme exempté du cycle métier ;
7. publier une annonce en raison d'un paiement seul ;
8. supprimer un historique afin de masquer une décision ;
9. restaurer directement une annonce Archivée ;
10. réutiliser une validation à quatre yeux pour un autre périmètre ;
11. approuver sa propre action à quatre yeux ;
12. exporter au seul motif que la consultation est autorisée ;
13. consulter des données personnelles par curiosité ou hors mission ;
14. modifier silencieusement l'identité d'un utilisateur ou professionnel ;
15. supprimer une ville, un quartier ou une catégorie encore utilisée ;
16. publier manuellement dans un sitemap une ressource non éligible ;
17. effacer un paiement utilisé pour une obligation ou un litige ;
18. modifier rétroactivement une offre sans traiter les engagements existants ;
19. agir au nom d'un utilisateur sans mandat vérifié ;
20. utiliser SYS pour automatiser un jugement humain non défini ;
21. effectuer une action de masse sans contrôle d'impact ;
22. rendre publics des paramètres internes, motifs sensibles ou données protégées ;
23. cumuler des rôles pour contourner une séparation de responsabilités ;
24. confondre retrait public, archivage et suppression définitive.

---

# 10. Cas exceptionnels

## 10.1 Urgence de sécurité ou contenu illicite

M ou SA habilité peut suspendre immédiatement la ressource concernée. L'urgence autorise le retrait de visibilité, pas l'effacement de l'historique ni le contournement de la revue ultérieure.

## 10.2 Injonction légale

SA coordonne avec les responsables habilités. Le retrait, le gel, l'export ou la conservation suivent la portée exacte de l'injonction. Les accès et actions restent journalisés.

## 10.3 Compte interne compromis

Les habilitations de l'acteur sont suspendues. Les actions récentes sont revues par une personne indépendante. Une restauration d'accès exige une nouvelle validation.

## 10.4 Indisponibilité d'un valideur

Un suppléant formellement habilité peut intervenir. L'urgence ou l'absence ne permet jamais à l'initiateur de s'auto-valider.

## 10.5 Erreur administrative

La correction rétablit la situation conforme sans effacer l'événement initial. Si la correction constitue elle-même une action sensible, elle suit la double validation.

## 10.6 Conflit d'intérêts

L'acteur se retire du dossier et le transmet. Sont notamment concernés : relation personnelle ou commerciale directe, intérêt financier, annonce propre, contestation visant l'acteur ou décision antérieure faisant l'objet d'un recours.

## 10.7 Migration

Les opérations de migration ne donnent pas de droits plus larges que les règles métier. Les cas ambigus sont isolés. Les publications, restaurations, fusions et suppressions issues de la migration suivent les validations correspondantes.

## 10.8 Action automatique erronée

SYS cesse la règle concernée selon la procédure d'incident. Les ressources affectées sont identifiées. Toute correction de masse suit le contrôle d'impact, la validation et le rapport requis.

## 10.9 Absence de rôle compétent

Si aucun rôle n'est officiellement habilité, l'action reste interdite. SA ne s'attribue pas implicitement la compétence : une décision de gouvernance doit d'abord désigner le propriétaire et les règles.

---

# 11. Critères d'acceptation

## 11.1 Couverture

- les neuf acteurs officiels sont définis ;
- les quatorze ressources sont couvertes ;
- chaque ressource traite créer, consulter, modifier, publier, retirer, suspendre, supprimer, restaurer, archiver et exporter ;
- toute absence de droit est interprétée comme interdiction ;
- propriété, périmètre et conditions sont explicites.

## 11.2 Contraintes absolues

- C ne peut jamais publier une annonce ;
- C ne peut jamais contourner M ;
- M ne peut jamais modifier une offre commerciale ;
- SEO ne peut jamais modifier une annonce ou son média ;
- F ne peut jamais publier ;
- SA reste soumis aux règles métier, à la séparation et au journal ;
- SYS ne prend aucune décision discrétionnaire.

## 11.3 Séparation

- paiement, commerce, modération et contenu sont des responsabilités distinctes ;
- une même personne ne satisfait pas seule les quatre yeux ;
- le cumul de rôles est explicite ;
- un conflit d'intérêts entraîne dessaisissement ;
- un signalant ne devient pas automatiquement décideur.

## 11.4 Actions sensibles

- les actions à quatre yeux sont identifiées ;
- initiateur et valideur sont distincts ;
- la validation possède une portée et une durée ;
- action de masse, export sensible, fusion et suppression définitive sont contrôlés ;
- l'urgence retire le risque sans effacer les contrôles ultérieurs.

## 11.5 Journal

- chaque action sensible indique acteur, rôle, ressource, motif, résultat et validations ;
- les actions de SA et SYS sont journalisées ;
- un journal n'est pas réécrit ;
- une correction produit une nouvelle trace ;
- la consultation du journal respecte la finalité et la confidentialité.

## 11.6 Ressources publiques

- la publication d'une Annonce relève de M selon le cycle officiel ;
- la publication d'une Page ou d'un Guide relève de SEO ;
- la publication d'une Offre exige quatre yeux ;
- la publication d'un Professionnel suit une vérification distincte de C ;
- le Sitemap ne contient que des ressources éligibles ;
- F ne publie aucune ressource.

## 11.7 Utilisateurs

- P et PRO agissent uniquement sur leurs ressources ;
- la soumission ne vaut pas publication ;
- leurs demandes de suppression respectent droits et obligations ;
- l'assistance ne masque jamais l'acteur réel ;
- un export personnel ne donne pas accès aux données de tiers.

---

# 12. Questions ouvertes

## Niveau 1 — Bloquantes

1. Qui valide officiellement un nouveau Professionnel et sa publication publique ?
2. Un Modérateur peut-il lever seul toutes les suspensions ordinaires ou faut-il un niveau senior ?
3. Quels seuils financiers imposent une double validation de remboursement ?
4. Quel volume définit une action de masse ?
5. Quel volume ou niveau de sensibilité définit un export soumis à quatre yeux ?
6. Qui est propriétaire de chaque famille de Paramètres métier ?
7. Qui valide les changements géographiques : SA seul après avis ou comité de référentiel ?
8. Quels rôles peuvent traiter un recours contre une décision de modération ?
9. Quelles pages sont considérées légales ou sensibles et exigent quatre yeux ?
10. Quelle personne indépendante valide une action sensible initiée par SA ?

## Niveau 2 — À trancher avant exploitation

11. M peut-il corriger une faute purement formelle dans une annonce sans retour au propriétaire ?
12. C peut-il créer entièrement un dossier Professionnel ou seulement assister le demandeur ?
13. Quelles informations financières limitées C peut-il consulter ?
14. F peut-il suspendre seul un Paiement suspect ou faut-il une confirmation rapide ?
15. Quels membres peuvent consulter les données d'un signalant ?
16. Quelle durée maximale s'applique à une habilitation temporaire ?
17. Quel processus approuve le cumul de rôles ?
18. Quels événements justifient une consultation sensible journalisée ?
19. Qui décide qu'un média contesté peut être restauré ?
20. Qui arbitre les fusions de Professionnels issues de la migration ?
21. Le rôle Lecture/Audit évoqué dans le Blueprint doit-il devenir un dixième acteur officiel ?
22. Faut-il un rôle juridique distinct pour les injonctions et litiges ?

## Niveau 3 — Gouvernance continue

23. À quelle fréquence les habilitations sont-elles revues ?
24. Quelle durée de conservation s'applique à chaque famille de journaux ?
25. Quels indicateurs détectent un usage anormal des permissions ?
26. Quelle procédure s'applique au départ ou changement de fonction d'un membre ?
27. Les exports doivent-ils tous avoir une date d'expiration d'usage métier ?
28. Quels paramètres peuvent être publics et lesquels restent internes ?
29. Comment les suppléances sont-elles désignées et révoquées ?
30. Une commission indépendante est-elle nécessaire pour les recours majeurs ou les conflits d'intérêts impliquant SA ?

---

# Résumé des rôles

- **Visiteur :** consulte le public et peut signaler ; aucune mutation du catalogue.
- **Particulier :** gère ses propres profil, annonces et médias ; soumet mais ne publie pas.
- **Professionnel :** gère son organisation et son portefeuille ; aucun privilège de modération.
- **Modérateur :** décide de la conformité des annonces, médias et signalements ; ne touche jamais aux offres commerciales.
- **Commercial :** accompagne les professionnels et prépare les offres ; ne publie jamais une annonce et ne contourne jamais la modération.
- **Responsable SEO / Contenu :** gouverne pages, guides, référentiels publics, URL et sitemaps ; ne modifie jamais une annonce.
- **Finance :** contrôle paiements et rapprochements ; ne publie aucune ressource.
- **Super Administrateur :** supervise habilitations et exceptions ; ne contourne aucune règle, ne s'auto-valide pas et reste audité.
- **Système :** exécute les règles automatiques approuvées ; ne remplace pas le jugement humain.

# Ambiguïtés restantes

Les principaux arbitrages portent sur :

- le rôle de validation des Professionnels ;
- le niveau requis pour lever certaines suspensions ;
- les seuils de double validation financière, d'export et d'action de masse ;
- les propriétaires des Paramètres métier ;
- la gouvernance du référentiel géographique ;
- le traitement des recours ;
- la validation indépendante des actions sensibles de SA ;
- l'étendue de l'assistance du Commercial et des corrections formelles du Modérateur ;
- la création éventuelle de rôles Lecture/Audit et Juridique ;
- la fréquence de revue et la durée des habilitations.

# Confirmation de périmètre

Ce document définit exclusivement des responsabilités, permissions, interdictions et contrôles métier. Aucun code ni mécanisme de réalisation n'a été créé ou défini.

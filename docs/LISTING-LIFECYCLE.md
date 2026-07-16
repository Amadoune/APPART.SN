# APPART.SN REBUILD 2026 — Cycle de vie officiel d'une annonce

## Statut du document

- **Sprint :** 3 — Document 1
- **Version :** 1.0
- **Date :** 16 juillet 2026
- **Statut :** proposition métier soumise à validation
- **Référence :** `MASTER-BLUEPRINT.md`
- **Nature :** règles métier exclusivement
- **Hors périmètre :** code, framework, API, modèle de données, architecture technique et choix d'implémentation

## Objet

Ce document établit le cycle de vie officiel d'une annonce immobilière sur APPART.SN. Il définit les états autorisés, les décisions qui permettent de passer d'un état à un autre, les acteurs responsables et les conséquences publiques, administratives, SEO et statistiques.

Une annonce ne peut avoir qu'un seul état officiel à un instant donné. Les notions telles que « premium », « urgente », « remontée », « à la une », « professionnelle » ou « très consultée » ne sont pas des états : ce sont des attributs commerciaux, éditoriaux ou statistiques qui ne modifient pas le cycle de vie.

## Principes métier

1. Une annonce ne devient jamais publique sans décision de publication.
2. Une décision de modération est motivée et traçable.
3. L'annonceur connaît toujours l'état de son annonce et, lorsqu'une action est attendue, la prochaine étape possible.
4. Une annonce non publique n'apparaît ni dans la recherche publique ni dans les sitemaps.
5. L'expiration n'est pas une suppression.
6. Le retrait par l'annonceur n'efface pas immédiatement l'historique métier.
7. L'archivage est l'état terminal normal ; la suppression définitive relève d'une politique distincte de conservation.
8. Une option commerciale ne peut ni contourner la modération ni prolonger silencieusement une annonce.
9. Les transitions automatiques sont explicables, datées et visibles dans l'historique administratif.
10. Une anomalie, un litige ou une obligation légale peut geler les transitions ordinaires sans altérer l'historique.

---

# 1. États possibles

## 1.1 Liste officielle

| État | Définition | Public | Modifiable par l'annonceur | Terminal |
|---|---|---:|---:|---:|
| **Brouillon** | Annonce commencée mais non soumise à APPART.SN | Non | Oui | Non |
| **Soumise** | Annonce envoyée par l'annonceur et placée dans la file de traitement | Non | Non, sauf annulation | Non |
| **En modération** | Annonce prise en charge et en cours de contrôle | Non | Non, sauf annulation demandée | Non |
| **À corriger** | Publication impossible sans correction par l'annonceur | Non | Oui, sur les éléments demandés | Non |
| **Publiée** | Annonce validée, visible, recherchable et contactable | Oui | Oui, sous conditions | Non |
| **Suspendue** | Publication interrompue par APPART.SN à titre préventif ou disciplinaire | Non | Non, sauf éléments demandés | Non |
| **Expirée** | Durée de publication terminée sans renouvellement effectif | Non | Oui, pour renouveler si éligible | Non |
| **Retirée** | Annonce volontairement retirée ou annulée par son propriétaire | Non | Limitée à une éventuelle republication autorisée | Non |
| **Refusée** | Annonce définitivement non admise dans sa forme ou son objet soumis | Non | Non ; une nouvelle soumission peut être permise | Non |
| **Archivée** | Annonce clôturée et conservée uniquement pour historique, obligations ou analyse | Non | Non | Oui |

## 1.2 Distinctions obligatoires

### Soumise et En modération

**Soumise** signifie que l'annonce attend une prise en charge. **En modération** signifie qu'un contrôle a commencé. Cette distinction permet de mesurer séparément le délai d'attente et le temps de traitement.

### À corriger et Refusée

**À corriger** signifie que l'annonce peut devenir conforme sans changer fondamentalement sa nature. **Refusée** signifie que la soumission ne peut pas être publiée telle quelle ou relève d'un motif non régularisable dans le même dossier.

### Suspendue et Expirée

**Suspendue** résulte d'une décision de contrôle, d'un signalement, d'un risque ou d'une violation. **Expirée** résulte uniquement de la fin normale de la période de publication.

### Retirée et Archivée

**Retirée** est une clôture volontaire encore identifiable comme action utilisateur. **Archivée** est la clôture terminale du cycle opérationnel.

### Indisponible

« Indisponible » n'est pas un état officiel. C'est un message public éventuel pouvant représenter une annonce expirée, retirée ou archivée dont la page est temporairement conservée pour assurer une continuité de navigation ou de référencement.

### Supprimée

« Supprimée » n'est pas un état fonctionnel. La suppression définitive est une opération de gouvernance des données, appliquée après archivage selon les obligations légales, les demandes recevables et la politique de conservation.

---

# 2. Transitions autorisées

## 2.1 Matrice officielle

| État de départ | État d'arrivée | Condition métier principale |
|---|---|---|
| Brouillon | Soumise | Champs obligatoires complets et confirmation de l'annonceur |
| Brouillon | Retirée | Abandon volontaire du dépôt |
| Brouillon | Archivée | Abandon ancien selon la durée de conservation des brouillons |
| Soumise | En modération | Prise en charge du dossier |
| Soumise | Retirée | Annulation demandée avant décision |
| En modération | Publiée | Contrôle favorable |
| En modération | À corriger | Anomalies régularisables |
| En modération | Refusée | Non-conformité non régularisable dans le même dossier |
| En modération | Retirée | Annulation acceptée avant décision finale |
| À corriger | Soumise | Corrections effectuées et nouvelle soumission |
| À corriger | Retirée | Abandon par l'annonceur |
| À corriger | Archivée | Absence de correction après le délai défini |
| Publiée | En modération | Modification substantielle nécessitant un nouveau contrôle |
| Publiée | Suspendue | Risque, signalement recevable ou non-conformité constatée |
| Publiée | Expirée | Fin de la période de publication |
| Publiée | Retirée | Retrait volontaire, bien indisponible ou transaction conclue |
| Suspendue | Publiée | Contrôle favorable ou régularisation validée |
| Suspendue | À corriger | Régularisation possible par l'annonceur |
| Suspendue | Refusée | Violation confirmée rendant la publication inadmissible |
| Suspendue | Archivée | Clôture définitive du dossier après décision |
| Expirée | En modération | Renouvellement nécessitant un nouveau contrôle |
| Expirée | Publiée | Renouvellement simple autorisé et contrôles toujours valides |
| Expirée | Retirée | L'annonceur confirme que le bien n'est plus disponible |
| Expirée | Archivée | Délai de renouvellement dépassé |
| Retirée | En modération | Demande de republication recevable |
| Retirée | Archivée | Fin du délai de réactivation ou clôture demandée |
| Refusée | Archivée | Fin du délai de recours ou confirmation définitive |

## 2.2 Transitions interdites

Sont notamment interdites :

- Brouillon vers Publiée sans soumission et contrôle ;
- À corriger vers Publiée sans nouvelle soumission ;
- Refusée vers Publiée sans nouveau contrôle formel ;
- Archivée vers n'importe quel autre état ;
- option payante vers Publiée sans modération ;
- Expirée vers Publiée lorsque l'annonceur, le bien ou le contenu ne sont plus éligibles ;
- modification substantielle d'une annonce Publiée sans réévaluation ;
- suppression d'un état ou de son historique afin de masquer une décision antérieure.

Une annonce archivée ne se réactive jamais. Si le métier autorise une reprise, une nouvelle annonce est créée à partir des informations admissibles et suit un nouveau cycle.

---

# 3. Acteurs autorisés

## 3.1 Annonceur

L'annonceur est le particulier ou le professionnel propriétaire fonctionnel de l'annonce.

Il peut :

- créer et modifier un Brouillon ;
- soumettre ;
- corriger une annonce À corriger ;
- demander le retrait d'une annonce non archivée ;
- retirer une annonce Publiée ;
- demander le renouvellement d'une annonce Expirée ;
- demander la republication d'une annonce Retirée lorsqu'elle reste éligible ;
- consulter les décisions et motifs qui le concernent ;
- exercer un recours selon la procédure prévue.

Il ne peut pas :

- publier directement ;
- lever une suspension ;
- modifier un motif de modération ;
- restaurer une annonce archivée ;
- effacer l'historique administratif.

## 3.2 Modérateur

Le modérateur peut :

- prendre en charge une annonce Soumise ;
- publier, demander une correction ou refuser ;
- suspendre préventivement ou après contrôle ;
- lever une suspension si son niveau d'habilitation le permet ;
- qualifier les signalements ;
- archiver un dossier clôturé selon les règles ;
- ajouter un motif et une note interne.

Le modérateur ne peut pas modifier silencieusement l'annonce au nom de l'annonceur. Une correction purement formelle éventuellement autorisée doit être identifiée comme telle et tracée.

## 3.3 Super administrateur

Il possède les capacités du modérateur et peut en plus traiter les recours, cas juridiques, erreurs administratives, conflits de propriété et décisions exceptionnelles. Ses décisions restent motivées et auditées.

## 3.4 Commercial

Le commercial peut accompagner un annonceur et consulter les informations nécessaires à sa mission. Il ne peut ni contourner la modération, ni publier une annonce, ni lever une suspension du seul fait d'un paiement ou d'une relation commerciale.

## 3.5 Responsable SEO ou contenu

Il peut décider du traitement public d'une ancienne URL dans le cadre des règles approuvées. Il ne change pas l'état métier d'une annonce et ne rend pas publiable une annonce non validée.

## 3.6 Système

Le système peut exécuter uniquement les transitions automatiques préalablement approuvées :

- Soumise vers En modération lors d'une affectation automatique formalisée ;
- Publiée vers Expirée à l'échéance ;
- Brouillon ou À corriger vers Archivée après le délai défini ;
- Expirée ou Retirée vers Archivée après le délai défini ;
- notifications de rappel ;
- retrait immédiat de la visibilité lors d'un gel de sécurité clairement défini.

Une transition automatique doit toujours indiquer sa règle déclenchante.

---

# 4. Déclencheurs

| Déclencheur | Transition ou action attendue | Nature |
|---|---|---|
| Confirmation du dépôt | Brouillon → Soumise | Annonceur |
| Prise en charge | Soumise → En modération | Modération ou règle d'affectation |
| Contrôle favorable | En modération → Publiée | Modération |
| Information manquante ou corrigeable | En modération → À corriger | Modération |
| Contenu inadmissible | En modération → Refusée | Modération |
| Modification substantielle publiée | Publiée → En modération | Annonceur ou modération |
| Modification non substantielle autorisée | Maintien Publiée avec trace | Annonceur ou modération |
| Signalement crédible | Publiée → Suspendue ou contrôle prioritaire | Modération |
| Risque de fraude ou sécurité | Publiée → Suspendue | Modération ou règle d'urgence |
| Fin de durée | Publiée → Expirée | Système |
| Bien loué, vendu ou indisponible | Publiée → Retirée | Annonceur ou modération justifiée |
| Demande de renouvellement | Expirée → Publiée ou En modération | Annonceur |
| Régularisation validée | Suspendue → Publiée | Modération |
| Absence de correction | À corriger → Archivée | Système après délai |
| Fin de fenêtre de réactivation | Expirée ou Retirée → Archivée | Système après délai |
| Décision juridique | Suspension, refus, archivage ou gel | Super administrateur habilité |

## 4.1 Modification substantielle

Est substantielle toute modification susceptible de changer la décision de publication ou de tromper le visiteur, notamment :

- changement de bien ;
- changement d'intention ;
- changement majeur de localisation ;
- changement de type de bien ;
- remplacement massif des images ;
- modification importante du prix ;
- changement d'annonceur ;
- modification de description révélant une offre différente ;
- ajout d'un contenu soumis à contrôle.

La liste exacte et les seuils doivent être validés par le métier. Une modification substantielle retire l'annonce de la publication jusqu'à nouvelle décision, sauf processus de révision différée expressément approuvé.

## 4.2 Modification non substantielle

Peuvent être considérées comme non substantielles : correction typographique, précision mineure ou mise à jour d'un équipement sans changement de nature. Elles sont tracées mais peuvent maintenir l'état Publiée selon les règles validées.

---

# 5. Notifications

## 5.1 Principes

- toute notification explique l'état, la raison utile, l'action attendue et le délai ;
- une notification ne révèle pas les notes internes, les méthodes de détection ou les données d'un tiers ;
- la notification dans l'espace utilisateur constitue la référence métier ;
- les canaux externes restent à confirmer ;
- les notifications sensibles ne contiennent pas de secret ni de donnée excessive ;
- l'envoi ou l'échec d'une notification ne modifie pas l'état de l'annonce.

## 5.2 Matrice minimale

| Événement | Destinataire | Contenu minimal | Priorité |
|---|---|---|---|
| Soumission reçue | Annonceur | Confirmation et délai indicatif | Normale |
| Prise en charge | Annonceur, facultatif | Début du contrôle | Faible |
| Publication | Annonceur | Date, durée et accès à l'annonce | Haute |
| Correction demandée | Annonceur | Motifs, éléments attendus et échéance | Haute |
| Refus | Annonceur | Motif communicable et recours éventuel | Haute |
| Suspension | Annonceur | Effet immédiat, motif communicable et action possible | Urgente |
| Expiration prochaine | Annonceur | Date d'échéance et conditions de renouvellement | Haute |
| Expiration | Annonceur | Fin de visibilité et options autorisées | Haute |
| Retrait confirmé | Annonceur | Date et effet | Normale |
| Archivage prochain | Annonceur, si action possible | Délai restant | Normale |
| Archivage | Annonceur | Clôture et conséquences | Normale |
| Recours reçu | Annonceur et équipe habilitée | Accusé de réception | Haute |
| Décision de recours | Annonceur | Décision et conséquences | Haute |

Les modérateurs reçoivent les alertes nécessaires aux files, dépassements de délai, signalements prioritaires et recours, sans créer de notifications inutiles pour chaque transition automatique ordinaire.

---

# 6. Effets SEO

## 6.1 Règle générale

Seule une annonce Publiée est indexable par défaut. Son URL publique stable doit rester associée à la même annonce pendant tout son cycle.

| État | Indexation | Sitemap | Traitement de l'URL publique |
|---|---|---|---|
| Brouillon | Interdite | Exclue | Aucune page publique |
| Soumise | Interdite | Exclue | Aucune page publique |
| En modération | Interdite | Exclue | Aucune page publique |
| À corriger | Interdite | Exclue | Aucune page publique |
| Publiée | Autorisée si qualité suffisante | Incluse | Page canonique accessible |
| Suspendue | Interdite pendant suspension | Retirée | Page non indexable ou indisponible selon le risque |
| Expirée | Interdite par défaut | Retirée | Maintien temporaire possible avec message utile |
| Retirée | Interdite | Retirée | Maintien temporaire ou retrait selon le motif |
| Refusée | Interdite | Exclue | Aucune page publique si jamais publiée |
| Archivée | Interdite | Exclue | 404, 410 ou redirection pertinente selon décision SEO |

## 6.2 Annonce anciennement publiée

Une annonce ayant déjà été publique ne doit pas être redirigée automatiquement vers l'accueil ou vers une page sans rapport. À la fin de sa vie publique, trois traitements sont possibles :

1. **maintien temporaire non indexable**, avec statut d'indisponibilité et liens réellement pertinents ;
2. **réponse de disparition**, lorsque l'annonce n'a plus de valeur utile ;
3. **redirection permanente**, uniquement lorsqu'une cible équivalente ou une fusion légitime existe.

La durée du maintien temporaire et les critères de choix restent à valider avec les données SEO externes.

## 6.3 Suspension sensible

Lorsqu'une suspension concerne fraude, contenu illicite, sécurité ou obligation légale, la priorité est le retrait immédiat. La préservation SEO ne peut jamais justifier le maintien du contenu contesté.

## 6.4 Renouvellement

Un renouvellement conserve l'URL historique de la même annonce. Il ne doit pas créer artificiellement une nouvelle page ou remettre à zéro son historique dans le seul but d'obtenir un avantage de classement.

---

# 7. Effets recherche

| État | Résultats publics | Pages géographiques/catégories | Suggestions similaires | Favoris |
|---|---:|---:|---:|---|
| Brouillon | Non | Non comptée | Non | Non disponible publiquement |
| Soumise | Non | Non comptée | Non | Non |
| En modération | Non | Non comptée | Non | Non |
| À corriger | Non | Non comptée | Non | Non |
| Publiée | Oui | Comptée | Éligible | Accessible |
| Suspendue | Non | Non comptée | Non | Signalée indisponible |
| Expirée | Non | Non comptée | Non | Signalée expirée |
| Retirée | Non | Non comptée | Non | Signalée indisponible |
| Refusée | Non | Non comptée | Non | Non |
| Archivée | Non | Non comptée | Non | Retirée des favoris actifs ou marquée indisponible selon politique |

Une annonce ne doit jamais rester dans les résultats après la prise d'effet d'une suspension, d'une expiration ou d'un retrait.

Les compteurs visibles sur les pages de ville, quartier, catégorie et intention ne comptent que les annonces Publiées et réellement éligibles.

Les options de visibilité modifient éventuellement l'ordre selon une règle commerciale transparente, mais jamais l'éligibilité à la recherche.

---

# 8. Effets administration

## 8.1 Files de travail

| État | File administrative principale | Action attendue |
|---|---|---|
| Brouillon | Aucune file de modération | Assistance éventuelle seulement |
| Soumise | À prendre en charge | Affecter et démarrer le contrôle |
| En modération | En cours | Décider ou escalader |
| À corriger | En attente annonceur | Suivre l'échéance |
| Publiée | Catalogue actif | Surveillance et traitement des signalements |
| Suspendue | Prioritaire/risque | Enquêter et décider |
| Expirée | Renouvellements | Suivre selon règles commerciales |
| Retirée | Clôtures/réactivations | Vérifier les demandes exceptionnelles |
| Refusée | Refus et recours | Suivre le délai de recours |
| Archivée | Historique | Lecture contrôlée uniquement |

## 8.2 Historique obligatoire

Chaque transition conserve :

- état de départ et état d'arrivée ;
- date et heure métier ;
- acteur ou règle automatique ;
- motif normalisé ;
- commentaire utile éventuel ;
- origine de la décision : soumission, modification, signalement, échéance, recours ou action administrative ;
- notification attendue et résultat connu ;
- référence de l'événement commercial si celui-ci influence une échéance, sans permettre de contourner la modération.

## 8.3 Permissions

- les listes et actions visibles dépendent du rôle ;
- la publication, le refus, la suspension et l'archivage exigent une permission explicite ;
- le traitement d'un recours ne doit pas être effectué par un acteur non habilité ;
- une action exceptionnelle exige une justification renforcée ;
- aucune action de masse sensible ne doit être possible sans contrôle d'impact et confirmation.

## 8.4 Indicateurs opérationnels

L'administration doit pouvoir suivre :

- stock Soumise ;
- stock En modération ;
- délai médian avant prise en charge ;
- délai médian de décision ;
- taux de correction ;
- taux de publication ;
- taux de refus ;
- suspensions ouvertes ;
- expirations à venir ;
- renouvellements ;
- recours en attente ;
- dépassements de délais internes.

---

# 9. Effets statistiques

## 9.1 Principes

- les changements d'état ne suppriment pas l'historique des mesures déjà acquises ;
- les mesures publiques et internes sont distinguées ;
- une même transition n'est comptée qu'une fois ;
- une republication ou un renouvellement ne crée pas artificiellement une nouvelle annonce statistique ;
- les métriques possèdent une définition, une période et une finalité.

## 9.2 Mesures par phase

### Avant publication

- brouillons commencés ;
- taux de soumission ;
- abandons ;
- temps de complétion ;
- motifs de correction ;
- délai de modération.

### Pendant publication

- impressions dans les résultats, si leur définition est validée ;
- consultations de fiche ;
- contacts téléphone, WhatsApp et message ;
- ajouts aux favoris ;
- signalements ;
- durée active ;
- modifications substantielles.

### Après publication

- motif de retrait ;
- expiration ;
- renouvellement ;
- suspension et issue ;
- archivage ;
- conversion déclarative « loué » ou « vendu », si la fiabilité est suffisante.

## 9.3 Règles d'attribution

- les vues et contacts sont attribués à l'annonce pendant ses périodes Publiées ;
- aucune vue publique ne doit être créée pendant les états non publics ;
- les consultations administratives ne comptent pas comme vues publiques ;
- les robots et abus identifiés ne doivent pas gonfler les statistiques commerciales ;
- après renouvellement, le total historique peut être conservé, mais les périodes doivent rester distinguables ;
- après archivage, les statistiques ne sont conservées que selon la politique de rétention approuvée.

---

# 10. Cas exceptionnels

## 10.1 Suspicion de fraude ou contenu illicite

L'annonce est retirée immédiatement de la visibilité et placée en Suspendue. L'enquête détermine ensuite publication, correction, refus ou archivage. L'annonceur reçoit une information compatible avec les obligations de sécurité et de droit.

## 10.2 Signalement abusif

Un signalement seul ne rend pas automatiquement la violation certaine. Selon sa gravité et sa crédibilité, l'annonce peut rester Publiée pendant le contrôle ou être Suspendue préventivement. La décision est tracée.

## 10.3 Compte annonceur suspendu ou compromis

Les annonces actives du compte sont évaluées. Elles peuvent être suspendues en groupe uniquement selon une règle approuvée et avec traçabilité individuelle ou collective consultable.

## 10.4 Litige de propriété

L'annonce est Suspendue jusqu'à décision. Aucun changement d'annonceur ne doit être effectué sur simple demande non vérifiée.

## 10.5 Décès, incapacité ou représentation

Le traitement est confié à un acteur habilité sur preuve recevable. L'historique reste intact. Le transfert ou retrait suit une procédure spécifique à définir juridiquement.

## 10.6 Incident de paiement

Une option commerciale peut être désactivée selon les règles contractuelles, mais l'annonce ne devient ni Refusée ni Suspendue pour le seul défaut d'une option facultative. Si la publication elle-même dépend d'une offre payante validée, l'état attendu doit être défini avant activation de ce modèle commercial.

## 10.7 Panne ou erreur APPART.SN

Une erreur interne ne doit pas conduire silencieusement à un refus, une expiration ou un archivage. Les corrections administratives sont tracées, les durées affectées peuvent être compensées selon une règle approuvée et les annonceurs concernés sont informés lorsque l'impact est réel.

## 10.8 Modification administrative urgente

Un administrateur peut masquer une donnée dangereuse ou personnelle sans réécrire l'historique. Si l'annonce change substantiellement, elle passe En modération ou Suspendue.

## 10.9 Obligation légale

Une décision légale prévaut sur le cycle ordinaire : retrait immédiat, gel, conservation probatoire ou suppression peuvent s'appliquer. L'accès à l'information est limité aux personnes habilitées.

## 10.10 Doublon d'annonce

Le doublon confirmé peut être Refusé ou Retiré selon son origine. Une éventuelle redirection SEO n'est décidée que si les deux pages représentent réellement le même bien et le même annonceur légitime.

---

# 11. Annulation

## 11.1 Définition

L'annulation est l'action par laquelle l'annonceur interrompt volontairement un dépôt avant publication ou demande l'arrêt d'un traitement en cours. L'état résultant est Retirée, puis Archivée après le délai applicable.

## 11.2 Règles

- un Brouillon peut être annulé immédiatement ;
- une annonce Soumise peut être annulée tant qu'aucune décision finale n'est prise ;
- en cours de modération, la demande d'annulation est prioritaire mais reste tracée ;
- l'annulation d'une annonce Publiée est traitée comme un retrait ;
- une annulation n'efface pas les traces nécessaires à la sécurité, au support ou aux obligations ;
- les conséquences financières éventuelles dépendent de conditions commerciales distinctes ;
- annuler puis resoumettre dans le but de contourner une décision ne doit pas remettre à zéro l'historique de contrôle.

## 11.3 Motifs déclaratifs

Motifs recommandés :

- dépôt abandonné ;
- erreur de saisie majeure ;
- bien déjà loué ;
- bien déjà vendu ;
- bien temporairement indisponible ;
- publication en doublon ;
- autre motif volontaire.

Le motif « loué » ou « vendu » est déclaratif et ne constitue pas à lui seul une preuve de transaction.

---

# 12. Archivage

## 12.1 Finalité

L'archivage clôt le cycle opérationnel. L'annonce n'est plus modifiable, renouvelable, recherchable ou contactable. Elle demeure consultable uniquement par les acteurs habilités pendant la durée de conservation applicable.

## 12.2 Entrées en archivage

Une annonce peut être archivée après :

- abandon prolongé d'un Brouillon ;
- absence de correction ;
- expiration non renouvelée ;
- retrait prolongé ;
- refus devenu définitif ;
- suspension conclue par une clôture ;
- obligation administrative ou légale.

## 12.3 Règles

- Archivée est un état terminal ;
- aucune restauration directe n'est autorisée ;
- une nouvelle annonce peut éventuellement reprendre des informations non sensibles, mais suit un nouveau cycle ;
- l'URL publique reçoit le traitement SEO validé ;
- les relations actives telles que favoris et options commerciales cessent leurs effets ;
- l'historique, les motifs et les preuves nécessaires sont conservés selon leur propre durée ;
- l'archivage ne signifie pas conservation illimitée.

## 12.4 Délais à décider

Les durées avant archivage et les durées de conservation après archivage ne sont pas fixées par le MASTER BLUEPRINT. Elles doivent être validées en fonction du produit, du SEO, du support, des obligations comptables, de la sécurité et de la protection des données.

---

# 13. Expiration

## 13.1 Définition

L'expiration est la fin normale de la période pendant laquelle une annonce est Publiée. Elle est indépendante du nombre de vues, de contacts ou d'options de visibilité, sauf engagement commercial explicite.

## 13.2 Date d'échéance

La date d'échéance doit être connue dès la publication et visible par l'annonceur. Une modification mineure ne la reporte pas. Une suspension ne la prolonge pas automatiquement ; les éventuelles compensations suivent une règle commerciale approuvée.

## 13.3 Rappels

Au moins un rappel doit être prévu avant l'échéance si un renouvellement est possible. Le calendrier exact et les canaux restent à valider.

## 13.4 Prise d'effet

À l'échéance :

- l'annonce passe Expirée ;
- elle disparaît immédiatement de la recherche et des listes publiques ;
- elle n'est plus contactable ;
- elle sort du sitemap ;
- sa page peut être temporairement conservée non indexable selon la politique SEO ;
- les options de visibilité cessent ;
- l'annonceur est informé ;
- la fenêtre de renouvellement commence si l'annonce reste éligible.

## 13.5 Expiration pendant un traitement

Une annonce en correction, modération ou suspension ne doit pas subir une seconde transition contradictoire. Son échéance reste une information métier, mais la décision en cours prévaut. La durée restante ou la nouvelle échéance éventuelle est déterminée lors de la résolution selon une règle approuvée.

---

# 14. Renouvellement

## 14.1 Définition

Le renouvellement prolonge la disponibilité de la même annonce pour une nouvelle période. Il ne crée pas un nouveau bien, une nouvelle identité publique ou un nouvel historique.

## 14.2 Éligibilité

Le renouvellement est possible si :

- l'annonce est Expirée ou approche de son expiration selon la règle retenue ;
- l'annonceur confirme que le bien est toujours disponible ;
- le compte reste autorisé ;
- le contenu et les médias restent conformes ;
- les informations obligatoires restent complètes ;
- aucun signalement ou litige bloquant n'est ouvert ;
- les conditions commerciales applicables sont satisfaites.

## 14.3 Renouvellement simple

Il peut conduire directement de Expirée à Publiée lorsque :

- l'expiration est récente ;
- aucune donnée substantielle n'a changé ;
- le dernier contrôle reste valable ;
- aucun risque nouveau n'est identifié.

## 14.4 Renouvellement avec contrôle

Il conduit de Expirée à En modération lorsque :

- l'annonce est ancienne ;
- une modification substantielle est demandée ;
- les règles de publication ont changé ;
- la qualité ou la disponibilité est incertaine ;
- le compte, le bien ou les médias nécessitent une nouvelle vérification ;
- un signalement ou antécédent le justifie.

## 14.5 Effets

- l'URL de l'annonce reste identique ;
- l'historique complet est conservé ;
- une nouvelle période active est ouverte ;
- les statistiques historiques ne sont pas effacées ;
- les statistiques de la nouvelle période peuvent être distinguées ;
- le renouvellement ne garantit aucun rang artificiel dans la recherche ;
- une option commerciale suit ses propres dates et ne se renouvelle pas implicitement sans accord.

## 14.6 Limites

Le nombre maximal de renouvellements automatiques, la durée de la fenêtre de renouvellement et l'obligation de nouvelle modération restent à fixer. Une annonce trop ancienne ou trop souvent renouvelée doit suivre un nouveau contrôle, voire un nouveau dépôt si sa réalité ne peut plus être raisonnablement confirmée.

---

# 15. Critères d'acceptation

## 15.1 États et transitions

- les dix états officiels sont utilisés avec une définition unique ;
- une annonce possède exactement un état officiel à la fois ;
- chaque transition autorisée possède un acteur et un déclencheur ;
- toute transition non listée est interdite par défaut ;
- Archivée est terminal ;
- « premium », « urgente », « indisponible » et « supprimée » ne sont pas utilisés comme états métier concurrents ;
- une option commerciale ne publie jamais une annonce non validée.

## 15.2 Modération

- une annonce ne devient Publiée qu'après décision favorable ;
- toute correction, suspension ou décision de refus possède un motif ;
- l'annonceur reçoit une explication communicable et une action attendue lorsqu'elle existe ;
- les notes internes ne sont pas révélées ;
- les modifications substantielles sont remises en contrôle ;
- le commercial ne peut pas contourner une décision de modération.

## 15.3 Visibilité et recherche

- seule une annonce Publiée est visible et contactable ;
- tout autre état est absent des résultats et suggestions publiques ;
- les compteurs publics n'incluent que les annonces Publiées éligibles ;
- suspension, expiration et retrait prennent effet sans délai indu ;
- les favoris signalent correctement l'indisponibilité sans republier l'annonce.

## 15.4 SEO

- seule une annonce Publiée et de qualité suffisante entre dans le sitemap ;
- l'URL stable est conservée pendant les périodes de publication et de renouvellement ;
- une annonce retirée du catalogue sort du sitemap ;
- aucune redirection vers l'accueil n'est appliquée par défaut ;
- le traitement final d'une URL est motivé par son historique et sa pertinence ;
- une suspension juridique ou de sécurité prime sur la continuité SEO.

## 15.5 Administration et audit

- chaque transition conserve état initial, état final, acteur, date, déclencheur et motif ;
- les actions automatiques citent la règle utilisée ;
- les permissions sont définies par action ;
- les cas exceptionnels disposent d'une justification renforcée ;
- les indicateurs distinguent attente, traitement, correction, publication, suspension, expiration et renouvellement ;
- aucune correction administrative n'efface l'historique.

## 15.6 Expiration et renouvellement

- l'annonceur connaît la date d'échéance ;
- un rappel est prévu lorsque le renouvellement est possible ;
- l'expiration retire visibilité et contact ;
- le renouvellement exige une confirmation de disponibilité ;
- un renouvellement substantiel repasse en modération ;
- le renouvellement conserve l'URL et l'historique ;
- aucune option payante n'est renouvelée silencieusement.

## 15.7 Archivage et données

- l'archivage ne permet plus de modification ou de réactivation ;
- la suppression définitive n'est pas confondue avec l'archivage ;
- les durées de conservation sont approuvées avant mise en œuvre ;
- les obligations légales peuvent imposer gel, retrait ou conservation spécifique ;
- les statistiques conservées respectent leur finalité et leur durée.

---

# Résumé des états

Le cycle officiel comprend dix états :

1. **Brouillon** — préparation privée ;
2. **Soumise** — attente de prise en charge ;
3. **En modération** — contrôle en cours ;
4. **À corriger** — régularisation attendue ;
5. **Publiée** — seule phase visible, recherchable et contactable ;
6. **Suspendue** — retrait préventif ou disciplinaire ;
7. **Expirée** — fin normale de la période publique ;
8. **Retirée** — clôture volontaire ;
9. **Refusée** — soumission non admissible ;
10. **Archivée** — clôture terminale et historique uniquement.

Le parcours normal est : **Brouillon → Soumise → En modération → Publiée → Expirée → Archivée**. Les branches À corriger, Suspendue, Retirée et Refusée traitent les corrections, risques, abandons et non-conformités.

# Ambiguïtés restant à arbitrer

Les règles structurantes sont définies, mais les paramètres suivants nécessitent une validation métier, SEO, commerciale ou juridique :

1. durée standard de publication ;
2. calendrier et canaux des rappels ;
3. durée maximale d'un Brouillon ;
4. délai accordé pour corriger ;
5. délai de recours après refus ;
6. fenêtre de renouvellement après expiration ;
7. nombre de renouvellements simples autorisés ;
8. seuils d'une modification de prix dite substantielle ;
9. liste définitive des modifications substantielles ;
10. politique de maintien temporaire des anciennes pages publiques ;
11. critères 404, 410 ou redirection après archivage ;
12. durées de conservation des annonces et historiques ;
13. effets d'une suspension sur la durée de publication ;
14. modèle commercial éventuel où la publication elle-même serait payante ;
15. canaux de notification obligatoires ;
16. niveau d'habilitation requis pour lever une suspension et traiter un recours ;
17. procédure juridique de transfert ou de retrait pour représentation, incapacité ou décès ;
18. politique précise pour les annonces migrées dont l'état Legacy est ambigu.

# Confirmation de périmètre

Ce document est purement métier. Il ne contient aucun code, aucun choix de framework, aucune définition d'API, aucun modèle de base de données et aucune décision d'implémentation technique.

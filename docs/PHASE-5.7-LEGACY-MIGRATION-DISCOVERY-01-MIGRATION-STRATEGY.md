# Legacy Migration & Reconciliation — Migration Strategy

## Dispositions

Chaque donnée reçoit exactement une disposition documentée : **Conserver**, **Nettoyer**, **Fusionner**, **Archiver** ou **Supprimer**. Une donnée non déterminée reste en quarantaine et n'entre jamais dans le périmètre actif.

## Transformations autorisées

- normalisation d'espaces, casse, ponctuation et encodage manifestement dégradé ;
- formats canoniques validés pour téléphone, date, devise et valeurs fermées ;
- correspondance d'un identifiant Legacy vers un identifiant owner stable ;
- requalification par une table de décision approuvée et versionnée ;
- déduplication certaine, avec conservation de toutes les correspondances ;
- minimisation, archivage ou suppression après contrôle de finalité et d'obligation.

## Transformations interdites

- invention d'une valeur manquante ;
- fusion sur un critère faible unique ;
- publication, activation, habilitation ou consentement implicite ;
- réattribution de propriété sans preuve ;
- correction silencieuse d'une preuve financière ou d'audit ;
- usage de Search, SEO ou d'une projection comme autorité métier.

## Vagues candidates

| Vague | Contenu | Gate de sortie |
|---:|---|---|
| 0 | extractions immuables, empreintes, schémas, volumes et registre des décisions | 100 % des sources qualifiées et volumes expliqués |
| 1 | identités minimales et correspondances d'identifiants | comptes candidats récupérables, secrets exclus, conflits en quarantaine |
| 2 | professionnels et géographie | owners/référentiels stables, doublons sensibles arbitrés |
| 3 | propriétés et annonces non publiques | rattachements prouvés, états qualifiés, aucune activation implicite |
| 4 | médias | fichiers lisibles, droits et propriétaires démontrés |
| 5 | favoris, leads, réservations, modération | intégrité référentielle et finalités validées |
| 6 | contenu, SEO et comparaison Search | chaque URL prioritaire possède une décision ; projections reconstruisibles |
| 7 | notifications et paramètres | consentements explicites, secrets exclus |
| 8 | audit et finance conditionnelle | obligations et autorisations spécifiques signées |

## Reprise et idempotence à qualifier

Une future réalisation devra checkpoint-er par source, partition et règle versionnée. Le rejeu d'une même extraction avec les mêmes décisions devra produire le même résultat. Une reprise commence au dernier checkpoint certifié et ne saute jamais un lot en erreur.

## Rollback candidat

Avant cutover, le rollback consiste à abandonner la génération cible de migration et à conserver les owners courants inchangés. Après cutover, aucun retour ne peut écraser les mutations nouvelles : elles doivent être gelées ou capturées, rapprochées puis rejouées selon une décision explicite.

## Cutover candidat

Le cutover requiert deux répétitions complètes successives stables, une heure de référence UTC, une fenêtre de limitation des mutations Legacy, le traitement du delta final, zéro écart critique inexpliqué, validation de chaque owner et autorisation indépendante de mise en service.

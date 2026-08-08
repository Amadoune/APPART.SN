# Legacy Migration & Reconciliation — Legacy Inventory

## Statut de l'inventaire

Le dépôt confirme une enveloppe physique temporaire `LegacyMigration`, actuellement vide, et des règles métier historiques de migration. Il ne contient pas, dans ce jalon, de snapshot Legacy qualifié ni de mesure de volumétrie certifiée. Toute volumétrie reste donc **À MESURER** sur des extractions immuables, datées et empreintées avant ouverture d'une Foundation technique.

| Domaine Legacy à inventorier | Sources d'observation candidates | Données candidates | Données exclues ou abandonnées par principe | Volumétrie | Ordre candidat |
|---|---|---|---|---|---:|
| Comptes et accès | comptes actifs, historiques autorisés, preuves de contact/consentement | identité minimale, coordonnées validées, propriété et consentements prouvés | sessions, jetons, secrets, comptes de test, données sans finalité | À mesurer | 1 |
| Professionnels | organisations, établissements, représentants, preuves professionnelles | identités validées, mandats, profils et portefeuilles légitimes | coquilles vides, doublons absorbés, profils sans propriétaire | À mesurer | 2 |
| Géographie | villes, quartiers, aliases, hiérarchies et coordonnées corroborées | référentiel validé et correspondances historiques | lieux non prouvés, hiérarchies incohérentes non arbitrées | À mesurer | 2 |
| Biens et annonces | propriétés, annonces, états, annonceurs, prix, caractéristiques | ressources rattachées, états requalifiés, identifiants historiques | annonces tests, orphelines ou incertaines rendues actives | À mesurer | 3 |
| Médias | fichiers, métadonnées, galeries, rattachements et droits | médias lisibles, conformes, attribués avec preuve | binaires absents, interdits, sans droit ou sans propriétaire | À mesurer | 4 |
| Favoris | relations compte–annonce | relations dont les deux extrémités sont acceptées | relations orphelines ou sans finalité | À mesurer | 5 |
| Contacts, leads et réservations | intentions, consentements, destinataires, états et dates | historique strictement nécessaire et relations légitimes | coordonnées sans finalité, doublons non résolus, traces expirées | À mesurer | 5 |
| Modération et signalements | dossiers, décisions, preuves et recours | décisions nécessaires, preuves conservables et rattachements valides | signalements non authentifiables ou données excessives | À mesurer | 5 |
| Contenu, SEO et recherche | contenus, URL, redirects, canonicals, sitemaps, index historiques | contenu approuvé et patrimoine URL qualifié | projections obsolètes comme autorité, pages trompeuses ou pauvres | À mesurer | 6 |
| Notifications et paramètres | préférences prouvées, modèles, canaux, paramètres métier | préférences et configurations avec owner et effet démontrés | consentements implicites, secrets fournisseur, paramètres orphelins | À mesurer | 7 |
| Paiements et droits commerciaux | preuves financières, références, offres et bénéficiaires | preuves légalement nécessaires et droits vérifiables | secrets, moyens expirés, valeurs approximées ou sans contexte | À mesurer | 8, conditionnel |
| Administration et audit | décisions administratives, acteurs, dates et motifs | preuves nécessaires, décisions traçables et historiques autorisés | accès administratifs historiques, secrets et traces sans finalité | À mesurer | 8 |

## Qualification obligatoire par source

Chaque source devra recevoir : propriétaire, période couverte, mode d'extraction, empreinte, schéma observé, encodage, timezone, cardinalité, clés distinctes, doublons, nulls, orphelins, fraîcheur, finalité, durée de conservation et niveau de confiance.

L'absence de source qualifiée ou de volume expliqué bloque la migration active du domaine concerné.

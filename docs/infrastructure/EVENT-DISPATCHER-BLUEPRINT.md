# Event Dispatcher Blueprint

## 1. Objet

Le Dispatcher distribue des événements déjà validés et enregistrés dans l’Outbox. Il ne décide jamais si une mutation métier est autorisée et ne remplace pas l’Unit of Work.

## 2. Modèle de livraison

La livraison officielle est asynchrone, au moins une fois, après commit. Un événement est remis à zéro, un ou plusieurs consommateurs explicitement enregistrés. L’absence de consommateur est valide seulement si le catalogue d’événements la déclare.

## 3. Catalogue

Un catalogue versionné associe chaque type et version d’événement à : propriétaire, schéma conceptuel, classification des données, consommateurs autorisés, politique de rétention, budget de traitement et stratégie de compatibilité.

Les abonnements implicites, la découverte magique et les gestionnaires génériques universels sont interdits.

## 4. Ordre, idempotence et concurrence

Pour un Aggregate, les événements sont traités dans l’ordre version/index. Un consommateur conserve son propre marqueur de traitement. Recevoir deux fois le même identifiant produit le même résultat sans second effet.

Deux Aggregates peuvent être traités en parallèle. Un événement futur reçu avant un événement attendu est différé ou provoque une reconstruction prudente ; il n’écrase jamais silencieusement un état plus récent.

## 5. Consommateurs

Un consommateur :

- dépend d’un contrat public et non des internes du module producteur ;
- valide type, version et métadonnées avant traitement ;
- applique ses politiques de fraîcheur et de visibilité ;
- effectue son écriture et son marqueur idempotent atomiquement ;
- ne modifie jamais directement l’Aggregate source ;
- classe ses erreurs comme transitoires, permanentes ou données incompatibles.

SearchDiscovery et ContentSeo restent des projections reconstruisibles. Une incertitude applique le retrait prudent défini par leurs politiques.

## 6. Compatibilité

Un changement compatible ajoute uniquement une donnée facultative ou un nouveau type. Renommer, supprimer, changer le sens ou la confidentialité exige une nouvelle version. Les consommateurs annoncent les versions supportées. Une version inconnue est mise en quarantaine, jamais interprétée approximativement.

## 7. Échecs

Les erreurs transitoires sont reprises avec temporisation. Les erreurs permanentes ou incompatibles vont en quarantaine avec corrélation et diagnostic minimisé. Le Dispatcher ne marque un événement distribué qu’après confirmation de tous les consommateurs obligatoires, chacun disposant de son propre état.

## 8. Observabilité

Mesures obligatoires : retard par consommateur, débit, durée, doublons, reprises, quarantaines, dernière version traitée et divergence de projection. Les journaux utilisent les identifiants techniques et excluent les charges utiles sensibles.

## 9. Critères d’acceptation

- catalogue explicite et versionné ;
- ordre par Aggregate ;
- idempotence par consommateur ;
- parallélisme entre Aggregates ;
- compatibilité et quarantaine définies ;
- aucune logique métier source dans les handlers ;
- aucune dépendance Laravel dans le Domaine ;
- projections reconstruisibles et fail-safe.

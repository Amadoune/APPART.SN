# Persistence Mapping Strategy

## 1. Objet

Cette stratégie définit comment représenter durablement la DDD Foundation sans modifier son modèle. Elle ne définit aucun schéma physique et ne contient aucun SQL.

## 2. Décision

Le mapping est explicite, bidirectionnel et propre à chaque Aggregate. Les objets du Domaine ne deviennent pas des modèles de persistance. Aucun attribut, héritage, proxy, chargement paresseux ou type du framework n’entre dans `src/Modules/*/Domain`.

## 3. Unité de persistance

L’unité d’écriture est l’Aggregate Root complet. Les Entités internes et Value Objects sont persistés sous l’autorité exclusive de leur Root. Ils ne disposent pas de Repository autonome, sauf s’ils sont déjà un Aggregate Root normatif.

Les références entre Aggregates sont conservées par identité stable uniquement. La persistance ne transforme jamais une référence en composition ou en navigation automatique.

## 4. Mappers

Chaque Aggregate possède un mapper périphérique responsable de :

- extraire un snapshot durable complet ;
- convertir explicitement Value Objects, enums, dates et collections ;
- reconstruire via le point officiel `reconstitute` ;
- préserver version, historique, ordre et états terminaux ;
- garantir une collection d’événements vide après reconstruction ;
- refuser toute valeur inconnue ou incohérente.

Les mappers n’appliquent aucune valeur par défaut métier et n’appellent aucun cas d’usage.

## 5. Types et normalisation

- Identifiants : représentation canonique, comparaison exacte et immutabilité.
- Dates : instant explicite avec fuseau normalisé ; aucune date serveur implicite pour un fait métier.
- Money : montant dans l’unité minimale définie par le Domaine et devise conservée séparément conceptuellement.
- Enums : valeur textuelle stable ; une valeur inconnue est une erreur d’intégrité.
- Booléens : réservés aux concepts réellement binaires.
- Collections ordonnées : ordre et identité de chaque élément conservés.
- Historique append-only : séquence et chronologie conservées sans réécriture.
- Secrets : uniquement sous leur forme protégée, jamais exposés par le mapper ou les journaux.

## 6. Évolution

Toute évolution physique doit rester compatible avec le snapshot conceptuel. La conversion est explicite et testée dans les deux sens. Une évolution de mapping ne peut modifier un invariant, un événement ou un contrat public. Si l’état historique ne peut être représenté fidèlement, l’implémentation est bloquée et un arbitrage explicite est demandé.

## 7. Projections

SearchDiscovery et ContentSeo utilisent des mappings distincts de leurs sources. Ils restent supprimables et entièrement reconstruisibles depuis les événements et snapshots publics autorisés. Une projection n’est jamais utilisée pour reconstruire l’Aggregate source.

## 8. Vérifications obligatoires

Pour chaque Aggregate : aller-retour état → snapshot → état, égalité de toutes les propriétés observables, version identique, historique identique, événements vides, refus des valeurs inconnues, collections complètes et absence de chargement paresseux.

## 9. Critères d’acceptation

- mapping explicite par Aggregate ;
- aucune annotation ou dépendance de persistance dans le Domaine ;
- reconstruction officielle exclusivement ;
- Aggregate complet et détaché ;
- versions et historiques préservés ;
- erreurs d’intégrité visibles ;
- projections séparées ;
- tests d’aller-retour exhaustifs.

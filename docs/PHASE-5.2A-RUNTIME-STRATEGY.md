# Phase 5.2A — Runtime Strategy

## Composition cible

- providers propriétaires et additifs pour Property Authoring et Listing
  Authoring ;
- bindings lazy, singleton seulement pour les services stateless ;
- ports Application, adapters PostgreSQL/Laravel en Infrastructure ;
- aucune dépendance Domain vers Laravel, PDO ou IAM concret ;
- aucune modification d'un provider IAM ou lifecycle gelé.

## Orchestration

Les opérations prévues sont :

- initiate property ;
- update property facts ;
- create listing draft ;
- update draft ;
- grant/revoke local delegation ;
- assess completeness ;
- submit to publication ;
- list/get private portfolio.

Une orchestration appelle les owners dans un ordre déterministe, persiste un
intent local et retourne un résultat fermé. Les échecs inter-domaines sont
rejouables ; aucune écriture partielle silencieuse n'est acceptée.

## Availability et autorisation

Pour chaque mutation :

1. Account Availability F-17 doit être `Available` ;
2. la session fournit l'AccountId canonique ;
3. l'owner ou la délégation est vérifié ;
4. les références Property/Listing sont validées ;
5. les préconditions métier sont évaluées ;
6. la mutation locale est tentée avec version attendue.

Toute réponse inconnue ou indisponible est refusée.

## Runtime Health

F-14 reste inchangé pendant 5.2A. Une future extension du catalogue nécessite
le jalon Runtime prévu et la gouvernance applicable.

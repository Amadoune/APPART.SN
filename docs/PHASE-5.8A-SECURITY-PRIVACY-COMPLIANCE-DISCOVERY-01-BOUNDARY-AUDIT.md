# Boundary Audit — Security, Privacy & Compliance

## Décision de frontière

L'owner recommandé est `SecurityCompliance`, owner unique de coordination des contrôles transverses de sécurité, confidentialité et conformité. Il ne reçoit aucune autorité métier appartenant aux autres domaines.

## Dans la frontière

- gouvernance des secrets et de leur rotation ;
- politiques cryptographiques, de signature et de hachage ;
- audit et journalisation de sécurité ;
- gestion et traçabilité des incidents ;
- protection et minimisation des PII ;
- politiques de conservation, purge, destruction et export ;
- contrôles de confidentialité, intégrité et disponibilité ;
- production et conservation des preuves de conformité.

## Hors frontière

- décision métier sur les identités, propriétés, médias, signalements, recherches, contenus, notifications, administration ou migrations Legacy ;
- réécriture des règles de rétention métier sans mandat du domaine propriétaire ;
- accès implicite aux données brutes, secrets ou journaux d'une capacité gelée ;
- Transport, Routing, Consumer et toute implémentation technique.

## Surfaces

Aucune surface publique n'est ouverte par ce Discovery. Les surfaces internes candidates sont des ports futurs de politiques, inventaires de contrôles, preuves, incidents et décisions de conservation. Leur matérialisation exige une Foundation ultérieure explicitement ouverte.

## Autorité

`SecurityCompliance` définit et vérifie les contrôles transverses. Chaque owner métier reste responsable de la finalité, de la licéité, de l'exactitude et de l'autorisation de ses traitements. Les arbitrages juridiques nécessitent validation par la fonction juridique compétente.

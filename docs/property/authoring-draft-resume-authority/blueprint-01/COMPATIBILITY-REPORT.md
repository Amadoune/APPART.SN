# Compatibility Report

Le Blueprint est compatible avec les Foundations fermées :

- IAM reste l’unique autorité d’identité ;
- Property Authoring et Listing Authoring restent propriétaires de leurs états ;
- F4-A reste l’autorité des nouvelles sélections Geography ;
- F6/F7 et Promotion ne sont ni lus ni modifiés par la reprise ;
- Media conserve ownership et persistance ;
- Projection et Search restent fermés.

Aucune migration n’est justifiée : les identités, versions, facts Property, Draft, ownership, Aggregate, Workflow et Media sont déjà persistés. Le proof context F4-A historique ne doit pas être ajouté rétroactivement puisqu’il n’est pas requis pour une reprise read-only.

Le seul ajout potentiel hors composition est une lecture binaire privée Media. Elle n’altère aucune donnée ni règle métier.

# Media Item Lifecycle Delivery Consumer Specification

Le Consumer vérifie avant routage :

- type et version du message ;
- owner `Media` ;
- aggregate `MediaItemLifecycle` ;
- identité du média ;
- version causale ;
- index événementiel égal à 1 ;
- restauration valide du payload.

Toute divergence retourne `DivergentPayload` sans appel au routeur. Un message cohérent est enveloppé selon 4.6F, routé une seule fois selon 4.6G et traduit exclusivement par la politique 4.6G-R1.

Le Consumer ne dépend ni du workflow, ni des stores Lifecycle, ni de PostgreSQL, ni de `MediaCollection`.

# Media Item Lifecycle Outbox Owner Schema Analysis

Le propriétaire requis par 4.6H-R1 est `Media → media`.

L'audit de l'infrastructure existante établit que ce propriétaire est déjà structurellement disponible :

- `PostgreSqlPublicProjectionOutboxSchema::for(Media)` retourne `media` ;
- la résolution inverse `moduleFor('media')` retourne `Media` ;
- `media` appartient aux huit owners reconnus ;
- la migration historique 005 crée déjà les quatre structures Outbox génériques dans le schéma `media`.

Créer une migration additive supplémentaire dupliquerait un owner existant et introduirait deux sources de vérité. Le sprint certifie donc la réutilisation du schéma historique sans modifier la migration 005.

# Stratégie de révision Public Geography

## Entrées normatives

Une révision est calculée avec :

1. une séquence source positive et explicite ;
2. un payload public canonique non vide ;
3. une clé de causalité non vide.

Les producteurs futurs devront garantir la canonicalisation du payload avant d’appeler la stratégie. La fondation ne trie ni ne réinterprète les données Geography : elle ne déplace donc aucune règle métier.

## Version et stabilité

La séquence source devient directement la version Public Geography utilisée par le watermark. Aucun timestamp, hash tronqué ou compteur local implicite n’est accepté comme version.

Le checksum porte sur le payload canonique complet. Il sert à distinguer une rediffusion identique d’une divergence à version égale.

Toute mutation ayant un effet public doit recevoir une nouvelle séquence explicite. Une mutation sans effet public peut conserver le payload mais doit rester explicable par sa causalité ; la décision de produire cette mutation demeure la responsabilité de la source future.

## Compatibilité Projection


`PublicGeographyRevision::watermarkVersion()` fournit directement l’entier positif attendu par `PublicProjectionWatermark`. Lorsqu’une révision Geography et une révision Media sont toutes deux présentes, `PromotionReadiness` devient `Ready` sans modification du Store ou de l’Updater.

# Reprise 3.6F — Laravel Runtime Binding Analysis

Le Provider `PublicProjectionRuntimeServiceProvider` est une racine de composition. Il relie les
ports certifiés aux implémentations PostgreSQL et applicatives existantes, sans décision métier.

La connexion `pgsql` de Laravel fournit une instance PDO unique au graphe. Les mappers, policies et
builders sans état sont construits par autowiring. Aucun composant n'est résolu au démarrage du
Provider ; la résolution reste paresseuse dans le conteneur.

Runtime Health est lui-même lié au conteneur. Sa factory résout les dix contrats certifiés et produit
les enregistrements inspectés par 3.7F. Elle n'exécute aucune méthode fonctionnelle.

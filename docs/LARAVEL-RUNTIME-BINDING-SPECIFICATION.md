# Laravel Runtime Binding Specification

Règles de composition :

1. chaque port possède un binding explicite unique ;
2. toute implémentation appartient aux composants de production certifiés ;
3. PDO provient exclusivement de la connexion Laravel `pgsql` ;
4. aucun Fake, Null Object ou fallback n'est enregistré ;
5. les dépendances transitives sont injectées par le conteneur ;
6. le Provider ne contient ni règle Search/SEO/canonical, ni exécution Delivery.

Le Provider est enregistré une seule fois dans `bootstrap/providers.php`.

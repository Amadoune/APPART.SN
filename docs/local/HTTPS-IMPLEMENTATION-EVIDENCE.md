# HTTPS Implementation Evidence

## Apache et TLS

`httpd -t` retourne `Syntax OK`. Le dump des VirtualHosts établit `appart.test` comme VirtualHost `*:443` unique, avec le DocumentRoot public attendu.

Apache 2.4.66 charge `ssl_module`; le transport autorise TLS 1.2 et TLS 1.3. Le processus Laragon a été redémarré explicitement après installation du VirtualHost.

## Confiance locale

Une CA locale dédiée a signé le certificat serveur. La CA a été ajoutée au magasin `CurrentUser\Root`; le navigateur ouvre directement `https://appart.test` sans interstitiel ni avertissement TLS.

## Laravel et assets

- Laravel 13.20.0, PHP 8.5.8, environnement `local`.
- caches configuration, routes, vues et application vidés après le changement de `APP_URL`.
- script chargé depuis `https://appart.test/build/assets/app-CV8soS5_.js`.
- feuille de style chargée depuis `https://appart.test/build/assets/app-CXT4JfDo.css`.
- aucun asset HTTP ni Mixed Content observé.

## Intégrité applicative

Aucun fichier IAM, middleware, Runtime, Domain, Authoring, Media, Search, Projection ou migration n'a été modifié. Les attributs `__Host-`, `Secure`, `HttpOnly` et `SameSite=Strict` restent strictement inchangés.

# APPART.TEST Local HTTPS Transport Qualification 01

## Périmètre

Qualification exclusivement locale du transport HTTPS nécessaire au cookie IAM certifié. Aucun composant IAM, Session, Domain, Authoring, Media, Search ou Projection n'a été modifié.

## Configuration matérialisée

- VirtualHost Apache TLS : `C:\laragon\etc\apache2\sites-enabled\auto.appart.test-ssl.conf`.
- DocumentRoot : `C:\laragon\www\APPART-REBUILD\public`.
- certificat serveur : `C:\laragon\etc\ssl\appart.test\appart.test.crt`.
- clé privée locale : `C:\laragon\etc\ssl\appart.test\appart.test.key`.
- autorité locale : `APPART.TEST Local Development CA`, installée dans les autorités racines de confiance de l'utilisateur Windows.
- `APP_URL=https://appart.test` dans la configuration locale ignorée par Git.

Le certificat serveur couvre `appart.test` et `*.appart.test`, avec l'usage `serverAuth`. Sa période de validité va du 10 août 2026 au 12 novembre 2028.

## Empreintes

| Élément | SHA-256 |
|---|---|
| CA locale | `009FB6BD993B5E6696BFA2744768136BEE5C1736BD4C2ADC148909F8652F8783` |
| Certificat serveur | `1DEDE58E10E1A126E86898D9E75C1EA82FC7E0EAE3DE6837E4EFC552A3A18298` |

## État de qualification

Le transport HTTPS, le certificat, la confiance locale, Laravel, `/up` et les assets sont qualifiés. La preuve terminale d'une session IAM réelle reste indisponible : aucun identifiant local autorisé et connu n'est fourni par le repository ou par la décision d'autorité, et le périmètre interdit de créer ou réinitialiser une identité IAM.

# HTTPS Browser Restoration 02 — Implementation Evidence

## Preuves environnementales

| Couche | Preuve | Résultat |
|---|---|---|
| DNS | `appart.test → 127.0.0.1` | PASS |
| hosts | entrée Laragon présente | PASS |
| Proxy | accès WinHTTP direct | PASS |
| TCP 443 | connexion réussie | PASS |
| Apache | processus actifs et `Syntax OK` | PASS |
| VirtualHost | ServerName, DocumentRoot et certificat corrects | PASS |
| Certificat serveur | SAN corrects, dates valides | PASS |
| Chaîne initiale | CA racine introuvable par le client | FAIL identifié |
| Confiance locale | CA réenregistrée dans le magasin utilisateur | APPLIED |
| Navigateur `/up` | page Laravel « Application up » | PASS |
| Home / Connexion / Recherche | navigation HTTPS réelle | PASS |

## Intégrité produit

Aucun code, Runtime, IAM, Cookie, P08, Property, Media, Search ou Projection n'est modifié par cette restauration. La seule mutation est le magasin de confiance local de l'utilisateur Windows.

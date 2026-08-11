# APPART.TEST Local HTTPS Browser Demonstration Restoration 02

## Diagnostic

La résolution locale et le serveur étaient corrects :

- `appart.test` résout vers `127.0.0.1` ;
- le fichier hosts contient l'entrée Laragon attendue ;
- aucun proxy WinHTTP n'est configuré ;
- Apache écoute sur 443 et sa configuration est `Syntax OK` ;
- le VirtualHost HTTPS pointe vers `C:/laragon/www/APPART-REBUILD/public` ;
- le certificat couvre `appart.test` et `*.appart.test` et reste valide jusqu'en 2028.

La divergence se produisait pendant la construction de chaîne TLS côté client. Windows signalait `CERT_E_CHAINING` pour l'émetteur `APPART.TEST Local Development CA`, avant toute requête HTTP.

## Restauration environnementale

La CA locale existante `C:/laragon/etc/ssl/appart.test/appart-test-ca.crt` a été réenregistrée dans le magasin racine de confiance de l'utilisateur. Aucun certificat n'a été régénéré et aucun fichier Apache, Laravel ou produit n'a été modifié.

Après rafraîchissement de confiance, le navigateur contrôlable ouvre réellement :

- `https://appart.test/up` ;
- `https://appart.test/` ;
- `https://appart.test/connexion` ;
- `https://appart.test/recherche`.

Le TLS local et le navigateur HTTPS sont donc restaurés.

## Limite de démonstration P08

Les routes authentifiées restent refusées en l'absence de session. Aucun credential reviewer autorisé ni preuve d'affectation `publication_reviewer` n'est disponible. Modifier un password, injecter une session ou attribuer un rôle dépasserait ce chantier et modifierait IAM.

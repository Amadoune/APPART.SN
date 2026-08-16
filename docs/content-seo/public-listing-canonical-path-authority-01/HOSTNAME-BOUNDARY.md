# Hostname Boundary

`canonicalPath` contient uniquement le path relatif. Le hostname n’y figure pas.

`CanonicalPolicy` est l’autorité de composition de l’URL absolue et produit l’apex HTTPS :

`https://appart.sn/{canonicalPath}`

`CanonicalUrl` accepte en entrée `appart.sn` ou `www.appart.sn`, puis canonicalise vers `appart.sn`.

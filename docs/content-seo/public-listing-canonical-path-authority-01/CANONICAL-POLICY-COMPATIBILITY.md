# Canonical Policy Compatibility

`CanonicalPolicy::fromPath()` ajoute `https://appart.sn/` après suppression d’un éventuel slash initial. `CanonicalUrl` impose HTTPS, un host APPART.SN, aucune query/fragment/credential/port et normalise les segments.

`annonces/{uuid}` est accepté sans évolution de policy et ressort inchangé dans :

`https://appart.sn/annonces/{uuid}`.

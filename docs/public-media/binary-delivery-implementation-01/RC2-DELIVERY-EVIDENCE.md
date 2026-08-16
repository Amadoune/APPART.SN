# RC2 Delivery Evidence

Préflight read-only :

- MediaId : `1586b2bc-48ab-57b7-948a-9d9a19813ca8` ;
- assetVersion : `2` ;
- état Media : actif ;
- état asset : ready ;
- attachment : appliqué ;
- MIME : `image/jpeg` ;
- taille : `35017` octets ;
- eligibility : `Found` ;
- flux storage : disponible.

Locator réel : `/media/1586b2bc-48ab-57b7-948a-9d9a19813ca8/revisions/2`.

Le navigateur intégré a ouvert le locator HTTPS en invité et rendu le binaire réel sous le titre natif `2 (1264×720)`, établissant HTTP 200 et le décodage JPEG. Les en-têtes `Content-Type: image/jpeg` et `Cache-Control: no-store` sont garantis par l'adapter et vérifiés par la Feature ciblée. Le client curl Windows local a échoué avant HTTP dans Schannel (`SEC_E_NO_CREDENTIALS`) ; cette limite cliente ne contredit pas la preuve navigateur obtenue.

Aucune donnée RC2 n'a été mutée et aucune PublicMediaDecision n'a été créée.

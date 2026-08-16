# Legacy Location Fields

## Décision

`city` et `neighborhood` cessent d’être des autorités pour toute nouvelle écriture utilisant la sélection F1.

- snapshots historiques : champs conservés et lisibles ;
- aucun backfill vers `geographicPlaceId` ;
- aucune reconstruction de PlaceId depuis un label ;
- nouvelles vues : labels affichés depuis les items sélectionnés, uniquement comme présentation ;
- nouvelle persistance F4 : `geographicPlaceId` est le seul fait Geography autoritatif.

Les colonnes legacy ne sont pas supprimées dans F4-A ou F4. Leur suppression éventuelle exige une migration et une décision distinctes après extinction des consumers historiques.

Lors d’une édition d’un ancien draft sans `geographicPlaceId`, le workspace affiche les anciens textes comme information non certifiée et exige une nouvelle sélection explicite avant complétude Promotion.

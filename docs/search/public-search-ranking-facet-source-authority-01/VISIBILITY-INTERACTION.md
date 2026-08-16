# Visibility Interaction

`SearchVisibilityPolicy` est déterministe :

- Listing terminal ou Property archivée : `Removed` ;
- Listing non publié, Property indisponible ou Media non ready : `Hidden` ;
- Listing publié, Property disponible et Media ready : `Visible`.

La visibilité ne fournit ni `SearchRank` ni facettes. Une annonce visible reste non matérialisable tant que ces autorités manquent.

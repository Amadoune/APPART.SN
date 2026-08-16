# Normalization

La seule normalisation publique est celle déjà appliquée par `PlaceName`: trim et réduction des espaces Unicode consécutifs. Casse, accents et ponctuation autorisés sont conservés.

`normalizationKey()` lowercase sert aux comparaisons Domain, pas à l'affichage, au slug ou au checksum du label affiché.

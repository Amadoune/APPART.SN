# Writer audit

`PostgreSqlPublicGeographyWriter` et `PostgreSqlPublicMediaWriter` existent et implémentent leurs interfaces. Ils utilisent transaction locale, advisory transaction lock par identité, lecture `FOR UPDATE`, classification monotone et upsert atomique.

Résultats : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`. Les interfaces writer n'ont pas de binding/alias nominatif dans le provider; seuls les readers sont bindés. Le prochain gate doit qualifier le binding du producteur sans modifier les readers.

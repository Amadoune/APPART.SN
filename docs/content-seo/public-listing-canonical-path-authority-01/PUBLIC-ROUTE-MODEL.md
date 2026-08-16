# Public Route Model

La route productive est `GET /{canonicalPath}` avec la contrainte `annonces/[^/]+`.

Elle accepte exactement un segment après `annonces/`. Un UUID canonical satisfait cette contrainte sans modification de route :

`/annonces/979cd5aa-ced1-48a1-8adf-8b29c843a0c2`

Les modèles `/{city}/{slug}` et multi-segments sont écartés : aucune autorité actuelle ne les impose.

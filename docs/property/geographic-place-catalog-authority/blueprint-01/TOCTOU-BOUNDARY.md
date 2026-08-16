# TOCTOU Boundary

F4-A sélectionne une Place et F4 rejoue cette sélection avant le save Authoring. Cette validation ne fige pas le lifecycle Geography.

La future Promotion doit relire l’état courant :

`PropertyAuthoringState.geographicPlaceId` → `GeographicPlaceCatalog::statusOf` → snapshot Geography courant → `RegisterProperty`.

Ainsi, une Place devenue disabled, merged, absente ou non adressable entre Authoring et Promotion bloque la registration. La cible d’une fusion n’est jamais substituée automatiquement : changer l’identité exige un nouveau choix owner-authored et sa validation F1/F4.

La lecture et `PropertyRegistry::add` ne forment pas une transaction distribuée avec Geography. Le contrôle est une revalidation ponctuelle, conforme à la frontière actuelle. Le blueprint n’ajoute ni verrou Geography ni copie locale de son état.

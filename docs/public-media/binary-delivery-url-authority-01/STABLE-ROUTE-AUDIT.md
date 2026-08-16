# Stable route audit

Option retenue : route stable → validation stricte du tuple MediaId/version → lectures owner → inspection du binaire privé → stream.

Elle préserve la storageKey, permet une révocation immédiate, survit au changement de backend et ne dépend pas de Projection. Le contrôleur ne porte aucune règle : il adapte le résultat fermé de l'autorité Media Public Delivery.

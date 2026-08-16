# Retired place semantics

Unavailable est un état durable versionné, pas une suppression. Il préserve terminalPlaceId, vector, cause et target éventuelle, tout en empêchant le reader V2 de produire Found.

Réactivation peut produire une représentation Available plus récente. L'historique n'est jamais réécrit silencieusement.

# Audit du rôle `particulier`

`particulier` apparaît dans les tests de l’Aggregate Account et des use cases génériques Grant/Revoke. Il n’est référencé par aucune route, Request, middleware, autorisation reader, Runtime ou Provider Authoring.

Il s’agit donc d’une valeur de test démontrant la mécanique générique des rôles, pas d’une autorité métier productive ni du rôle owner.

La décision interdit de l’attribuer au principal RC2 comme moyen de débloquer l’Authoring.

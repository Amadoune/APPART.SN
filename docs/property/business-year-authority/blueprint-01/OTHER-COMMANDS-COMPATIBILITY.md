# Other Commands Compatibility

`UpdateProperty` consomme déjà BusinessYear et un DateTimeImmutable `at`. La même autorité V1 doit résoudre l'année civile UTC de cet instant de commande stable.

`ChangeAddress` et `ArchiveProperty` ne consomment pas BusinessYear et restent hors intégration. `PropertyMapper` utilise un `validationYear` lors de la reconstitution ; cette vérification de snapshot n'est pas une commande métier et ne redéfinit pas l'autorité V1.

Le Blueprint n'élargit donc la capacité qu'aux deux commandes Domain qui exigent réellement le même concept : Register et Update.

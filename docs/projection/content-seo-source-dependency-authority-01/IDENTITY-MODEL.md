# Identity Model

Le snapshot est adressé par ListingId et possède un `snapshotId` UUID unique. Le constructeur valide seulement la forme UUID et la cohérence des ListingId.

Aucune stratégie productive ne détermine actuellement le snapshotId : ni namespace UUIDv5, ni identité de commande, ni autre autorité stable. Les UUID codés dans les commandes locales ne sont pas généralisables.

L’identité stable du snapshot doit être fermée par la prochaine Authority.

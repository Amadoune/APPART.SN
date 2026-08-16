# Nature d’AddressId

F2 définit AddressId comme une identité technique déterministe UUIDv5 issue exclusivement de :

`PropertyId + AddressIntentId`.

Dans une Promotion, elle identifie l’Address construite pour l’intention Authoring exacte. Elle n’est ni un fait client, ni un UUID aléatoire, ni une donnée reprise depuis un Aggregate historique comme nouvelle autorité.

Lors d’un replay avec ledger, le checksum et le succès atomique prouvent l’application de cette identité au moment de la commande. Lors d’un rattrapage sans ledger, l’AddressId persisté doit être comparé strictement à l’AddressId recalculé par F2.

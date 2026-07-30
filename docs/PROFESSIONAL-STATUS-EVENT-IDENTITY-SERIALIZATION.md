# Professional Status Event Identity and Serialization

`eventId` est le SHA-256 canonique de : type, version de payload, identité Professional Status, état précédent, action, état courant et version causale.

L'enveloppe JSON canonique respecte l'ordre : `eventId → eventType → payloadVersion → payload → metadata`. La sérialisation utilise JSON sans échappement des slashs ou d'Unicode et reste stable byte-for-byte pour un événement identique.

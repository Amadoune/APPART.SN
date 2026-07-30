# Lead Lifecycle Event Serialization and Compatibility

L'enveloppe canonique suit l'ordre `eventId → eventType → payloadVersion → payload → metadata`. Le JSON conserve l'ordre des champs, les slashs et les caractères Unicode sans transformation.

L'identité SHA-256 couvre le type, la version du payload, le `LeadId`, la transition exacte et `occurredVersion`. Une modification de l'un de ces éléments produit une autre identité.

Une évolution compatible ajoute une nouvelle version explicite sans modifier V1. Une modification du sens, de l'ordre, du type ou du caractère obligatoire d'un champ exige une nouvelle version. Le catalogue refuse toute transition non certifiée ; aucune branche implicite n'existe.

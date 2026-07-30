# Property Lifecycle Event Metadata Specification

Les métadonnées contiennent exactement `occurredAt`, puis `recordedAt`. Les deux instants sont obligatoires au format UTC canonique `Y-m-dTH:i:s.uZ` et sont fournis explicitement par le futur appelant.

`recordedAt` ne peut précéder `occurredAt`. Aucune horloge, valeur par défaut ou normalisation implicite n'existe. Un rejeu avec les mêmes métadonnées conserve exactement les mêmes chaînes.

Les métadonnées ne participent pas à l'identité métier de l'événement : celle-ci décrit la transition persistée stable.

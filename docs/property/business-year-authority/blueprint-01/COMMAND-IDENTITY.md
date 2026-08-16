# Command Identity

`occurredAt` UTC canonique est déjà un composant du checksum de `PromoteAuthoredPropertyV1`. BusinessYear en est une dérivation pure et ne doit pas être ajouté comme seconde source au payload ou au checksum.

Même commandId avec occurredAt différent : commande divergente. Nouveau commandId pour retry fonctionnel de la même intention conserve occurredAt et produit donc la même année.

La version de politique est portée par le nom de contrat `V1`, pas par une valeur client.

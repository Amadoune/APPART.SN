# Binary access policy

Un GET est Allowed uniquement si toutes les preuves convergent :

1. MediaId appartient à une collection unique;
2. item `active`;
3. asset `ready`, version demandée exacte, checksum/bytes/storageKey intègres;
4. attachment appliqué et cohérent collection/property/media;
5. Listing associé à la Property et `published`;
6. binaire inspectable dans le store privé.

Sinon : NotFound ou DependencyUnavailable interne; aucune donnée n'est servie.

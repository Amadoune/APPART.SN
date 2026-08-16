# Property Type Independence

L’adressabilité dépend uniquement de `PlaceType`.

Une City, un District ou un Neighborhood localise de la même manière un appartement, une maison, une villa, un terrain, un bureau, un local commercial ou un type Other. Aucun invariant inventorié ne justifie une matrice `PlaceType × PropertyType`.

`PropertyTypePolicy` conserve une responsabilité distincte : déterminer si les faits Property, dont l’Address éventuelle, sont requis et cohérents. La policy d’adressabilité ne décide jamais de la présence de l’Address.

Pour les types actuellement connus, Apartment, House, Villa, Land, Office et Commercial exigent une Address ; Other peut ne pas en avoir. Lorsqu’une Address existe, le même verdict géographique s’applique à tous.

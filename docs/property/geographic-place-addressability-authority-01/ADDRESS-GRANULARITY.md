# Address Granularity

Une Address Property peut être rattachée directement à une `City`, un `District` ou un `Neighborhood`.

La policy n’exige pas le niveau le plus fin :

- City est finalisable lorsqu’aucun District ou Neighborhood n’existe ;
- City reste finalisable même si une subdivision existe, car l’AddressLine apporte la précision physique et la couverture Geography peut varier ;
- District est finalisable avec ou sans Neighborhood ;
- Neighborhood est finalisable lorsqu’il est disponible et choisi.

Country, Region et Department sont navigables dans la hiérarchie, mais ne constituent pas une granularité finale suffisante pour une Address Property.

Cette règle s’appuie sur la sémantique des niveaux, pas sur l’UX courante, des données de démonstration ou une obligation de complétude uniforme.

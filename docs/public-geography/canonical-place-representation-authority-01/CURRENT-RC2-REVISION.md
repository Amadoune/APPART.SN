# Current RC2 revision

Vecteur root→leaf :

```text
[(countryId,1),(regionId,1),(cityId,1)]
```

Version scalaire publique : `1 + 1 + 1 = 3`. Le checksum et la causationKey sont déterministes sur le payload/vecteur canonique V1; ils seront calculés par l'implémentation, pas inscrits arbitrairement ici.

Conclusion : révision RC2 calculable et stable.

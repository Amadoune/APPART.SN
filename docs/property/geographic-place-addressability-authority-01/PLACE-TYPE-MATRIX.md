# Place Type Matrix

| PlaceType | Verdict | Justification normative |
|---|---|---|
| `Country` | NotAddressable | territoire national trop large pour localiser une Address Property |
| `Region` | NotAddressable | division régionale trop large ; elle sert de navigation/hiérarchie |
| `Department` | NotAddressable | division administrative intermédiaire, non-localisation finale Property |
| `City` | Addressable | unité locale minimale garantie par les branches réelles ; adresse légitime sans niveau inférieur |
| `District` | Addressable | subdivision locale apte à localiser une adresse lorsqu’elle existe |
| `Neighborhood` | Addressable | subdivision locale fine apte à localiser une adresse lorsqu’elle existe |

Aucun type n’est `UNKNOWN`. La présence d’un enfant plus fin ne rend pas son parent local addressable (`City` ou `District`) non adressable.

# Facet Policy

`SearchFacetPolicy` accepte uniquement :

- multi-valeurs : `amenity`, `feature` ;
- valeur unique : `category`, `city`, `district`, `has_image`, `property_type`.

La policy déduplique par `key|value|source` et trie canoniquement par cette clé. `SearchFacetKey` normalise la clé; `SearchFacetValue` réduit les espaces sans inventer de vocabulaire.

Ce catalogue gouverne un ensemble déjà produit. Aucun adapter productif n'établit :

- quelles clés sont obligatoires ;
- la valeur normative de `category`, `city`, `district` ou `has_image` ;
- la correspondance exacte Property type → facet ;
- les sources d'amenities/features.

Les commandes locales n'émettent qu'une facette de type avec une clé différente (`property.type`) de la clé gouvernée (`property_type`), preuve supplémentaire qu'elles ne constituent pas une autorité productive.

**Catalogue de validation existant, politique de matérialisation absente.**

## Completion 01

Le constat historique est fermé par la décision normative v1 : la liste de facettes produite est exactement `[]`. Aucun assembler Property, Listing, Media ou Geography n'est requis en v1.

Le catalogue technique reste disponible mais n'émet aucune clé tant qu'une évolution versionnée de policy ne l'autorise pas.

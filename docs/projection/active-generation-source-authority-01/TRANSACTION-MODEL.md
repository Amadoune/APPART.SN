# Transaction Model

Le manager rejoint une transaction externe ou ouvre une transaction owner-locale Public Projection. Activation et rollback verrouillent les générations concernées, retirent l'Active puis activent la cible avant commit. Listing, Property, Media, Search et ContentSeo ne participent pas à cette transaction.

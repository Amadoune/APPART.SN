# Listing Publication Persistence Analysis

La persistance 4.1B est un journal append-only par `ListingId`. La version 1 matérialise un état initial; chaque version suivante copie exactement le triplet état précédent, action et état courant de la transition fournie par le workflow 4.1A.

Le repository ne possède aucune matrice en PHP et n'appelle jamais `decide`. PostgreSQL protège la forme, les valeurs fermées et les vingt-sept triplets certifiés. Cette contrainte est une barrière d'intégrité, pas une seconde décision : l'adaptateur ne choisit jamais une transition.

Un verrou transactionnel consultatif par Listing sérialise l'initialisation et les append concurrents. Le writer exige une version contiguë et la continuité entre l'état courant stocké et l'état source fourni. Le checksum rend toute altération lisible comme Corrupted.

# Generation Identity Model

L'identité est unique dans la base par clé primaire, stable pour tous les replays d'un bootstrap et nouvelle pour chaque rebuild/cutover distinct. Elle est indépendante des Listings et de la release applicative. L'unicité inter-environnements est recherchée par UUIDv4 mais l'autorité durable est le store de chaque environnement. Une collision retourne un état existant; elle ne provoque jamais une seconde identité implicite.

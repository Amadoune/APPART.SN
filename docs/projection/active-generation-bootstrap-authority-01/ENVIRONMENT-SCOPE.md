# Environment Scope

Chaque environnement/base possède son lifecycle global. L'UUIDv4 évite les collisions inter-environnements, mais aucune valeur commune hardcodée n'est autorisée. Local, test, staging et production suivent la même procédure; seuls l'opérateur, la base cible et le scope diffèrent. Les tests utilisent des identités réservées à leurs fixtures sans devenir des identités productives.

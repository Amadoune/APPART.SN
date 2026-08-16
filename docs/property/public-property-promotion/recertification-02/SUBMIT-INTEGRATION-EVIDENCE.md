# Preuves d’intégration Submit

Le Submit positif et le Submit bloqué par une Geography négative sont démontrés en PostgreSQL. F7-A démontre également le rollback post-Promotion et le retry déterministe.

Cependant, lorsqu’une Property préexistante possède un AddressId divergent mais les mêmes Place/Line, F6 peut retourner `AlreadyApplied` et autoriser Submit. La précondition « AlreadyApplied compatible » n’est donc pas complètement démontrée.

La certification terminale Submit reste bloquée par cette unique divergence.

# Event transport

Les événements d'aggregate Media existent et sont versionnés, mais aucun transport/consumer productif vers Public Media n'a été trouvé pour Added/Removed/Archived/Reordered/MarkedPrimary. Le catalogue Projection connaît un message générique `media.reconstruction.requested` et les événements Media Item Lifecycle, pas un refresh Public Media complet.

Le transport de refresh reste à composer avec l'existant après décision URL; aucun second framework n'est autorisé.

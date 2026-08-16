# V1/V2 compatibility

V1 reste lisible pour records/générations historiques et conserve ses URLs historiques sans les consacrer comme authority. V2 est obligatoire pour toute nouvelle décision Public Geography.

Mapper/reader discriminent `schemaVersion`; consumers versionnés acceptent leur schéma exact. Aucun V1→V2 implicite, aucune synthèse URL et aucun mélange d'items.

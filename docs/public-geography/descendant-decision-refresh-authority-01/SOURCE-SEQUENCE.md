# Source sequence

Chaque refresh recalcule root→leaf depuis Geography. RevisionVector et somme vérifiée sont reconstruits; l'ancien watermark n'est jamais incrémenté artificiellement.

Le payload/checksum complet distingue les collisions/divergences et le writer reste l'arbitre monotone.

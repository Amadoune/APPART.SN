# Migration decision

**NO MIGRATION.** La représentation est une policy/DTO pure et le store JSONB Public Geography peut porter un payload V1 enrichi.

Aucun registre durable de hierarchy revision n'est nécessaire : le vecteur et la somme sont calculés depuis les versions owner monotones.

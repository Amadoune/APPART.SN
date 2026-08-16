# Generation and rebuild compatibility

Une future génération peut matérialiser V2 car le store conserve un read model opaque et ses watermarks/checksums. Le builder V2 doit encoder explicitement son schemaVersion.

V1 et V2 ne se mélangent pas dans une génération. ActiveGeneration demeure suspendue dans cette authority.

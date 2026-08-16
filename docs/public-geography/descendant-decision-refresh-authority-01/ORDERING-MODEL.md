# Ordering model

Le consumer ne suppose pas une livraison parfaite. Il relit toujours les facts actuels. Un ancien event peut matérialiser l'état latest; le suivant devient AlreadyApplied. Une candidate réellement stale est RejectedObsolete.

Même watermark avec payload différent est Divergent et quarantiné.

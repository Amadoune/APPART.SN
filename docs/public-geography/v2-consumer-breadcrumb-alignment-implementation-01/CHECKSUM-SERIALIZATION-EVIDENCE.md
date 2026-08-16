# Checksum and serialization evidence

La sérialisation V2 fixe l’ordre : schemaVersion, terminalPlaceId, status, locality, breadcrumb, revisionVector. Chaque item et chaque vecteur suivent un ordre de clés stable. Le checksum de révision certifie ce payload exact.

V1 et V2 ont des payloads différents et ne partagent jamais implicitement leur checksum. Le test de décision V2 compare byte-for-byte le payload canonique.

# Pagination model

Ordre déterministe : `place_id ASC`. Cursor : dernier terminalPlaceId traité, encodé/validé de manière opaque. Limite bornée avec défaut fixe. Continuation : `place_id > cursor`; fin : page Empty ou nextCursor absent.

Chaque page est chargée séparément; aucun tableau global des décisions.

# Disable semantics

Terminal ou ancêtre disabled rend la représentation indisponible. Le refresh écrit une décision V2 terminale `Unavailable`, avec identité, reason=`place_disabled`, vecteur courant, version/checksum/causalité.

Le reader V2 retourne `Unavailable`, jamais Found stale. L'ancienne représentation reste historique dans la causalité/store; aucune suppression.

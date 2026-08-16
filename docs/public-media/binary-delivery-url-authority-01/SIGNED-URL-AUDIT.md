# Signed URL audit

Une URL signée temporaire est incompatible avec une `PublicMediaDecision` persistée et consommée par Projection/SEO : expiration, rebuild, cache et replay rendraient le payload obsolète sans mutation owner.

Option rejetée pour V1. Aucune signature ni durée n'est persistée.

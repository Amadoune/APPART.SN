# Concurrency model

Mutations rapprochées, parent/enfant simultanés et refresh concurrents sont admis. Chaque terminal relit le vecteur actuel; le writer verrouille placeId et accepte latest, AlreadyApplied ou rejette stale/divergent.

Une mutation pendant pagination peut entraîner un replay; aucun snapshot distribué n'est exigé. Le résultat converge vers les facts les plus récents.

# Affected terminal evidence

Le reader PostgreSQL filtre uniquement les payloads schema V2 dont `revisionVector` contient mutatedPlaceId. Il retourne seulement place_id, ordonné ASC, avec cursor base64url et limite 1..500. Le test PostgreSQL retrouve exclusivement le terminal attendu.

# Professional Status Delivery Consumer Specification

Le Consumer valide type, version, owner, aggregate, identité, version causale et index avant tout routage. Une divergence retourne `DivergentPayload` sans appeler le routeur. Sinon, il restaure le payload, reconstruit uniquement l'enveloppe technique certifiée 4.5F, route une fois et applique exclusivement la politique 4.5G-R1.

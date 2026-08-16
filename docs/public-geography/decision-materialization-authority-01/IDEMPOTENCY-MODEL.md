# Idempotency model

Lorsque l'autorité préalable existera : premier materialize → `Applied`; mêmes sources, même payload, même séquence et même causalité → `AlreadyApplied`; une seule ligne par placeId.

Sans séquence/causalité canoniques, un replay stable ne peut pas être garanti et aucun substitut timestamp/UUID n'est recevable.

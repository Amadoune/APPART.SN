# Replay model

Le replay du même source event relit le même scope logique. Les terminaux déjà actualisés retournent AlreadyApplied; les autres convergent. Aucune version n'est incrémentée par le consumer.

Si le scope a évolué légitimement, chaque décision est évaluée contre les owner facts actuels et le writer arbitre la monotonie.

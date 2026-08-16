# Final version model

- aucune ligne → watermark positif → Applied;
- même vecteur, payload, watermark et causalité → AlreadyApplied;
- vecteur légitimement avancé → somme supérieure → Applied;
- somme inférieure → RejectedObsolete;
- même somme/identité mais vector ou payload différent → Divergent.

Les relations parentales étant immuables dans le Domain actuel et les Aggregate versions monotones, toute mutation supportée d'un membre fixe augmente la somme. Le checksum du payload empêche toute réduction silencieuse du vecteur.

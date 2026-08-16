# Missing decision policy

Le refresh ne matérialise pas une décision absente, car le terminal n'appartient pas au scope public prouvé. Il retourne un no-op/AlreadyConsumed pour ce terminal.

La première matérialisation reste exclusivement ListingPublished ou son catch-up certifié.

# Initial vs Rebuild Generation

Initiale et rebuild utilisent le même `PublicProjectionGenerationId`, le même Manager, le même Validator et les mêmes états. Elles diffèrent par précondition: initial exige zéro Active; rebuild exige une Active et prépare son remplacement. La commande bootstrap initiale refuse donc un environnement déjà actif au lieu de devenir une commande de rebuild implicite.

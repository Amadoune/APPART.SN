# Direct storage URL audit

Option rejetée. Le disk Media est privé, n'expose aucune URL et sa clé contient une arborescence owner-scoped. Le rendre public casserait l'isolation, la révocation et la portabilité backend.

Le disk Laravel `public` n'est pas une source Media et ne doit pas être utilisé comme contournement.

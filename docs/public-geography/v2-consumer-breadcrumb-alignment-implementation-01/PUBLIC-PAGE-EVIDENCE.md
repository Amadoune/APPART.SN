# Public page evidence

La vue publique branche explicitement sur le schema du breadcrumb :

- V1 : liens historiques conservés;
- V2 : liste ordonnée de `span`, sans `href`, avec `aria-current="location"` sur le terminal.

Aucun nouveau design et aucun BreadcrumbList JSON-LD ne sont introduits. Le test Feature vérifie le rendu non navigable.

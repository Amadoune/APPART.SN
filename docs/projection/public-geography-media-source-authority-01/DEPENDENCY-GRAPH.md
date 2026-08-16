# Dependency graph

```text
Geography owner facts -> Public Geography decision --+
                                                   +-> Candidate readiness -> Candidate -> rebuild -> validation -> activation
Media owner facts     -> Public Media decision ----+
```

Listing Published peut livrer une intention à chaque owner. Il n'existe aucun retour Projection vers une source, donc aucun cycle.

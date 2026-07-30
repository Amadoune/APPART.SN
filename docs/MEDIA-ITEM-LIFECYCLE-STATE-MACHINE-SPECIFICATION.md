# Media Item Lifecycle State Machine Specification

```text
              Remove
Active -----------------> Removed (terminal)
   |
   | Archive
   v
Archived (terminal)
```

`initialState()` retourne `Active`. Il n'existe ni restauration, ni suppression physique, ni création implicite dans cette machine.

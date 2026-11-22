# Working agreements

These are Dennis Claassen's standing preferences. They apply to all work, in this repository and any other.

## Comments and documentation

- Explain intent, not mechanics. Word comments abstractly enough that they stay true when the details change.
- Do not enumerate steps, paths, checks or values in comments. Code, commit messages and pull request descriptions hold those details.

## Tests and checks

- Test what users experience: the installed plugin, what a release delivers, how updates behave.
- Do not add fine-grained tests or checks on intermediate layers. Those layers must stay cheap to change; a check that fails when internals change but the user sees no difference costs more than it protects.

## Git

- Commits are authored and committed as Dennis Claassen <declaassen@gmail.com>.
- No AI attribution anywhere in Git: no co-author or session trailers, no mentions in commit messages.

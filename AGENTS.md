# Panahi Car — Agent Instructions

Before making any change:

1. Read `docs/ai-handoff/CHECKPOINT.md` completely.
2. Run `git status`.
3. Inspect the relevant existing code before editing.
4. Do not modify unrelated files.
5. Do not delete or overwrite historical financial data unless explicitly requested.
6. Preserve append-only notes and financial history.
7. Treat existing sale and purchase USD snapshots as immutable.
8. Keep frontend and backend financial calculations consistent.
9. Vehicle mileage is required on every in-stock vehicle record.
10. VIN is optional but must remain unique when provided.
11. Do not introduce hosting dependencies that require macOS, Homebrew, or local-only shell tools for normal production runtime.
12. Prefer robust semantic/token-based edits over fragile whitespace-sensitive patches.
13. Before committing: run relevant tests, `php artisan test`, `npm run build` when frontend changed, `git diff --check`.
14. Use `docs/ai-handoff/CHECKPOINT.md` as the current project handoff/source of truth.

Current project branch: `main`.

This is Panahi Car dealership software. Git remote is `https://github.com/mhfarhadi/PanahiCar.git` only. It is not MayaHamrah and must not be treated as a fork, clone, or continuation of that project. Work only inside `/Users/macbook/Desktop/automaya`.

# MayaHamrah — Agent Instructions

Before making any change:

1. Read `docs/ai-handoff/CHECKPOINT.md` completely.
2. Run `git status`.
3. Inspect the relevant existing code before editing.
4. Do not modify unrelated files.
5. Do not delete or overwrite historical financial data unless explicitly requested.
6. Preserve append-only notes and financial history.
7. Treat existing sale and purchase USD snapshots as immutable:
   - `usd_rate`
   - `usd_rate_date`
   - `usd_rate_source`
8. Historical USD backfills must only fill missing snapshots and must never overwrite existing snapshots.
9. Divar asking prices must never be used directly in MayaHamrah's price-estimation formula.
10. Current demo/test sale prices are not real market data and must not be used to judge estimator accuracy.
11. Avoid aggressive estimation logic when comparable data is sparse.
12. Keep frontend and backend financial calculations consistent.
13. Do not introduce hosting dependencies that require macOS, Homebrew, or local-only shell tools for normal production runtime.
14. Prefer robust semantic/token-based edits over fragile whitespace-sensitive patches.
15. Before committing:
    - run relevant targeted tests
    - run `php artisan test`
    - run `npm run build` when frontend code changed
    - run `git diff --check`
16. Do not include unrelated untracked files in commits.
17. Do not clean up or delete existing test/demo records without explicit approval.
18. When uncertain about a product or financial rule, stop and ask before implementing it.
19. Keep changes small, reviewable, and reversible.
20. Use `docs/ai-handoff/CHECKPOINT.md` as the current project handoff/source of truth.

Current project branch: `master`.

The repository contains the authoritative implementation. If documentation and code disagree, inspect both and report the discrepancy before changing anything.

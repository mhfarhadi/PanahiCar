# MayaHamrah — Quick Checkpoint

Date: 2026-08-13

Current stable commit:

`97399c9 — refine price estimates with device similarity`

Previous main commits:

- `08d7c45 — add USD-adjusted price estimation`
- `c660368 — backfill historical USD rates from Navasan`
- `5359570 — archive exchange rates and snapshot USD on sales`
- `0017b9c — make fresh database migrations test-safe`

Status:

- app builds successfully
- `php artisan test` passes: 25 tests / 61 assertions
- repository is clean
- GitHub `master` is pushed through `97399c9`
- exchange rates are archived in `exchange_rates`
- all existing sales now have USD-rate snapshots
- historical missing USD rates can be fetched automatically from Navasan
- manual USD rate entry remains only as a fallback when Navasan cannot provide data
- sale details show sale-day USD and current USD context
- inventory editing, sales filters, global navigation and dashboard remain operational
- price estimation page is available from the dashboard

Price estimation — current behavior:

1. The estimator uses actual MayaHamrah sales as the primary data source.
2. Historical sale prices are normalized to today's USD rate:
   - normalized price = sale price / sale-day USD rate × current USD rate
3. Comparables currently require exact:
   - brand
   - model
   - storage
4. Without specification inputs, the central estimate uses the median normalized price.
5. Optional device specifications now affect similarity:
   - condition grade: A+ / A / B / C
   - registration status
   - Apple battery health percentage
   - Samsung battery condition
6. Similarity is used for weighted-median selection; no fixed toman adjustment coefficients are invented.
7. Each comparable exposes a similarity score.
8. Confidence is currently based on comparable count:
   - 1–2 = low
   - 3–5 = medium
   - 6+ = high
9. Current test/demo sale data is random and must not be used to judge the estimator's real market accuracy.
10. The estimator UI accepts Persian/Arabic/Latin digits for Apple battery health and clamps values to 0–100.

Divar:

- Divar listing prices are NOT part of the MayaHamrah price formula.
- The estimator page includes a secondary “آگهی‌های مشابه در دیوار” section.
- It opens a smart Divar search using brand + model + storage.
- Search scope is currently all Iran.
- The official Divar SEARCH_POST API was deliberately not used in the core flow because its application quota is too limited for repeated production usage.
- Later, if suitable API access becomes available, this section can be upgraded to embedded live listing cards without changing the estimation engine.

Historical USD:

- `CurrencyRateService` handles current and historical Navasan rates.
- historical `dailyCurrency` values use the last recorded rate of the requested day.
- source is stored as `navasan_historical_last`.
- `app:backfill-sale-usd-rates` fills only sales with missing USD snapshots and does not overwrite existing snapshots.

Latest completed:

1. Automatic historical Navasan rate retrieval.
2. Backfill of all existing missing sale USD snapshots.
3. Historical/current Navasan source labeling in sale creation.
4. Base USD-adjusted price estimator.
5. Dashboard link and dedicated price estimation page.
6. Smart Divar search for all Iran.
7. Specification-aware similarity scoring.
8. Apple/Samsung-specific battery inputs.
9. Persian-digit battery entry and 0–100 clamping.
10. Select dropdown arrow alignment fix.
11. Production build and full Laravel test suite passed after the changes.

Next tasks:

- improve estimation methodology carefully as real sales accumulate
- consider recency weighting without overfitting
- improve range/confidence calculation based on actual comparable spread and similarity
- decide whether model-level fallback should be allowed when exact storage has no data
- add automated tests for `PriceEstimationService`
- update the broader AI handoff/project state documents
- later, near deployment, implement and test system backup/restore

Before starting:

- run `git status`
- preserve append-only notes and financial history
- never overwrite immutable sale USD snapshots
- do not use Divar asking prices as a direct pricing formula
- keep current tested sales/device flows stable

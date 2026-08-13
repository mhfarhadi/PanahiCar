# MayaHamrah — Quick Checkpoint

Date: 2026-08-13

Current stable commit:

`60f73a7 — refine price estimates and purchase finance tracking`

Previous main commits:

- `e769d54 — add price estimation service tests`
- `85ba0f9 — update handoff after price estimation`
- `97399c9 — refine price estimates with device similarity`
- `08d7c45 — add USD-adjusted price estimation`
- `c660368 — backfill historical USD rates from Navasan`
- `5359570 — archive exchange rates and snapshot USD on sales`
- `0017b9c — make fresh database migrations test-safe`

Status:

- production frontend build passes
- `php artisan test` passes: 34 tests / 106 assertions
- GitHub `master` is pushed through `60f73a7`
- two unrelated local untracked image files may exist:
  - `logo.png`
  - `Screenshot 2026-08-12 at 23.07.08.png`
- do not include those image files in commits unless explicitly requested
- all existing sales have USD-rate snapshots
- all existing purchases have USD-rate snapshots
- price estimation is available from dashboard and global desktop/mobile navigation

## Price estimation — current behavior

1. Primary data source is actual completed MayaHamrah sales.
2. Historical sale prices are normalized to current USD:
   - normalized price = sale price / sale-day USD rate × current USD rate
3. Exact comparables currently require:
   - brand
   - model
   - storage
4. Optional similarity inputs:
   - condition grade: A+ / A / B / C
   - registration status
   - Apple battery health percentage
   - Samsung battery condition
5. Each comparable exposes:
   - `similarity_score`
   - `recency_score`
   - `combined_weight`
6. Recency scoring policy:
   - 0–30 days old = 100
   - gradually decays between 30 and 365 days
   - 365+ days = floor of 70
   - old sales are never discarded solely because of age
7. Combined weight:
   - similarity × recency / 100
8. Sparse-data protection:
   - fewer than 3 comparables without specification inputs => ordinary median
   - fewer than 3 comparables with specification inputs => similarity-weighted median
   - 3+ comparables => combined similarity + recency weighted median
9. No fixed toman price-adjustment coefficients are invented.
10. Current range is still raw min/max of normalized comparables.
11. Confidence is still count-based:
   - 1–2 = low
   - 3–5 = medium
   - 6+ = high
12. Current demo/test sale data is random and must not be used to judge real estimator accuracy.
13. Apple battery input accepts Persian/Arabic/Latin digits and clamps to 0–100.

Dedicated estimator tests:

- 7 estimator tests are currently included in the full suite
- normalization
- exact brand/model/storage filtering
- similarity weighting
- no-comparable behavior
- recency metadata
- sparse-data conservative behavior
- recency weighting when enough comparables exist

## Divar

- Divar asking prices do NOT affect MayaHamrah price calculations.
- Estimator provides a secondary smart Divar search.
- Search uses brand + model + storage.
- Search scope is all Iran.
- Official Divar `SEARCH_POST` API is deliberately not used as a core runtime dependency because its quota is too limited.
- Embedded Divar cards may be reconsidered later if appropriate API access becomes available.

## USD exchange-rate architecture

`CurrencyRateService` handles current and historical Navasan USD rates.

Historical policy:

- official Navasan historical data is used
- last recorded rate of the requested historical day is selected
- historical source is stored as `navasan_historical_last`
- manual rate is fallback only when Navasan cannot supply a rate

Sales:

- immutable sale snapshot fields:
  - `usd_rate`
  - `usd_rate_date`
  - `usd_rate_source`
- `app:backfill-sale-usd-rates` only fills missing snapshots
- existing snapshot values are never overwritten

Purchases:

- purchase snapshot fields now also exist:
  - `usd_rate`
  - `usd_rate_date`
  - `usd_rate_source`
- both normal inventory purchases and announced-device purchases automatically snapshot USD for `purchase_date`
- `app:backfill-purchase-usd-rates` only fills missing snapshots
- all 12 existing purchases were successfully backfilled
- current purchase missing USD count = 0
- device detail page shows purchase-day USD rate and date

## Installment checks

Customer-check amounts are now normalized to practical check-writing amounts.

Current rule:

- calculate raw per-installment amount
- round each check to nearest 10,000 toman
- every generated installment has the same rounded check amount
- installment total is recalculated from actual generated checks
- installment profit is recalculated from that real installment total
- contract total is recalculated from down payment + actual check total
- therefore stored contract totals exactly match the checks customers actually write
- frontend preview and backend persistence use the same rule
- UI label changed from approximate installment amount to actual check amount

A dedicated feature test locks this behavior.

## Navigation

`برآورد قیمت` is now part of the shared authenticated navigation array.

Therefore it appears automatically in:

- desktop top navigation
- mobile access drawer

## Purchase detail UI

Inventory device details now show:

- seller
- purchase date
- purchase price
- USD rate on purchase date
- USD-rate date
- suggested sale price

## Latest completed

1. Price-estimator recency scoring metadata.
2. Conservative recency weighting strategy.
3. Automated tests for estimator behavior.
4. Global desktop/mobile price-estimate navigation.
5. Practical rounding of installment checks to nearest 10,000 toman.
6. Backend/frontend consistency for installment totals and profit.
7. Dedicated installment-sale feature test.
8. Purchase-day USD snapshot schema.
9. Automatic USD snapshot for both purchase flows.
10. Historical purchase USD backfill command.
11. Backfill of all 12 existing purchases.
12. Device detail display of purchase-day USD.
13. Purchase USD snapshot feature test.
14. Production frontend build passed.
15. Full Laravel suite passed: 34 tests / 106 assertions.
16. Changes committed and pushed as `60f73a7`.

## Next estimator work

Improve methodology carefully as real sales accumulate:

- robust outlier handling
- improve price range beyond raw min/max
- improve confidence using:
  - comparable count
  - similarity
  - recency
  - price spread
- decide whether model-level fallback is allowed when exact storage has no data
- avoid aggressive weighting with very sparse samples
- never judge algorithm quality from current random demo sale prices

## Other future backlog

- undo/reversal for mistakenly marked checks
- check metadata:
  - number
  - bank
  - Sayad information
  - images
- review Sales/Index finance-profit calculation
- sold-device historical detail handling
- sale cancellation/reversal safeguards
- old invalid test sale cleanup only with explicit approval
- internal user-management UI
- middleware for users deactivated while already logged in
- Apple model sorting cleanup
- Samsung manufacturing-country rules if model-specific behavior is needed
- dashboard due-label cleanup
- exchange-rate archive/history UI
- fix CurrencyRateService current-rate cache around date boundaries
- review `exchange_rates` created_at semantics on update
- near deployment:
  - hosting-safe backup feature
  - restore flow
  - real restore test

## Before starting any new development session

- run `git status`
- read this checkpoint
- preserve append-only notes and financial history
- never overwrite immutable sale or purchase USD snapshots
- do not use Divar asking prices directly in pricing formulas
- keep tested purchase/sale/installment flows stable
- use one terminal command per assistant turn and wait for its output

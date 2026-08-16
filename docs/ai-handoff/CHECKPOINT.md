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

## Visual redesign — 2026-08-13

Completed in `ea57ccc`:

- refreshed the authenticated application shell with a narrow desktop navigation rail
- replaced the generic Tailwind-style dashboard composition with a softer custom visual system
- introduced restrained pastel surfaces, asymmetric dashboard composition, lighter borders and shadows
- preserved RTL, responsive/mobile behavior, routes, finance logic, currency logic and dark mode
- mobile navigation drawer remains available
- moved theme switching out of Settings into the shared application chrome
- desktop theme control is in the navigation rail
- mobile theme control is in the top header
- theme switch uses sun/moon controls for light/dark
- existing `maya_theme` localStorage behavior is preserved
- removed the duplicate theme selector from Settings
- user reviewed the visual result and approved it
- `php artisan test` passed: 34 tests / 106 assertions
- `npm run build` passed
- `git diff --check` passed

Important:
- this was a visual-only pass; established business and financial logic must remain unchanged
- continue to preserve the existing estimator and finance backlog after visual work

## Account menu and receivables — 2026-08-13

Completed:

- `0cb28f9` — fixed account/profile menu positioning
  - desktop profile dropdown opens upward from the bottom navigation rail
  - default Dropdown behavior remains downward for other usages
  - mobile drawer is constrained to `100dvh`
  - mobile navigation body scrolls independently
  - mobile account footer remains visible inside the viewport

- `f10e729` — added dedicated installment checks and receivables page
  - new authenticated route: `installments.index`
  - Dashboard "مطالبات اقساطی" now opens the checks/receivables page instead of Sales
  - Dashboard "همه چک‌ها" links to the same page
  - filters:
    - all
    - open
    - overdue
    - due within 7 days
    - paid
  - search supports customer name/mobile and device brand/model/storage/IMEI
  - page shows open, overdue, due-soon and paid summaries
  - each check links back to its sale contract
  - overdue is derived from `due_date` versus today; it is not written as a new historical status
  - existing installment financial data and schema were not modified
  - existing `mark-paid` behavior remains unchanged

Validation after these changes:

- `php artisan test` passed: 34 tests / 106 assertions
- `npm run build` passed
- `git diff --check` passed

## Internal visual system rollout — 2026-08-13

Completed:

- `f37fa88` — applied the approved MayaHamrah visual system across internal application pages.
- Added reusable `mh-*` visual classes in `resources/css/app.css` for:
  - application page surfaces
  - page headers/kickers
  - cards and soft surfaces
  - inputs
  - primary/secondary actions
  - filter chips
  - coral accent treatment
- Updated internal pages for:
  - inventory/devices
  - announced devices
  - contacts
  - sales
  - price estimates
  - settings
- Preserved the approved Dashboard and dedicated receivables styling.
- Light and dark modes now use the same visual language as the redesigned Dashboard.
- Native select controls received a consistent custom caret and explicit Vazirmatn typography.
- Fixed sidebar active-state overlap so `devices.create` activates only “ثبت دستگاه” rather than both it and “موجودی”.
- No routes, validation, financial calculations, estimator rules, or stored financial history were changed.

Validation:

- `php artisan test` passed: 34 tests / 106 assertions
- `npm run build` passed
- `git diff --check` passed
- Visual checks confirmed by user in Light and Dark modes.

## Installment check management — 2026-08-13

Completed:

- `e981421` — added installment check metadata and document management.
  - each installment/check can store:
    - bank name
    - check number
    - 16-digit Sayad ID
    - multiple check images
  - bank is selected from a predefined list rather than entered manually
  - Persian/Arabic digits in check number and Sayad ID are normalized before storage
  - Sayad ID is validated as exactly 16 digits when provided
  - check images are stored on the existing `public` disk
  - new `installment_images` table keeps multiple images per installment
  - image uploader records the user who uploaded each image
  - check notes use existing append-only `entity_notes`; previous notes are preserved
  - receivables search now also supports bank, check number and Sayad ID
  - check details and images are managed directly from the checks/receivables page
  - existing installment amounts, due dates, paid-state logic and financial calculations were not changed
  - historical installment records remain valid because new metadata fields are nullable

- Added `InstallmentCheckDetailsTest` covering:
  - check metadata persistence
  - Persian digit normalization
  - image storage
  - uploader attribution
  - append-only note preservation
  - 16-digit Sayad validation

- `283c080` — completed final internal visual-system cleanup.
  - removed remaining old violet/purple theme tokens from authenticated UI
  - migrated shared `EntityNoteHistory` to the `mh-*` design system
  - Persian date-picker accent now follows MayaHamrah coral visual language
  - shared select styling now also targets `select.mh-input`
  - select caret placement, RTL spacing and Vazirmatn typography now apply inside modals and future shared forms as well

Validation:

- user manually verified check metadata, image display, note history and bank select
- user manually verified global select caret correction
- user manually verified removal of old purple visual remnants
- `php artisan test` passed: 36 tests / 128 assertions
- `npm run build` passed
- `git diff --check` passed

Next logical installment/check work:

- expose check metadata/images more fully inside sale details
- allow marking a check paid directly from the receivables page
- add safe reversal/undo for mistakenly marked-paid checks
- add controlled image removal/replacement while preserving financial history

## Device details layout refinement — 2026-08-13

Completed:

- `ff92152` — redesigned the inventory device detail page layout.
- Visual/hierarchy-only change; backend and financial logic were not modified.
- New structure:
  - compact page header and inventory/status context
  - prominent device gallery
  - commercial summary beside gallery
  - purchase price and suggested sale price surfaced near the top
  - primary sale CTA moved into the main decision area
  - seller and purchase-day USD information grouped with commercial summary
  - IMEI/registration/SIM information given a compact quick panel
  - technical specifications reorganized into structured groups instead of many equal-weight cards
  - device and purchase notes moved to the natural lower section of the page
- Desktop hierarchy and mobile responsiveness preserved.
- User reviewed `/devices/6` and approved the new layout.

Validation:

- `php artisan test` passed: 36 tests / 128 assertions
- `npm run build` passed
- `git diff --check` passed

## Receivables workflow completion — 2026-08-14

Completed:

- `3688e9d` — completed the current check/receivables workflow.
- Product rule confirmed:
  - check metadata is NOT entered during installment sale creation
  - sale creation only defines installment/check count and financial terms
  - check metadata is entered later only from the dedicated “Checks & Receivables” page
  - sale details are read-only for check metadata
- Sale details now display previously registered check information:
  - bank
  - check number
  - 16-digit Sayad ID
  - check images
- Checks & Receivables now allows marking an open check as paid directly from the list.
- The direct mark-paid flow uses the existing `installments.mark-paid` backend endpoint and Persian date picker.
- Existing financial values, installment amounts, due dates and mark-paid backend logic were not changed.

Regression coverage:

- added mark-paid regression coverage verifying:
  - status becomes `paid`
  - `paid_amount` becomes exactly the installment amount
  - real `paid_at` date is stored
  - another installment remains unchanged
  - sale financial snapshot remains unchanged
  - a repeated mark-paid request does not rewrite the original paid date

Manual verification:

- user verified check metadata and image registration from Checks & Receivables
- user verified the registered check data appears correctly in sale details
- user verified direct mark-paid from Checks & Receivables

Validation:

- `php artisan test` passed: 37 tests / 152 assertions
- `npm run build` passed
- `git diff --check` passed

Next check-management work:

- safe reversal/undo for a mistakenly marked-paid check
- controlled check-image removal/replacement while preserving financial history

## Safe installment payment reversal — 2026-08-14

Completed:

- `aa8a4a7` — added safe correction for mistakenly marked-paid checks.
- Paid checks on Checks & Receivables now expose an “اصلاح وصول” action.
- Reversal requires a mandatory reason.
- A successful reversal:
  - changes the current installment status back to `pending`
  - resets `paid_amount` to zero
  - clears the current `paid_at`
  - does not alter the installment amount, due date or sale financial values
  - preserves bank, check number, Sayad ID and check images
- Payment and reversal actions are audit-visible through append-only installment notes.
  - marking a check paid records the paid date and amount
  - reversing payment records the previous paid date, previous paid amount and correction reason
- Repeating reversal on an already-open installment is a no-op and does not create duplicate audit history.
- Existing repeated mark-paid behavior remains a no-op and does not rewrite the original paid date.

UX clarification:

- receivables action label `مشخصات` was changed to `مشخصات چک`
- the ambiguous arrow-only sale link was replaced with `جزئیات فروش`
- check metadata management remains exclusively on Checks & Receivables
- Sale Details remains read-only for check metadata

Manual verification:

- user verified a paid check can be corrected and returns to the open list
- user verified the previous payment and correction reason remain visible in check history

Validation:

- `php artisan test` passed: 38 tests / 183 assertions
- `npm run build` passed
- `git diff --check` passed

Next check-management work:

- controlled removal/replacement of check images

## Controlled check image management — 2026-08-14

Completed:

- `ea3f89f` — added controlled removal and replacement of installment check images.
- Check images are no longer physically destroyed when removed or replaced.
- `installment_images` now stores:
  - `removed_at`
  - `removed_by`
  - `removal_reason`
- Removed images disappear from active check views but remain preserved as historical evidence.
- Replacement creates a new active image and archives the previous image.
- Both removal and replacement require a reason.
- Audit notes are appended to the installment history for both actions.
- Sale Details only displays active check images.
- The six-image limit now applies to the total active images for a check, not six additional images per upload.
- If replacement fails after the new file is stored, the newly uploaded orphan file is cleaned up.

Manual verification:

- user verified image removal hides the image from active check images and records the reason in history
- user verified image replacement shows the new image and preserves replacement history
- user verified archived images do not appear after reopening check details
- user verified Sale Details displays only active images
- user verified bank, check number, Sayad ID, installment amount, due date and payment state remain unchanged

Validation:

- migration `2026_08_14_130800_add_removal_audit_to_installment_images` ran successfully
- targeted check tests passed: 5 tests / 103 assertions
- full test suite passed: 39 tests / 209 assertions
- `npm run build` passed
- `git diff --check` passed

Check-management phase status:

- check metadata and documents: complete
- direct payment registration: complete
- safe paid-state reversal with audit history: complete
- controlled check image removal/replacement: complete

The installment check-management phase is considered complete for the current scope.

## Global ambient ring styling — 2026-08-14

Completed:

- `aa7d6d0` — extended the Dashboard ambient-ring visual language across internal surfaces/pages.
- The shared page/surface styling now uses the same two subtle outlined circles seen in Dashboard.
- Previous radial-gradient attempt was replaced with actual clipped circular borders matching the Dashboard treatment.
- Dark mode keeps the rings very subtle.
- Change is visual only; no product or financial logic changed.

Validation:

- user manually confirmed the intended visual effect
- `npm run build` passed
- full test suite passed
- `git diff --check` passed


## Public installment calculator — 2026-08-14

Implemented and manually verified, but public Features visual design is still provisional and must NOT be treated as final.

Public routes:

- `GET /features/installments`
- `POST /features/installments/calculate`

Architecture:

- Added shared `InstallmentCalculatorService`.
- Existing real sale installment calculations in `SaleController` now use the same shared service.
- Public calculator and authenticated sale flow therefore share one backend financial source of truth.
- Existing installment sale behavior remains unchanged.
- Frontend public calculator does not duplicate the financial formula; calculation results come from backend.
- Added CSRF token meta support for the public JSON POST endpoint.

Regular installments:

- preserves current MayaHamrah logic
- principal = sale price - down payment
- monthly linear profit
- Jalali deferment calculation
- first standard due date = one Jalali month after sale date
- each generated check rounded to nearest 10,000 toman
- totals and profit recalculated from actual rounded checks
- reference case remains:
  - sale price: 300,000,000
  - down payment: 100,000,000
  - monthly rate: 6.5%
  - 3 installments
  - each check: 79,670,000
  - installment total: 239,010,000
  - installment profit: 39,010,000
  - contract total: 339,010,000

Monthly-payment-cap mode:

- user provides maximum desired check amount
- system runs the exact existing installment calculation for counts 1 through 60
- returns the smallest installment count whose real rounded check amount does not exceed the cap
- no separate financial formula was introduced

Custom / irregular checks — confirmed business rule:

- customer states the actual amount they can pay on each chosen date
- that stated check amount is NOT increased to include accrued profit
- profit for each interval is calculated on the current carried balance
- interval timing uses full Jalali months plus remaining days / 30
- after each interval:
  - accrued profit is added to the current balance
  - the customer's stated check amount is then subtracted
- therefore:
  - new balance = previous balance + interval profit - payment
- the next interval's profit is calculated on this new carried balance
- first custom payment does NOT need to be one full month after sale; its actual interval is used
- custom checks may have different dates and amounts
- UI shows each interval's profit, time span and balance after payment

Public calculator UX:

- three modes:
  - regular
  - monthly payment cap
  - custom / irregular checks
- Jalali date pickers
- displayed check dates are Jalali
- fixed issue where selecting today's sale date required selecting another date first
- after choosing sale date in regular/cap modes, first due date automatically becomes one Jalali month later
- custom mode supports adding/removing checks with individual dates and amounts
- user manually verified custom calculation logic and current UI behavior
- current visual direction is acceptable temporarily only; final visual redesign decision is deferred until the end of the public Features phase

Validation:

- `npm run build` passed
- targeted installment/public calculator tests passed: 16 tests / 173 assertions
- full Laravel suite passed: 50 tests / 271 assertions
- `git diff --check` passed

## Public installment sales contract — 2026-08-15

Implemented and manually verified for the current scope.

Public route:

- `GET /features/contracts`

Purpose:

- public standalone installment-sale contract tool
- intended for mobile/device sellers without requiring authentication
- current scope is installment sales only
- seller information is entered manually for now
- future authenticated flow may auto-fill seller/shop profile data

Contract inputs:

- seller:
  - shop name
  - seller/representative full name
  - national ID
  - mobile
  - address
- buyer:
  - full name
  - national ID
  - mobile
  - address
  - occupation
- sold device:
  - brand
  - model
  - storage
  - color
  - IMEI
- optional accessories:
  - item title
  - description/specification
- sale financial data:
  - sale date
  - sale price
  - down payment
  - monthly sale profit rate
- guarantee type:
  - installment checks
  - gold collateral
- guarantee types are mutually exclusive

Device catalog integration:

- public contract tool reuses the same existing MayaHamrah device catalog tables used by internal device creation
- brand selection filters models
- model selection filters supported storage options and colors
- no new duplicate catalog/table was introduced
- displayed color labels reuse MayaHamrah `colorLabel()` Persian labels
- stored device color value remains the existing catalog value

Numeric / date UX:

- public contract date fields use Persian/Jalali date picker UI
- printed contract dates are displayed in Jalali format
- visible numeric inputs use Persian digits while normalized values remain suitable for internal calculations/data
- money formatting uses Persian digit grouping
- monthly profit rate and other numeric contract fields are displayed with Persian digits

Installment-check guarantee:

- installment checks are the actual payment instruments; there is no separate guarantee check
- each installment/check supports:
  - due date
  - amount
  - bank
  - check number
  - 16-digit Sayad ID
  - account holder / issuer name
  - explicit Sayad registration/acceptance confirmation
- bank is selected from the predefined MayaHamrah bank list

Gold guarantee:

- installment schedule remains separate from the gold guarantee
- gold fields:
  - type
  - weight
  - karat
  - optional visual description
- buyer declares lawful ownership of the pledged gold and absence of third-party rights
- gold is held until final installment settlement
- contract wording provides seller authority to sell collateral after the agreed default condition, subject to applicable law
- sale proceeds are applied to remaining debt, accrued contractual delay charge and actual/provable costs
- surplus is returned to buyer
- any deficit remains buyer debt

Confirmed payment/default business rules reflected in the contract draft:

- seller may wait up to 10 days after an installment/check due date before legal action
- this 10-day waiting period is not a grace-free period
- delay charge begins the day after the original due date
- delay charge applies only to the overdue installment/check amount
- daily delay formula:
  - installment amount × monthly sale profit rate ÷ 30
- if the next installment due date arrives while the previous installment remains unpaid:
  - entire remaining debt becomes immediately claimable
  - delay charge for each installment still begins only from that installment's own original due date
- HAMTA ownership transfer is delayed until final settlement
- contract separately reserves a contractual rescission/right-to-return mechanism instead of treating registry status itself as the sole legal ownership basis
- if the device is returned following rescission, previously paid amounts are settled after deduction of:
  - usage cost
  - depreciation
  - damages
  - actual/provable costs

Print output:

- web UI and print document are now completely separate render modes
- print mode hides the public web UI and renders only the formal contract
- output is designed specifically for A4 portrait
- print presentation is intentionally minimal and formal, similar to a Word-designed contract
- no web cards, hero layout, gradients or application chrome appear in print
- print contract includes:
  - compact formal header
  - contract date and guarantee type
  - seller and buyer information
  - device information
  - accessory information when present
  - financial summary
  - installment/check table
  - gold-collateral table when applicable
  - contractual/default terms
  - seller/buyer signature areas
- print tables are explicitly RTL
- party-information tables were corrected to stay inside A4 printable width
- Vazirmatn is used for Persian print typography
- font sizing remains restrained and document-like
- previous public disclaimer text saying the contract was only a draft and should be lawyer-reviewed was removed from the visible UI at user request

Public Features UX:

- guarantee selection was moved above guarantee-specific details so users first select check vs gold
- selected guarantee state is visually explicit
- contract web UI was refined to a modern/minimal direction
- current contract UI is accepted for the current phase
- overall public Features visual design is still provisional and must NOT be treated as final

Files / areas changed in this phase:

- `resources/js/Pages/Features/Contracts/Index.vue`
- `resources/js/Pages/Features/Index.vue`
- `routes/web.php`
- `tests/Feature/PublicFeaturesTest.php`

Validation:

- user manually confirmed final web-form behavior and print layout
- `npm run build` passed
- public feature tests passed: 2 tests / 2 assertions
- full Laravel suite passed: 51 tests / 272 assertions
- `git diff --check` passed

## Public price estimator — 2026-08-16

Public Features tool #3, `برآورد قیمت`, was implemented as a public no-login estimator.

Architecture / source of truth:

- public estimator reuses the existing `PriceEstimationService`
- no duplicate estimator formula was created
- existing authenticated `/price-estimates` flow remains in place
- public route:
  - GET `/features/price-estimate`
  - route name `features.price-estimates.index`
- public controller:
  - `app/Http/Controllers/PublicPriceEstimateController.php`
- public page:
  - `resources/js/Pages/Features/PriceEstimate/Index.vue`
- Features landing-page estimator card now links to the public estimator

Current estimator evidence / calculation rules:

- estimator currently uses completed MayaHamrah sales as its evidence pool
- exact comparable pool still requires:
  - same brand
  - same model
  - same storage
- historical sale price is normalized using:
  - sale USD rate
  - current USD rate
- sparse comparable behavior remains conservative
- recency weighting remains the existing `PriceEstimationService` behavior
- specification matching influences comparable weighting rather than applying invented fixed price adjustments

Color support added in this phase:

- color is now a real estimator input
- public estimator reuses existing:
  - `color_options`
  - `device_model_color_option`
- selected model filters the available color list
- displayed color labels reuse `colorLabel()` Persian labels
- existing stored catalog value remains unchanged
- color was added to `PriceEstimationService` similarity scoring
- matching color receives stronger similarity than a non-matching color
- no artificial fixed toman/percentage adjustment was assigned to any color
- a regression test confirms requested color affects comparable weighting

Other specification inputs retained:

- condition grade
- registration status
- Apple/non-Samsung battery health percentage
- Samsung battery condition

Divar rule:

- Divar remains external market context only
- public estimator provides a link to search similar listings
- Divar asking prices do NOT enter the MayaHamrah estimator formula
- public UI explicitly states this separation

Public estimator UX:

- page is visually separate from the authenticated portal
- current public Features visual direction remains provisional and is not final
- page uses MayaHamrah `Vazirmatn Variable` typography
- native browser `<select>` controls were replaced on this page with a custom `MayaSelect` component because Safari/macOS native option rendering did not reliably honor project typography
- custom dropdown interaction was corrected for Safari:
  - dropdown buttons are not wrapped in `<label>`
  - option selection commits on pointer interaction
  - brand → model → storage → color dependent selection was manually confirmed working
- custom dropdown is currently introduced as:
  - `resources/js/Components/MayaSelect.vue`

Estimator backlog remains open:

- outlier handling
- better range presentation
- multi-factor confidence
- fallback memory decision
- future integration of proprietary public `چی می‌خوام؟ / چیا می‌خوان؟` demand/price-expectation signals

Important future estimator rule:

- public wanted-device signals may later join the estimator evidence pool
- this future integration must be designed explicitly and tested
- Divar must remain display/context only and must never directly enter the estimator formula

Validation:

- user manually confirmed dependent custom dropdown selection works
- `npm run build` passed
- targeted public + estimator suite passed:
  - 11 tests
  - 36 assertions
- full Laravel suite passed:
  - 53 tests
  - 278 assertions
- `git diff --check` passed

Files / areas changed in this phase:

- `app/Http/Controllers/PublicPriceEstimateController.php`
- `app/Services/PriceEstimationService.php`
- `resources/js/Components/MayaSelect.vue`
- `resources/js/Pages/Features/PriceEstimate/Index.vue`
- `resources/js/Pages/Features/Index.vue`
- `routes/web.php`
- `tests/Feature/PriceEstimationServiceTest.php`
- `tests/Feature/PublicFeaturesTest.php`


## Internal installment guarantee type — check vs gold — 2026-08-16

The authenticated/internal sale workflow now explicitly distinguishes the collateral mechanism for installment sales.

Confirmed product rule:

- installment schedule and debt accounting are independent from guarantee type
- an installment sale has exactly one guarantee type:
  - `check`
  - `gold`
- check and gold guarantees are alternatives, not simultaneous
- cash sales have no guarantee type
- existing historical installment sales are preserved as check-backed for backward compatibility
- financial installment calculation, installment amounts, due dates and paid-state mechanics were not changed

Database / persistence:

- `sales.guarantee_type` was added
- existing installment sales are backfilled to `check`
- cash sales keep `guarantee_type = null`
- gold collateral is stored once per sale in `sale_gold_collaterals`
- stored gold collateral snapshot includes:
  - original financed principal
  - coverage months
  - monthly contract profit rate
  - calculated two-month coverage profit
  - total collateral coverage amount
  - gold rate item
  - gold rate per gram
  - gold rate date/source
  - gold karat
  - calculated required weight
  - actual received weight
  - type/description of received gold
- `gold_rates` archives gold price snapshots

Confirmed gold collateral formula for the current version:

- principal / financed balance:
  - sale price − cash down payment
- collateral coverage:
  - principal + two months of profit at the same monthly profit rate used by that sale
- formula:
  - `coverage profit = principal × monthly profit rate × 2`
  - `coverage amount = principal + coverage profit`
  - `required gold weight = coverage amount ÷ 18k gold price per gram`
- no additional arbitrary fixed gram buffer is applied after this formula
- actual received gold weight is stored separately and must be at least the calculated required weight

Example confirmed during implementation:

- sale price: 140,000,000 toman
- down payment: 40,000,000 toman
- financed principal: 100,000,000 toman
- monthly profit rate: 6.5%
- two-month coverage profit: 13,000,000 toman
- collateral coverage amount: 113,000,000 toman
- with 18k rate 19,052,130 toman/gram:
  - required weight ≈ 5.9311 grams
- a received weight of 6 grams is valid

Gold price source:

- Navasan item `18ayar` is used as the current reference for 18-karat gold price per gram
- `gerami` is not used; it represents gram coin pricing
- `bub_18ayar` / `bub_gerami` are not used; they are bubble values
- a dedicated `GoldRateService` was added
- current and historical Navasan snapshots can be archived
- when an automatic rate for the sale date is unavailable, the internal sale flow supports a manual per-gram rate
- the sale preserves the rate snapshot used at contract time so later market-price changes do not rewrite historical collateral calculations

Internal sale form:

- installment sale now requires choosing:
  - ضمانت چک
  - ضمانت طلا
- check-backed sale keeps the existing later check-registration workflow
- gold-backed sale shows:
  - financed principal
  - two-month profit coverage
  - total amount under collateral coverage
  - current 18k gold rate
  - calculated minimum required gold weight
  - actual received gold weight
  - received gold type
  - optional received-gold description
- gold collateral weight lighter than the calculated minimum is rejected server-side

Receivables / installments:

- financial receivables continue to come from the same `installments` rows for both guarantee types
- terminology was generalized from check-only wording toward installment/receivable wording
- gold-backed installments are shown in the receivables list with `ضمانت طلا`
- gold-backed installments do not show a check-registration button
- backend also rejects attempts to register or modify check details/images for a gold-backed installment
- this protection is server-side and does not rely only on hidden UI controls
- check-backed sales retain existing:
  - check number
  - bank
  - Sayad ID
  - check images
  - image replacement/removal audit history

Installment payment / reversal wording:

- paid-state and financial behavior remain unchanged
- for check-backed installments, check-oriented wording is retained where appropriate
- for gold-backed installments, payment is described as installment/payment collection rather than “check clearance”
- audit notes generated for gold-backed payment/reversal use gold/installment-specific wording

Sale details:

- installment sale details now display guarantee type
- gold-backed sale details display the stored gold collateral snapshot, including:
  - base principal
  - two-month coverage profit
  - required weight
  - actual received weight
  - received gold type
  - gold rate and source/date
  - optional description

Dashboard:

- receivables calculations remain installment-based and unchanged financially
- check-specific dashboard wording was generalized to installments/receivables
- upcoming installment data now includes the sale guarantee type

Persian validation labels:

- Laravel application locale was already `fa`
- missing `attributes` mappings caused messages such as `buyer id` to appear inside otherwise Persian validation text
- Persian validation attribute labels were added for sale, installment, check and gold-collateral fields
- manually confirmed example now displays `خریدار` instead of `buyer id`

Files / areas changed in this phase:

- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/InstallmentController.php`
- `app/Http/Controllers/SaleController.php`
- `app/Services/GoldCollateralService.php`
- `app/Services/GoldRateService.php`
- `database/migrations/2026_08_16_120000_add_installment_guarantees_and_gold_collateral.php`
- `lang/fa/validation.php`
- `resources/js/Pages/Dashboard.vue`
- `resources/js/Pages/Installments/Index.vue`
- `resources/js/Pages/Sales/Create.vue`
- `resources/js/Pages/Sales/Show.vue`
- `routes/web.php`
- `tests/Feature/GoldInstallmentGuaranteeTest.php`
- `tests/Unit/GoldCollateralServiceTest.php`

Validation completed during implementation:

- migration ran successfully
- targeted legacy check workflow remained green
- gold collateral formula unit test passed
- gold-backed sale creation test passed
- insufficient gold collateral rejection test passed
- server-side block on check registration for gold-backed sales passed
- user manually tested an actual installment sale with gold guarantee and confirmed calculation/form behavior is correct
- user manually confirmed Persian validation field names are correct
- production Vite build passed
- full suite before the final Persian-label adjustment passed:
  - 56 tests
  - 306 assertions
- final full-suite verification is run immediately after this checkpoint update

## Public gold collateral tool — 2026-08-16

Public Features tool #4, `ضمانت طلا`, was implemented and manually verified.

Architecture / source of truth:

- public gold tool reuses the same internal financial services
- no duplicate collateral formula was created
- collateral calculation comes from:
  - `GoldCollateralService`
- live 18k gold pricing comes from:
  - `GoldRateService`
- installment amounts and due dates come from:
  - `InstallmentCalculatorService`

Public routes:

- GET `/features/gold-collateral`
  - route name `features.gold-collateral.index`
- POST `/features/gold-collateral/calculate`
  - route name `features.gold-collateral.calculate`
- the `ضمانت طلا` card on `/features` now links to the live public tool

Confirmed collateral formula:

- financed principal:
  - sale price − cash down payment
- coverage profit:
  - principal × monthly contract profit rate × 2 months
- collateral coverage:
  - principal + two-month coverage profit
- required gold weight:
  - collateral coverage ÷ current 18k gold price per gram
- gold price source remains Navasan `18ayar`
- the public tool uses the same formula as authenticated/internal gold-backed sales

Installment behavior:

- user enters:
  - sale price
  - cash down payment
  - monthly profit rate
  - installment count
  - sale date
  - first due date
- first due date follows the same Jalali one-month minimum rule as the internal/public installment calculator
- output includes:
  - base financed principal
  - two-month collateral profit
  - total collateral coverage
  - required 18k gold weight
  - live/current gold rate
  - installment amount
  - total installments
  - total installment profit
  - full installment due-date schedule

Reference scenario manually confirmed:

- sale price: 140,000,000 toman
- down payment: 40,000,000 toman
- financed principal: 100,000,000 toman
- monthly rate: 6.5%
- two-month collateral profit: 13,000,000 toman
- collateral coverage: 113,000,000 toman
- with the tested Navasan 18k rate, required weight displayed at approximately 5.9311 grams
- user manually confirmed calculations and page behavior are correct

Public UX / visual direction:

- the page is intentionally distinct from the authenticated portal
- visual composition is tailored specifically to the gold-collateral concept rather than reusing a generic form/card layout
- key visual elements include:
  - gold-rate orbit / circular live-rate focal point
  - prominent required-weight result
  - explicit collateral-coverage breakdown
  - separate installment schedule section
  - contextual explanation of why two months of profit are included
- user explicitly praised this page as highly appropriate and visually strong
- future public tools should continue this design approach:
  - each tool should have a distinct visual personality appropriate to its subject
  - avoid repetitive generic AI/Tailwind card layouts
  - keep the overall public Features family coherent
  - prioritize premium, modern, app-like mobile presentation

Files / areas changed:

- `app/Http/Controllers/PublicGoldCollateralController.php`
- `resources/js/Pages/Features/GoldCollateral/Index.vue`
- `resources/js/Pages/Features/Index.vue`
- `routes/web.php`
- `tests/Feature/PublicGoldCollateralTest.php`
- `tests/Feature/PublicFeaturesTest.php`

Validation completed:

- targeted public gold collateral tests passed
- public Features accessibility test passed
- shared `GoldCollateralService` unit test passed
- production Vite build passed
- `git diff --check` passed
- user manually verified the live public page and confirmed both calculation and visual design are correct

## Public «چی می‌خوام؟» wanted-device request tool — completed (2026-08-16)

Public tool #5 is implemented and manually approved.

### Routes
- `GET /features/wanted` → `features.wanted.index`
- `POST /features/wanted` → `features.wanted.store`
- Publicly accessible without authentication.

### Data model
- New independent table: `wanted_device_requests`.
- Public wanted requests do **not** create fake `devices` records.
- Public submissions do **not** automatically create/reuse internal `contacts`.
- Requester name/mobile are stored as snapshots on the request itself.
- Structured demand fields are kept compatible with MayaHamrah's existing device/catalog vocabulary:
  - brand
  - model
  - storage
  - color
  - condition grade
  - registration status
  - Apple-style battery health percentage
  - Samsung battery condition
  - maximum/target buying price
  - free-form description
- Brand/model/storage/color relationships are server-side validated against the existing catalog/pivot tables.
- Persian/Arabic mobile digits are normalized before persistence.
- These requests are intended to become the data source for public tool #6 «چیا می‌خوان؟».
- Their real colleague price expectations are also future estimator evidence; Divar asking prices remain display/context only and must not directly enter the estimator formula.

### UI / visual behavior
- Tool has its own premium request/radar visual identity and is intentionally different from generic internal portal cards.
- Uses existing Safari-safe `MayaSelect`.
- Brand → model → storage → color selections are dependent on the existing catalog.
- Live phone preview reacts to:
  - Apple / Samsung / other brand identity.
  - selected device color, including frame/glow/surface treatment.
  - condition grade:
    - A+ «در حد نو» → strong animated polish/shine.
    - A «بسیار تمیز» → softer shine.
    - B «تمیز» → light wear/scratches.
    - C «خط و خش‌دار» → visibly stronger scratches/scuffs without misrepresenting it as a broken device.
  - battery selection:
    - Apple/non-Samsung percentage is shown in an iPhone-style top-right battery badge.
    - battery percentage is intentionally displayed with English digits, e.g. `79%`.
    - Samsung battery condition is represented in the same live battery badge.
- Samsung receives a small dedicated visual mark; Apple uses the Apple mark.
- User manually reviewed and explicitly approved the final visual behavior.

### Validation / tests
- Dedicated `PublicWantedDeviceTest`.
- Covers:
  - unauthenticated public access.
  - successful structured public submission.
  - Persian mobile normalization.
  - no unintended internal contact creation.
  - rejection of storage not attached to selected model.
- Public Features access test includes the wanted tool.
- Targeted tests and production build passed before final approval.

## Wanted-market demand intelligence + smart price guard — completed (2026-08-16)

The public «چی می‌خوام؟» tool has been upgraded from a simple request form into a robust demand-data source for MayaHamrah's pricing intelligence.

### Product meaning / pricing architecture
- `max_price` is treated as a real colleague buy bid / buy-side liquidity signal, not as a sale asking price.
- Wanted demand remains separate from completed-sale evidence.
- Estimator architecture now keeps distinct anchors:
  - colleague demand / suggested purchase anchor
  - real completed-sale / transaction anchor
- Wanted bids are never simply averaged with sales.
- Demand volume matters: many independent buyers around a price are stronger evidence than one or two isolated bids.

### Currency snapshot
New migration:
- `database/migrations/2026_08_16_124000_add_currency_snapshot_to_wanted_device_requests.php`

Wanted requests now store:
- `usd_rate`
- `usd_rate_date`
- `usd_rate_source`

Purpose:
- preserve the exchange-rate context at request time
- allow historical demand prices to be normalized to current market conditions
- keep demand evidence comparable with existing purchase/sale currency snapshots

### Demand provenance
New migration:
- `database/migrations/2026_08_16_125000_add_origin_to_wanted_device_requests.php`

Fields:
- `origin`, default `organic`
- `market_reference_source`

Known origins:
- `organic`
- `bootstrap_market`

Bootstrap rows remain clearly distinguishable from real colleague demand.

### WantedMarketSignalService
New service:
- `app/Services/WantedMarketSignalService.php`

Core behavior:
- exact brand/model/storage matching for estimator demand
- 45-day demand lookback
- historical USD normalization
- fresh raw-price fallback only for recent rows lacking a snapshot
- one mobile contributes only its latest effective opinion
- specification similarity includes:
  - condition grade
  - battery health / Samsung battery condition
  - registration
  - color
- recency weighting
- source weighting:
  - organic demand has full authority
  - bootstrap market samples have reduced weight
- robust aggregation uses weighted median and MAD-style outlier bounds rather than simple average
- organic consensus requires at least 5 unique organic buyers
- bootstrap rows cannot manufacture organic consensus
- bootstrap evidence retires once sufficient organic demand exists

Demand summary exposes:
- reference price
- market range
- robust bounds
- organic / bootstrap / unique demand counts
- provisional state
- organic consensus state
- confidence
- specification-adjusted state

### Same-model / other-storage sanity fallback
`WantedMarketSignalService` also provides a model-level sanity reference across other storage variants of the same exact model.

Important:
- this fallback is **not** used as the estimator's actual exact-storage price
- it exists only as a gross sanity check when the requested storage has little or no direct evidence
- eligible other-storage evidence must have either:
  - bootstrap provenance, or
  - at least 3 unique organic colleagues for that storage

This closes cases such as an absurd `iPhone 15 Pro 512GB` bid when 512GB itself has no history but 256GB of the exact same model has strong market evidence.

### WantedPriceGuardService
New service:
- `app/Services/WantedPriceGuardService.php`

Submission behavior:
1. Check exact organic demand consensus.
2. If enough organic evidence exists, use robust demand bounds.
3. If exact demand is sparse, use provisional exact-storage evidence when available.
4. If exact storage has no usable anchor, use same-model / other-storage evidence only for extreme-sanity checking.
5. Completed-sale evidence may independently corroborate an extreme bid.
6. Reject grossly absurd prices **before insert**.
7. Rejected attempts never contaminate the wanted-demand dataset.

Bootstrap/external market samples:
- remain provisional
- have reduced weight
- cannot create organic consensus
- may reject only grossly absurd values outside a deliberately widened sanity corridor

Confirmed live example:
- Apple
- iPhone 15 Pro
- 512GB
- Natural Titanium
- condition C
- registered
- battery 88
- candidate price: 1,000,000 toman

Result:
- rejected before insert
- guard source: provisional extreme sanity
- model-level reference from other storage variants: approximately 122,000,000 toman
- widened lower sanity bound: approximately 43,920,000 toman

User manually confirmed the public UI correctly rejects and explains this case.

### Bootstrap market samples
New seeder:
- `database/seeders/WantedMarketBootstrapSeeder.php`

Behavior:
- rerunnable
- replaces only `bootstrap_market` rows
- creates 20 provisional market-reference wanted rows
- samples cover common Apple and Samsung models
- provenance references Divar / Sheypoor market context
- bootstrap rows are **not fake completed sales**
- bootstrap rows do **not** represent callable colleagues
- no real personal phone numbers are used

Divar / Sheypoor principle remains:
- public asking prices are contextual/provisional evidence only
- they do not become real transaction evidence
- they are not blended into completed-sale history

### Mandatory specification quality
Public wanted submissions now require price-relevant specification evidence:
- condition grade is required
- registration status is required
- Apple/non-Samsung battery health is required
- Samsung battery condition is required

Removed ambiguous defaults:
- no «تمیزی مهم نیست»
- no «رجیستری مهم نیست»
- no «باتری مهم نیست»

Reason:
- these attributes materially affect market-price comparison
- weak/default specifications created loopholes for poor price validation

Color remains optional.

### Mobile number behavior
- visible mobile input uses Persian digits for the user experience
- server still normalizes and stores canonical Latin digits
- canonical storage is intentional for:
  - deduplication
  - contact behavior
  - future demand-feed actions

### Smart market feedback UI
When a submitted price is rejected:
- backend returns structured `market_feedback`
- public UI shows an immediate intelligent market-analysis card near the price field
- feedback includes, when available:
  - candidate price
  - demand reference
  - completed-sale reference
  - contextual explanation of why the price was rejected
- copy is intentionally conversational and colleague-friendly rather than formal
- rejected request explicitly says it will not be registered

Desktop:
- feedback card spans the full price section width
- price/reference frames remain contained and responsive

Mobile:
- feedback layout remains compact and stacked

### Mobile live device preview
Desktop preview remains unchanged and sticky in its original right-side layout.

Mobile now has a dedicated live preview placed directly in the device/specification flow:
- visible while entering specification data
- reacts instantly to:
  - color
  - condition
  - battery
  - brand/model/storage
- compact sticky presentation keeps the visual feedback in view while editing
- full desktop preview is hidden on mobile to avoid duplicate previews

User explicitly approved the resulting mobile experience.

### Price estimator integration
`PriceEstimationService` now consumes `WantedMarketSignalService`.

Estimator keeps sale and demand evidence separate:
- completed-sale estimate stays transaction-based
- `demand_signal` is returned separately
- `suggested_purchase_price` comes from the demand reference
- demand is never averaged into completed-sale pricing

Public and internal estimator UIs now expose:
- colleague demand signal
- organic demand count
- confidence
- provisional/real-demand distinction
- purchase-side reference even when completed-sale history is unavailable

Specification similarity already accounts for:
- condition
- battery
- registration

Any stale UI wording claiming otherwise has been corrected.

### Public form flow
Reviewed and intentionally kept unchanged because dependency order is already logical:

- brand
- model
- storage
- color
- condition
- registration
- battery
- maximum buy price
- requester/contact information

Dependency resets are correct:
- changing brand clears model/storage/color/battery fields
- changing model clears storage/color
- model depends on brand
- storage/color depend on model

User approved the current order and requested no unnecessary rearrangement.

### Validation / verification
Relevant automated coverage now includes:
- gross organic-demand outlier rejection
- repeated same-mobile demand does not create false consensus
- immutable USD snapshot behavior
- bootstrap does not reject plausible aggressive bids
- provisional demand + completed sale can reject gross outliers
- mandatory condition / registration / battery evidence
- absurd provisional-market bid rejected even without exact completed sale
- same-model other-storage fallback catches sparse exact-storage absurd prices
- estimator exposes demand separately from sales

Final targeted suite:
- `PublicWantedDeviceTest`
- `PriceEstimationServiceTest`
- `PublicFeaturesTest`

Result:
- 28 tests passed
- 99 assertions passed

Also confirmed:
- production Vite build passed
- `git diff --check` passed
- user manually verified desktop and mobile wanted-tool behavior and approved the final UX

### Next product area
Next public tool:
- #6 «چیا می‌خوان؟»

Planned direction:
- public live demand board powered by `wanted_device_requests`
- organic requester mobile hidden initially
- explicit reveal/contact action
- bootstrap rows shown transparently as provisional market samples
- bootstrap rows must never expose fake callable phone numbers
- visual direction should feel like a live market pulse / demand board, complementary to the radar/request identity of «چی می‌خوام؟»

## Public «چیا می‌خوان؟» market demand board — completed (2026-08-16)

Public tool #6 is implemented and manually approved.

### Routes
- `GET /features/wanted-market`
  - route name: `features.wanted-market.index`
- `GET /features/wanted-market/{requestId}/contact`
  - route name: `features.wanted-market.contact`
- public/no-login access

### Data source
- reads directly from `wanted_device_requests`
- no duplicate demand table was introduced
- feed is ordered newest-first
- pagination uses 18 requests per page
- search supports:
  - brand
  - model
  - storage
  - color
  - requester name
  - description
- filters support:
  - brand
  - organic demand only
  - bootstrap market samples only

### Privacy / contact reveal
Organic requester mobile numbers are **not included in initial Inertia page props**.

Contact flow:
- organic requests expose only `can_reveal_contact = true`
- user must explicitly click «نمایش شماره»
- dedicated contact endpoint then returns:
  - requester name
  - mobile
- revealed mobile becomes a callable `tel:` action

Bootstrap rows:
- never expose their synthetic bootstrap phone identifiers
- contact endpoint rejects bootstrap rows
- UI identifies them as provisional/reference market samples instead of real colleagues

This privacy behavior is enforced server-side and covered by tests.

### Organic vs bootstrap presentation
Organic rows:
- labeled `درخواست واقعی`
- requester contact can be explicitly revealed

Bootstrap rows:
- labeled `نمونه‌ی بازار`
- shown transparently as provisional/reference evidence
- source such as Divar/Sheypoor may be displayed
- no fake callable contact is presented

### Market-board UX
Visual direction is intentionally different from generic cards.

Identity:
- `Maya Market Pulse`
- live market / radar / signal composition
- dark pulse panel with demand counts
- timeline-like demand stream
- each request appears as a market signal rather than a generic card

Hero copy approved as:
- `تو بازار دنبال چی می‌گردن؟`

Board communicates clearly:
- displayed values are colleague buy ceilings / demand-side signals
- they are **not sale prices**

Summary metrics:
- total signals
- organic requests
- bootstrap samples
- requests from the last 24 hours

### Request interaction
Each demand row is interactive.

Clicking a row:
- expands an inline detail panel
- clicking again closes it
- keyboard Enter/Space also toggles it
- details include:
  - device/model/storage
  - color
  - condition
  - registration
  - battery
  - maximum buy price
  - organic vs sample state

Contact remains a separate explicit action inside the expanded request.

### Responsive behavior
Desktop:
- market-stream/table hybrid layout
- expanded detail remains inline with the selected demand row
- price and contact actions remain clearly separated

Mobile:
- rows collapse into a vertical demand timeline
- price receives a prominent dark treatment
- expanded details switch to a compact two-column specification grid
- contact controls become full-width
- overall market-pulse identity remains intact

User manually reviewed and approved the final desktop/mobile experience.

### Features index
The existing `چیا می‌خوان؟` public Features card now links to:
- `features.wanted-market.index`

### Files / areas changed
- `app/Http/Controllers/PublicWantedMarketController.php`
- `resources/js/Pages/Features/WantedMarket/Index.vue`
- `resources/js/Pages/Features/Index.vue`
- `routes/web.php`
- `tests/Feature/PublicFeaturesTest.php`

### Automated coverage
Public Features tests now also verify:
- wanted market page is public
- organic mobile is absent from initial page output
- organic contact is revealed only through explicit endpoint
- bootstrap rows cannot reveal contact data

Latest targeted validation:
- `PublicFeaturesTest`: 9 passed / 14 assertions
- production Vite build passed
- `git diff --check` passed

### Public Features status
Completed public tools:
1. ماشین حساب اقساط
2. فرم قرارداد فروش
3. برآورد قیمت
4. ضمانت طلا
5. چی می‌خوام؟
6. چیا می‌خوان؟

Remaining planned public tool:
7. پرینتر چک

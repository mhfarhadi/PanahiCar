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

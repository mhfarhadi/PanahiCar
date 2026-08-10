# MayaHamrah Project Checkpoint

Date: 2026-08-10

## Project
- Laravel 13
- PHP 8.4
- Vue 3
- Inertia
- Tailwind
- MySQL 8.4
- Project path: ~/Desktop/maya-hamrah

## Working Features

### Authentication
- Login/register scaffold works.
- Public registration is temporary and should later be disabled.

### Persian / RTL
- UI is RTL.
- Dashboard date is Jalali/Persian.
- Purchase date uses Persian date picker.
- Laravel validation messages are Persian.
- Field names in validation are Persian.

### Device Catalog
Implemented catalog tables:
- brands
- device_models
- storage_options
- color_options
- part_number_options
- model/storage pivot
- model/color pivot
- model/part-number pivot

Seeded examples:
- Apple
- Samsung
- iPhone 13
- iPhone 13 Pro
- iPhone 15 Pro
- Galaxy S23
- Galaxy S24 Ultra

Part numbers:
- ZA/A
- LL/A
- CH/A
- ZP/A
- J/A
- AE/A

### Device Registration
Fields include:
- brand
- model
- storage
- color
- part number
- single/dual SIM
- battery health
- condition grade
- IMEI
- registration status
- description
- images

Price field:
- thousands separator
- Persian number-to-words display

Mobile:
- accepts Persian/Latin digits
- backend normalizes digits

Images:
- stored on public disk
- php artisan storage:link completed
- images display correctly

## Important Domain Rule

There are TWO completely different device flows.

### 1. Shop Inventory
status = in_stock

This means:
- We have actually purchased the phone.
- The phone is physically/internally part of our inventory.
- A Purchase record must exist.
- Purchase contains seller, purchase price and purchase date.

### 2. Announced Devices
status = announced

This means:
- The phone is still owned/held by another person or colleague.
- They have only announced/offered the phone to us.
- It must NOT appear in shop inventory.
- It must NOT have a Purchase record yet.
- Device stores:
  - announced_by_id
  - announced_price
  - announced_at

If we later buy an announced device:
- change status from announced to in_stock
- create Purchase at that moment

## Current Database State

Three test devices currently exist.

They were originally incorrectly stored as in_stock.

They have now been corrected to:
status = announced

Their old seller/contact information was moved to:
announced_by_id

Their old purchase_price was moved to:
announced_price

Their old purchase_date was moved to:
announced_at

The related test Purchase rows were deleted.

Therefore:
- Dashboard inventory count = 0
- Shop inventory page = empty
- Announced Devices page = 3 devices

## Dashboard

Working:
- Inventory count comes from database:
  status = in_stock
- Device registration shortcut
- Inventory shortcut
- Announced devices shortcut

Static/not implemented yet:
- sales this month
- installment receivables
- currency rates

## Inventory Page

Route:
devices.index

Working:
- only status=in_stock devices
- images
- brand/model/storage/color
- battery health
- condition
- part number
- SIM type
- purchase price
- suggested sale price = purchase price + 10%
- seller

## Announced Devices Page

Controller:
AnnouncedDeviceController

Route:
announced-devices.index

Page:
resources/js/Pages/AnnouncedDevices/Index.vue

Working:
- only status=announced devices
- image
- specs
- announced price
- announcer name
- announcer mobile
- clickable tel: link

## Latest Work

Created:
app/Http/Controllers/AnnouncedDeviceCreateController.php

The controller already contains:

create():
- loads brand/model/storage/color/part-number catalog
- renders AnnouncedDevices/Create

store():
- validates phone specifications
- validates announcer name/mobile
- announced_price
- announced_at
- images
- creates Contact
- creates Device with status=announced
- sets announced_by_id
- sets announced_price
- sets announced_at
- stores images
- DOES NOT create Purchase

## NEXT STEP

Continue from here:

1. Add routes:
   GET /announced-devices/create
   POST /announced-devices

2. Create:
   resources/js/Pages/AnnouncedDevices/Create.vue

The announced-device form should be similar to the normal device form but use:
- announcer name
- announcer mobile
- announced price
- announced date

It must NOT ask for purchase information.

3. Add "ثبت گوشی اعلامی" button to AnnouncedDevices/Index.vue.

4. Test registration of a new announced phone.

## Later Work

- Clicking a device opens full details page.
- Buy an announced device -> convert to in_stock and create Purchase.
- Real shop device registration flow should remain separate.
- Avoid duplicate Contacts by reusing contacts by mobile.
- Build sales flow.
- Build sold devices.
- Installment calculator.
- Persian/Jalali dates everywhere.
- Activity/audit logs.
- Disable public registration.

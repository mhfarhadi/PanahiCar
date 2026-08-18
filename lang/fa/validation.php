<?php

return [

    'required' => 'وارد کردن :attribute الزامی است.',
    'string' => ':attribute باید به صورت متن باشد.',
    'integer' => ':attribute باید عدد صحیح باشد.',
    'numeric' => ':attribute باید عدد باشد.',
    'array' => ':attribute باید به صورت لیست باشد.',
    'date' => 'تاریخ وارد شده برای :attribute معتبر نیست.',
    'image' => ':attribute باید یک تصویر معتبر باشد.',
    'unique' => 'این :attribute قبلاً ثبت شده است.',
    'in' => 'مقدار انتخاب شده برای :attribute معتبر نیست.',
    'digits' => ':attribute باید دقیقاً :digits رقم باشد.',
    'max' => [
        'numeric' => ':attribute نباید بیشتر از :max باشد.',
        'file' => 'حجم :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'string' => ':attribute نباید بیشتر از :max کاراکتر باشد.',
        'array' => ':attribute نباید بیشتر از :max مورد داشته باشد.',
    ],
    'min' => [
        'numeric' => ':attribute نباید کمتر از :min باشد.',
        'file' => 'حجم :attribute نباید کمتر از :min کیلوبایت باشد.',
        'string' => ':attribute باید حداقل :min کاراکتر باشد.',
        'array' => ':attribute باید حداقل :min مورد داشته باشد.',
    ],

    'attributes' => [
        'brand' => 'برند',
        'model' => 'مدل',
        'model_year' => 'سال مدل',
        'mileage' => 'کارکرد',
        'color' => 'رنگ',
        'transmission' => 'گیربکس',
        'fuel_type' => 'نوع سوخت',
        'body_condition' => 'وضعیت بدنه',
        'insurance_months' => 'بیمه شخص ثالث',
        'vin' => 'VIN',
        'description' => 'توضیحات',
        'seller_name' => 'نام فروشنده',
        'seller_mobile' => 'شماره موبایل فروشنده',
        'seller_id' => 'فروشنده',
        'announcer_id' => 'اعلام‌کننده',
        'purchase_price' => 'قیمت خرید',
        'purchase_date' => 'تاریخ خرید',
        'announced_price' => 'قیمت اعلامی',
        'announced_at' => 'تاریخ اعلام',
        'images' => 'تصاویر خودرو',
        'images.*' => 'تصویر خودرو',

        // فروش و اقساط
        'buyer_id' => 'خریدار',
        'sale_type' => 'نوع فروش',
        'sale_price' => 'قیمت فروش',
        'down_payment' => 'پیش‌پرداخت',
        'monthly_profit_rate' => 'نرخ سود ماهانه',
        'installment_count' => 'تعداد اقساط',
        'first_due_date' => 'تاریخ اولین سررسید',
        'sale_date' => 'تاریخ فروش',
        'usd_rate' => 'نرخ دلار',

        // نوع ضمانت و طلای وثیقه
        'guarantee_type' => 'نوع ضمانت',
        'gold_rate_per_gram' => 'نرخ هر گرم طلای ۱۸ عیار',
        'gold_received_weight' => 'وزن طلای دریافتی',
        'gold_type' => 'نوع طلای دریافتی',
        'gold_description' => 'توضیحات طلای دریافتی',

        'requester_name' => 'نام ثبت‌کننده درخواست',
        'requester_mobile' => 'شماره موبایل',
        'max_price' => 'حداکثر قیمت خرید',

        // چک و وصول اقساط
        'check_number' => 'شماره چک',
        'bank_name' => 'بانک',
        'sayad_id' => 'شناسه صیاد',
        'paid_at' => 'تاریخ وصول',
        'reason' => 'دلیل',
        'note' => 'یادداشت',
    ],

];

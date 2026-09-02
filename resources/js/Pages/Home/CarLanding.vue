<script setup>
import PublicShell from '@/Layouts/PublicShell.vue';
import { illustration, modelPhoto } from '@/Utils/carPhotos';
import { BRAND_FA, pageTitle } from '@/Utils/brand';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));

const categories = ['همه', 'کراس‌اوور', 'سدان', 'شاسی‌بلند'];
const activeCategory = ref('همه');

const popularCars = [
    { model: 'دنا پلاس', type: 'سدان خانوادگی', price: '۱.۲۸ میلیارد', rating: '۴.۹', category: 'سدان' },
    { model: 'تیگو ۷ پرو', type: 'کراس‌اوور', price: '۱.۹۵ میلیارد', rating: '۴.۸', category: 'کراس‌اوور' },
    { model: 'شاهین', type: 'شهری اقتصادی', price: '۹۸۰ میلیون', rating: '۴.۷', category: 'سدان' },
    { model: 'فیدلیتی', type: 'شاسی‌بلند هفت‌نفره', price: '۲.۴۵ میلیارد', rating: '۴.۸', category: 'شاسی‌بلند' },
];

const filteredCars = computed(() => {
    if (activeCategory.value === 'همه') {
        return popularCars;
    }

    return popularCars.filter((car) => car.category === activeCategory.value);
});

const featuredCar = computed(() => filteredCars.value[0] ?? popularCars[0]);

const stats = [
    { label: 'ابزار عمومی', value: '۷+', hint: 'بدون نیاز به ورود' },
    { label: 'تمرکز اصلی', value: 'اقساط', hint: 'لیزینگ و فروش' },
    { label: 'پنل مدیریت', value: '۲۴/۷', hint: 'برای کارکنان نمایشگاه' },
];
</script>

<template>
    <Head :title="pageTitle('لیزینگ خودرو')" />

    <PublicShell>
        <div class="ph-rx-home">
            <div class="ph-rx-home__toolbar">
                <nav class="ph-rx-pills" aria-label="بخش‌های اصلی">
                    <span class="ph-rx-pill is-active">خودرو</span>
                    <Link :href="route('features.index')" class="ph-rx-pill">امکانات</Link>
                </nav>
                <Link
                    :href="loggedIn ? route('dashboard') : route('login')"
                    class="ph-rx-btn ph-rx-btn--dark"
                >
                    {{ loggedIn ? 'داشبورد' : 'ورود کارکنان' }}
                </Link>
            </div>

            <section class="ph-rx-home__hero">
                <div class="ph-rx-home__intro">
                    <p class="ph-rx-kicker">لیزینگ و مدیریت نمایشگاه · {{ BRAND_FA }}</p>
                    <h1 class="ph-rx-title">
                        خودروهایی که با
                        <span class="ph-rx-title__muted">سبک زندگی شما</span>
                        هم‌خوان‌اند
                    </h1>
                    <p class="ph-rx-lede">
                        از برآورد قیمت و قرارداد تا مدیریت موجودی و اقساط — همه‌چیز در یک تجربه تمیز و سریع.
                    </p>

                    <div class="ph-rx-search">
                        <svg viewBox="0 0 24 24" class="ph-rx-search__icon" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M20 20l-3.5-3.5" />
                        </svg>
                        <span class="ph-rx-search__text">جستجوی مدل خودرو…</span>
                        <Link :href="route('features.price-estimates.index')" class="ph-rx-search__action">
                            برآورد قیمت
                        </Link>
                    </div>

                    <div class="ph-rx-chips">
                        <button
                            v-for="category in categories"
                            :key="category"
                            type="button"
                            class="ph-rx-chip"
                            :class="{ 'is-active': activeCategory === category }"
                            @click="activeCategory = category"
                        >
                            {{ category }}
                        </button>
                    </div>
                </div>

                <aside class="ph-rx-rail">
                    <div class="ph-rx-rail__head">
                        <div>
                            <p class="ph-rx-rail__title">پرفروش‌ترین‌ها</p>
                            <p class="ph-rx-rail__sub">محبوب‌ترین انتخاب‌ها در بازار</p>
                        </div>
                        <span class="ph-rx-rail__badge">Live</span>
                    </div>

                    <Link
                        v-for="car in filteredCars.slice(0, 3)"
                        :key="car.model"
                        :href="route('features.price-estimates.index')"
                        class="ph-rx-car-row"
                    >
                        <img :src="modelPhoto(car.model)" :alt="car.model" class="ph-rx-car-row__thumb" />
                        <div class="ph-rx-car-row__body">
                            <p class="ph-rx-car-row__name">{{ car.model }}</p>
                            <p class="ph-rx-car-row__meta">{{ car.price }} تومان</p>
                        </div>
                        <div class="ph-rx-car-row__rating">
                            <span>{{ car.rating }}</span>
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="currentColor"><path d="M12 2l2.9 6.9 7.1.6-5.4 4.7 1.7 7-6.3-3.8-6.3 3.8 1.7-7L2 9.5l7.1-.6L12 2z" /></svg>
                        </div>
                    </Link>
                </aside>
            </section>

            <section class="ph-rx-featured">
                <article class="ph-rx-featured__card">
                    <div class="ph-rx-featured__copy">
                        <p class="ph-rx-featured__type">{{ featuredCar.type }}</p>
                        <h2 class="ph-rx-featured__name">{{ featuredCar.model }}</h2>
                        <div class="ph-rx-featured__stats">
                            <div>
                                <p class="ph-rx-featured__stat-label">قیمت تقریبی</p>
                                <p class="ph-rx-featured__stat-value">{{ featuredCar.price }}</p>
                            </div>
                            <div>
                                <p class="ph-rx-featured__stat-label">امتیاز</p>
                                <p class="ph-rx-featured__stat-value">{{ featuredCar.rating }}</p>
                            </div>
                            <div>
                                <p class="ph-rx-featured__stat-label">دسته</p>
                                <p class="ph-rx-featured__stat-value">{{ featuredCar.category }}</p>
                            </div>
                        </div>
                        <div class="ph-rx-featured__actions">
                            <Link :href="route('features.installments.index')" class="ph-rx-btn ph-rx-btn--yellow">
                                محاسبه اقساط
                            </Link>
                            <Link :href="route('features.index')" class="ph-rx-btn ph-rx-btn--outline">
                                همه امکانات
                            </Link>
                        </div>
                    </div>
                    <div class="ph-rx-featured__visual">
                        <img :src="modelPhoto(featuredCar.model)" :alt="featuredCar.model" class="ph-rx-featured__photo" />
                        <img :src="illustration('showroom')" alt="" class="ph-rx-featured__glow" aria-hidden="true" />
                    </div>
                </article>
            </section>

            <section class="ph-rx-metrics">
                <article v-for="item in stats" :key="item.label" class="ph-rx-metric" :class="{ 'ph-rx-metric--accent': item.value === 'اقساط' }">
                    <p class="ph-rx-metric__value">{{ item.value }}</p>
                    <p class="ph-rx-metric__label">{{ item.label }}</p>
                    <p class="ph-rx-metric__hint">{{ item.hint }}</p>
                </article>
            </section>
        </div>
    </PublicShell>
</template>

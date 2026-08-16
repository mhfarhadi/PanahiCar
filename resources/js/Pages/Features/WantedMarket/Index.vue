<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { colorLabel } from '@/Utils/deviceLabels';

const props = defineProps({
    requests: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    brands: {
        type: Array,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const filterForm = reactive({
    q: props.filters.q ?? '',
    brand: props.filters.brand ?? '',
    origin: props.filters.origin ?? '',
});

const revealedContacts = ref({});
const contactLoading = ref({});
const contactErrors = ref({});
const expandedRequestId = ref(null);

const toggleRequest = (item) => {
    expandedRequestId.value =
        expandedRequestId.value === item.id
            ? null
            : item.id;
};

const rows = computed(() => props.requests.data ?? []);

const conditionLabels = {
    'A+': 'در حد نو',
    A: 'بسیار تمیز',
    B: 'تمیز',
    C: 'خط و خش‌دار',
};

const registrationLabels = {
    registered: 'رجیستر شده',
    unregistered: 'رجیستر نشده',
};

const batteryConditionLabels = {
    excellent: 'باتری عالی',
    good: 'باتری خوب',
    poor: 'باتری ضعیف',
    replace: 'نیاز به تعویض باتری',
};

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatDate = (value) => {
    if (!value) return '';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('fa-IR', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
};

const conditionLabel = (value) =>
    conditionLabels[value] ?? value ?? '—';

const registrationLabel = (value) =>
    registrationLabels[value] ?? value ?? '—';

const batteryLabel = (item) => {
    if (item.brand === 'Samsung') {
        return batteryConditionLabels[item.battery_condition] ?? '—';
    }

    return item.battery_health !== null && item.battery_health !== undefined
        ? `باتری ${toPersianDigits(item.battery_health)}٪`
        : '—';
};

const submitFilters = () => {
    router.get(
        route('features.wanted-market.index'),
        {
            q: filterForm.q || undefined,
            brand: filterForm.brand || undefined,
            origin: filterForm.origin || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    filterForm.q = '';
    filterForm.brand = '';
    filterForm.origin = '';

    router.get(
        route('features.wanted-market.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const revealContact = async (item) => {
    if (!item.can_reveal_contact || revealedContacts.value[item.id]) {
        return;
    }

    contactLoading.value = {
        ...contactLoading.value,
        [item.id]: true,
    };

    contactErrors.value = {
        ...contactErrors.value,
        [item.id]: '',
    };

    try {
        const response = await fetch(
            route('features.wanted-market.contact', item.id),
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || 'شماره تماس در دسترس نیست.'
            );
        }

        revealedContacts.value = {
            ...revealedContacts.value,
            [item.id]: data.contact,
        };
    } catch (error) {
        contactErrors.value = {
            ...contactErrors.value,
            [item.id]:
                error?.message || 'شماره تماس در دسترس نیست.',
        };
    } finally {
        contactLoading.value = {
            ...contactLoading.value,
            [item.id]: false,
        };
    }
};

const telHref = (mobile) =>
    `tel:${String(mobile ?? '').replace(/[^\d+]/g, '')}`;
</script>

<template>
    <Head title="چیا می‌خوان؟ | مایاهمراه" />

    <div dir="rtl" class="market-page">
        <div class="market-shell">
            <div class="ambient ambient-one" />
            <div class="ambient ambient-two" />

            <header class="topbar">
                <div class="brand-side">
                    <Link
                        :href="route('features.index')"
                        class="back-button"
                        aria-label="بازگشت به امکانات"
                    >
                        ←
                    </Link>

                    <div>
                        <strong>چیا می‌خوان؟</strong>
                        <small>Maya Market Pulse</small>
                    </div>
                </div>

                <Link
                    :href="route('features.wanted.index')"
                    class="add-request"
                >
                    <span>+</span>
                    من چی می‌خوام؟
                </Link>
            </header>

            <main class="content">
                <section class="hero">
                    <div class="hero-copy">
                        <div class="eyebrow">
                            <span class="pulse-dot" />
                            تقاضای بازار همکارها، همین الان
                        </div>

                        <h1>
                            تو بازار دنبال
                            <span>چی می‌گردن؟</span>
                        </h1>

                        <p>
                            درخواست‌های خرید ثبت‌شده را ببین؛ از مدل و
                            مشخصات تا سقف خرید. درخواست‌های واقعی همکارها
                            از نمونه‌های اولیه‌ی بازار کاملاً جدا هستند.
                        </p>

                        <div class="hero-actions">
                            <a
                                href="#demand-board"
                                class="primary-action"
                            >
                                دیدن تقاضاها
                                <b>↓</b>
                            </a>

                            <Link
                                :href="route('features.wanted.index')"
                                class="secondary-action"
                            >
                                ثبت درخواست خرید
                            </Link>
                        </div>
                    </div>

                    <div class="pulse-panel">
                        <div class="pulse-orbit">
                            <span class="orbit orbit-one" />
                            <span class="orbit orbit-two" />
                            <span class="orbit orbit-three" />

                            <span class="signal signal-a" />
                            <span class="signal signal-b" />
                            <span class="signal signal-c" />
                            <span class="signal signal-d" />

                            <div class="pulse-center">
                                <small>MARKET</small>
                                <strong>{{ summary.total }}</strong>
                                <span>سیگنال ثبت‌شده</span>
                            </div>
                        </div>

                        <div class="pulse-footer">
                            <div>
                                <span>تقاضای واقعی</span>
                                <strong>{{ summary.organic }}</strong>
                            </div>

                            <div>
                                <span>نمونه بازار</span>
                                <strong>{{ summary.bootstrap }}</strong>
                            </div>

                            <div>
                                <span>۲۴ ساعت اخیر</span>
                                <strong>{{ summary.recent }}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    id="demand-board"
                    class="board-shell"
                >
                    <div class="board-head">
                        <div>
                            <small>LIVE DEMAND BOARD</small>
                            <h2>نبض خرید بازار</h2>
                            <p>
                                هر ردیف یک سقف خرید است، نه قیمت فروش.
                            </p>
                        </div>

                        <div class="legend">
                            <span>
                                <i class="organic-dot" />
                                درخواست واقعی
                            </span>

                            <span>
                                <i class="sample-dot" />
                                نمونه‌ی اولیه بازار
                            </span>
                        </div>
                    </div>

                    <form
                        class="filters"
                        @submit.prevent="submitFilters"
                    >
                        <label class="search-box">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-4-4" />
                            </svg>

                            <input
                                v-model="filterForm.q"
                                type="search"
                                placeholder="مثلاً iPhone 15 Pro یا 256GB"
                            >
                        </label>

                        <select v-model="filterForm.brand">
                            <option value="">
                                همه برندها
                            </option>

                            <option
                                v-for="brand in brands"
                                :key="brand"
                                :value="brand"
                            >
                                {{ brand }}
                            </option>
                        </select>

                        <select v-model="filterForm.origin">
                            <option value="">
                                همه سیگنال‌ها
                            </option>
                            <option value="organic">
                                فقط درخواست واقعی
                            </option>
                            <option value="bootstrap_market">
                                فقط نمونه بازار
                            </option>
                        </select>

                        <button
                            type="submit"
                            class="filter-button"
                        >
                            اعمال فیلتر
                        </button>

                        <button
                            v-if="
                                filterForm.q ||
                                filterForm.brand ||
                                filterForm.origin
                            "
                            type="button"
                            class="clear-button"
                            @click="clearFilters"
                        >
                            پاک کردن
                        </button>
                    </form>

                    <div
                        v-if="rows.length"
                        class="demand-stream"
                    >
                        <article
                            v-for="(item, index) in rows"
                            :key="item.id"
                            class="demand-row"
                            :class="{
                                provisional: item.is_provisional,
                                expanded: expandedRequestId === item.id,
                            }"
                            role="button"
                            tabindex="0"
                            :aria-expanded="
                                expandedRequestId === item.id
                            "
                            @click="toggleRequest(item)"
                            @keydown.enter.prevent="toggleRequest(item)"
                            @keydown.space.prevent="toggleRequest(item)"
                        >
                            <div class="row-index">
                                <span>
                                    {{
                                        String(index + 1)
                                            .padStart(2, '0')
                                    }}
                                </span>
                                <i />
                            </div>

                            <div class="device-block">
                                <div class="device-kicker">
                                    <span>
                                        {{ item.brand }}
                                    </span>

                                    <small>
                                        {{ formatDate(item.created_at) }}
                                    </small>
                                </div>

                                <h3>
                                    {{ item.model }}
                                </h3>

                                <div class="device-spec-line">
                                    <strong>{{ item.storage }}</strong>

                                    <span v-if="item.color">
                                        {{ colorLabel(item.color) }}
                                    </span>

                                    <span>
                                        {{ conditionLabel(item.condition_grade) }}
                                    </span>
                                </div>
                            </div>

                            <div class="spec-cluster">
                                <span>
                                    {{
                                        registrationLabel(
                                            item.registration_status
                                        )
                                    }}
                                </span>

                                <span>
                                    {{ batteryLabel(item) }}
                                </span>

                                <span
                                    v-if="item.is_provisional"
                                    class="sample-chip"
                                >
                                    نمونه‌ی بازار
                                </span>

                                <span
                                    v-else
                                    class="organic-chip"
                                >
                                    درخواست واقعی
                                </span>
                            </div>

                            <div class="price-block">
                                <small>تا سقف</small>
                                <strong>
                                    {{ formatMoney(item.max_price) }}
                                </strong>
                                <span>تومان</span>
                            </div>

                            <div class="contact-block">
                                <template v-if="item.can_reveal_contact">
                                    <div
                                        v-if="revealedContacts[item.id]"
                                        class="revealed-contact"
                                    >
                                        <small>
                                            {{
                                                revealedContacts[item.id]
                                                    .requester_name
                                            }}
                                        </small>

                                        <a
                                            :href="
                                                telHref(
                                                    revealedContacts[item.id]
                                                        .mobile
                                                )
                                            "
                                            @click.stop
                                        >
                                            {{
                                                toPersianDigits(
                                                    revealedContacts[item.id]
                                                        .mobile
                                                )
                                            }}
                                        </a>
                                    </div>

                                    <button
                                        v-else
                                        type="button"
                                        class="reveal-button"
                                        :disabled="contactLoading[item.id]"
                                        @click.stop="revealContact(item)"
                                    >
                                        <span class="eye-icon">
                                            ◉
                                        </span>

                                        {{
                                            contactLoading[item.id]
                                                ? 'در حال دریافت...'
                                                : 'نمایش شماره'
                                        }}
                                    </button>

                                    <small
                                        v-if="contactErrors[item.id]"
                                        class="contact-error"
                                    >
                                        {{ contactErrors[item.id] }}
                                    </small>
                                </template>

                                <div
                                    v-else
                                    class="sample-contact"
                                >
                                    <span>↗</span>
                                    <div>
                                        <strong>مرجع اولیه</strong>
                                        <small>
                                            {{
                                                item.market_reference_source ||
                                                'بازار'
                                            }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="item.description"
                                class="row-description"
                            >
                                {{ item.description }}
                            </p>

                            <Transition name="request-detail">
                                <div
                                    v-if="
                                        expandedRequestId === item.id
                                    "
                                    class="request-detail"
                                    @click.stop
                                >
                                    <div class="detail-head">
                                        <div>
                                            <small>
                                                جزئیات درخواست
                                            </small>
                                            <strong>
                                                {{ item.model }}
                                                {{ item.storage }}
                                            </strong>
                                        </div>

                                        <span
                                            :class="
                                                item.is_provisional
                                                    ? 'detail-sample'
                                                    : 'detail-organic'
                                            "
                                        >
                                            {{
                                                item.is_provisional
                                                    ? 'نمونه بازار'
                                                    : 'درخواست واقعی'
                                            }}
                                        </span>
                                    </div>

                                    <div class="detail-grid">
                                        <div>
                                            <span>رنگ</span>
                                            <strong>
                                                {{
                                                    item.color
                                                        ? colorLabel(item.color)
                                                        : 'مهم نیست'
                                                }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>وضعیت</span>
                                            <strong>
                                                {{
                                                    conditionLabel(
                                                        item.condition_grade
                                                    )
                                                }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>رجیستری</span>
                                            <strong>
                                                {{
                                                    registrationLabel(
                                                        item.registration_status
                                                    )
                                                }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>باتری</span>
                                            <strong>
                                                {{ batteryLabel(item) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="detail-price">
                                        <div>
                                            <small>
                                                سقف واقعی خرید
                                            </small>
                                            <strong>
                                                {{
                                                    formatMoney(
                                                        item.max_price
                                                    )
                                                }}
                                            </strong>
                                            <span>تومان</span>
                                        </div>

                                        <template
                                            v-if="
                                                item.can_reveal_contact
                                            "
                                        >
                                            <div
                                                v-if="
                                                    revealedContacts[item.id]
                                                "
                                                class="detail-contact-ready"
                                            >
                                                <small>
                                                    {{
                                                        revealedContacts[
                                                            item.id
                                                        ].requester_name
                                                    }}
                                                </small>

                                                <a
                                                    :href="
                                                        telHref(
                                                            revealedContacts[
                                                                item.id
                                                            ].mobile
                                                        )
                                                    "
                                                >
                                                    تماس با همکار
                                                </a>
                                            </div>

                                            <button
                                                v-else
                                                type="button"
                                                class="detail-contact-button"
                                                :disabled="
                                                    contactLoading[item.id]
                                                "
                                                @click.stop="
                                                    revealContact(item)
                                                "
                                            >
                                                {{
                                                    contactLoading[item.id]
                                                        ? 'در حال دریافت...'
                                                        : 'نمایش شماره و تماس'
                                                }}
                                            </button>
                                        </template>

                                        <div
                                            v-else
                                            class="detail-reference"
                                        >
                                            <small>
                                                مرجع اولیه بازار
                                            </small>
                                            <strong>
                                                {{
                                                    item.market_reference_source
                                                        || 'بازار'
                                                }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </article>
                    </div>

                    <div
                        v-else
                        class="empty-state"
                    >
                        <div class="empty-radar">
                            <span />
                            <span />
                            <i />
                        </div>

                        <strong>با این فیلتر چیزی پیدا نشد</strong>
                        <p>
                            فیلترها را سبک‌تر کن یا خودت یک درخواست جدید
                            ثبت کن.
                        </p>

                        <button
                            type="button"
                            @click="clearFilters"
                        >
                            نمایش همه تقاضاها
                        </button>
                    </div>

                    <nav
                        v-if="requests.links?.length > 3"
                        class="pagination"
                        aria-label="صفحه‌بندی"
                    >
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="link in requests.links"
                            :key="link.label"
                            :href="link.url || undefined"
                            preserve-scroll
                            class="page-link"
                            :class="{
                                active: link.active,
                                disabled: !link.url,
                            }"
                        >
                            <span
                                v-html="
                                    link.label
                                        .replace('&laquo;', 'قبلی')
                                        .replace('&raquo;', 'بعدی')
                                "
                            />
                        </component>
                    </nav>

                    <div class="board-note">
                        <span class="note-icon">i</span>

                        <div>
                            <strong>
                                این صفحه قیمت فروش نیست
                            </strong>
                            <p>
                                اعداد نمایش‌داده‌شده سقف خرید همکارها و
                                سیگنال سمت تقاضا هستند. نمونه‌های اولیه‌ی
                                بازار هم با برچسب جدا نمایش داده می‌شوند و
                                شماره تماس واقعی ندارند.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.market-page {
    min-height: 100dvh;
    padding: 24px;
    overflow-x: hidden;
    background: #8b8c8e;
    color: #1d2020;
}

.market-shell {
    position: relative;
    width: min(1240px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background:
        radial-gradient(
            circle at 90% 6%,
            rgba(115, 244, 194, .34),
            transparent 25%
        ),
        radial-gradient(
            circle at 8% 72%,
            rgba(255, 222, 108, .28),
            transparent 24%
        ),
        linear-gradient(
            135deg,
            #edf7f1 0%,
            #eef0f5 52%,
            #f6f0e5 100%
        );
    box-shadow:
        0 28px 80px rgba(37, 46, 61, .16),
        inset 0 1px 0 rgba(255, 255, 255, .72);
}

.ambient {
    position: absolute;
    border: 1px solid rgba(77, 98, 89, .08);
    border-radius: 999px;
    pointer-events: none;
}

.ambient-one {
    width: 420px;
    height: 420px;
    top: 70px;
    right: -210px;
}

.ambient-two {
    width: 260px;
    height: 260px;
    left: -120px;
    bottom: 180px;
}

.topbar,
.content {
    position: relative;
    z-index: 2;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 26px 28px 10px;
}

.brand-side {
    display: flex;
    align-items: center;
    gap: 14px;
}

.brand-side > div {
    display: flex;
    flex-direction: column;
}

.brand-side strong {
    font-size: 30px;
    font-weight: 950;
}

.brand-side small {
    margin-top: 2px;
    color: #748078;
    font-size: 11px;
    letter-spacing: .04em;
}

.back-button {
    display: grid;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 999px;
    background: rgba(255,255,255,.76);
    color: #1c2521;
    font-size: 21px;
    box-shadow: 0 8px 22px rgba(62, 81, 72, .08);
}

.add-request {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    gap: 9px;
    padding: 0 16px;
    border-radius: 999px;
    background: #1f2824;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    box-shadow: 0 10px 28px rgba(30, 43, 37, .16);
}

.add-request span {
    display: grid;
    width: 24px;
    height: 24px;
    place-items: center;
    border-radius: 999px;
    background: #76e9bb;
    color: #18392d;
    font-size: 18px;
}

.content {
    display: grid;
    gap: 18px;
    padding: 8px 28px 28px;
}

.hero {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(360px, .88fr);
    gap: 18px;
}

.hero-copy,
.pulse-panel,
.board-shell {
    border: 1px solid rgba(255,255,255,.62);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.82),
        0 16px 44px rgba(60, 72, 66, .07);
    backdrop-filter: blur(12px);
}

.hero-copy {
    padding: 36px;
    border-radius: 36px;
    background: rgba(255,255,255,.45);
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #62736b;
    font-size: 11px;
    font-weight: 850;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #43d59d;
    box-shadow: 0 0 0 6px rgba(67,213,157,.13);
    animation: live-pulse 2s ease-in-out infinite;
}

.hero-copy h1 {
    max-width: 650px;
    margin: 13px 0 13px;
    font-size: clamp(44px, 6vw, 75px);
    line-height: 1.04;
    letter-spacing: -.055em;
    font-weight: 950;
}

.hero-copy h1 span {
    color: #319c75;
}

.hero-copy p {
    max-width: 660px;
    margin: 0;
    color: #65716b;
    font-size: 14px;
    line-height: 2.05;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 24px;
}

.primary-action,
.secondary-action {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 0 16px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 900;
}

.primary-action {
    background: #1f2824;
    color: #fff;
}

.primary-action b {
    display: grid;
    width: 25px;
    height: 25px;
    place-items: center;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
}

.secondary-action {
    background: rgba(255,255,255,.72);
    color: #44524c;
}

.pulse-panel {
    display: flex;
    min-height: 370px;
    flex-direction: column;
    padding: 22px;
    border-radius: 36px;
    background:
        radial-gradient(
            circle at 50% 32%,
            rgba(83, 243, 179, .16),
            transparent 35%
        ),
        linear-gradient(145deg, #17221d, #21332b);
    color: #fff;
}

.pulse-orbit {
    position: relative;
    flex: 1;
    display: grid;
    min-height: 265px;
    place-items: center;
    overflow: hidden;
    border-radius: 27px;
    background:
        linear-gradient(
            rgba(255,255,255,.025) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(255,255,255,.025) 1px,
            transparent 1px
        );
    background-size: 32px 32px;
}

.orbit {
    position: absolute;
    border: 1px solid rgba(119, 239, 191, .17);
    border-radius: 999px;
}

.orbit-one {
    width: 120px;
    height: 120px;
}

.orbit-two {
    width: 205px;
    height: 205px;
}

.orbit-three {
    width: 295px;
    height: 295px;
}

.pulse-center {
    position: relative;
    z-index: 2;
    display: flex;
    width: 132px;
    height: 132px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background:
        linear-gradient(145deg, #7befbe, #3bb583);
    color: #13231c;
    box-shadow:
        0 0 0 10px rgba(110,235,185,.06),
        0 18px 48px rgba(48, 181, 128, .24);
}

.pulse-center small {
    font-size: 7px;
    font-weight: 950;
    letter-spacing: .16em;
}

.pulse-center strong {
    margin: 1px 0 -2px;
    font-size: 43px;
    line-height: 1;
    font-weight: 300;
}

.pulse-center span {
    font-size: 8px;
    font-weight: 850;
}

.signal {
    position: absolute;
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #ffe46f;
    box-shadow:
        0 0 0 5px rgba(255,228,111,.08),
        0 0 18px rgba(255,228,111,.45);
    animation: signal-blink 3s ease-in-out infinite;
}

.signal-a {
    top: 21%;
    right: 22%;
}

.signal-b {
    right: 17%;
    bottom: 23%;
    animation-delay: .8s;
}

.signal-c {
    bottom: 19%;
    left: 24%;
    animation-delay: 1.4s;
}

.signal-d {
    top: 28%;
    left: 18%;
    animation-delay: 2s;
}

.pulse-footer {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-top: 12px;
}

.pulse-footer > div {
    padding: 10px;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    background: rgba(255,255,255,.045);
}

.pulse-footer span {
    display: block;
    color: #91a69c;
    font-size: 7px;
}

.pulse-footer strong {
    display: block;
    margin-top: 2px;
    color: #fff;
    font-size: 17px;
    font-weight: 900;
}

.board-shell {
    padding: 25px;
    border-radius: 36px;
    background: rgba(255,255,255,.55);
}

.board-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
}

.board-head small {
    color: #3d9d77;
    font-size: 9px;
    font-weight: 950;
    letter-spacing: .09em;
}

.board-head h2 {
    margin: 4px 0 3px;
    font-size: 31px;
    font-weight: 950;
}

.board-head p {
    margin: 0;
    color: #78817c;
    font-size: 10px;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.legend > span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 9px;
    border-radius: 999px;
    background: rgba(255,255,255,.72);
    color: #66736c;
    font-size: 8px;
    font-weight: 850;
}

.legend i {
    width: 7px;
    height: 7px;
    border-radius: 999px;
}

.organic-dot {
    background: #45d79f;
}

.sample-dot {
    background: #e7c957;
}

.filters {
    display: grid;
    grid-template-columns:
        minmax(220px, 1fr)
        minmax(135px, .34fr)
        minmax(160px, .42fr)
        auto
        auto;
    gap: 8px;
    margin-top: 20px;
    padding: 10px;
    border-radius: 19px;
    background: rgba(230,237,233,.75);
}

.search-box {
    display: flex;
    min-height: 45px;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    border-radius: 13px;
    background: #fff;
}

.search-box svg {
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    color: #7b8881;
}

.search-box input,
.filters select,
.filters button {
    border: 0;
    outline: 0;
    font-family: inherit;
}

.search-box input {
    width: 100%;
    background: transparent;
    color: #26302b;
    font-size: 11px;
    font-weight: 750;
}

.filters select {
    min-width: 0;
    padding: 0 11px;
    border-radius: 13px;
    background: #fff;
    color: #46534d;
    font-size: 10px;
    font-weight: 800;
}

.filter-button,
.clear-button {
    min-height: 45px;
    padding: 0 14px;
    border-radius: 13px;
    cursor: pointer;
    font-size: 9px;
    font-weight: 900;
}

.filter-button {
    background: #27332d;
    color: #fff;
}

.clear-button {
    background: rgba(255,255,255,.72);
    color: #6b7771;
}

.demand-stream {
    position: relative;
    display: grid;
    margin-top: 18px;
}

.demand-stream::before {
    content: '';
    position: absolute;
    top: 0;
    right: 23px;
    bottom: 0;
    width: 1px;
    background:
        linear-gradient(
            to bottom,
            rgba(68, 209, 155, .34),
            rgba(68, 209, 155, .05)
        );
}

.demand-row {
    position: relative;
    cursor: pointer;
    display: grid;
    grid-template-columns:
        47px
        minmax(205px, 1.15fr)
        minmax(200px, .9fr)
        minmax(150px, .7fr)
        minmax(140px, .62fr);
    gap: 13px;
    align-items: center;
    padding: 14px 7px;
    border-bottom: 1px solid rgba(73, 94, 84, .08);
}

.demand-row:last-child {
    border-bottom: 0;
}

.demand-row.expanded {
    margin: 6px 0;
    padding-top: 18px;
    padding-bottom: 18px;
    border: 1px solid rgba(65, 169, 126, .14);
    border-radius: 22px;
    background: rgba(255, 255, 255, .58);
    box-shadow:
        0 12px 30px rgba(48, 74, 61, .07),
        inset 0 1px 0 rgba(255, 255, 255, .82);
}

.request-detail {
    grid-column: 2 / -1;
    display: grid;
    gap: 12px;
    margin-top: 2px;
    padding: 14px;
    border-radius: 18px;
    background:
        linear-gradient(
            135deg,
            rgba(236, 248, 241, .95),
            rgba(255, 250, 235, .82)
        );
    cursor: default;
}

.detail-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.detail-head > div {
    display: grid;
}

.detail-head small {
    color: #849089;
    font-size: 7px;
    font-weight: 850;
}

.detail-head strong {
    margin-top: 2px;
    color: #233029;
    font-size: 14px;
    font-weight: 950;
}

.detail-head > span {
    padding: 6px 9px;
    border-radius: 999px;
    font-size: 7px;
    font-weight: 900;
}

.detail-organic {
    background: rgba(69, 215, 159, .15);
    color: #247b59;
}

.detail-sample {
    background: rgba(228, 198, 80, .18);
    color: #806c22;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 7px;
}

.detail-grid > div {
    padding: 10px;
    border: 1px solid rgba(68, 96, 82, .07);
    border-radius: 13px;
    background: rgba(255, 255, 255, .68);
}

.detail-grid span {
    display: block;
    color: #8b9690;
    font-size: 6px;
}

.detail-grid strong {
    display: block;
    margin-top: 2px;
    color: #39473f;
    font-size: 9px;
    font-weight: 900;
}

.detail-price {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
    padding: 11px 12px;
    border-radius: 15px;
    background: #203128;
    color: #fff;
}

.detail-price > div:first-child {
    min-width: 0;
}

.detail-price small {
    display: block;
    color: #99afa4;
    font-size: 7px;
}

.detail-price strong {
    display: inline-block;
    margin-top: 2px;
    font-size: 18px;
    font-weight: 900;
}

.detail-price span {
    margin-right: 5px;
    color: #7ce3b8;
    font-size: 7px;
    font-weight: 850;
}

.detail-contact-button {
    min-height: 39px;
    padding: 0 13px;
    border: 0;
    border-radius: 12px;
    background: #7be6bb;
    color: #173a2c;
    font-family: inherit;
    font-size: 8px;
    font-weight: 950;
    cursor: pointer;
}

.detail-contact-ready {
    display: grid;
    gap: 3px;
    text-align: left;
}

.detail-contact-ready small {
    color: #a6b9b0;
}

.detail-contact-ready a {
    color: #8df0c8;
    font-size: 9px;
    font-weight: 950;
}

.detail-reference {
    display: grid;
    gap: 2px;
    text-align: left;
}

.detail-reference strong {
    color: #f0d76e;
    font-size: 9px;
}

.request-detail-enter-active,
.request-detail-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.request-detail-enter-from,
.request-detail-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.demand-row.provisional {
    opacity: .82;
}

.row-index {
    position: relative;
    z-index: 2;
    display: grid;
    width: 47px;
    place-items: center;
    color: #87938d;
}

.row-index span {
    font-size: 8px;
    font-weight: 900;
}

.row-index i {
    display: block;
    width: 9px;
    height: 9px;
    margin-top: 5px;
    border: 2px solid #f4faf6;
    border-radius: 999px;
    background: #49d79f;
    box-shadow: 0 0 0 4px rgba(73,215,159,.1);
}

.provisional .row-index i {
    background: #dfc45b;
    box-shadow: 0 0 0 4px rgba(223,196,91,.11);
}

.device-block {
    min-width: 0;
}

.device-kicker {
    display: flex;
    align-items: center;
    gap: 8px;
}

.device-kicker span {
    color: #43856c;
    font-size: 8px;
    font-weight: 950;
}

.device-kicker small {
    color: #99a19d;
    font-size: 7px;
}

.device-block h3 {
    margin: 3px 0 4px;
    overflow: hidden;
    color: #202923;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 17px;
    font-weight: 950;
}

.device-spec-line {
    display: flex;
    min-width: 0;
    flex-wrap: wrap;
    gap: 5px;
}

.device-spec-line strong,
.device-spec-line span {
    padding: 4px 7px;
    border-radius: 999px;
    background: rgba(81,119,101,.07);
    color: #68756e;
    font-size: 7px;
    font-weight: 850;
}

.spec-cluster {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.spec-cluster > span {
    padding: 6px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.68);
    color: #69766f;
    font-size: 7px;
    font-weight: 850;
}

.spec-cluster .organic-chip {
    background: rgba(71,215,160,.13);
    color: #26795a;
}

.spec-cluster .sample-chip {
    background: rgba(228,196,76,.15);
    color: #8b7422;
}

.price-block {
    min-width: 0;
}

.price-block small {
    display: block;
    color: #8a948f;
    font-size: 7px;
}

.price-block strong {
    display: block;
    margin-top: 1px;
    color: #1d2b24;
    font-size: clamp(15px, 1.8vw, 21px);
    line-height: 1.2;
    font-weight: 900;
    white-space: nowrap;
}

.price-block span {
    color: #5b8e77;
    font-size: 7px;
    font-weight: 850;
}

.contact-block {
    min-width: 0;
}

.reveal-button {
    display: flex;
    width: 100%;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 10px;
    border: 1px solid rgba(45,89,69,.09);
    border-radius: 13px;
    background: rgba(255,255,255,.76);
    color: #315a48;
    font-family: inherit;
    font-size: 8px;
    font-weight: 950;
    cursor: pointer;
}

.reveal-button:disabled {
    opacity: .55;
    cursor: wait;
}

.eye-icon {
    color: #45b887;
    font-size: 11px;
}

.revealed-contact {
    display: grid;
    gap: 3px;
    padding: 9px 10px;
    border-radius: 13px;
    background: #20322a;
}

.revealed-contact small {
    overflow: hidden;
    color: #9bb2a7;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 7px;
}

.revealed-contact a {
    color: #8cf0c8;
    font-size: 11px;
    font-weight: 950;
    direction: ltr;
    text-align: right;
}

.sample-contact {
    display: flex;
    min-height: 42px;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 13px;
    background: rgba(233, 204, 91, .1);
    color: #7f6a25;
}

.sample-contact > span {
    display: grid;
    width: 27px;
    height: 27px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 9px;
    background: rgba(227,194,70,.2);
}

.sample-contact > div {
    display: grid;
}

.sample-contact strong {
    font-size: 7px;
}

.sample-contact small {
    margin-top: 1px;
    font-size: 6px;
    opacity: .72;
}

.contact-error {
    display: block;
    margin-top: 4px;
    color: #b34a5b;
    font-size: 6px;
    line-height: 1.6;
}

.row-description {
    grid-column: 2 / -1;
    margin: -5px 0 2px;
    padding: 8px 11px;
    border-radius: 11px;
    background: rgba(255,255,255,.5);
    color: #77817c;
    font-size: 7px;
    line-height: 1.8;
}

.empty-state {
    display: grid;
    justify-items: center;
    margin-top: 18px;
    padding: 50px 20px;
    border: 1px dashed rgba(69,104,87,.14);
    border-radius: 25px;
    background: rgba(255,255,255,.4);
    text-align: center;
}

.empty-radar {
    position: relative;
    display: grid;
    width: 74px;
    height: 74px;
    place-items: center;
    margin-bottom: 14px;
}

.empty-radar span {
    position: absolute;
    border: 1px solid rgba(72,181,136,.18);
    border-radius: 999px;
}

.empty-radar span:first-child {
    width: 72px;
    height: 72px;
}

.empty-radar span:nth-child(2) {
    width: 43px;
    height: 43px;
}

.empty-radar i {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: #4bd39f;
}

.empty-state strong {
    font-size: 15px;
    font-weight: 950;
}

.empty-state p {
    max-width: 380px;
    margin: 7px 0 13px;
    color: #7a8580;
    font-size: 9px;
    line-height: 1.8;
}

.empty-state button {
    min-height: 40px;
    padding: 0 13px;
    border: 0;
    border-radius: 12px;
    background: #24332c;
    color: #fff;
    font-family: inherit;
    font-size: 8px;
    font-weight: 900;
    cursor: pointer;
}

.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin-top: 20px;
}

.page-link {
    display: grid;
    min-width: 36px;
    height: 36px;
    place-items: center;
    padding: 0 9px;
    border-radius: 11px;
    background: rgba(255,255,255,.7);
    color: #5c6a63;
    font-size: 8px;
    font-weight: 850;
}

.page-link.active {
    background: #25352d;
    color: #fff;
}

.page-link.disabled {
    opacity: .38;
}

.board-note {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 11px;
    margin-top: 19px;
    padding: 14px;
    border-radius: 18px;
    background: rgba(232, 205, 94, .11);
}

.note-icon {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 11px;
    background: #e7cc62;
    color: #5e521e;
    font-family: Georgia, serif;
    font-size: 13px;
    font-weight: 900;
}

.board-note strong {
    font-size: 9px;
}

.board-note p {
    margin: 3px 0 0;
    color: #77745e;
    font-size: 8px;
    line-height: 1.8;
}

@keyframes live-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 4px rgba(67,213,157,.09);
    }

    50% {
        box-shadow: 0 0 0 8px rgba(67,213,157,.16);
    }
}

@keyframes signal-blink {
    0%,
    100% {
        opacity: .38;
        transform: scale(.75);
    }

    50% {
        opacity: 1;
        transform: scale(1.15);
    }
}

@media (hover: hover) {
    .add-request,
    .primary-action,
    .secondary-action,
    .back-button,
    .reveal-button,
    .filter-button,
    .clear-button,
    .page-link {
        transition:
            transform 180ms ease,
            box-shadow 180ms ease;
    }

    .add-request:hover,
    .primary-action:hover,
    .secondary-action:hover,
    .back-button:hover,
    .filter-button:hover,
    .clear-button:hover,
    .page-link:hover:not(.disabled) {
        transform: translateY(-2px);
    }

    .demand-row {
        transition: background 180ms ease;
    }

    .demand-row:hover {
        background: rgba(255,255,255,.34);
    }
}

@media (max-width: 980px) {
    .hero {
        grid-template-columns: 1fr;
    }

    .pulse-panel {
        min-height: 330px;
    }

    .filters {
        grid-template-columns: 1fr 1fr;
    }

    .search-box {
        grid-column: 1 / -1;
    }

    .demand-row {
        grid-template-columns:
            42px
            minmax(180px, 1fr)
            minmax(150px, .72fr)
            minmax(130px, .6fr);
    }

    .contact-block {
        grid-column: 2 / -1;
        width: min(250px, 100%);
    }

    .row-description {
        grid-column: 2 / -1;
    }
}

@media (max-width: 680px) {
    .market-page {
        padding: 0;
        background: #eef4f0;
    }

    .market-shell {
        min-height: 100dvh;
        border-radius: 0;
    }

    .topbar {
        padding: 17px 14px 8px;
    }

    .brand-side {
        gap: 9px;
    }

    .brand-side strong {
        font-size: 21px;
    }

    .brand-side small {
        font-size: 8px;
    }

    .back-button {
        width: 44px;
        height: 44px;
    }

    .add-request {
        min-height: 42px;
        padding: 0 10px;
        font-size: 8px;
    }

    .add-request span {
        width: 20px;
        height: 20px;
        font-size: 14px;
    }

    .content {
        padding: 7px 12px 18px;
    }

    .hero-copy,
    .pulse-panel,
    .board-shell {
        border-radius: 27px;
    }

    .hero-copy {
        padding: 24px 19px;
    }

    .hero-copy h1 {
        font-size: 44px;
    }

    .hero-copy p {
        font-size: 11px;
    }

    .pulse-panel {
        min-height: 300px;
        padding: 15px;
    }

    .pulse-orbit {
        min-height: 220px;
    }

    .orbit-three {
        width: 240px;
        height: 240px;
    }

    .orbit-two {
        width: 168px;
        height: 168px;
    }

    .pulse-center {
        width: 112px;
        height: 112px;
    }

    .pulse-center strong {
        font-size: 35px;
    }

    .pulse-footer span {
        font-size: 6px;
    }

    .pulse-footer strong {
        font-size: 14px;
    }

    .board-shell {
        padding: 17px 13px;
    }

    .board-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .board-head h2 {
        font-size: 26px;
    }

    .filters {
        grid-template-columns: 1fr;
        padding: 8px;
    }

    .search-box {
        grid-column: auto;
    }

    .filters select {
        min-height: 43px;
    }

    .demand-stream::before {
        right: 9px;
    }

    .demand-row {
        grid-template-columns: 19px minmax(0, 1fr);
        gap: 8px 10px;
        padding: 14px 0;
    }

    .row-index {
        width: 19px;
        align-self: stretch;
        align-content: start;
        padding-top: 4px;
    }

    .row-index span {
        display: none;
    }

    .row-index i {
        width: 8px;
        height: 8px;
        margin-top: 0;
    }

    .device-block,
    .spec-cluster,
    .price-block,
    .contact-block,
    .row-description {
        grid-column: 2;
    }

    .device-block h3 {
        font-size: 20px;
        white-space: normal;
    }

    .device-spec-line strong,
    .device-spec-line span,
    .spec-cluster > span {
        font-size: 8px;
    }

    .price-block {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: baseline;
        gap: 5px;
        padding: 11px 12px;
        border-radius: 15px;
        background: #223129;
        color: #fff;
    }

    .price-block small {
        color: #9eb1a7;
        font-size: 8px;
    }

    .price-block strong {
        color: #fff;
        font-size: 22px;
        text-align: left;
    }

    .price-block span {
        color: #82dfb8;
        font-size: 8px;
    }

    .contact-block {
        width: 100%;
    }

    .reveal-button,
    .revealed-contact,
    .sample-contact {
        min-height: 48px;
        border-radius: 15px;
    }

    .revealed-contact a {
        font-size: 13px;
    }

    .row-description {
        margin-top: 0;
        font-size: 8px;
    }

    .request-detail {
        grid-column: 2;
        padding: 12px;
    }

    .detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .detail-price {
        grid-template-columns: 1fr;
        gap: 9px;
    }

    .detail-contact-button,
    .detail-contact-ready,
    .detail-reference {
        width: 100%;
        text-align: center;
    }

    .board-note {
        grid-template-columns: 1fr;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}
</style>

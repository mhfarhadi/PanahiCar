<script setup>
import { Head, Link } from '@inertiajs/vue3';

const tools = [
    {
        title: 'ماشین حساب اقساط',
        description: 'محاسبه اقساط منظم، نامنظم و سقف پرداخت ماهانه',
        accent: 'mint',
        route: 'features.installments.index',
        icon: 'M5 3h14v18H5V3Zm3 4h8M8 11h2M14 11h2M8 15h2M14 15h2',
    },
    {
        title: 'فرم قرارداد فروش',
        description: 'قرارداد فروش با ضمانت چک یا طلا',
        accent: 'lavender',
        route: 'features.contracts.index',
        icon: 'M7 3h7l4 4v14H7V3Zm7 0v5h4M10 12h5M10 16h5',
    },
    {
        title: 'برآورد قیمت',
        description: 'برآورد قیمت براساس دیتاهای واقعی و آگهی‌های مشابه',
        accent: 'sky',
        route: 'features.price-estimates.index',
        icon: 'M4 19V11M10 19V5M16 19v-9M3 19h18',
    },
    {
        title: 'ضمانت طلا',
        description: 'محاسبه وزن طلا، پوشش ضمانت و برنامه اقساط',
        accent: 'yellow',
        route: 'features.gold-collateral.index',
        icon: 'm7 9 2-5h6l2 5 3 9H4l3-9Zm1 4h8',
    },
    {
        title: 'چی می‌خوام؟',
        description: 'ثبت نیازمندی همکارها و درخواست خرید',
        accent: 'pink',
        route: 'features.wanted.index',
        icon: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 8v8M8 12h8',
    },
    {
        title: 'چیا می‌خوان؟',
        description: 'نمایش نیازهای ثبت‌شده بازار همکارها',
        accent: 'mint',
        route: 'features.wanted-market.index',
        icon: 'M4 8h16l-1-4H5L4 8Zm1 0v12h14V8M9 20v-6h6v6',
    },
    {
        title: 'پرینتر چک',
        description: 'آماده‌سازی چاپ مستقیم روی برگه چک',
        accent: 'peach',
        icon: 'M7 9V4h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v7H7v-7Z',
    },
];

const highlightCards = [
    {
        title: 'چی می‌خوام؟',
        subtitle: 'ثبت نیازمندی همکارها',
        meta: 'درخواست خرید',
        tone: 'lavender',
    },
    {
        title: 'چیا می‌خوان؟',
        subtitle: 'مشاهده تقاضای بازار',
        meta: 'بازار همکارها',
        tone: 'yellow',
    },
    {
        title: 'پرینتر چک',
        subtitle: 'چاپ دقیق اطلاعات چک',
        meta: 'چاپ و خروجی',
        tone: 'peach',
    },
];
</script>

<template>
    <Head title="امکانات | مایاهمراه" />

    <div dir="rtl" class="features-page">
        <div class="page-shell">
            <div class="shell-background" />

            <header class="topbar">
                <div class="topbar-left">
                    <Link href="/" class="circle-btn">
                        ←
                    </Link>

                    <div class="title-wrap">
                        <strong>امکانات</strong>
                        <small>Maya Tools</small>
                    </div>
                </div>

                <div class="topbar-right">
                    <span class="avatar-badge">M</span>

                    <Link
                        :href="$page.props.auth?.user ? route('dashboard') : route('login')"
                        class="circle-btn ghost"
                    >
                        •••
                    </Link>
                </div>
            </header>

            <main class="main-grid">
                <section class="hero-card reveal">
                    <div class="hero-upper">
                        <small class="hero-kicker">
                            ابزارهای عمومی مایاهمراه
                        </small>

                        <div class="hero-metric">
                            <span>۷</span>
                            <strong>ابزار</strong>
                            <small>رایگان و عمومی</small>
                        </div>

                        <div class="hero-meta">
                            <div>
                                <span>کاربرد</span>
                                <strong>مخصوص موبایل‌فروش‌ها</strong>
                            </div>

                            <div>
                                <span>دسترسی</span>
                                <strong>بدون نیاز به نصب</strong>
                            </div>

                            <div>
                                <span>ورود</span>
                                <strong>قابل استفاده بدون لاگین</strong>
                            </div>
                        </div>
                    </div>

                    <div class="hero-lower">
                        <div class="hero-rails">
                            <div class="rail mint">
                                <span>اقساط</span>
                            </div>

                            <div class="rail yellow">
                                <span>قرارداد</span>
                            </div>
                        </div>

                        <div class="hero-action-card">
                            <div class="hero-action-head">
                                <span>شروع سریع</span>
                                <small>محاسبه و ابزارهای روزمره</small>
                            </div>

                            <div class="hero-tags">
                                <span>محاسبه اقساط</span>
                                <span>برآورد قیمت</span>
                                <span>پرینتر چک</span>
                            </div>

                            <button type="button" class="dark-cta">
                                ورود به امکانات
                            </button>
                        </div>
                    </div>
                </section>

                <section class="content-grid">
                    <article class="surface-card tools-surface reveal delay-1">
                        <div class="surface-toolbar">
                            <div class="surface-toolbar-left">
                                <span class="toolbar-icon active">
                                    ≡
                                </span>

                                <span class="toolbar-icon">⌕</span>
                                <span class="toolbar-icon">⌁</span>
                                <span class="toolbar-icon">◌</span>
                            </div>

                            <div class="surface-toolbar-right">
                                <span class="toolbar-badge">۷</span>
                            </div>
                        </div>

                        <div class="surface-head">
                            <div>
                                <h2>فهرست امکانات</h2>
                                <p>ابزارهای کلیدی برای کار روزانه فروش موبایل</p>
                            </div>

                            <div class="surface-count">
                                <strong>7</strong>
                                <span>ابزار</span>
                            </div>
                        </div>

                        <div class="tools-list">
                            <component
                                :is="tool.route ? Link : 'button'"
                                v-for="tool in tools"
                                :key="tool.title"
                                :href="tool.route ? route(tool.route) : undefined"
                                :type="tool.route ? undefined : 'button'"
                                class="tool-row"
                                :class="tool.accent"
                            >
                                <span class="tool-row-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.55"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path :d="tool.icon" />
                                    </svg>
                                </span>

                                <div class="tool-row-copy">
                                    <strong>{{ tool.title }}</strong>
                                    <small>{{ tool.description }}</small>
                                </div>

                                <span class="tool-row-arrow">
                                    {{ tool.route ? '←' : '…' }}
                                </span>
                            </component>
                        </div>
                    </article>

                    <article class="surface-card highlights-surface reveal delay-2">
                        <div class="highlights-head">
                            <h2>دسترسی سریع</h2>

                            <div class="quick-actions">
                                <span class="quick-plus">+</span>

                                <div class="quick-pills">
                                    <span>شروع</span>
                                    <span>امروز</span>
                                    <span>رایگان</span>
                                </div>
                            </div>
                        </div>

                        <div class="highlight-stack">
                            <article
                                v-for="card in highlightCards"
                                :key="card.title"
                                class="highlight-item"
                                :class="card.tone"
                            >
                                <div class="highlight-date">
                                    <span>{{ card.meta }}</span>
                                </div>

                                <div class="highlight-copy">
                                    <strong>{{ card.title }}</strong>
                                    <small>{{ card.subtitle }}</small>
                                </div>
                            </article>
                        </div>

                        <div class="footer-strip">
                            <div class="footer-strip-copy">
                                <strong>چرا این صفحه مهم است؟</strong>
                                <small>
                                    برای افزایش بازدید، جذب کاربر و ساخت دیتای
                                    واقعی بازار.
                                </small>
                            </div>

                            <span class="footer-pill">Public</span>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.features-page {
    min-height: 100dvh;
    padding: 24px;
    background: #8e8e8e;
}

.page-shell {
    position: relative;
    width: min(1240px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background: linear-gradient(135deg, #b7efe4 0%, #edf2fb 100%);
    box-shadow:
        0 28px 80px rgba(37, 46, 61, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.shell-background {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 10% 12%, rgba(255, 255, 255, 0.45), transparent 22%),
        radial-gradient(circle at 86% 14%, rgba(255, 255, 255, 0.32), transparent 18%),
        radial-gradient(circle at 92% 90%, rgba(255, 170, 150, 0.18), transparent 14%);
    opacity: 0.8;
}

.topbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 28px 10px;
}

.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.circle-btn {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.82);
    color: #161616;
    font-size: 21px;
    box-shadow: 0 8px 24px rgba(60, 73, 91, 0.08);
    transition:
        transform 180ms ease,
        box-shadow 180ms ease,
        background 180ms ease;
}

.circle-btn.ghost {
    font-size: 18px;
    letter-spacing: 2px;
}

.avatar-badge {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ffb19b 0%, #ff8ba7 100%);
    color: #fff;
    font-size: 20px;
    font-weight: 900;
    box-shadow: 0 10px 24px rgba(255, 140, 160, 0.24);
}

.title-wrap {
    display: flex;
    flex-direction: column;
}

.title-wrap strong {
    font-size: 34px;
    font-weight: 900;
    color: #101215;
    letter-spacing: -0.04em;
}

.title-wrap small {
    margin-top: 3px;
    color: #6a717a;
    font-size: 13px;
    font-weight: 600;
}

.main-grid {
    position: relative;
    z-index: 1;
    padding: 8px 28px 28px;
}

.hero-card {
    padding: 26px;
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.38);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.65),
        0 16px 44px rgba(60, 73, 91, 0.08);
    backdrop-filter: blur(10px);
}

.hero-upper {
    display: grid;
    gap: 18px;
}

.hero-kicker {
    color: #66707a;
    font-size: 13px;
    font-weight: 700;
}

.hero-metric {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
}

.hero-metric span {
    font-size: 82px;
    line-height: 0.95;
    font-weight: 300;
    color: #0f1115;
    letter-spacing: -0.06em;
}

.hero-metric strong {
    font-size: 34px;
    font-weight: 900;
    color: #0f1115;
}

.hero-metric small {
    color: #75808a;
    font-size: 18px;
    font-weight: 500;
}

.hero-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.hero-meta div {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.hero-meta span {
    color: #74808a;
    font-size: 12px;
}

.hero-meta strong {
    color: #16191d;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.6;
}

.hero-lower {
    display: grid;
    grid-template-columns: 132px 1fr;
    gap: 18px;
    margin-top: 28px;
    align-items: stretch;
}

.hero-rails {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.rail {
    position: relative;
    min-height: 220px;
    border-radius: 32px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 20px 10px;
    overflow: hidden;
}

.rail span {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    font-size: 24px;
    font-weight: 700;
    color: rgba(0, 0, 0, 0.52);
}

.rail.mint {
    background: #67ef8e;
}

.rail.yellow {
    background: #ece86a;
}

.hero-action-card {
    min-height: 220px;
    padding: 20px;
    border-radius: 32px;
    background:
        repeating-linear-gradient(
            90deg,
            rgba(159, 175, 193, 0.48) 0,
            rgba(159, 175, 193, 0.48) 3px,
            transparent 3px,
            transparent 10px
        ),
        rgba(232, 239, 245, 0.82);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
}

.hero-action-head {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.hero-action-head span {
    font-size: 28px;
    font-weight: 900;
    color: #15191d;
}

.hero-action-head small {
    color: #69727c;
    font-size: 15px;
}

.hero-tags {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.hero-tags span {
    padding: 10px 16px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.72);
    color: #44505b;
    font-size: 13px;
    font-weight: 700;
}

.dark-cta {
    align-self: flex-start;
    min-width: 190px;
    height: 58px;
    padding: 0 28px;
    border-radius: 999px;
    background: #0c0c0d;
    color: white;
    font-size: 17px;
    font-weight: 800;
    box-shadow: 0 14px 30px rgba(12, 12, 13, 0.18);
    transition:
        transform 180ms ease,
        box-shadow 180ms ease;
}

.content-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.85fr;
    gap: 24px;
    margin-top: 24px;
}

.surface-card {
    padding: 22px;
    border-radius: 34px;
    background: rgba(248, 250, 252, 0.9);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        0 16px 40px rgba(60, 73, 91, 0.07);
}

.surface-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.surface-toolbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toolbar-icon {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: #eef2f3;
    color: #98a0a8;
    font-size: 20px;
    transition:
        transform 180ms ease,
        background 180ms ease;
}

.toolbar-icon.active {
    background: #dcf4ef;
    color: #61746f;
    box-shadow: inset 0 0 0 1px rgba(128, 178, 168, 0.18);
}

.toolbar-badge {
    display: grid;
    place-items: center;
    min-width: 42px;
    height: 42px;
    padding: 0 12px;
    border-radius: 999px;
    background: #ffffff;
    color: #fb6f8e;
    font-size: 13px;
    font-weight: 900;
    box-shadow: inset 0 0 0 1px rgba(230, 234, 238, 0.95);
}

.surface-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-top: 20px;
}

.surface-head h2,
.highlights-head h2 {
    color: #101215;
    font-size: 36px;
    font-weight: 900;
    letter-spacing: -0.04em;
}

.surface-head p {
    margin-top: 8px;
    color: #7d8790;
    font-size: 15px;
    line-height: 1.8;
}

.surface-count {
    display: flex;
    align-items: baseline;
    gap: 8px;
    white-space: nowrap;
}

.surface-count strong {
    color: #101215;
    font-size: 54px;
    font-weight: 300;
    letter-spacing: -0.05em;
}

.surface-count span {
    color: #7c858e;
    font-size: 19px;
}

.tools-list {
    display: grid;
    gap: 16px;
    margin-top: 22px;
}

.tool-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: inset 0 0 0 1px rgba(235, 239, 243, 0.95);
    transition:
        transform 180ms ease,
        box-shadow 180ms ease;
}

.tool-row-icon {
    display: grid;
    place-items: center;
    width: 62px;
    height: 62px;
    border-radius: 999px;
    background: #eef2f3;
    color: #59636d;
}

.tool-row-icon svg {
    width: 24px;
    height: 24px;
}

.tool-row-copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
    text-align: right;
}

.tool-row-copy strong {
    color: #111418;
    font-size: 20px;
    font-weight: 800;
}

.tool-row-copy small {
    color: #78828c;
    font-size: 14px;
    line-height: 1.7;
}

.tool-row-arrow {
    display: grid;
    place-items: center;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    background: #f1f3f4;
    color: #8c959e;
    font-size: 20px;
}

.tool-row.mint .tool-row-icon {
    background: #def4ee;
    color: #5f7d76;
}

.tool-row.lavender .tool-row-icon {
    background: #ece7fb;
    color: #7b6fa1;
}

.tool-row.sky .tool-row-icon {
    background: #e6f2fb;
    color: #6786a0;
}

.tool-row.yellow .tool-row-icon {
    background: #f7f4d6;
    color: #8b834d;
}

.tool-row.pink .tool-row-icon {
    background: #fbe5ea;
    color: #9e6e7c;
}

.tool-row.peach .tool-row-icon {
    background: #fde6dc;
    color: #9e7868;
}

.highlights-surface {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.highlights-head {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.quick-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quick-plus {
    display: grid;
    place-items: center;
    width: 58px;
    height: 58px;
    border-radius: 999px;
    background: #eef2f3;
    color: #59636d;
    font-size: 28px;
}

.quick-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 10px 14px;
    border-radius: 999px;
    background: #eef2f3;
}

.quick-pills span {
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.68);
    color: #535d68;
    font-size: 13px;
    font-weight: 700;
}

.highlight-stack {
    display: grid;
    gap: 14px;
}

.highlight-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 18px;
    align-items: center;
    padding: 18px 18px 18px 22px;
    border-radius: 28px;
}

.highlight-item.lavender {
    background: #d9c1f1;
}

.highlight-item.yellow {
    background: #ece86a;
}

.highlight-item.peach {
    background: #f6a08d;
}

.highlight-date {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 94px;
    padding: 10px 12px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.42);
    color: rgba(24, 26, 30, 0.72);
    font-size: 13px;
    font-weight: 700;
    text-align: center;
}

.highlight-copy {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.highlight-copy strong {
    color: #17191d;
    font-size: 24px;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.highlight-copy small {
    color: rgba(23, 25, 29, 0.72);
    font-size: 15px;
    line-height: 1.7;
}

.footer-strip {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding-top: 8px;
}

.footer-strip-copy {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.footer-strip-copy strong {
    color: #14171b;
    font-size: 19px;
    font-weight: 800;
}

.footer-strip-copy small {
    color: #76818a;
    font-size: 14px;
    line-height: 1.8;
}

.footer-pill {
    padding: 10px 16px;
    border-radius: 999px;
    background: #eef2f3;
    color: #64707a;
    font-size: 13px;
    font-weight: 800;
}

.reveal {
    animation: revealUp 540ms ease both;
}

.delay-1 {
    animation-delay: 90ms;
}

.delay-2 {
    animation-delay: 170ms;
}

@keyframes revealUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (hover: hover) {
    .circle-btn:hover,
    .toolbar-icon:hover {
        transform: translateY(-2px);
    }

    .dark-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(12, 12, 13, 0.22);
    }

    .tool-row:hover {
        transform: translateY(-2px);
        box-shadow:
            inset 0 0 0 1px rgba(228, 233, 237, 0.95),
            0 10px 24px rgba(73, 84, 99, 0.07);
    }
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 920px) {
    .features-page {
        padding: 16px;
    }

    .page-shell {
        border-radius: 34px;
    }

    .topbar {
        padding: 22px 20px 10px;
    }

    .main-grid {
        padding: 8px 20px 20px;
    }

    .title-wrap strong {
        font-size: 28px;
    }

    .hero-meta {
        grid-template-columns: 1fr;
    }

    .hero-lower {
        grid-template-columns: 1fr;
    }

    .hero-rails {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rail {
        min-height: 140px;
    }

    .rail span {
        font-size: 20px;
    }

    .hero-action-card {
        min-height: unset;
    }

    .surface-head {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 680px) {
    .topbar {
        align-items: flex-start;
    }

    .topbar-left,
    .topbar-right {
        gap: 10px;
    }

    .circle-btn,
    .avatar-badge {
        width: 48px;
        height: 48px;
    }

    .title-wrap strong {
        font-size: 24px;
    }

    .title-wrap small {
        font-size: 12px;
    }

    .hero-card,
    .surface-card {
        padding: 18px;
        border-radius: 28px;
    }

    .hero-metric span {
        font-size: 58px;
    }

    .hero-metric strong {
        font-size: 26px;
    }

    .hero-metric small {
        font-size: 15px;
    }

    .surface-head h2,
    .highlights-head h2 {
        font-size: 28px;
    }

    .surface-count strong {
        font-size: 42px;
    }

    .toolbar-icon {
        width: 46px;
        height: 46px;
        font-size: 16px;
    }

    .tool-row {
        grid-template-columns: auto 1fr;
    }

    .tool-row-arrow {
        display: none;
    }

    .tool-row-icon {
        width: 54px;
        height: 54px;
    }

    .tool-row-copy strong {
        font-size: 18px;
    }

    .tool-row-copy small {
        font-size: 13px;
    }

    .highlight-item {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .highlight-copy strong {
        font-size: 20px;
    }

    .footer-strip {
        flex-direction: column;
        align-items: flex-start;
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

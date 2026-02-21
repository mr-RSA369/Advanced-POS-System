<script setup>
import { ref, onMounted, computed } from "vue";
import Layout from "@/layouts/layout.vue";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import CustomAlert from "../components/CustomAlert.vue";

defineOptions({ layout: Layout });

/* ================= BUSINESS DAY ================= */
const businessDay = ref(null);
const dayLoading = ref(false);
const dayError = ref("");

/* ================= WEEKLY SALES STATS ================= */
const weekDays = ref([]);
const weekTotal = ref(0);
const weekLoading = ref(false);

/* ================= MONTHLY STATS ================= */
const totalSales = ref(0);
const totalPurchases = ref(0);

const orders = ref({
    delivery: 0,
    dine_in: 0,
    takeaway: 0,
});

// Computed property for trend
const trend = computed(() => {
    const firstDay = weekDays.value[0]?.total || 0;
    const lastDay = weekDays.value[6]?.total || 0;

    if (firstDay === 0) return 0;
    return Math.round(((lastDay - firstDay) / firstDay) * 100);
});

/* ================= FETCH CURRENT BUSINESS DAY ================= */
const fetchBusinessDay = async () => {
    try {
        const res = await axios.get("/api/business-day/current");
        businessDay.value = res.data || null;
        dayError.value = "";
    } catch (e) {
        businessDay.value = null;
        dayError.value =
            e.response?.data?.message || "Failed to fetch business day";
    }
};

/* ================= OPEN BUSINESS DAY ================= */
const openDay = async () => {
    if (dayLoading.value) return;

    try {
        dayLoading.value = true;
        dayError.value = "";

        await axios.post("/api/business-day/open");

        // refresh state after opening
        await fetchBusinessDay();
        await fetchMonthlyStats();
    } catch (e) {
        dayError.value =
            e.response?.data?.message || "Failed to open business day";
        showCustomAlert(
            "error",
            dayError.value
        );
    } finally {
        dayLoading.value = false;
    }
};

/* ================= CLOSE BUSINESS DAY ================= */
const closeDay = async () => {
    if (dayLoading.value) return;

    const confirmed = confirm(
        "Are you sure you want to close the business day?"
    );
    if (!confirmed) return;

    try {
        dayLoading.value = true;
        dayError.value = "";

        await axios.post("/api/business-day/close");

        // refresh state after closing
        await fetchBusinessDay();
        await fetchMonthlyStats();
    } catch (e) {
        dayError.value =
            e.response?.data?.message || "Failed to close business day";
        showCustomAlert(
            "error",
            dayError.value
        );
    } finally {
        dayLoading.value = false;
    }
};

/* ================= FETCH MONTHLY STATS ================= */
const fetchMonthlyStats = async () => {
    try {
        const [sales, purchases, orderStats] = await Promise.all([
            axios.get("/api/stats/monthly-sales"),
            axios.get("/api/stats/monthly-purchases"),
            axios.get("/api/stats/monthly-orders"),
        ]);

        totalSales.value = sales.data.total;
        totalPurchases.value = purchases.data.total;
        orders.value = orderStats.data;
    } catch {

    }
};

/* ================= FETCH WEEKLY STATS ================= */
const fetchWeeklySales = async () => {
    try {
        weekLoading.value = true;
        const response = await axios.get("/api/stats/weekly-sales");
        weekDays.value = response.data.days;
        weekTotal.value = response.data.total;
    } catch (error) {
        console.error("Failed to fetch weekly sales:", error);
    } finally {
        weekLoading.value = false;
    }
};

/* ================= PROFIT / LOSS ================= */
const profit = computed(() => totalSales.value - totalPurchases.value);
const isProfit = computed(() => profit.value >= 0);

/* ================= MONTH LABEL + RANGE ================= */
const monthLabel = ref("");
const monthRange = ref("");

const buildMonthMeta = () => {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    monthLabel.value = now.toLocaleString("default", {
        month: "long",
        year: "numeric",
    });

    monthRange.value = `${start.getDate()} - ${end.getDate()}`;
};

/* ================= INIT ================= */
onMounted(async () => {
    buildMonthMeta();
    await fetchBusinessDay();
    await fetchMonthlyStats();
    await fetchWeeklySales();
});
</script>

<template>
    <div class="flex flex-col gap-y-8">
        <!-- Custom Alerts Component -->
        <CustomAlert />

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500">
                    Business overview and insights
                </p>
            </div>
            <div class="text-sm text-gray-500">
                <div class="flex gap-x-6 items-center">
                <p v-if="businessDay" class="text-sm text-gray-600">
                            {{
                                new Date(
                                    businessDay.business_date
                                ).toLocaleDateString("en-US", {
                                    weekday: "long",
                                    month: "long",
                                    day: "numeric",
                                })
                            }}
                        </p>
                        <p v-else class="text-sm text-red-600">
                            No active business day
                        </p>
                <button
                        v-if="!businessDay"
                        @click="openDay"
                        :disabled="dayLoading"
                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all disabled:opacity-50"
                    >
                        Open Day
                    </button>

                    <button
                        v-if="businessDay"
                        @click="closeDay"
                        :disabled="dayLoading"
                        class="px-4 py-2 border border-red-300 text-red-600 bg-white rounded-xl text-sm font-medium hover:bg-red-50 transition-all disabled:opacity-50"
                    >
                        Close Day
                    </button>
                    <div class="flex items-center gap-2">
                        <div
                            class="h-2 w-2 rounded-full"
                            :class="businessDay ? 'bg-green-500' : 'bg-red-500'"
                        ></div>
                        <span
                            class="text-sm font-medium"
                            :class="
                                businessDay ? 'text-green-700' : 'text-red-700'
                            "
                        >
                            {{ businessDay ? "Active" : "Inactive" }}
                        </span>
                    </div>

                </div>


            </div>
        </div>

<!-- WEEKLY SALES CARD - Clean & Professional -->
<div class="bg-white rounded-xl border border-gray-100 p-6 w-full shadow-sm">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Weekly Revenue</span>
            <div class="flex items-baseline gap-3 mt-1">
                <span class="text-2xl font-semibold text-gray-900">Rs {{ weekTotal.toLocaleString() }}</span>
                <span class="text-xs text-gray-400">total</span>
            </div>
        </div>
        <div class="px-3 py-1.5 bg-gray-50 rounded-lg">
            <span class="text-xs font-medium text-gray-600">
                {{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }} -
                {{ new Date(Date.now() + 6 * 24 * 60 * 60 * 1000).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
            </span>
        </div>
    </div>

    <!-- Daily Sales Grid -->
    <div class="grid grid-cols-7 gap-3 mb-6">
        <div v-for="(day, index) in weekDays" :key="index" class="text-center">
            <div class="mb-2">
                <span class="text-xs font-medium text-gray-400">{{ day.day_short }}</span>
            </div>
            <div class="relative">
                <!-- Mini bar indicator (subtle) -->
                <div class="h-1 w-full bg-gray-100 rounded-full mb-2 overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="day.total > 0 ? 'bg-green-500' : 'bg-gray-200'"
                        :style="{
                            width: day.total > 0 ? `${(day.total / Math.max(...weekDays.map(d => d.total), 1) * 100)}%` : '0%'
                        }"
                    ></div>
                </div>
                <!-- Exact amount -->
                <span class="block text-sm font-medium text-gray-900">
                    Rs {{ day.total.toLocaleString() }}
                </span>
                <span v-if="!day.has_sales" class="block text-xs text-gray-300 mt-0.5">No sales</span>
            </div>
        </div>
    </div>

    <!-- Mini Stats Row -->
    <div class="grid grid-cols-4 gap-4 pt-4 border-t border-gray-100">
        <!-- Average Daily -->
        <div>
            <span class="text-xs text-gray-400 block mb-1">Daily avg</span>
            <span class="text-sm font-semibold text-gray-900">
                Rs {{ Math.round(weekTotal / 7).toLocaleString() }}
            </span>
        </div>

        <!-- Best Day -->
        <div>
            <span class="text-xs text-gray-400 block mb-1">Best day</span>
            <div v-if="weekDays.filter(d => d.has_sales).length > 0">
                <span class="text-sm font-semibold text-gray-900">
                    {{ weekDays.reduce((best, day) => day.total > best.total ? day : best).day_short }}
                </span>
                <span class="text-xs text-gray-500 ml-1">
                    Rs {{ Math.max(...weekDays.map(d => d.total)).toLocaleString() }}
                </span>
            </div>
            <span v-else class="text-sm text-gray-300">—</span>
        </div>

        <!-- Active Days -->
        <div>
            <span class="text-xs text-gray-400 block mb-1">Active days</span>
            <span class="text-sm font-semibold text-gray-900">
                {{ weekDays.filter(d => d.has_sales).length }}/7
            </span>
        </div>

        <!-- Trend -->
        <div>
            <span class="text-xs text-gray-400 block mb-1">vs last week</span>
            <div class="flex items-center gap-1">
                <span class="text-sm font-semibold" :class="trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-gray-500'">
                    {{ trend > 0 ? '+' : '' }}{{ trend }}%
                </span>
                <svg v-if="trend > 0" class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                <svg v-else-if="trend < 0" class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </div>
    </div>
</div>

        <!-- FINANCIAL OVERVIEW -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Financial Overview
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ monthLabel }} ({{ monthRange }})
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Investment Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50"
                        >
                            <svg
                                class="w-5 h-5 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">
                        Total Investment
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rs 500,000
                    </div>
                </div>

                <!-- Sales Card -->
                <Link
                    href="/sales"
                    class="bg-white rounded-xl border border-gray-200 p-5 hover:border-green-300 hover:shadow-md transition-all group cursor-pointer block"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50"
                        >
                            <svg
                                class="w-5 h-5 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <svg
                            class="w-5 h-5 text-gray-400 group-hover:text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">Total Sales</div>
                    <div class="text-2xl font-bold text-green-700">
                        Rs {{ totalSales.toLocaleString() }}
                    </div>
                </Link>

                <!-- Purchases Card -->
                <Link
                    href="/purchases"
                    class="bg-white rounded-xl border border-gray-200 p-5 hover:border-red-300 hover:shadow-md transition-all group cursor-pointer block"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50"
                        >
                            <svg
                                class="w-5 h-5 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <svg
                            class="w-5 h-5 text-gray-400 group-hover:text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">
                        Total Purchases
                    </div>
                    <div class="text-2xl font-bold text-red-600">
                        Rs {{ totalPurchases.toLocaleString() }}
                    </div>
                </Link>

                <!-- Profit/Loss Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="isProfit ? 'bg-green-50' : 'bg-red-50'"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="
                                    isProfit ? 'text-green-600' : 'text-red-600'
                                "
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    :d="
                                        isProfit
                                            ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'
                                            : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'
                                    "
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">
                        {{ isProfit ? "Net Profit" : "Net Loss" }}
                    </div>
                    <div
                        class="text-2xl font-bold"
                        :class="isProfit ? 'text-green-700' : 'text-red-600'"
                    >
                        {{ isProfit ? "+" : "-" }} Rs
                        {{ Math.abs(profit).toLocaleString() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDER STATISTICS -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Order Statistics
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ monthLabel }} ({{ monthRange }})
                    </p>
                </div>
                <Link
                    href="/orders"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    View all orders →
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Delivery Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-purple-50 to-purple-100"
                        >
                            <svg
                                class="w-6 h-6 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">Deliveries</div>
                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ orders.delivery }}
                        </div>
                        <div class="text-sm text-purple-600 font-medium">
                            +12%
                        </div>
                    </div>
                </div>

                <!-- Dine-In Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-blue-100"
                        >
                            <svg
                                class="w-6 h-6 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">Dine-In</div>
                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ orders.dine_in }}
                        </div>
                        <div class="text-sm text-blue-600 font-medium">+8%</div>
                    </div>
                </div>

                <!-- Takeaway Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-green-50 to-green-100"
                        >
                            <svg
                                class="w-6 h-6 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">Takeaway</div>
                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ orders.takeaway }}
                        </div>
                        <div class="text-sm text-green-600 font-medium">
                            +15%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import brozLogo from "@/logo/broz logo.png";

const isSidebarOpen = ref(true);
const isMobile = ref(false);

// Daily Sales Data
const dailySales = ref({
    total: 0,
    order_count: 0,
    trend: 0,
    formatted_date: '',
    comparison: {
        yesterday: 0,
        difference: 0,
        trend_percent: 0
    }
});

const dailySalesLoading = ref(false);
const showDailyDetails = ref(false);


const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) {
        isSidebarOpen.value = false;
    } else {
        isSidebarOpen.value = true;
    }
};

// Fetch Daily Sales
const fetchDailySales = async () => {
    try {
        dailySalesLoading.value = true;
        const response = await axios.get("/api/stats/daily-sales");
        dailySales.value = response.data;
    } catch (error) {
        console.error("Failed to fetch daily sales:", error);
    } finally {
        dailySalesLoading.value = false;
    }
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener("resize", checkScreenSize);
    fetchDailySales();
});

onUnmounted(() => {
    window.removeEventListener("resize", checkScreenSize);
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value).replace('PKR', 'Rs');
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex">
        <!-- Mobile Overlay -->
        <div
            v-if="isMobile && isSidebarOpen"
            class="fixed inset-0 bg-black/50 z-30 lg:hidden transition-opacity duration-300"
            :class="
                isMobile && isSidebarOpen
                    ? 'opacity-100'
                    : 'opacity-0 pointer-events-none'
            "
            @click="toggleSidebar"
        />

        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 bottom-0 z-40 transition-all duration-300 ease-in-out"
            :class="[
                isSidebarOpen
                    ? 'translate-x-0 w-64'
                    : '-translate-x-full lg:translate-x-0 lg:w-20',
            ]"
        >
            <div
                class="h-full bg-gradient-to-b from-[#1f2a44] to-[#152238] text-gray-200 flex flex-col shadow-xl relative"
            >
                <!-- Logo Section -->
                <div
                    class="flex justify-center items-center bg-gradient-to-r from-green-800 to-green-900 p-4 lg:p-6 flex-shrink-0"
                >
                    <div class="flex items-center space-x-3">
                        <img
                            v-if="isSidebarOpen"
                            :src="brozLogo"
                            alt="BROZ Pizza & Cafe"
                            class="h-8 lg:h-10 transition-all duration-300"
                        />
                        <img
                            v-else
                            :src="brozLogo"
                            alt="BROZ"
                            class="h-8 w-8 lg:h-10 lg:w-10 object-contain transition-all duration-300"
                        />
                    </div>
                </div>

                <!-- Menu -->
                <nav
                    class="flex-1 px-3 lg:px-4 py-4 lg:py-6 space-y-1 text-sm overflow-y-auto"
                >
                    <!-- Menu items -->
                    <Link
                        :href="route('home')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/home'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Home
                        </span>
                    </Link>

                    <Link
                        :href="route('menu')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/menu'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Menu
                        </span>
                    </Link>

                    <Link
                        :href="route('place.order')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/place-order'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Place Order
                        </span>
                    </Link>

                    <Link
                        :href="route('orders')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/orders'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Orders
                        </span>
                    </Link>

                    <Link
                        :href="route('purchases')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/purchases'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Purchases
                        </span>
                    </Link>

                    <Link
                        :href="route('sales')"
                        class="flex items-center space-x-3 px-3 py-2.5 lg:px-4 lg:py-3 rounded-lg lg:rounded-xl transition-all duration-200 group"
                        :class="
                            $page.url === '/sales'
                                ? 'bg-gradient-to-r from-green-700 to-green-600 text-white shadow-md'
                                : 'hover:bg-green-800/50 hover:shadow-sm hover:translate-x-1'
                        "
                    >
                        <div
                            class="flex items-center justify-center w-5 h-5 lg:w-6 lg:h-6 shrink-0"
                        >
                            <svg
                                class="w-4 h-4 lg:w-5 lg:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <span
                            class="whitespace-nowrap transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            Sales
                        </span>
                    </Link>
                </nav>

                <!-- User Section -->
                <div
                    class="p-3 lg:p-4 border-t border-gray-700/50 flex-shrink-0"
                    v-if="$page.props.auth.user"
                >
                    <div class="flex items-center space-x-3">
                        <img
                            class="h-8 w-8 lg:h-10 lg:w-10 rounded-full object-cover ring-2 ring-green-600/20 shrink-0"
                            :src="
                                $page.props.auth.user.avatar
                                    ? '/storage/' + $page.props.auth.user.avatar
                                    : '/storage/avatars/default.jpg'
                            "
                            alt="User avatar"
                        />
                        <div
                            class="flex-1 min-w-0 transition-all duration-300 overflow-hidden"
                            :class="
                                isSidebarOpen
                                    ? 'opacity-100 max-w-full'
                                    : 'opacity-0 max-w-0 lg:opacity-0 lg:max-w-0'
                            "
                        >
                            <p class="text-sm font-medium truncate">
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p class="text-xs text-gray-400 truncate">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div
            class="flex-1 flex flex-col min-h-screen transition-all duration-300 ml-0"
            :class="[isSidebarOpen && !isMobile ? 'lg:ml-64' : 'lg:ml-20']"
        >
            <!-- Navbar -->
            <header
                class="sticky top-0 z-10 bg-white/90 backdrop-blur-sm border-b border-gray-200/50 shadow-sm h-14 lg:h-16 flex items-center justify-between px-4 sm:px-6 lg:px-4 transition-all duration-300 flex-shrink-0"
                :class="[isSidebarOpen && !isMobile ? 'lg:ml-0' : 'lg:ml-0']"
            >
                <div class="flex items-center space-x-3">
                    <!-- Mobile Menu Toggle -->
                    <button
                        @click="toggleSidebar"
                        class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200 hover:scale-105 active:scale-95"
                    >
                        <svg
                            v-if="!isSidebarOpen"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Desktop Toggle Button -->
                    <button
                        @click="toggleSidebar"
                        class="hidden lg:flex items-center justify-center p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200 hover:scale-105 active:scale-95"
                    >
                        <svg
                            class="h-4 w-4 transition-transform duration-300"
                            :class="{ 'rotate-180': isSidebarOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7"
                            />
                        </svg>
                    </button>

                    <div class="flex items-center space-x-3">
                        <div
                            class="h-6 lg:h-8 w-1 rounded-full bg-gradient-to-b from-green-600 to-green-400"
                        ></div>
                        <h1
                            class="text-lg lg:text-xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
                        >
                            Dashboard
                        </h1>
                    </div>
                </div>



                <div class="flex items-center gap-x-3 lg:gap-x-4">

                    <div class="relative">
        <button
            @click="showDailyDetails = !showDailyDetails"
            class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg hover:shadow-md transition-all duration-200 group"
            :class="{ 'shadow-md ring-2 ring-green-200': showDailyDetails }"
        >
            <!-- Loading Spinner -->
            <div v-if="dailySalesLoading" class="flex items-center">
                <div class="animate-spin h-4 w-4 border-2 border-green-600 border-t-transparent rounded-full"></div>
            </div>

            <!-- Sales Icon & Amount -->
            <template v-else>
                <div class="relative">
      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
</svg>
                    <!-- Small indicator dot for orders -->
                    <span v-if="dailySales.order_count > 0"
                        class="absolute -top-1 -right-1 w-2 h-2 bg-green-500 rounded-full ring-1 ring-white">
                    </span>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-sm font-semibold text-gray-900">
                        Rs {{ dailySales.total.toLocaleString() }}
                    </span>
                    <!-- Trend indicator -->
                    <span v-if="dailySales.trend !== 0"
                        class="text-xs font-medium"
                        :class="dailySales.trend > 0 ? 'text-green-600' : 'text-red-600'">
                        {{ dailySales.trend > 0 ? '↑' : '↓' }} {{ Math.abs(dailySales.trend) }}%
                    </span>
                </div>

                <!-- Chevron icon -->
                <svg class="w-3 h-3 text-gray-400 transition-transform duration-200"
                    :class="{ 'rotate-180': showDailyDetails }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </template>
        </button>

        <!-- Dropdown Details Card -->
        <div v-if="showDailyDetails"
            class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 p-4 z-50 animate-fadeIn">
            <div class="space-y-3">
                <!-- Header -->
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Today's Sales</span>
                    <span class="text-xs text-gray-500">{{ dailySales.formatted_date }}</span>
                </div>

                <!-- Main Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-xs text-gray-400 block">Total</span>
                        <span class="text-lg font-bold text-gray-900">Rs {{ dailySales.total.toLocaleString() }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Orders</span>
                        <span class="text-lg font-bold text-gray-900">{{ dailySales.order_count }}</span>
                    </div>
                </div>

                <!-- Comparison -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">vs Yesterday</span>
                        <span class="text-sm font-medium"
                            :class="dailySales.comparison.difference >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ dailySales.comparison.difference >= 0 ? '+' : '' }}{{ dailySales.comparison.difference.toLocaleString() }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-xs text-gray-500">Yesterday</span>
                        <span class="text-sm font-medium text-gray-700">Rs {{ dailySales.comparison.yesterday.toLocaleString() }}</span>
                    </div>
                </div>

                <!-- Average per order -->
                <div class="flex justify-between items-center pt-1">
                    <span class="text-xs text-gray-400">Avg per order</span>
                    <span class="text-sm font-medium text-gray-800">
                        Rs {{ dailySales.order_count > 0 ? Math.round(dailySales.total / dailySales.order_count).toLocaleString() : 0 }}
                    </span>
                </div>

                <!-- Quick link to sales page -->
                <Link :href="route('sales')"
                    class="block text-center text-xs text-green-600 hover:text-green-700 font-medium pt-2 border-t border-gray-100">
                    View all sales →
                </Link>
            </div>
        </div>
    </div>

                    <!-- User Info -->
                    <div
                        v-if="$page.props.auth.user"
                        class="flex items-center space-x-3"
                    >
                        <div
                            class="hidden sm:flex flex-col items-end"
                            v-if="!isSidebarOpen || isMobile"
                        >
                            <p class="text-sm font-medium text-gray-700">
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>
                        <img
                            class="h-8 w-8 lg:h-10 lg:w-10 rounded-full object-cover ring-2 ring-green-500/30"
                            :src="
                                $page.props.auth.user.avatar
                                    ? '/storage/' + $page.props.auth.user.avatar
                                    : '/storage/avatars/default.jpg'
                            "
                            alt="User avatar"
                        />
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="hidden sm:inline-flex items-center px-3 lg:px-4 py-1.5 lg:py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200 border border-red-200 hover:border-red-300 hover:scale-105"
                        >
                            Logout
                        </Link>
                    </div>

                    <!-- Mobile Logout -->
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="sm:hidden p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 hover:scale-105"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />
                        </svg>
                    </Link>

                    <!-- Login Button -->
                    <template v-if="!$page.props.auth.user">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center px-3 lg:px-4 py-1.5 lg:py-2 rounded-lg text-sm font-medium bg-gradient-to-r from-green-600 to-green-500 text-white hover:from-green-700 hover:to-green-600 transition-all duration-200 shadow-sm hover:shadow hover:scale-105"
                        >
                            Login
                        </Link>
                    </template>
                </div>
            </header>

            <!-- Page Content -->
            <main
                class="flex-1 overflow-y-auto p-2 sm:p-2 lg:p-4 transition-all duration-300"
                :class="[isSidebarOpen && !isMobile ? 'lg:ml-0' : 'lg:ml-0']"
            >
                <div
                    class="max-w-full mx-auto bg-white rounded-xl lg:rounded-2xl shadow-sm border border-gray-200/50 p-2 sm:p-2 lg:py-4 lg:px-6 transition-all duration-300"
                >
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
nav::-webkit-scrollbar {
    width: 4px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

nav::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

nav::-webkit-scrollbar-thumb {
    background: rgba(34, 197, 94, 0.3);
    border-radius: 10px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 197, 94, 0.5);
}

aside {
    position: fixed !important;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 40;
    height: 100vh;
    overflow-y: auto;
}
</style>

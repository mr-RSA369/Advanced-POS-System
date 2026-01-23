<script setup>
import { ref, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import SalesOrders from "@/Components/SalesOrders.vue";
import CustomAlert from "../components/CustomAlert.vue";

const showSalesOrders = ref(false);
const selectedSaleDate = ref(null);

const openDayOrders = (date) => {
    selectedSaleDate.value = date;
    showSalesOrders.value = true;
};

/* ================= STATE ================= */
const sales = ref([]);
const loading = ref(false);

/* ================= FILTERS ================= */
const filters = ref({
    from_date: "",
    to_date: "",
    month: "",
    year: "",
});

/* ================= FETCH SALES ================= */
const fetchSales = async () => {
    try {
        loading.value = true;

        const params = {};

        /* ===== DATE RANGE ===== */
        if (filters.value.from_date && filters.value.to_date) {
            params.from_date = filters.value.from_date;
            params.to_date = filters.value.to_date;
        } else if (filters.value.month && filters.value.year) {
            /* ===== MONTH FILTER ===== */
            params.month = filters.value.month;
            params.year = filters.value.year;
        } else if (filters.value.year) {
            /* ===== YEAR FILTER ===== */
            params.year = filters.value.year;
        }

        const res = await axios.get("/api/sales", { params });
        sales.value = Array.isArray(res.data) ? res.data : [];
    } catch (err) {
        console.error(err);
        sales.value = [];
    } finally {
        loading.value = false;
    }
};

/* ================= RESET FILTERS ================= */
const resetFilters = () => {
    filters.value.from_date = "";
    filters.value.to_date = "";
    filters.value.month = "";
    filters.value.year = "";
    fetchSales();
};

/* ================= COMPUTED ================= */
const totalSalesAmount = computed(() =>
    sales.value.reduce((sum, s) => sum + Number(s.total_sale || 0), 0)
);

const totalOrdersCount = computed(() =>
    sales.value.reduce((sum, s) => sum + Number(s.order_count || 0), 0)
);

/* ================= EXPORT PDF ================= */

const exportPDF = () => {
    if (!sales.value.length) {
        CustomAlert("error", "No sales data to export");
        return;
    }

    const doc = new jsPDF("p", "mm", "a4");

    // Title
    doc.setFontSize(16);
    doc.text("Sales Report", 14, 15);

    // Prepare table data
    const tableBody = sales.value.map((s, i) => [
        i + 1,
        new Date(s.date).toDateString(),
        s.order_count ?? 0,
        `Rs. ${Number(s.total_sale).toLocaleString()}`,
    ]);

    // Table
    autoTable(doc, {
        startY: 25,
        head: [["#", "Date", "Orders", "Total Sale"]],
        body: tableBody,
        styles: { fontSize: 10 },
        headStyles: { fillColor: [22, 163, 74] },
    });

    // Total
    const finalY = doc.lastAutoTable?.finalY || 25;

    doc.setFontSize(12);
    doc.text(
        `Total Sale: Rs. ${Number(totalSalesAmount.value).toLocaleString()}`,
        14,
        finalY + 10
    );

    // Save
    doc.save("sales_report.pdf");
};

/* ================= INIT ================= */
onMounted(fetchSales);
</script>

<template>
    <div class="space-y-6">
        <!-- Custom Alerts Component -->
        <CustomAlert />
        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Sales Report
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Track your daily sales and order performance
                </p>
            </div>
        </div>

        <!-- FILTERS CARD -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-medium text-gray-700">Filter Data</h2>
                <div class="flex items-center gap-3">
                    <button
                        @click="resetFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Reset
                    </button>
                    <button
                        @click="fetchSales"
                        class="text-sm text-white bg-green-600 hover:bg-green-700 px-4 py-1.5 rounded-lg font-medium transition-colors flex items-center gap-2"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                            />
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- FILTER GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2"
                        >Date Range</label
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            type="date"
                            v-model="filters.from_date"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="From"
                        />
                        <input
                            type="date"
                            v-model="filters.to_date"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="To"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2"
                        >Month</label
                    >
                    <select
                        v-model="filters.month"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">All Months</option>
                        <option v-for="m in 12" :key="m" :value="m">
                            {{
                                new Date(2024, m - 1).toLocaleString(
                                    "default",
                                    { month: "long" }
                                )
                            }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2"
                        >Year</label
                    >
                    <select
                        v-model="filters.year"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">All Years</option>
                        <option
                            v-for="y in [2024, 2025, 2026]"
                            :key="y"
                            :value="y"
                        >
                            {{ y }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2"
                        >Export</label
                    >
                    <button
                        @click="exportPDF"
                        :disabled="!sales.length"
                        class="w-full text-sm text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </div>

            <!-- RESULTS INFO -->
            <div v-if="sales.length" class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        <span class="font-medium">{{ sales.length }}</span>
                        {{ sales.length === 1 ? "day" : "days" }} found
                    </span>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <div class="text-xs text-gray-500">
                                Total Orders
                            </div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ totalOrdersCount }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Total Sales</div>
                            <div class="text-lg font-semibold text-green-700">
                                Rs. {{ totalSalesAmount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LOADING STATE -->
        <div
            v-if="loading"
            class="bg-white rounded-xl border border-gray-200 p-8 text-center"
        >
            <div
                class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mb-3"
            ></div>
            <p class="text-gray-600">Loading sales data...</p>
        </div>

        <!-- SALES TABLE -->
        <div
            v-else-if="sales.length"
            class="bg-white rounded-xl border border-gray-200 overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="text-left py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                class="text-right py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                            >
                                Orders
                            </th>
                            <th
                                class="text-right py-4 px-6 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                            >
                                Total Sale
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="sale in sales"
                            :key="sale.date"
                            @click="openDayOrders(sale.date)"
                            class="cursor-pointer hover:bg-gray-50 transition-colors group"
                        >
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-4 h-4 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <div
                                            class="font-medium text-gray-900 group-hover:text-green-700"
                                        >
                                            {{
                                                new Date(
                                                    sale.date
                                                ).toLocaleDateString("en-US", {
                                                    weekday: "short",
                                                    day: "numeric",
                                                    month: "short",
                                                    year: "numeric",
                                                })
                                            }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Click to view day's orders
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div
                                    class="inline-flex items-center justify-center bg-blue-50 text-blue-700 text-sm font-medium px-3 py-1 rounded-full"
                                >
                                    {{ sale.order_count ?? 0 }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div
                                    class="text-lg font-semibold text-green-700"
                                >
                                    Rs.
                                    {{
                                        Number(sale.total_sale).toLocaleString()
                                    }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Average: Rs.
                                    {{
                                        sale.order_count
                                            ? (
                                                  sale.total_sale /
                                                  sale.order_count
                                              ).toFixed(2)
                                            : "0.00"
                                    }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="py-4 px-6 font-semibold text-gray-900">
                                Total
                            </td>
                            <td
                                class="py-4 px-6 text-right font-semibold text-gray-900"
                            >
                                <span
                                    class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"
                                >
                                    {{ totalOrdersCount }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="text-xl font-bold text-green-800">
                                    Rs.
                                    {{
                                        Number(
                                            totalSalesAmount
                                        ).toLocaleString()
                                    }}
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div
            v-else
            class="bg-white rounded-xl border border-gray-200 p-12 text-center"
        >
            <div class="max-w-md mx-auto">
                <div
                    class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
                >
                    <svg
                        class="w-8 h-8 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No sales data found
                </h3>
                <p class="text-gray-500 mb-6">
                    Try adjusting your filters or select a different date range
                </p>
                <button
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 text-sm font-medium text-green-700 hover:text-green-800 px-4 py-2 border border-green-200 rounded-lg hover:bg-green-50 transition-colors"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    Reset filters
                </button>
            </div>
        </div>
    </div>

    <SalesOrders
        v-if="showSalesOrders"
        :date="selectedSaleDate"
        @close="showSalesOrders = false"
    />
</template>

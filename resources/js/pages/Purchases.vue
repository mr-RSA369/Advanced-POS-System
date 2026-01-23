<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import CustomAlert from "../components/CustomAlert.vue";

/* ================= STATE ================= */
const purchases = ref([]);
const categories = ref([]);
const showAddModal = ref(false);
const showCategoryModal = ref(false);
const loading = ref(false);
const errors = ref({});
const businessDay = ref(null);

/* ================= FILTERS ================= */
const filters = ref({
    from_date: "",
    to_date: "",
    month: "",
    year: "",
});

/* ================= PURCHASE FORM ================= */
const form = ref({
    purchase_category_id: "",
    items: [{ title: "", price: "" }],
    bill_image: null,
});

/* ================= FETCH CURRENT BUSINESS DAY ================= */
const fetchBusinessDay = async () => {
    try {
        const { data } = await axios.get("/api/business-day/current");
        businessDay.value = data;
    } catch {
        businessDay.value = null;
    }
};

/* ================= CATEGORY FORM ================= */
const categoryForm = ref({ name: "" });
const categoryErrors = ref({});
const categoryLoading = ref(false);

/* ================= FETCH PURCHASES (FILTER AWARE) ================= */
const fetchPurchases = async () => {
    try {
        const params = {};

        // DATE RANGE (highest priority)
        if (filters.value.from_date && filters.value.to_date) {
            params.from_date = filters.value.from_date;
            params.to_date = filters.value.to_date;
        }
        // MONTH FILTER
        else if (filters.value.month && filters.value.year) {
            params.month = filters.value.month;
            params.year = filters.value.year;
        }
        // YEAR FILTER
        else if (filters.value.year) {
            params.year = filters.value.year;
        }

        const { data } = await axios.get("/api/purchases", { params });

        purchases.value = Array.isArray(data) ? data : [];
    } catch {
        purchases.value = [];
    }
};

/* ================= FETCH CATEGORIES ================= */
const fetchCategories = async () => {
    try {
        const { data } = await axios.get("/api/purchase_categories");
        categories.value = data;
    } catch {
        categories.value = [];
    }
};

/* ================= GROUP PURCHASES BY DATE ================= */
const groupedPurchases = computed(() => {
    if (!Array.isArray(purchases.value)) return {};

    return purchases.value.reduce((groups, purchase) => {
        const dateKey = purchase.created_at
            ? purchase.created_at.split("T")[0]
            : "unknown";

        if (!groups[dateKey]) groups[dateKey] = [];
        groups[dateKey].push(purchase);
        return groups;
    }, {});
});

/* ================= TOTAL PER DAY ================= */
const getDateTotal = (dayPurchases) =>
    dayPurchases.reduce((sum, p) => sum + Number(p.total_bill || 0), 0);

/* ================= FORM HELPERS ================= */
const addItemRow = () => {
    form.value.items.push({ title: "", price: "" });
};

const removeItemRow = (index) => {
    form.value.items.splice(index, 1);
};

const handleImage = (e) => {
    form.value.bill_image = e.target.files[0] || null;
};

/* ================= SUBMIT PURCHASE ================= */
const submitPurchase = async () => {
    errors.value = {};

    if (!businessDay.value) {
        CustomAlert("error", "No business day is open");
        return;
    }

    const fd = new FormData();
    fd.append("business_day_id", businessDay.value.id);
    fd.append("purchase_category_id", form.value.purchase_category_id);
    fd.append("items", JSON.stringify(form.value.items));

    if (form.value.bill_image) {
        fd.append("bill_image", form.value.bill_image);
    }

    try {
        loading.value = true;
        const { data } = await axios.post("/api/purchases", fd);
        CustomAlert("success", data.message);
        showAddModal.value = false;
        resetForm();
        fetchPurchases();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        } else {
            CustomAlert("error", e.response?.data?.message || "Failed to submit purchase");
        }
    } finally {
        loading.value = false;
    }
};

/* ================= RESET FORM ================= */
const resetForm = () => {
    form.value = {
        purchase_category_id: "",
        items: [{ title: "", price: "" }],
        bill_image: null,
    };
    errors.value = {};
};

/* ================= SUBMIT CATEGORY ================= */
const submitCategory = async () => {
    categoryErrors.value = {};

    if (!categoryForm.value.name) {
        categoryErrors.value.name = ["Category name is required"];
        return;
    }

    try {
        categoryLoading.value = true;
        await axios.post("/api/purchase_categories", categoryForm.value);
        showCategoryModal.value = false;
        categoryForm.value.name = "";
        fetchCategories();
    } finally {
        categoryLoading.value = false;
    }
};

const resetFilters = () => {
    filters.value.from_date = "";
    filters.value.to_date = "";
    filters.value.month = "";
    filters.value.year = "";
    fetchPurchases();
};

/* ================= EXPAND PURCHASE ================= */
const expandedPurchaseId = ref(null);
const togglePurchase = (id) => {
    expandedPurchaseId.value = expandedPurchaseId.value === id ? null : id;
};

/* ================= EXPORT PURCHASES PDF ================= */
const exportWithItems = ref(false);

const exportPurchasesPDF = () => {
    if (!purchases.value.length) {
        CustomAlert("error", "No purchases data to export");
        return;
    }

    const doc = new jsPDF("p", "mm", "a4");

    /* ===== TITLE ===== */
    doc.setFontSize(16);
    doc.text("Purchases Report", 14, 15);

    /* ===== FILTER INFO ===== */
    doc.setFontSize(10);
    const filtersInfo = [];

    if (filters.value.business_date) {
        filtersInfo.push(`Date: ${filters.value.business_date}`);
    }
    if (filters.value.month && filters.value.year) {
        filtersInfo.push(
            `Month: ${filters.value.month}, Year: ${filters.value.year}`
        );
    }
    if (!filtersInfo.length) {
        filtersInfo.push("All Purchases");
    }

    doc.text(filtersInfo.join(" | "), 14, 22);

    let currentY = 28;
    let grandTotal = 0;

    /* ======================================================
       SUMMARY (WITHOUT ITEMS)
    ====================================================== */
    if (!exportWithItems.value) {
        const body = purchases.value.map((p, i) => {
            grandTotal += Number(p.total_bill || 0);

            return [
                i + 1,
                p.business_day?.business_date ??
                    new Date(p.created_at).toISOString().split("T")[0],
                p.category?.name ?? "-",
                p.items?.length ?? 0,
                `Rs. ${Number(p.total_bill).toLocaleString()}`,
            ];
        });

        autoTable(doc, {
            startY: currentY,
            head: [["#", "Date", "Category", "Items", "Total"]],
            body,
            styles: { fontSize: 10 },
            headStyles: {
                fillColor: [22, 163, 74],
                textColor: 255,
            },
            columnStyles: {
                0: { cellWidth: 10, halign: "center" },
                3: { halign: "center" },
                4: { halign: "right" },
            },
        });

        currentY = doc.lastAutoTable.finalY + 8;
    } else {
        /* ======================================================
       DETAILED (WITH ITEMS)
    ====================================================== */
        purchases.value.forEach((p, index) => {
            const purchaseDate =
                p.business_day?.business_date ??
                new Date(p.created_at).toISOString().split("T")[0];

            grandTotal += Number(p.total_bill || 0);

            // Purchase Header
            doc.setFontSize(11);
            doc.text(
                `${index + 1}. ${purchaseDate} | ${p.category?.name ?? "-"}`,
                14,
                currentY
            );

            currentY += 4;

            // Items table
            const itemsBody = p.items.map((item, i) => [
                i + 1,
                item.title,
                `Rs. ${Number(item.price).toLocaleString()}`,
            ]);

            autoTable(doc, {
                startY: currentY,
                head: [["#", "Item", "Price"]],
                body: itemsBody,
                styles: { fontSize: 9 },
                columnStyles: {
                    0: { cellWidth: 10, halign: "center" },
                    2: { halign: "right" },
                },
            });

            currentY = doc.lastAutoTable.finalY + 4;

            // Purchase total
            doc.setFontSize(10);
            doc.text(
                `Purchase Total: Rs. ${Number(p.total_bill).toLocaleString()}`,
                150,
                currentY
            );

            currentY += 8;

            // Page break protection
            if (currentY > 260) {
                doc.addPage();
                currentY = 20;
            }
        });
    }

    /* ===== GRAND TOTAL ===== */
    doc.setFontSize(12);
    doc.text(
        `Grand Total: Rs. ${grandTotal.toLocaleString()}`,
        14,
        currentY + 5
    );

    /* ===== FOOTER ===== */
    doc.setFontSize(9);
    doc.text(`Generated on ${new Date().toLocaleString()}`, 14, 290);

    doc.save(
        exportWithItems.value
            ? "purchases_detailed_report.pdf"
            : "purchases_summary_report.pdf"
    );
};

/* ================= LIFECYCLE ================= */
onMounted(() => {
    fetchBusinessDay();
    fetchCategories();
    fetchPurchases();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Custom Alerts Component -->
        <CustomAlert />
        <!-- Header Section -->
        <div class="bg-white border-b shadow-sm sticky top-0 z-10">
            <div class="container mx-auto px-4 py-4">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Purchases Management
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Track all business expenses and purchases
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="showCategoryModal = true"
                            class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Add Category
                        </button>

                        <button
                            @click="showAddModal = true"
                            :disabled="!businessDay"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-medium rounded-lg hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Add Purchase
                        </button>
                    </div>
                </div>

                <!-- Business Day Status -->
                <div v-if="businessDay" class="mt-3 text-sm">
                    <span class="text-gray-500">Active Business Day:</span>
                    <span class="font-semibold text-green-700 ml-2">
                        {{ new Date(businessDay.business_date).toDateString() }}
                    </span>
                </div>
                <div v-else class="mt-3 text-sm text-red-600">
                    No active business day. Please open a business day first.
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Filter Section -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6"
            >
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end"
                >
                    <!-- Date Range -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >From Date</label
                        >
                        <input
                            type="date"
                            v-model="filters.from_date"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >To Date</label
                        >
                        <input
                            type="date"
                            v-model="filters.to_date"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Month Filter -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >Month</label
                        >
                        <select
                            v-model="filters.month"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">All Months</option>
                            <option v-for="m in 12" :key="m" :value="m">
                                {{
                                    new Date(2000, m - 1).toLocaleString(
                                        "default",
                                        { month: "long" }
                                    )
                                }}
                            </option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >Year</label
                        >
                        <select
                            v-model="filters.year"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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

                    <!-- Filter Actions -->
                    <div class="flex gap-2">
                        <button
                            @click="fetchPurchases"
                            class="flex-1 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium py-2 px-4 rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            Apply
                        </button>

                        <button
                            @click="resetFilters"
                            class="border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-4">
                        <label
                            class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                v-model="exportWithItems"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            />
                            Include item details in export
                        </label>

                        <div class="text-sm text-gray-600">
                            {{ purchases.length }} record{{
                                purchases.length !== 1 ? "s" : ""
                            }}
                            found
                        </div>
                    </div>

                    <button
                        @click="exportPurchasesPDF"
                        :disabled="purchases.length === 0"
                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"
                            />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </div>

            <!-- Purchases List -->
            <div
                v-if="Object.keys(groupedPurchases).length > 0"
                class="space-y-6"
            >
                <div
                    v-for="(dayPurchases, date) in groupedPurchases"
                    :key="date"
                    class="space-y-4"
                >
                    <!-- Day Header -->
                    <div
                        class="bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-xl p-4"
                    >
                        <div
                            class="flex flex-col md:flex-row md:items-center justify-between"
                        >
                            <div>
                                <div class="font-bold text-lg">
                                    {{
                                        date === "unknown"
                                            ? "Date not available"
                                            : new Date(
                                                  date + "T00:00:00"
                                              ).toLocaleDateString("en-US", {
                                                  weekday: "long",
                                                  year: "numeric",
                                                  month: "long",
                                                  day: "numeric",
                                              })
                                    }}
                                </div>
                            </div>
                            <div
                                class="text-2xl font-bold text-green-300 mt-2 md:mt-0"
                            >
                                Rs.
                                {{
                                    getDateTotal(dayPurchases).toLocaleString()
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Cards -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        style="align-items: start"
                    >
                        <div
                            v-for="purchase in dayPurchases"
                            :key="purchase.id"
                            class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
                        >
                            <!-- Purchase Header -->
                            <div
                                class="p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors"
                                @click="togglePurchase(purchase.id)"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div
                                            class="font-bold text-gray-900 text-sm mb-1"
                                        >
                                            {{ purchase.category?.name || "—" }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ purchase.items?.length || 0 }}
                                            item{{
                                                purchase.items?.length !== 1
                                                    ? "s"
                                                    : ""
                                            }}
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div
                                            class="font-bold text-green-700 text-lg"
                                        >
                                            Rs. {{ purchase.total_bill }}
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-400 transition-transform"
                                            :class="{
                                                'rotate-180':
                                                    expandedPurchaseId ===
                                                    purchase.id,
                                            }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Expanded Details -->
                            <div
                                v-if="expandedPurchaseId === purchase.id"
                                class="max-h-60 overflow-y-auto p-4 bg-gray-50 border-t border-gray-100 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 scrollbar-thumb-rounded-full"
                            >
                                <!-- Items List -->
                                <div class="space-y-2 mb-4">
                                    <div
                                        class="text-xs font-medium text-gray-500 mb-2"
                                    >
                                        ITEMS
                                    </div>
                                    <div
                                        v-for="(item, i) in purchase.items"
                                        :key="i"
                                        class="flex justify-between items-center bg-white rounded-lg p-3 border border-gray-100"
                                    >
                                        <div
                                            class="font-medium text-gray-900 text-sm"
                                        >
                                            {{ item.title }}
                                        </div>
                                        <div class="font-bold text-green-700">
                                            Rs. {{ item.price }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Bill Image -->
                                <div v-if="purchase.bill_image" class="mt-4">
                                    <a
                                        :href="`/storage/${purchase.bill_image}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
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
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        View Bill
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="bg-white border border-gray-200 rounded-xl p-8 text-center"
            >
                <svg
                    class="w-16 h-16 text-gray-300 mx-auto mb-4"
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No purchases found
                </h3>
                <p class="text-gray-500 mb-4">
                    No purchases match your filters.
                </p>
                <button
                    @click="resetFilters"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors"
                >
                    Clear filters
                </button>
            </div>
        </div>

        <!-- Add Purchase Modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showAddModal = false"
        >
            <div
                class="bg-white w-full max-w-lg rounded-xl shadow-xl overflow-hidden"
                @click.stop
            >
                <!-- Modal Header -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 p-4 text-white"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-bold">Add New Purchase</h2>
                            <p class="text-sm text-green-100 opacity-90">
                                Record business expense
                            </p>
                        </div>
                        <button
                            @click="showAddModal = false"
                            class="text-white hover:text-gray-200 transition-colors"
                        >
                            <svg
                                class="w-5 h-5"
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
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Category Selection -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Category *</label
                        >
                        <select
                            v-model="form.purchase_category_id"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            :class="{
                                'border-red-300': errors.purchase_category_id,
                            }"
                        >
                            <option value="">Select category</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <p
                            v-if="errors.purchase_category_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.purchase_category_id[0] }}
                        </p>
                    </div>

                    <!-- Items List -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Items *</label
                            >
                            <button
                                @click="addItemRow"
                                class="text-sm text-green-600 font-medium hover:text-green-700 flex items-center"
                            >
                                <svg
                                    class="w-4 h-4 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="flex gap-3 items-start"
                            >
                                <div class="flex-1">
                                    <input
                                        v-model="item.title"
                                        placeholder="Item name"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        :class="{
                                            'border-red-300':
                                                errors[`items.${index}.title`],
                                        }"
                                    />
                                    <p
                                        v-if="errors[`items.${index}.title`]"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errors[`items.${index}.title`][0] }}
                                    </p>
                                </div>

                                <div class="w-32">
                                    <input
                                        v-model="item.price"
                                        type="number"
                                        placeholder="Price"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        :class="{
                                            'border-red-300':
                                                errors[`items.${index}.price`],
                                        }"
                                    />
                                    <p
                                        v-if="errors[`items.${index}.price`]"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errors[`items.${index}.price`][0] }}
                                    </p>
                                </div>

                                <button
                                    v-if="form.items.length > 1"
                                    @click="removeItemRow(index)"
                                    class="mt-2 text-red-500 hover:text-red-700 transition-colors"
                                >
                                    <svg
                                        class="w-5 h-5"
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
                            </div>
                        </div>
                    </div>

                    <!-- Bill Image Upload -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Bill Image (Optional)</label
                        >
                        <input
                            type="file"
                            @change="handleImage"
                            accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Upload a photo of the purchase bill
                        </p>
                        <p
                            v-if="errors.bill_image"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.bill_image[0] }}
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showAddModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitPurchase"
                            :disabled="loading"
                            class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium rounded-lg hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                        >
                            <svg
                                v-if="loading"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ loading ? "Saving..." : "Save Purchase" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div
            v-if="showCategoryModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showCategoryModal = false"
        >
            <div
                class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden"
                @click.stop
            >
                <!-- Modal Header -->
                <div
                    class="bg-gradient-to-r from-gray-700 to-gray-800 p-4 text-white"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-bold">Add New Category</h2>
                            <p class="text-sm text-gray-300 opacity-90">
                                Create purchase category
                            </p>
                        </div>
                        <button
                            @click="showCategoryModal = false"
                            class="text-white hover:text-gray-200 transition-colors"
                        >
                            <svg
                                class="w-5 h-5"
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
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Category Name *</label
                        >
                        <input
                            v-model="categoryForm.name"
                            placeholder="Enter category name"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            :class="{ 'border-red-300': categoryErrors.name }"
                        />
                        <p
                            v-if="categoryErrors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ categoryErrors.name[0] }}
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showCategoryModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitCategory"
                            :disabled="categoryLoading"
                            class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium rounded-lg hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                        >
                            <svg
                                v-if="categoryLoading"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{
                                categoryLoading ? "Saving..." : "Save Category"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for modal */
.max-h-\[70vh\]::-webkit-scrollbar {
    width: 6px;
}

.max-h-\[70vh\]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.max-h-\[70vh\]::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.max-h-\[70vh\]::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
}

/* Button hover effects */
button:not(:disabled):hover {
    transform: translateY(-1px);
}

/* Modal backdrop */
.fixed {
    backdrop-filter: blur(4px);
}

/* File input styling */
input[type="file"]::-webkit-file-upload-button {
    cursor: pointer;
}

/* For Webkit browsers (Chrome, Safari, Edge) */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #f3f4f6; /* gray-100 */
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db; /* gray-300 */
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #9ca3af; /* gray-400 */
}

/* For Firefox */
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6; /* thumb track */
}
</style>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import CustomAlert from "../components/CustomAlert.vue";
import OrderConfirmation from "../components/OrderConfirmation.vue";

const selectedOrderIdForAction = ref("");

const serviceCharges = ref();
const discount = ref();
const deliveryCharges = ref();

const page = usePage();
const query = page.props.ziggy?.query || {};

const serviceChargesAmount = computed(
    () => (total.value * Number(serviceCharges.value || 0)) / 100
);

const discountAmount = computed(
    () => (total.value * Number(discount.value || 0)) / 100
);

/* ================= STATE ================= */
const activeStatus = ref("created");
const orders = ref([]);
const loading = ref(false);
const cashError = ref("");

/* ================= FILTERS ================= */
const filters = ref({
    from: "",
    to: "",
    order_type: "",
    item: "",
});

/* ================= VIEW MODAL ================= */
const showView = ref(false);
const selectedOrder = ref(null);

/* ================= CONFIRMATION MODALS ================= */
const showCloseConfirmation = ref(false);
const showCancelConfirmation = ref(false);
const processingAction = ref(false);
const actionOrderId = ref(null);
const orderDisplayId = ref(null);

/* ================= FETCH ORDERS ================= */
const fetchOrders = async () => {
    try {
        loading.value = true;

        const params = {
            status: activeStatus.value,
        };

        /* DATE RANGE */
        if (filters.value.from && filters.value.to) {
            params.from_date = filters.value.from;
            params.to_date = filters.value.to;
        }

        /* ORDER TYPE */
        if (filters.value.order_type) {
            params.order_type = filters.value.order_type;
        }

        /* ITEM SEARCH */
        if (filters.value.item && filters.value.item.trim() !== "") {
            params.item = filters.value.item.trim();
        }

        const res = await axios.get("/api/orders", { params });
        orders.value = res.data.data || [];
    } catch (e) {
        console.error(e);
        orders.value = [];
        window.showCustomAlert('error', 'Failed to fetch orders');
    } finally {
        loading.value = false;
    }
};

/* ================= INIT ================= */
onMounted(() => {
    if (query.from && query.to) {
        filters.value.from = query.from;
        filters.value.to = query.to;
    }

    if (query.status) {
        activeStatus.value = query.status;
    }

    fetchOrders();
});

watch(activeStatus, () => {
    fetchOrders();
});

/* ================= RESET FILTERS ================= */
const resetFilters = () => {
    filters.value.from = "";
    filters.value.to = "";
    filters.value.order_type = "";
    filters.value.item = "";
    fetchOrders();
};

/* ================= SHOW CONFIRMATION MODALS ================= */
const showCloseOrderConfirmation = (orderId, order_id) => {
    actionOrderId.value = orderId;
    orderDisplayId.value = order_id;
    showCloseConfirmation.value = true;
};

const showCancelOrderConfirmation = (orderId, order_id) => {
    actionOrderId.value = orderId;
    orderDisplayId.value = order_id;
    showCancelConfirmation.value = true;
};

/* ================= UPDATE STATUS ================= */
const updateStatus = async (orderId, status, order_id) => {
    if (status === "closed") {
        showCloseOrderConfirmation(orderId, order_id);
    } else if (status === "cancelled") {
        showCancelOrderConfirmation(orderId, order_id);
    }
};

/* ================= HANDLE CONFIRMATION ================= */
const handleCloseOrder = async () => {
    try {
        processingAction.value = true;

        const response = await axios.patch(`/api/orders/${actionOrderId.value}`, {
            status: "closed"
        });

        // Show success message from API response or default
        const message = response.data?.message || "Order closed successfully!";
        window.showCustomAlert('success', message);

        // Close modal and refresh orders
        showCloseConfirmation.value = false;
        fetchOrders();

    } catch (error) {
        console.error("Failed to close order:", error);
        // Show error message from API response or default
        const errorMessage = error.response?.data?.message || "Failed to close order";
        window.showCustomAlert('error', errorMessage);
    } finally {
        processingAction.value = false;
        actionOrderId.value = null;
    }
};

const handleCancelOrder = async () => {
    try {
        processingAction.value = true;

        const response = await axios.patch(`/api/orders/${actionOrderId.value}`, {
            status: "cancelled"
        });

        // Show success message from API response or default
        const message = response.data?.message || "Order cancelled successfully!";
        window.showCustomAlert('success', message);

        // Close modal and refresh orders
        showCancelConfirmation.value = false;
        fetchOrders();

    } catch (error) {
        console.error("Failed to cancel order:", error);
        // Show error message from API response or default
        const errorMessage = error.response?.data?.message || "Failed to cancel order";
        window.showCustomAlert('error', errorMessage);
    } finally {
        processingAction.value = false;
        actionOrderId.value = null;
    }
};

/* ================= VIEW ORDER ================= */
const viewOrder = (order) => {
    resetBillInputs();
    selectedOrder.value = order;
    showView.value = true;
};

/* ================= CASH SYSTEM ================= */
const cashReceived = ref("");
const change = computed(() => {
    const cash = Number(cashReceived.value || 0);
    const total = Number(netBill.value || 0);

    return cash >= total ? cash - total : 0;
});

const printBill = async () => {
    try {
        /* ===============================
           1️⃣ STORE BILL DATA
        =============================== */
        const response = await axios.post(`/api/orders/${selectedOrder.value.id}/bill`, {
            discount: Number(discountAmount.value || 0),
            delivery_charges: Number(deliveryCharges.value || 0),
            service_charges: Number(serviceChargesAmount.value || 0),
            net_bill: Number(netBill.value || 0),
            cash: cashReceived.value,
            change: change.value,
        });

        // Show success message from API response
        const message = response.data?.message || "Bill data stored successfully!";
        window.showCustomAlert('success', message);

    } catch (e) {
        console.error(e);
        const errorMessage = e.response?.data?.message || "Failed to store bill data";
        window.showCustomAlert('error', errorMessage);
    }

    // Print bill
    try {
        await axios.post("/api/print/test");
        window.showCustomAlert('success', "Bill printed successfully!");
    } catch (e) {
        console.error(e);
        window.showCustomAlert('error', "Failed to print bill");
    }
};

const resetBillInputs = () => {
    discount.value = null;
    serviceCharges.value = null;
    deliveryCharges.value = null;
    cashReceived.value = "";
    change.value = 0;
    cashError.value = "";
};

const total = computed(() => {
    return Number(selectedOrder.value?.total_bill || 0);
});

const netBill = computed(
    () =>
        total.value +
        serviceChargesAmount.value +
        Number(deliveryCharges.value || 0) -
        discountAmount.value
);
</script>


<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Custom Alerts Component -->
        <CustomAlert />

        <!-- Close Order Confirmation Modal -->
        <OrderConfirmation
            v-if="showCloseConfirmation"
            :show="showCloseConfirmation"
            type="close"
            title="Close Order"
            :message="`Are you sure you want to close this order? This action cannot be undone.`"
            :order-id="actionOrderId"
            :order-display-id= "orderDisplayId"
            confirm-text="Yes, Close Order"
            :loading="processingAction"
            @close="showCloseConfirmation = false"
            @cancel="showCloseConfirmation = false"
            @confirm="handleCloseOrder"
        />

        <!-- Cancel Order Confirmation Modal -->
        <OrderConfirmation
            v-if="showCancelConfirmation"
            :show="showCancelConfirmation"
            type="cancel"
            title="Cancel Order"
            :message="`Are you sure you want to cancel this order? This action cannot be undone.`"
            :order-id="actionOrderId"
            :order-display-id= "orderDisplayId"
            confirm-text="Yes, Cancel Order"
            :loading="processingAction"
            @close="showCancelConfirmation = false"
            @cancel="showCancelConfirmation = false"
            @confirm="handleCancelOrder"
        />

        <!-- Header Section -->
        <div class="bg-white border-b shadow-sm sticky top-0 z-10">
            <div class="container mx-auto px-4 py-4">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Orders Management
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            View and manage all customer orders
                        </p>
                    </div>

                    <div
                        class="text-sm text-gray-700 font-medium px-3 py-1 bg-gray-100 rounded-full"
                    >
                        {{ orders.length }} order{{
                            orders.length !== 1 ? "s" : ""
                        }}
                        found
                    </div>
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
                            v-model="filters.from"
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
                            v-model="filters.to"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Order Type Filter -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >Order Type</label
                        >
                        <select
                            v-model="filters.order_type"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">All Types</option>
                            <option value="Takeaway">Takeaway</option>
                            <option value="Dine-in">Dine In</option>
                            <option value="Delivery">Delivery</option>
                        </select>
                    </div>

                    <!-- Item Search -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >Search Item</label
                        >
                        <input
                            type="text"
                            v-model="filters.item"
                            placeholder="Search items..."
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                        />
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex gap-2">
                        <button
                            @click="fetchOrders"
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

            <!-- Status Tabs -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="status in ['created', 'closed', 'cancelled']"
                        :key="status"
                        @click="activeStatus = status"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 flex items-center"
                        :class="
                            activeStatus === status
                                ? status === 'cancelled'
                                    ? 'bg-red-600 text-white shadow-md'
                                    : status === 'closed'
                                    ? 'bg-gray-700 text-white shadow-md'
                                    : 'bg-green-600 text-white shadow-md'
                                : 'bg-white text-gray-600 border border-gray-200 hover:border-green-400 hover:text-green-700'
                        "
                    >
                        <!-- Status Icons -->
                        <svg
                            v-if="status === 'created'"
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <svg
                            v-if="status === 'closed'"
                            class="w-4 h-4 mr-2"
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
                        <svg
                            v-if="status === 'cancelled'"
                            class="w-4 h-4 mr-2"
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
                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"
                ></div>
                <p class="mt-2 text-gray-500">Loading orders...</p>
            </div>

            <!-- Orders Grid -->
            <div
                v-else-if="orders.length"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5"
            >
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1"
                >
                    <!-- Order Header -->
                    <div class="px-5 py-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="space-y-1">
                                <div
                                    class="font-semibold text-gray-900 text-sm"
                                >
                                    {{ order.order_id }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ order.business_day?.business_date }}
                                </div>
                                <div
                                    v-if="order.table_no"
                                    class="text-xs text-gray-900"
                                >
                                    Table no: {{ order.table_no }}
                                </div>
                            </div>
                            <div
                                class="text-xs px-2.5 py-1 rounded-full font-medium border"
                                :class="{
                                    'border-green-200 bg-green-50 text-green-700':
                                        order.order_type === 'Takeaway',
                                    'border-blue-200 bg-blue-50 text-blue-700':
                                        order.order_type === 'Dine-in',
                                    'border-purple-200 bg-purple-50 text-purple-700':
                                        order.order_type === 'Delivery',
                                }"
                            >
                                {{ order.order_type }}
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div
                            class="flex items-center text-sm text-gray-600 mb-6"
                        >
                            <svg
                                class="w-4 h-4 mr-2 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                />
                            </svg>
                            {{ order.customer_phone ?? "No phone" }}
                        </div>

                        <!-- Order Summary -->
                        <div class="flex justify-between items-center mb-5">
                            <div class="text-sm text-gray-500">
                                {{ order.items?.length || 0 }} items
                            </div>
                            <div class="font-bold text-gray-900 text-xl">
                                Rs. {{ Math.floor(order.net_bill || 0) }}
                            </div>
                        </div>

                        <!-- View Details Button - Top Section -->
                        <button
                            @click="viewOrder(order)"
                            class="w-full text-right text-green-700 text-sm font-medium hover:text-green-900 transition-all duration-200"
                        >
                            View Details
                        </button>
                    </div>

                    <!-- Status Actions - Separated Section -->
                    <div
                        v-if="activeStatus !== 'cancelled'"
                        class="border-t border-gray-300 bg-gray-50 p-5"
                    >
                        <div class="space-y-3">
                            <button
                                v-if="activeStatus === 'created'"
                                @click="updateStatus(order.id, 'closed', order.order_id)"
                                class="w-full bg-gray-600 text-white text-sm font-medium py-2.5 px-4 rounded-lg hover:bg-gray-700 transition-all duration-200"
                            >
                                Close Order
                            </button>

                            <button
                                v-if="
                                    activeStatus === 'created' ||
                                    activeStatus === 'closed'
                                "
                                @click="updateStatus(order.id, 'cancelled', order.order_id)"
                                class="w-full border border-red-300 bg-white text-red-600 text-sm font-medium py-2.5 px-4 rounded-lg hover:bg-red-50 transition-all duration-200"
                            >
                                Cancel Order
                            </button>
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
                    No orders found
                </h3>
                <p class="text-gray-500 mb-4">
                    No {{ activeStatus }} orders match your filters.
                </p>
                <button
                    @click="resetFilters"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors"
                >
                    Clear filters
                </button>
            </div>
        </div>

        <!-- View Order Modal -->
        <div
            v-if="showView"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showView = false"
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
                            <h2 class="text-lg font-bold">Order Details</h2>
                            <p class="text-sm text-green-100 opacity-90">
                                {{ selectedOrder.order_id }}
                            </p>
                        </div>
                        <button
                            @click="
                                showView = false;
                                resetBillInputs();
                            "
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

                <!-- Order Information -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Order Summary -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div
                                    class="text-gray-500 text-xs font-medium mb-1"
                                >
                                    Order Type
                                </div>
                                <div class="font-medium text-gray-900">
                                    {{ selectedOrder.order_type }}
                                </div>
                            </div>
                            <div v-if="selectedOrder.table_no">
                                <div
                                    class="text-gray-500 text-xs font-medium mb-1"
                                >
                                    Table Number
                                </div>
                                <div class="font-medium text-gray-900">
                                    {{ selectedOrder.table_no }}
                                </div>
                            </div>

                            <div>
                                <div
                                    class="text-gray-500 text-xs font-medium mb-1"
                                >
                                    Date
                                </div>
                                <div class="font-medium text-gray-900">
                                    {{
                                        selectedOrder.business_day
                                            ?.business_date
                                    }}
                                </div>
                            </div>
                            <div>
                                <div
                                    class="text-gray-500 text-xs font-medium mb-1"
                                >
                                    Status
                                </div>
                                <div
                                    class="font-medium"
                                    :class="{
                                        'text-green-600':
                                            selectedOrder.status === 'created',
                                        'text-gray-600':
                                            selectedOrder.status === 'closed',
                                        'text-red-600':
                                            selectedOrder.status ===
                                            'cancelled',
                                    }"
                                >
                                    {{ selectedOrder.status }}
                                </div>
                            </div>
                            <div v-if="selectedOrder.order_type === 'Dine-in'">
                                <div
                                    class="text-gray-500 text-xs font-medium mb-1"
                                >
                                    Table No
                                </div>
                                <div class="font-medium text-gray-900">
                                    {{ selectedOrder.table_no || "N/A" }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900 mb-3">
                            Order Items
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="(item, i) in selectedOrder.items"
                                :key="i"
                                class="flex justify-between items-center bg-white border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition-colors"
                            >
                                <div>
                                    <div
                                        class="font-medium text-gray-900 text-sm"
                                    >
                                        {{ item.item_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ item.type }} • {{ item.qty }} × Rs.
                                        {{ item.unit_price }}
                                    </div>
                                </div>
                                <div class="font-bold text-green-700">
                                    Rs. {{ item.line_total }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bill Calculation -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-3">
                                Bill Calculation
                            </h3>

                            <div class="space-y-3">
                                <!-- Discount -->
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 mb-1"
                                        >Discount (%)</label
                                    >
                                    <input
                                        v-model="discount"
                                        type="number"
                                        placeholder="Enter discount percentage"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>

                                <!-- Service Charges -->
                                <div
                                    v-if="
                                        selectedOrder.order_type === 'Dine-in'
                                    "
                                >
                                    <label
                                        class="block text-xs font-medium text-gray-500 mb-1"
                                        >Service Charges (%)</label
                                    >
                                    <input
                                        v-model="serviceCharges"
                                        type="number"
                                        placeholder="Enter service charges"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>

                                <!-- Delivery Charges -->
                                <div
                                    v-if="
                                        selectedOrder.order_type === 'Delivery'
                                    "
                                >
                                    <label
                                        class="block text-xs font-medium text-gray-500 mb-1"
                                        >Delivery Charges (PKR)</label
                                    >
                                    <input
                                        v-model="deliveryCharges"
                                        type="number"
                                        placeholder="Enter delivery charges"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>

                                <!-- Cash Received -->
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 mb-1"
                                        >Cash Received (Optional)</label
                                    >
                                    <input
                                        v-model="cashReceived"
                                        type="number"
                                        placeholder="Enter cash received"
                                        class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Leave empty if not applicable
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Bill Summary -->
                        <div
                            class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4"
                        >
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium"
                                        >Rs.
                                        {{ selectedOrder.total_bill }}</span
                                    >
                                </div>

                                <div
                                    v-if="discountAmount > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">Discount</span>
                                    <span class="font-medium text-red-600"
                                        >- Rs. {{ discountAmount }}</span
                                    >
                                </div>

                                <div
                                    v-if="serviceChargesAmount > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600"
                                        >Service Charges</span
                                    >
                                    <span class="font-medium"
                                        >+ Rs. {{ serviceChargesAmount }}</span
                                    >
                                </div>

                                <div
                                    v-if="deliveryCharges > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600"
                                        >Delivery Charges</span
                                    >
                                    <span class="font-medium"
                                        >+ Rs. {{ deliveryCharges }}</span
                                    >
                                </div>

                                <div
                                    class="flex justify-between text-lg font-bold pt-2 border-t border-gray-300"
                                >
                                    <span class="text-gray-900">Net Total</span>
                                    <span class="text-green-700"
                                        >Rs.
                                        {{ Math.floor(netBill || 0) }}</span
                                    >
                                </div>

                                <div
                                    v-if="cashReceived > 0"
                                    class="flex justify-between text-sm pt-2"
                                >
                                    <span class="text-gray-600"
                                        >Cash Received</span
                                    >
                                    <span class="font-medium"
                                        >Rs. {{ cashReceived }}</span
                                    >
                                </div>

                                <div
                                    v-if="cashReceived > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">Change</span>
                                    <span
                                        class="font-bold"
                                        :class="
                                            change > 0
                                                ? 'text-green-700'
                                                : 'text-gray-900'
                                        "
                                    >
                                        Rs. {{ Math.floor(change) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-end gap-3">
                        <button
                            @click="printBill"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center"
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
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                />
                            </svg>
                            Print Bill
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
</style>

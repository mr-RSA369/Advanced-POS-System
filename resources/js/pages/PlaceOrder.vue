<!-- PlaceOrder.vue (updated) -->
<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import KitchenBillConfirmation from "@/components/KitchenBillConfirmation.vue";
import CustomAlert from "../components/CustomAlert.vue";

/* ================= STATE ================= */
const categories = ref([]);
const expandedCategoryId = ref(null);
const cart = ref([]);
const loading = ref(false);

/* ===== BUSINESS DAY ===== */
const businessDay = ref(null);
const businessDayError = ref("");

/* ===== ORDER STATE ===== */
const orderType = ref("Takeaway");
const orderId = ref("");
const lastOrderNumber = ref(0);
const customerPhone = ref("");
const tableNum = ref("");

const submitting = ref(false);
const orderLocked = ref(false);

/* ===== PRINT CONFIRMATION STATE ===== */
const showPrintConfirmation = ref(false);
const printing = ref(false);

/* ================= CONSTANTS ================= */
const ORDER_PREFIX = {
    Takeaway: "TK",
    "Dine-in": "DN",
    Delivery: "DL",
};

/* ================= FETCH CURRENT BUSINESS DAY ================= */
const fetchBusinessDay = async () => {
    try {
        const res = await axios.get("/api/business-day/current");
        businessDay.value = res.data;
    } catch (err) {
        businessDayError.value =
            "Business day is not open. Please open day first.";
        showCustomAlert(
            "error",
            "Business day is not open. Please open day first."
        );
    }
};

/* ================= FETCH MENU ================= */
const fetchMenu = async () => {
    try {
        loading.value = true;
        const res = await axios.get("/api/menu");
        categories.value = res.data;
    } catch (error) {
        console.error("Failed to load menu", error);
        window.showCustomAlert(
            "error",
            "Failed to load menu. Please try again."
        );
    } finally {
        loading.value = false;
    }
};

/* ================= FETCH LAST ORDER NUMBER ================= */
const fetchLastOrderNumber = async () => {
    try {
        const res = await axios.get("/api/order_id");
        lastOrderNumber.value = Number(res.data) || 0;
    } catch {
        lastOrderNumber.value = 0;
        window.showCustomAlert("error", "Failed to fetch last order number");
    }
};

/* ================= ORDER ID GENERATOR ================= */
const generateOrderId = () => {
    const prefix = ORDER_PREFIX[orderType.value];
    const nextNumber = lastOrderNumber.value + 1;
    orderId.value = `${prefix}-${String(nextNumber).padStart(7, "0")}`;
};

/* ================= HELPERS ================= */
const parsePrices = (price) => JSON.parse(price);

/* ================= CART FUNCTIONS ================= */
const getCartItem = (itemId, type) =>
    cart.value.find((i) => i.item_id === itemId && i.type === type);

const getQty = (itemId, type) => getCartItem(itemId, type)?.qty || 0;

const increaseQty = (item, typeObj) => {
    const existing = getCartItem(item.id, typeObj.type);

    if (existing) {
        existing.qty++;
        existing.line_total = existing.qty * existing.unit_price;
    } else {
        cart.value.push({
            item_id: item.id,
            item_name: item.name,
            type: typeObj.type,
            unit_price: typeObj.price,
            qty: 1,
            line_total: typeObj.price,
        });
    }
};

const decreaseQty = (item, typeObj) => {
    const existing = getCartItem(item.id, typeObj.type);
    if (!existing) return;

    existing.qty--;
    existing.line_total = existing.qty * existing.unit_price;

    if (existing.qty === 0) {
        cart.value = cart.value.filter((i) => i !== existing);
    }
};

const removeFromBill = (billItem) => {
    cart.value = cart.value.filter((i) => i !== billItem);
};

/* ================= TOTALS ================= */
const total = computed(() =>
    cart.value.reduce((sum, i) => sum + i.line_total, 0)
);

/* ================= PRINT KITCHEN BILL ================= */
const printKitchenBill = async (orderPayload) => {
    try {
        printing.value = true;
        await axios.post("/api/print/kitchen", orderPayload);

        window.showCustomAlert("success", "Kitchen bill printed successfully!");
        return true;
    } catch (error) {
        console.error("Failed to print kitchen bill:", error);
        window.showCustomAlert(
            "error",
            error.response?.data?.message || "Failed to print kitchen bill"
        );
        return false;
    } finally {
        printing.value = false;
    }
};

/* ================= CONFIRM ORDER ================= */
const confirmOrder = async () => {
    if (!businessDay.value) {
        window.showCustomAlert("error", "Business day is not open");
        return;
    }

    if (cart.value.length === 0) {
        window.showCustomAlert("error", "No items in order");
        return;
    }
    showPrintConfirmation.value = true;
};

// Function to save the order
const saveOrder = async (shouldPrint = false) => {
    const payload = {
        order_id: orderId.value,
        order_type: orderType.value,
        items: cart.value,
        total_bill: total.value,
        status: "created",
        customer_phone: customerPhone.value || null,
        discount: 0,
        delivery_charges: 0,
        service_charges: 0,
        net_bill: total.value,
        table_no: tableNum.value || null,
    };

    try {
        submitting.value = true;

        // 1. Save the order
        await axios.post("/api/orders", payload);

        // 2. Print kitchen bill if requested
        if (shouldPrint) {
            const printSuccess = await printKitchenBill(payload);
            if (printSuccess) {
                window.showCustomAlert(
                    "success",
                    `Order ${orderId.value} created and kitchen bill printed successfully!`
                );
            } else {
                window.showCustomAlert(
                    "warning",
                    `Order ${orderId.value} created, but failed to print kitchen bill.`
                );
            }
        } else {
            window.showCustomAlert(
                "success",
                `Order ${orderId.value} created successfully!`
            );
        }

        // Reload the page after a short delay
       setTimeout(() => {
            window.location.reload();
        }, 5000);
    } catch (error) {
        console.error("Failed to create order:", error);
        window.showCustomAlert(
            "error",
            error.response?.data?.message || "Failed to create order"
        );
    } finally {
        submitting.value = false;
    }
};

// Handle print confirmation result
const handlePrintConfirm = () => {
    saveOrder(true);
    showPrintConfirmation.value = false;
};

const handleSkipPrinting = () => {
    saveOrder(false);
    showPrintConfirmation.value = false;
};

/* ================= LIFECYCLE ================= */
onMounted(async () => {
    await fetchBusinessDay();
    await fetchMenu();
    await fetchLastOrderNumber();
    generateOrderId();
});

watch(orderType, generateOrderId);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Custom Alerts Component -->
        <CustomAlert />

        <!-- Kitchen Bill Confirmation Modal -->
        <KitchenBillConfirmation
            v-if="showPrintConfirmation"
            :order-id="orderId"
            :show="showPrintConfirmation"
            :loading="printing"
            @close="showPrintConfirmation = false"
            @confirm="handlePrintConfirm"
            @skip="handleSkipPrinting"
        />
        <!-- ================= HEADER ================= -->
        <div class="bg-white border-b shadow-sm sticky top-0 z-10">
            <div class="container mx-auto px-4 py-3">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <!-- Order Info -->
                    <div
                        class="flex flex-col md:flex-row md:items-center gap-4"
                    >
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 mb-1"
                                >Order Type</label
                            >
                            <select
                                v-model="orderType"
                                class="border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option>Takeaway</option>
                                <option>Dine-in</option>
                                <option>Delivery</option>
                            </select>
                        </div>

                        <!-- Dine-in Table Selection -->
                        <div v-if="orderType === 'Dine-in'">
                            <label
                                class="block text-xs font-medium text-gray-500 mb-1"
                                >Select Table</label
                            >
                            <select
                                v-model="tableNum"
                                class="border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="T1">Table T1</option>
                                <option value="T2">Table T2</option>
                                <option value="T3">Table T3</option>
                                <option value="T4">Table T4</option>
                                <option value="T5">Table T5</option>
                                <option value="T6">Table T6</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 mb-1"
                                >Order ID</label
                            >
                            <div
                                class="px-4 py-2 bg-green-50 border border-green-200 rounded-lg"
                            >
                                <span class="font-bold text-green-700">{{
                                    orderId
                                }}</span>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 mb-1"
                                >Customer's Info</label
                            >
                            <input
                                v-model="customerPhone"
                                type="tel"
                                inputmode="numeric"
                                placeholder="Enter Customer's Info"
                                class="w-full md:w-auto border border-gray-300 px-4 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Business Day Info -->
                    <div class="text-right">
                        <div v-if="businessDay" class="text-sm">
                            <span class="text-gray-500">Business Day:</span>
                            <span class="font-semibold text-green-700 ml-2">
                                {{
                                    new Date(
                                        businessDay.business_date
                                    ).toDateString()
                                }}
                            </span>
                        </div>
                        <div
                            v-if="businessDayError"
                            class="text-xs text-red-600 mt-1"
                        >
                            {{ businessDayError }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <div class="grid lg:grid-cols-3 gap-6">

                <!-- ================= MENU SECTION ================= -->
                <div class="lg:col-span-2">
                    <!-- Category Navigation -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="expandedCategoryId = category.id"
                                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
                                :class="{
                                    'bg-green-600 text-white shadow-md':
                                        expandedCategoryId === category.id,
                                    'bg-white text-gray-700 border border-gray-200 hover:border-green-400 hover:text-green-700':
                                        expandedCategoryId !== category.id,
                                }"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Menu Items Grid -->
                    <div v-if="loading" class="text-center py-12">
                        <div
                            class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"
                        ></div>
                        <p class="mt-2 text-gray-500">Loading menu...</p>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Selected Category View -->
                        <template v-if="expandedCategoryId">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <div
                                    v-for="item in categories.find(
                                        (c) => c.id === expandedCategoryId
                                    )?.items || []"
                                    :key="item.id"
                                    class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 h-full flex flex-col"
                                >
                                    <!-- Item Header -->
                                    <div class="p-3 flex-1">
                                        <h3
                                            class="font-semibold text-gray-900 text-sm mb-1 truncate"
                                        >
                                            {{ item.name }}
                                        </h3>
                                        <div
                                            class="flex items-center justify-between mb-2"
                                        >
                                            <div class="text-xs text-gray-500">
                                                {{
                                                    parsePrices(item.price)
                                                        .length
                                                }}
                                                size{{
                                                    parsePrices(item.price)
                                                        .length > 1
                                                        ? "s"
                                                        : ""
                                                }}
                                            </div>
                                            <div
                                                class="text-[10px] px-2 py-0.5 bg-green-100 text-green-800 rounded-full truncate"
                                            >
                                                {{ item.category_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price Variants -->
                                    <div class="border-t border-gray-100">
                                        <div
                                            v-for="type in parsePrices(
                                                item.price
                                            )"
                                            :key="type.type"
                                            class="px-3 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-2"
                                            >
                                                <div class="min-w-0 flex-1">
                                                    <div
                                                        class="font-medium text-gray-900 text-xs truncate"
                                                    >
                                                        {{ type.type }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-green-600 font-semibold"
                                                    >
                                                        Rs. {{ type.price }}
                                                    </div>
                                                </div>

                                                <!-- Quantity Controls -->
                                                <div
                                                    class="flex items-center space-x-1 shrink-0"
                                                >
                                                    <button
                                                        @click="
                                                            decreaseQty(
                                                                item,
                                                                type
                                                            )
                                                        "
                                                        :disabled="
                                                            getQty(
                                                                item.id,
                                                                type.type
                                                            ) === 0
                                                        "
                                                        class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-700 rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                    >
                                                        <svg
                                                            class="w-3 h-3"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M20 12H4"
                                                            />
                                                        </svg>
                                                    </button>

                                                    <div
                                                        class="w-6 text-center"
                                                    >
                                                        <span
                                                            class="font-bold text-gray-900 text-xs"
                                                        >
                                                            {{
                                                                getQty(
                                                                    item.id,
                                                                    type.type
                                                                )
                                                            }}
                                                        </span>
                                                    </div>

                                                    <button
                                                        @click="
                                                            increaseQty(
                                                                item,
                                                                type
                                                            )
                                                        "
                                                        class="w-6 h-6 flex items-center justify-center bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                                                    >
                                                        <svg
                                                            class="w-3 h-3"
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
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- All Categories View (Grid Layout) -->
                        <template v-else>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                            >
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    @click="expandedCategoryId = category.id"
                                    class="bg-white rounded-lg border border-gray-200 p-4 cursor-pointer hover:shadow-md hover:border-green-300 transition-all duration-200 group h-full flex flex-col items-center text-center"
                                >
                                    <!-- Category Icon Placeholder -->
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform duration-200"
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
                                                stroke-width="2"
                                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                            />
                                        </svg>
                                    </div>

                                    <h3
                                        class="font-bold text-gray-900 text-sm mb-1 truncate w-full"
                                    >
                                        {{ category.name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mb-2">
                                        {{ category.items?.length || 0 }} items
                                    </p>

                                    <div
                                        class="text-xs text-green-600 font-medium flex items-center"
                                    >
                                        Browse
                                        <svg
                                            class="w-3 h-3 ml-1"
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
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ================= BILL SECTION ================= -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white rounded-lg border border-gray-200 shadow-sm sticky top-20"
                    >
                        <!-- Bill Header -->
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Order Summary
                                </h2>
                                <div class="text-sm font-medium text-green-700">
                                    {{ cart.length }} item{{
                                        cart.length !== 1 ? "s" : ""
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Bill Items -->
                        <div class="p-4 max-h-[400px] overflow-y-auto">
                            <div
                                v-if="cart.length === 0"
                                class="text-center py-8"
                            >
                                <svg
                                    class="w-12 h-12 text-gray-300 mx-auto mb-3"
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
                                <p class="text-gray-500">No items added yet</p>
                                <p class="text-sm text-gray-400 mt-1">
                                    Select items from the menu
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="bill in cart"
                                    :key="bill.item_id + bill.type"
                                    class="bg-gray-50 rounded p-3 hover:bg-gray-100 transition-colors"
                                >
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <div
                                                class="font-medium text-gray-900 text-sm truncate"
                                            >
                                                {{ bill.item_name }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 mt-1 truncate"
                                            >
                                                {{ bill.type }} •
                                                {{ bill.qty }} × Rs.
                                                {{ bill.unit_price }}
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center space-x-2 shrink-0"
                                        >
                                            <div class="text-right">
                                                <div
                                                    class="font-bold text-gray-900 text-sm"
                                                >
                                                    Rs. {{ bill.line_total }}
                                                </div>
                                            </div>

                                            <button
                                                @click="removeFromBill(bill)"
                                                class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors"
                                                title="Remove item"
                                            >
                                                <svg
                                                    class="w-3 h-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bill Footer -->
                        <div
                            class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg"
                        >
                            <!-- Total -->
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-bold text-gray-900"
                                    >Total Amount</span
                                >
                                <span class="text-lg font-bold text-green-700"
                                    >Rs. {{ total }}</span
                                >
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button
                                    @click="confirmOrder"
                                    :disabled="submitting || cart.length === 0"
                                    class="w-2/4 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-medium py-2 rounded-full hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center"
                                >
                                    <svg
                                        v-if="submitting"
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
                                        submitting
                                            ? "Processing..."
                                            : "Confirm Order"
                                    }}
                                </button>

                                <button
                                    v-if="cart.length > 0"
                                    @click="cart = []"
                                    class="w-2/4 border border-red-300 text-red-600 font-medium py-2 rounded-full hover:bg-red-50 transition-colors text-sm"
                                >
                                    Clear Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for bill section */
.max-h-\[400px\]::-webkit-scrollbar {
    width: 4px;
}

.max-h-\[400px\]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>

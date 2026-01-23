<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

const props = defineProps({
    date: String,
});

const emit = defineEmits(["close"]);

const orders = ref([]);
const loading = ref(false);

/* FETCH CLOSED ORDERS OF THAT DATE */
const fetchOrders = async () => {
    loading.value = true;

    const res = await axios.get("/api/orders", {
        params: {
            status: "closed",
            from_date: props.date,
            to_date: props.date,
        },
    });

    orders.value = res.data.data || [];
    loading.value = false;
};

onMounted(fetchOrders);

/* TOTAL */
const total = computed(() =>
    orders.value.reduce((s, o) => s + Number(o.net_bill || 0), 0)
);

/* EXPORT PDF */
const exportPDF = () => {
    if (!orders.value.length) return;

    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text(`Orders (${props.date})`, 14, 15);

    autoTable(doc, {
        startY: 25,
        head: [["#", "Order ID", "Type", "Amount"]],
        body: orders.value.map((o, i) => [
            i + 1,
            o.order_id,
            o.order_type,
            `Rs. ${o.net_bill}`,
        ]),
    });

    doc.text(`Total: Rs. ${total.value}`, 14, doc.lastAutoTable.finalY + 10);
    doc.save(`orders_${props.date}.pdf`);
};
</script>

<template>
    <!-- Blurred background backdrop -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="emit('close')"
    >
        <!-- Blur overlay -->
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>

        <!-- Main modal container -->
        <div
            class="relative bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden"
        >
            <!-- HEADER -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-8 py-6"
            >
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        Closed Orders
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            new Date(date).toLocaleDateString("en-US", {
                                weekday: "long",
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                            })
                        }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="exportPDF"
                        :disabled="!orders.length || loading"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition-all hover:bg-blue-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
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

                    <button
                        @click="emit('close')"
                        class="inline-flex items-center justify-center rounded-xl p-2.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
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

            <!-- CONTENT -->
            <div class="max-h-[calc(100vh-12rem)] overflow-y-auto px-8 py-6">
                <!-- LOADING STATE -->
                <div v-if="loading" class="py-16 text-center">
                    <div
                        class="inline-flex flex-col items-center justify-center"
                    >
                        <div
                            class="h-10 w-10 animate-spin rounded-full border-3 border-blue-600 border-t-transparent mb-4"
                        ></div>
                        <span class="text-gray-600"
                            >Loading orders for
                            {{
                                new Date(date).toLocaleDateString("en-US", {
                                    month: "short",
                                    day: "numeric",
                                })
                            }}...</span
                        >
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-else-if="!orders.length" class="py-16 text-center">
                    <div
                        class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-gray-100"
                    >
                        <svg
                            class="h-10 w-10 text-gray-400"
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
                        No closed orders found
                    </h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        There are no closed orders for
                        {{
                            new Date(date).toLocaleDateString("en-US", {
                                month: "long",
                                day: "numeric",
                                year: "numeric",
                            })
                        }}
                    </p>
                </div>

                <!-- ORDERS LIST -->
                <div v-else class="space-y-5">
                    <div class="grid gap-5">
                        <div
                            v-for="(order, index) in orders"
                            :key="order.id"
                            class="group rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-300 hover:border-green-300 hover:shadow-lg hover:-translate-y-0.5"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-5">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-base font-bold text-blue-700 shadow-sm"
                                    >
                                        #{{ index + 1 }}
                                    </div>
                                    <div>
                                        <div
                                            class="flex items-center gap-3 mb-2"
                                        >
                                            <h4
                                                class="text-sm font-semibold text-gray-900 group-hover:text-green-700"
                                            >
                                                {{ order.order_id }}
                                            </h4>
                                            <span
                                                class="rounded-full px-3.5 py-1.5 text-xs font-medium border shadow-sm"
                                                :class="{
                                                    'border-green-200 bg-gradient-to-br from-green-50 to-green-100 text-green-700':
                                                        order.order_type ===
                                                        'Takeaway',
                                                    'border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100 text-blue-700':
                                                        order.order_type ===
                                                        'Dine-in',
                                                    'border-purple-200 bg-gradient-to-br from-purple-50 to-purple-100 text-purple-700':
                                                        order.order_type ===
                                                        'Delivery',
                                                }"
                                            >
                                                {{ order.order_type }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="order.customer_phone"
                                            class="flex items-center text-sm text-gray-500"
                                        >
                                            <svg
                                                class="mr-2 h-4 w-4"
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
                                            {{ order.customer_phone }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-lg font-bold text-green-700"
                                    >
                                        Rs. {{ order.net_bill }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-2">
                                        <span
                                            class="inline-flex items-center gap-1"
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
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                                />
                                            </svg>
                                            {{ order.items?.length || 0 }} items
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL SUMMARY -->
                    <div
                        class="sticky bottom-0 mt-8 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-7 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div
                                    class="text-sm font-medium text-green-800 mb-2"
                                >
                                    TOTAL SUMMARY
                                </div>
                                <div class="flex items-center gap-6">
                                    <div>
                                        <div class="text-xs text-green-600">
                                            Orders Count
                                        </div>
                                        <div
                                            class="text-3xl font-bold text-green-900"
                                        >
                                            {{ orders.length }}
                                        </div>
                                    </div>
                                    <div class="h-12 w-px bg-green-200"></div>
                                    <div>
                                        <div class="text-xs text-green-600">
                                            Date
                                        </div>
                                        <div
                                            class="text-lg font-semibold text-green-900"
                                        >
                                            {{
                                                new Date(
                                                    date
                                                ).toLocaleDateString("en-US", {
                                                    month: "short",
                                                    day: "numeric",
                                                    year: "numeric",
                                                })
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-sm font-medium text-green-800 mb-2"
                                >
                                    TOTAL AMOUNT
                                </div>
                                <div class="text-xl font-bold text-green-900">
                                    Rs. {{ total }}
                                </div>
                                <div class="text-sm text-green-600 mt-2">
                                    Average: Rs.
                                    {{ (total / orders.length).toFixed(2) }} per
                                    order
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

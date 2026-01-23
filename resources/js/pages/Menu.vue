<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import Layout from "@/layouts/layout.vue";
import EditItem from "./EditItem.vue";
import AddCategory from "./AddCategory.vue";
import CustomAlert from "../components/CustomAlert.vue";

defineOptions({ layout: Layout });

const showEdit = ref(false);
const editingItemId = ref(null);
const showAddCategory = ref(false);

// Edit Item
const editItem = (id) => {
    console.log("working", id);
    editingItemId.value = id;
    showEdit.value = true;
};

// Delete item
const deleteItem = async (id) => {
    const confirmed = confirm("Are you sure you want to delete this item?");

    if (!confirmed) return;

    try {
        await axios.delete(`/api/items/${id}`);

        // remove item from UI instantly
        items.value = items.value.filter((item) => item.id !== id);
        window.location.reload();
    } catch (error) {
        console.error("Failed to delete item", error);
        showCustomAlert("error", "Delete failed!");
    }
};

// Delete Category
const deleteCategory = async (id) => {
    const confirmed = confirm("Are you sure you want to delete this category?");

    if (!confirmed) return;

    try {
        await axios.delete(`/api/categories/${id}`);

        // remove category from UI instantly
        categories.value = categories.value.filter(
            (category) => category.id !== id
        );
        window.location.reload();
    } catch (error) {
        console.error("Failed to delete category", error);
        showCustomAlert("error", "Delete failed!");
    }
};

/* categories from API */
const categories = ref([]);

/* active category (id) */
const activeCategoryId = ref(null);

/* fetch menu */
const fetchMenu = async () => {
    const res = await axios.get("/api/menu");
    categories.value = res.data;

    if (categories.value.length) {
        activeCategoryId.value = categories.value[0].id;
    }
};

/* active category */
const activeCategory = computed(() =>
    categories.value.find((cat) => cat.id === activeCategoryId.value)
);

/* active category items */
const items = computed(() => activeCategory.value?.items || []);

onMounted(fetchMenu);
</script>

<template>
    <div class="space-y-6">
        <!-- Custom Alerts Component -->
        <CustomAlert />
        <!-- PAGE TITLE -->
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Menu</h1>
            <p class="text-sm text-gray-500">Select category to view items</p>
        </div>

        <!-- CATEGORY TABS -->
        <div class="flex gap-2 border-b pb-2 items-center">
            <div
                v-for="category in categories"
                :key="category.id"
                class="group relative transition-all duration-300 ease-out"
            >
                <div
                    class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-t-lg bg-white transition-all duration-300 ease-out cursor-pointer relative overflow-hidden"
                    :class="
                        activeCategoryId === category.id
                            ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-sm'
                            : 'bg-white text-gray-700 hover:bg-gray-200 hover:text-gray-900 hover:shadow-sm'
                    "
                >
                    <div
                        v-if="activeCategoryId === category.id"
                        class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20"
                    ></div>

                    <button
                        @click="activeCategoryId = category.id"
                        class="relative flex items-center gap-2"
                    >
                        <!-- Animated dot indicator for active state -->
                        <span
                            v-if="activeCategoryId === category.id"
                            class="absolute -left-2 w-1.5 h-1.5 bg-white rounded-full animate-pulse"
                        ></span>
                        {{ category.name }}
                    </button>

                    <!-- Delete button-->
                    <button
                        @click="deleteCategory(category.id)"
                        class="relative ml-4 w-6 h-6 p-1 flex items-center justify-center rounded-full transition-all duration-200 group-hover:scale-110"
                        :class="
                            activeCategoryId === category.id
                                ? 'hover:bg-white/20 text-white'
                                : 'hover:bg-gray-100 text-gray-500'
                        "
                        title="Delete category"
                    >
                        <span
                            class="absolute right-1 text-lg text-center leading-none transform transition-transform duration-200 group-hover:rotate-90"
                            >×</span
                        >
                    </button>
                </div>

                <div
                    v-if="activeCategoryId === category.id"
                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-green-400 to-emerald-400 animate-pulse"
                ></div>
            </div>

            <!-- ADD CATEGORY BUTTON-->
            <button
                @click="showAddCategory = true"
                class="ml-auto px-5 py-2.5 text-sm font-medium rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 transition-all duration-300 ease-out shadow-sm hover:shadow-md focus:ring-2 focus:ring-green-500/30 focus:outline-none flex items-center gap-2 group"
            >
                <span class="text-sm transition-transform duration-300">+</span>
                Add Category
            </button>
        </div>

        <!-- CATEGORY TITLE -->
        <div v-if="activeCategory" class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ activeCategory.name }}
                    </h1>
                    <p class="text-gray-500 text-sm mt-1">
                        {{ items.length }}
                        {{ items.length === 1 ? "item" : "items" }} available
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('additem')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition duration-200 shadow-sm hover:shadow-md"
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
                        Add Item
                    </Link>
                </div>
            </div>
            <div class="mt-4 border-t border-gray-200"></div>
        </div>

        <!-- ITEMS GRID -->
        <div
            v-if="items.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 items-start"
        >
            <div
                v-for="item in items"
                :key="item.id"
                class="group h-full bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col"
            >
                <!-- Image Container -->
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100"
                >
                    <div
                        class="aspect-square flex items-center justify-center p-3"
                    >
                        <img
                            :src="
                                item.image
                                    ? `/storage/${item.image}`
                                    : 'https://via.placeholder.com/400x400/EDF2F7/667EEA?text=No+Image'
                            "
                            :alt="item.name"
                            class="w-full h-full rounded-lg object-contain transition-transform duration-500 group-hover:scale-105"
                        />
                    </div>
                </div>

                <!-- Content Container -->
                <div class="p-2 flex-1 flex flex-col">
                    <!-- Header with Name -->
                    <div class="mb-1">
                        <h3
                            class="text-md font-semibold text-gray-900 line-clamp-1"
                        >
                            {{ item.name }}
                        </h3>
                    </div>

                    <!-- Price List -->
                    <div v-if="item.price" class="mb-4">
                        <div class="space-y-2">
                            <div
                                v-for="(row, i) in JSON.parse(item.price)"
                                :key="i"
                                class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg"
                            >
                                <span class="text-sm text-gray-600">{{
                                    row.type
                                }}</span>
                                <span
                                    class="text-sm font-semibold text-green-700"
                                    >{{ row.price }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Description -->
                    <div v-if="item.description" class="mt-auto">
                        <div
                            :class="{
                                'max-h-24 overflow-hidden': !item.expanded,
                                'max-h-96': item.expanded,
                            }"
                            class="transition-all duration-300"
                        >
                            <div class="border-t pt-4">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Description
                                    </h4>
                                    <button
                                        @click="item.expanded = !item.expanded"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1"
                                    >
                                        {{
                                            item.expanded
                                                ? "Show Less"
                                                : "Show More"
                                        }}
                                        <svg
                                            :class="{
                                                'rotate-180': item.expanded,
                                            }"
                                            class="w-3 h-3 transition-transform duration-300"
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
                                    </button>
                                </div>

                                <div
                                    v-if="
                                        JSON.parse(item.description).length ===
                                        0
                                    "
                                >
                                    <p class="text-sm text-gray-500 italic">
                                        No description provided
                                    </p>
                                </div>
                                <ul v-else class="space-y-2">
                                    <li
                                        v-for="(point, i) in JSON.parse(
                                            item.description
                                        )"
                                        :key="i"
                                        class="text-sm text-gray-600 flex items-start"
                                    >
                                        <svg
                                            class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span>{{ point }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="flex justify-end space-x-3">
                            <!-- Edit Button -->
                            <button
                                @click="editItem(item.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition duration-200 group/edit"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                                    />
                                </svg>
                                Edit
                            </button>

                            <!-- Delete Button -->
                            <button
                                @click="deleteItem(item.id)"
                                class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-lg transition duration-200 group/delete"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NO ITEMS -->
        <div v-else class="max-w-lg mx-auto my-16">
            <div class="text-center">
                <!-- Icon -->
                <div
                    class="mx-auto w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6"
                >
                    <svg
                        class="w-12 h-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                        />
                    </svg>
                </div>

                <!-- Text -->
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    No items found
                </h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    This category doesn't have any items yet. Start by adding
                    your first item.
                </p>

                <!-- Action Button -->
                <Link
                    :href="route('additem')"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5"
                >
                    <svg
                        class="w-5 h-5 mr-2"
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
                    Add Your First Item
                </Link>

                <p class="text-xs text-gray-400 mt-6">
                    Tip: Items added here will appear in this category
                </p>
            </div>
        </div>
    </div>

    <EditItem
        v-if="showEdit"
        :item-id="editingItemId"
        @close="showEdit = false"
    />

    <AddCategory v-if="showAddCategory" @close="showAddCategory = false" />
</template>

<template>
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <!-- Custom Alerts Component -->
        <CustomAlert />
    <h2 class="text-xl font-semibold mb-6">Add Item</h2>

    <form @submit.prevent="submitForm" class="space-y-5">

      <!-- Image -->
      <div>
        <label class="block mb-1 text-sm font-medium">Item Image</label>
        <input
          type="file"
          @change="handleImage"
          class="file:bg-green-700 file:text-white file:text-sm file:border-0 file:px-4 file:py-2 file:rounded-md file:cursor-pointer"
        />
        <small v-if="errors.image" class="text-red-600">
          {{ errors.image[0] }}
        </small>
      </div>

      <!-- Item Name -->
      <div>
        <label class="block mb-1 text-sm font-medium">Item Name</label>
        <input
          v-model="form.name"
          type="text"
          :class="[
            'w-full border-2 rounded-lg px-3 py-2 focus:outline-none',
            errors.name ? 'border-red-500 focus:border-red-500' : 'border-gray-300 focus:border-green-700'
          ]"
        />
        <small v-if="errors.name" class="text-red-600">
          {{ errors.name[0] }}
        </small>
      </div>

      <!-- Category -->
      <div>
        <label class="block mb-1 text-sm font-medium">Category</label>
        <select
          v-model="form.category_id"
          :class="[
            'w-full border-2 rounded-lg px-3 py-2 focus:outline-none',
            errors.category_id ? 'border-red-500 focus:border-red-500' : 'border-gray-300 focus:border-green-700'
          ]"
        >
          <option value="">Select Category</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <small v-if="errors.category_id" class="text-red-600">
          {{ errors.category_id[0] }}
        </small>
      </div>

      <!-- Prices -->
<div>
  <label class="block mb-2 text-sm font-medium">Prices</label>

  <div
    v-for="(row, index) in prices"
    :key="index"
    class="flex gap-2 mb-2"
  >
    <input
      v-model="row.type"
      type="text"
      placeholder="Type (Small / Medium / Large)"
      class="w-1/2 border rounded-lg px-3 py-2 focus:border-green-700 focus:outline-none"
    />

    <input
      v-model="row.price"
      type="number"
      placeholder="Price"
      class="w-1/2 border rounded-lg px-3 py-2 focus:border-green-700 focus:outline-none"
    />

    <button
      type="button"
      @click="removePrice(index)"
      class="text-red-600 px-2"
    >
      ✕
    </button>
  </div>

  <button
    type="button"
    @click="addPrice"
    class="text-sm text-green-700"
  >
    + Add price
  </button>

  <small v-if="errors.price" class="text-red-600">
    {{ errors.price[0] }}
  </small>
</div>


      <!-- Status -->
      <div>
        <label class="block mb-1 text-sm font-medium">Status</label>
        <select
          v-model="form.status"
          :class="[
            'w-full border-2 rounded-lg px-3 py-2 focus:outline-none',
            errors.status ? 'border-red-500 focus:border-red-500' : 'border-gray-300 focus:border-green-700'
          ]"
        >
          <option value="available">Available</option>
          <option value="not-available">Not Available</option>
        </select>
        <small v-if="errors.status" class="text-red-600">
          {{ errors.status[0] }}
        </small>
      </div>

      <!-- DESCRIPTION -->
      <div>
        <label class="block mb-2 text-sm font-medium">
          Description (Deal Items)
        </label>

        <div
          v-for="(point, index) in description"
          :key="index"
          class="flex gap-2 mb-2"
        >
          <input
            v-model="description[index]"
            type="text"
            class="flex-1 border rounded-lg px-3 py-2 focus:border-green-700 focus:outline-none"
            placeholder="e.g. 1 Large Pizza"
          />

          <button
            type="button"
            @click="removeDescription(index)"
            class="px-3 text-red-600 rounded"
          >
            ✕
          </button>
        </div>

        <button
          type="button"
          @click="addDescription"
          class="text-sm text-green-700"
        >
          + Add
        </button>

        <small v-if="errors.description" class="text-red-600">
          {{ errors.description[0] }}
        </small>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full bg-green-700 text-white py-2 rounded-lg"
      >
        Save Item
      </button>

    </form>
  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import CustomAlert from "../components/CustomAlert.vue";

/* categories */
const categories = ref([])

/* form */
const form = ref({
  category_id: '',
  name: '',
  price: '',
  status: 'available',
  image: null
})

/* description (JSON array) */
const description = ref([])

/* errors */
const errors = ref({})

/* methods */
const addDescription = () => {
  description.value.push('')
}

const removeDescription = (index) => {
  description.value.splice(index, 1)
}

/* prices (JSON array) */
const prices = ref([])

const addPrice = () => {
  prices.value.push({
    type: '',
    price: ''
  })
}

const removePrice = (index) => {
  prices.value.splice(index, 1)
}


const handleImage = (e) => {
  form.value.image = e.target.files[0] || null
}

const submitForm = async () => {
  errors.value = {}

  try {
    const formData = new FormData()

    formData.append('category_id', form.value.category_id)
    formData.append('name', form.value.name)
    formData.append('price', JSON.stringify(prices.value))
    formData.append('status', form.value.status)
    formData.append('description', JSON.stringify(description.value))

    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await axios.post('/api/items', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    // Reset form after success
    form.value = {
      category_id: '',
      name: '',
      price: '',
      status: 'available',
      image: null
    }
    description.value = []
    window.location.href = '/menu'
    showCustomAlert(
            "success",
            "Item Added Succesfully!"
        );

  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Server error:', error)
    }
  }
}

/* fetch categories */
onMounted(async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (error) {
    console.error('Failed to load categories', error)
  }
})
</script>

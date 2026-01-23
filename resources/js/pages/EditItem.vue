<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'updated'])


/* props */
const props = defineProps({
  itemId: {
    type: Number,
    required: true
  }
})

/* categories */
const categories = ref([])

/* form */
const form = ref({
  category_id: '',
  name: '',
  status: 'available',
  image: null,
  image_url: null
})

/* prices (JSON array) */
const prices = ref([])

/* description (JSON array) */
const description = ref([])

/* errors */
const errors = ref({})

/* description methods */
const addDescription = () => {
  description.value.push('')
}
const removeDescription = (index) => {
  description.value.splice(index, 1)
}

/* price methods */
const addPrice = () => {
  prices.value.push({ type: '', price: '' })
}
const removePrice = (index) => {
  prices.value.splice(index, 1)
}

/* image handler */
const handleImage = (e) => {
  form.value.image = e.target.files[0] || null
}

/* UPDATE ITEM */
const submitForm = async () => {
  errors.value = {}

  try {
    const formData = new FormData()

    formData.append('_method', 'PUT') // Laravel method override
    formData.append('category_id', form.value.category_id)
    formData.append('name', form.value.name)
    formData.append('status', form.value.status)
    formData.append('price', JSON.stringify(prices.value))
    formData.append('description', JSON.stringify(description.value))

    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await axios.post(`/api/items/${props.itemId}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    window.location.reload()

  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Update failed', error)
    }
  }
}

/* load categories + item data */
onMounted(async () => {
  try {
    const catRes = await axios.get('/api/categories')
    categories.value = catRes.data

    const itemRes = await axios.get(`/api/items/${props.itemId}`)
    const item = itemRes.data

    form.value.category_id = item.category_id
    form.value.name = item.name
    form.value.status = item.status
    form.value.image_url = item.image

    prices.value = item.price ? JSON.parse(item.price) : []
    description.value = item.description ? JSON.parse(item.description) : []

  } catch (error) {
    console.error('Failed to load item data', error)
  }
})
</script>


<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">

    <!-- Modal Container -->
    <div
      class="bg-white w-full max-w-2xl rounded-xl shadow-lg
             max-h-[90vh] overflow-hidden relative"
    >

      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">Edit Item</h2>
        <button
          class="text-gray-500 hover:text-black"
          @click="$emit('close')"
        >
          ✕
        </button>
      </div>

      <!-- SCROLLABLE BODY -->
      <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-64px)]">

        <form @submit.prevent="submitForm" class="space-y-5">

          <!-- Image -->
          <div>
            <label class="block mb-1 text-sm font-medium">Item Image</label>

            <input
              type="file"
              @change="handleImage"
              class="file:bg-green-700 file:text-white file:text-sm
                     file:border-0 file:px-4 file:py-2 file:rounded-md"
            />

            <img
              v-if="form.image_url"
              :src="`/storage/${form.image_url}`"
              class="w-32 h-32 object-cover rounded mt-2"
            />

            <small v-if="errors.image" class="text-red-600 text-sm">
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
                errors.name ? 'border-red-500' : 'border-gray-300 focus:border-green-700'
              ]"
            />
            <small v-if="errors.name" class="text-red-600 text-sm">
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
                errors.category_id ? 'border-red-500' : 'border-gray-300 focus:border-green-700'
              ]"
            >
              <option value="">Select Category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <small v-if="errors.category_id" class="text-red-600 text-sm">
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
                placeholder="Type"
                class="w-1/2 border rounded-lg px-3 py-2"
              />
              <input
                v-model="row.price"
                type="number"
                placeholder="Price"
                class="w-1/2 border rounded-lg px-3 py-2"
              />
              <button type="button" @click="removePrice(index)" class="text-red-600">
                ✕
              </button>
            </div>

            <button type="button" @click="addPrice" class="text-sm text-green-700">
              + Add price
            </button>

            <small v-if="errors.price" class="text-red-600 text-sm">
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
                errors.status ? 'border-red-500' : 'border-gray-300 focus:border-green-700'
              ]"
            >
              <option value="available">Available</option>
              <option value="not-available">Not Available</option>
            </select>
            <small v-if="errors.status" class="text-red-600 text-sm">
              {{ errors.status[0] }}
            </small>
          </div>

          <!-- Description -->
          <div>
            <label class="block mb-2 text-sm font-medium">Description</label>

            <div
              v-for="(point, index) in description"
              :key="index"
              class="flex gap-2 mb-2"
            >
              <input
                v-model="description[index]"
                type="text"
                class="flex-1 border rounded-lg px-3 py-2"
              />
              <button type="button" @click="removeDescription(index)" class="text-red-600">
                ✕
              </button>
            </div>

            <button type="button" @click="addDescription" class="text-sm text-green-700">
              + Add
            </button>

            <small v-if="errors.description" class="text-red-600 text-sm">
              {{ errors.description[0] }}
            </small>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="w-full bg-green-700 text-white py-2 rounded-lg"
          >
            Update Item
          </button>

        </form>

      </div>
    </div>
  </div>
</template>


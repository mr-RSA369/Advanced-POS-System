import { createRouter, createWebHistory } from 'vue-router'

// import pages
import ItemList from '../pages/ItemList.vue'
import EditItem from '../pages/EditItem.vue'

const routes = [
  {
    path: '/items',
    name: 'items.index',
    component: ItemList
  },
  {
    path: '/items/:id/edit',
    name: 'items.edit',
    component: EditItem
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router

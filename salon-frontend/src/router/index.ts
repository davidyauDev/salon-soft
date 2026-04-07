import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../components/views/DashboardView.vue'
import ProductsView from '../components/views/ProductsView.vue'
import ServicesView from '../components/views/ServicesView.vue'
import SalesView from '../components/views/SalesView.vue'
import ClientsView from '../components/views/ClientsView.vue'
import CommissionsView from '../components/views/CommissionsView.vue'
import ReportsView from '../components/views/ReportsView.vue'
import AuditLogsView from '../components/views/AuditLogsView.vue'

const routes = [
  { path: '/', name: 'dashboard', component: DashboardView },
  { path: '/inventario', name: 'inventory', component: ProductsView },
  { path: '/servicios', name: 'services', component: ServicesView },
  { path: '/ventas', name: 'sales', component: SalesView },
  { path: '/clientes', name: 'clients', component: ClientsView },
  { path: '/comisiones', name: 'commissions', component: CommissionsView },
  { path: '/reportes', name: 'reports', component: ReportsView },
  { path: '/auditoria', name: 'audit', component: AuditLogsView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router

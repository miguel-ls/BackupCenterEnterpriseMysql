import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import JobsView from '../views/JobsView.vue'
import ConnectionsView from '../views/ConnectionsView.vue'
import HistoryView from '../views/HistoryView.vue'
import LogsView from '../views/LogsView.vue'
import SettingsView from '../views/SettingsView.vue'
import AboutView from '../views/AboutView.vue'
import QueueView from '../views/QueueView.vue'
import UsersView from '../views/UsersView.vue'
import AuditView from '../views/AuditView.vue'
import ReportsView from '../views/ReportsView.vue'
import GraphicsView from '../views/GraphicsView.vue'
import ClientsView from "../views/ClientsView.vue"
import documentationRoutes from "../documentation/router/documentation"
import Transfers from '../views/Transfers.vue'


console.log("Transfers cargado", Transfers)

const router = createRouter({

    history: createWebHistory(),

    routes: [

        {
            path: '/login',
            component: LoginView
        },

        {
            path: "/transfers",
            name: "Transfers",
            component: Transfers
        },        

        {
            path: '/',
            component: DashboardView,
            meta: { requiresAuth: true }
        },

        {
            path: '/jobs',
            component: JobsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/connections',
            component: ConnectionsView,
            meta: { requiresAuth: true }
        },

        {
            path: "/clients",
            component: ClientsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/queue',
            component: QueueView,
            meta: { requiresAuth: true }
        },

        {
            path: '/history',
            component: HistoryView,
            meta: { requiresAuth: true }
        },

        {
            path: '/logs',
            component: LogsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/users',
            component: UsersView,
            meta: { requiresAuth: true }
        },

        {
            path: '/audit',
            component: AuditView,
            meta: { requiresAuth: true }
        },

        {
            path: '/reports',
            component: ReportsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/graphics',
            component: GraphicsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/settings',
            component: SettingsView,
            meta: { requiresAuth: true }
        },

        {
            path: '/about',
            component: AboutView,
            meta: { requiresAuth: true }
        },

        ...documentationRoutes

    ]

})

router.beforeEach((to, from, next) => {

    const user = localStorage.getItem("user")

    if (to.meta.requiresAuth && !user) {

        next("/login")
        return

    }

    if (to.path === "/login" && user) {

        next("/")
        return

    }

    next()

})

export default router
export default [

    {
        path: "/documentation",
        name: "DocumentationHome",
        component: () => import("../views/HomeView.vue")
    },

    {
        path: "/documentation/development",
        name: "DocumentationDevelopment",
        component: () => import("../views/DevelopmentView.vue")
    },

    {
        path: "/documentation/architecture",
        name: "DocumentationArchitecture",
        component: () => import("../views/ArchitectureView.vue")
    },

    {
        path: "/documentation/database",
        name: "DocumentationDatabase",
        component: () => import("../views/DatabaseView.vue")
    },

    {
        path: "/documentation/api",
        name: "DocumentationApi",
        component: () => import("../views/ApiView.vue")
    },

    {
        path: "/documentation/agent",
        name: "DocumentationAgent",
        component: () => import("../views/AgentView.vue")
    },

    {
        path: "/documentation/docker",
        name: "DocumentationDocker",
        component: () => import("../views/DockerView.vue")
    }

]
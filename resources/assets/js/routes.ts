import {RouteConfig} from "vue-router";
import AssignmentListView from "./components/modules/assignment/ListView.vue";
import AssignmentListViewBreadcrumbs from "./components/modules/assignment/ListViewBreadcrumbs.vue";
import PersonDetailView from "./components/modules/person/DetailView.vue";
import PersonDetailViewBreadcrumbs from "./components/modules/person/DetailViewBreadcrumbs.vue";
import PersonListView from "./components/modules/person/ListView.vue";
import PersonListViewBreadcrumbs from "./components/modules/person/ListViewBreadcrumbs.vue";
import UnitDetailView from "./components/modules/unit/DetailView.vue";
import UnitDetailViewBreadcrumbs from "./components/modules/unit/DetailViewBreadcrumbs.vue";
import UnitListView from "./components/modules/unit/ListView.vue";
import UnitListViewBreadcrumbs from "./components/modules/unit/ListViewBreadcrumbs.vue";
import HouseDetailView from "./components/modules/house/DetailView.vue";
import HouseDetailViewBreadcrumbs from "./components/modules/house/DetailViewBreadcrumbs.vue";
import HouseListView from "./components/modules/house/ListView.vue";
import HouseListViewBreadcrumbs from "./components/modules/house/ListViewBreadcrumbs.vue";
import ObjectDetailView from "./components/modules/object/DetailView.vue";
import ObjectDetailViewBreadcrumbs from "./components/modules/object/DetailViewBreadcrumbs.vue";
import ObjectListView from "./components/modules/object/ListView.vue";
import ObjectListViewBreadcrumbs from "./components/modules/object/ListViewBreadcrumbs.vue";
import InvoiceDetailView from "./components/modules/invoice/DetailView.vue";
import InvoiceDetailViewBreadcrumbs from "./components/modules/invoice/DetailViewBreadcrumbs.vue";
import DashboardView from "./components/modules/dashboard/DetailView.vue";
import DashboardBreadcrumbs from "./components/modules/dashboard/DetailViewBreadcrumbs.vue";
import MainMenu from "./components/shared/main/Menu.vue";
import login from "./components/auth/Login.vue";

const routes: RouteConfig[] = [
    {
        path: '/',
        components: {
            default: DashboardView,
            breadcrumbs: DashboardBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.dashboard.show',
        props: {
            default: true,
            breadcrumbs: true
        },
    },
    {
        path: '/login',
        component: login,
        name: 'web.login',
        props: true
    },
    {
        path: '/assignments',
        components: {
            default: AssignmentListView,
            breadcrumbs: AssignmentListViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.assignments.index',
        props: true
    },
    {
        path: '/persons/:id',
        components: {
            default: PersonDetailView,
            breadcrumbs: PersonDetailViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.persons.show',
        props: {
            default: true,
            breadcrumbs: true
        }
    },
    {
        path: '/persons',
        components: {
            default: PersonListView,
            breadcrumbs: PersonListViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.persons.index'
    },
    {
        path: '/units/:id',
        components: {
            default: UnitDetailView,
            breadcrumbs: UnitDetailViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.units.show',
        props: {
            default: true,
            breadcrumbs: true
        }
    },
    {
        path: '/units',
        components: {
            default: UnitListView,
            breadcrumbs: UnitListViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.units.index'
    },
    {
        path: '/houses/:id',
        components: {
            default: HouseDetailView,
            breadcrumbs: HouseDetailViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.houses.show',
        props: {
            default: true,
            breadcrumbs: true
        }
    },
    {
        path: '/houses',
        components: {
            default: HouseListView,
            breadcrumbs: HouseListViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.houses.index'
    },
    {
        path: '/objects/:id',
        components: {
            default: ObjectDetailView,
            breadcrumbs: ObjectDetailViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.objects.show',
        props: {
            default: true,
            breadcrumbs: true
        }
    },
    {
        path: '/objects',
        components: {
            default: ObjectListView,
            breadcrumbs: ObjectListViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.objects.index'
    },
    {
        path: '/invoices/:id',
        components: {
            default: InvoiceDetailView,
            breadcrumbs: InvoiceDetailViewBreadcrumbs,
            mainmenu: MainMenu
        },
        name: 'web.invoices.show',
        props: {
            default: true,
            breadcrumbs: true
        }
    }
];

export default routes;



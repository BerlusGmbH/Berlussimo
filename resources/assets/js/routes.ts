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
import Menu from "./components/shared/main/Menu.vue";
import login from "./components/auth/Login.vue";

const routes: RouteConfig[] = [
    {
        path: '/',
        components: {
            default: DashboardView,
            breadcrumbs: DashboardBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.dashboard.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
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
            mainmenu: Menu
        },
        name: 'web.assignments.index',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/persons/:id',
        components: {
            default: PersonDetailView,
            breadcrumbs: PersonDetailViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.persons.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/persons',
        components: {
            default: PersonListView,
            breadcrumbs: PersonListViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.persons.index',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/units/:id',
        components: {
            default: UnitDetailView,
            breadcrumbs: UnitDetailViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.units.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/units',
        components: {
            default: UnitListView,
            breadcrumbs: UnitListViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.units.index',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/houses/:id',
        components: {
            default: HouseDetailView,
            breadcrumbs: HouseDetailViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.houses.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/houses',
        components: {
            default: HouseListView,
            breadcrumbs: HouseListViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.houses.index',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/objects/:id',
        components: {
            default: ObjectDetailView,
            breadcrumbs: ObjectDetailViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.objects.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/objects',
        components: {
            default: ObjectListView,
            breadcrumbs: ObjectListViewBreadcrumbs,
            mainmenu: Menu
        },
        name: 'web.objects.index',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/invoices/:id',
        components: {
            default: InvoiceDetailView,
            breadcrumbs: InvoiceDetailViewBreadcrumbs,
            mainmenu: Menu,
            submenu: Menu
        },
        name: 'web.invoices.show',
        props: {
            default: true,
            breadcrumbs: true,
            mainmenu: {url: '/api/v1/menu'},
            submenu: {url: '/api/v1/menu/invoice'}
        }
    }
];

export default routes;



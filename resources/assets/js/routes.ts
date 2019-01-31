import {RouteConfig} from "vue-router";
import Breadcrumbs from "./components/modules/Breadcrumbs.vue";
import AssignmentListView from "./components/modules/assignment/ListView.vue";
import PersonDetailView from "./components/modules/person/DetailView.vue";
import PersonListView from "./components/modules/person/ListView.vue";
import UnitDetailView from "./components/modules/unit/DetailView.vue";
import UnitListView from "./components/modules/unit/ListView.vue";
import HouseDetailView from "./components/modules/house/DetailView.vue";
import HouseListView from "./components/modules/house/ListView.vue";
import ObjectDetailView from "./components/modules/object/DetailView.vue";
import ObjectListView from "./components/modules/object/ListView.vue";
import InvoiceDetailView from "./components/modules/invoice/DetailView.vue";
import DashboardView from "./components/modules/dashboard/DetailView.vue";
import Menu from "./components/shared/main/Menu.vue";
import login from "./components/auth/Login.vue";

const routes: RouteConfig[] = [
    {
        path: '/',
        components: {
            default: DashboardView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.dashboard.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Bereiche',
                        href: 'web.dashboard.show'
                    }
                ]
            },
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
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.assignments.index',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Aufträge',
                        href: 'web.assignments.index'
                    }
                ]
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/persons/:id',
        components: {
            default: PersonDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.persons.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Personen',
                        href: 'web.persons.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'person.show.person'
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/persons',
        components: {
            default: PersonListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.persons.index',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Personen',
                        href: 'web.persons.index'
                    }
                ]
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/units/:id',
        components: {
            default: UnitDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.units.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Einheiten',
                        href: 'web.units.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'unit.show.unit'
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/units',
        components: {
            default: UnitListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.units.index',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Einheiten',
                        href: 'web.units.index'
                    }
                ]
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/houses/:id',
        components: {
            default: HouseDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.houses.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Häuser',
                        href: 'web.houses.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'house.show.house'
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/houses',
        components: {
            default: HouseListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.houses.index',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Häuser',
                        href: 'web.houses.index'
                    }
                ]
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/objects/:id',
        components: {
            default: ObjectDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.objects.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Objekte',
                        href: 'web.objects.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'object.show.object'
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/objects',
        components: {
            default: ObjectListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.objects.index',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Objekte',
                        href: 'web.objects.index'
                    }
                ]
            },
            mainmenu: {url: '/api/v1/menu'}
        }
    },
    {
        path: '/invoices/:id',
        components: {
            default: InvoiceDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            submenu: Menu
        },
        name: 'web.invoices.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Rechnungen',
                        href: 'web.invoices.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'invoice.show.invoice'
            },
            mainmenu: {url: '/api/v1/menu'},
            submenu: {url: '/api/v1/menu/invoice'}
        }
    }
];

export default routes;



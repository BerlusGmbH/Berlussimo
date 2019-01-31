import {RouteConfig} from "vue-router";

import PersonDetailView from "./components/modules/person/DetailView.vue";
import ListView from "./components/shared/listviews/ListView.vue";
import UnitDetailView from "./components/modules/unit/DetailView.vue";
import HouseDetailView from "./components/modules/house/DetailView.vue";
import ObjectDetailView from "./components/modules/property/DetailView.vue";
import InvoiceDetailView from "./components/modules/invoice/DetailView.vue";
import DashboardView from "./components/modules/dashboard/DetailView.vue";

import Breadcrumbs from "./components/shared/breadcrumbs/Breadcrumbs.vue";
import Menu from "./components/shared/main/Menu.vue";
import login from "./components/auth/Login.vue";

import AssignmentListViews from "./components/modules/assignment/ListViews";
import PersonListViews from "./components/modules/person/ListViews";
import PropertyListViews from "./components/modules/property/ListViews";
import HouseListViews from "./components/modules/house/ListViews";
import UnitListViews from "./components/modules/unit/ListViews";
import RentalContractListViews from "./components/modules/rentalcontract/ListViews";
import AssignmentListViewActions from "./components/modules/assignment/ListViewActions.vue";
import PersonListViewActions from "./components/modules/person/ListViewActions.vue";
import PropertyListViewActions from "./components/modules/property/ListViewActions.vue";
import HouseListViewActions from "./components/modules/house/ListViewActions.vue";
import UnitListViewActions from "./components/modules/unit/ListViewActions.vue";
import RentalContractListViewActions from "./components/modules/rentalcontract/ListViewActions.vue";

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
            mainmenu: {module: 'MAIN'}
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
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            defaultSlot: AssignmentListViewActions,
        },
        name: 'web.assignments.index',
        props: {
            default: {
                views: AssignmentListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Aufträge',
                        href: 'web.assignments.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'}
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
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/persons',
        components: {
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            defaultSlot: PersonListViewActions,
        },
        name: 'web.persons.index',
        props: {
            default: {
                views: PersonListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Personen',
                        href: 'web.persons.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'}
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
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/units',
        components: {
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            defaultSlot: UnitListViewActions,
        },
        name: 'web.units.index',
        props: {
            default: {
                views: UnitListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Einheiten',
                        href: 'web.units.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'}
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
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/houses',
        components: {
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            defaultSlot: HouseListViewActions,
        },
        name: 'web.houses.index',
        props: {
            default: {
                views: HouseListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Häuser',
                        href: 'web.houses.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/properties/:id',
        components: {
            default: ObjectDetailView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu
        },
        name: 'web.properties.show',
        props: {
            default: true,
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Objekte',
                        href: 'web.properties.index'
                    },
                    {
                        type: 'entity'
                    }
                ],
                path: 'property.show.property'
            },
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/properties',
        components: {
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            defaultSlot: PropertyListViewActions,
        },
        name: 'web.properties.index',
        props: {
            default: {
                views: PropertyListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Objekte',
                        href: 'web.properties.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'}
        }
    },
    {
        path: '/rentalcontracts',
        components: {
            default: ListView,
            breadcrumbs: Breadcrumbs,
            mainmenu: Menu,
            submenu: Menu,
            defaultSlot: RentalContractListViewActions,
        },
        name: 'web.rentalcontracts.index',
        props: {
            default: {
                views: RentalContractListViews
            },
            breadcrumbs: {
                items: [
                    {
                        type: 'category',
                        name: 'Mietverträge',
                        href: 'web.rentalcontracts.index'
                    }
                ]
            },
            mainmenu: {module: 'MAIN'},
            submenu: {module: 'RENTAL_CONTRACT'}
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
            mainmenu: {module: 'MAIN'},
            submenu: {module: 'INVOICE'}
        }
    }
];

export default routes;



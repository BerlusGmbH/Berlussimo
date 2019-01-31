import UnitsQuery from "./UnitsQuery.graphql";
import TenantContactsQuery from "./TenantContactsQuery.graphql";
import VacanciesQuery from "./VacanciesQuery.graphql";

const UnitListViews = [
    {
        text: "Listenansicht",
        query: UnitsQuery
    },
    {
        text: "Mieterkontakte",
        query: TenantContactsQuery
    },
    {
        text: "Leerstand",
        query: VacanciesQuery
    }];
export default UnitListViews;

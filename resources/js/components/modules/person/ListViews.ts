import TenantsQuery from "./TenantsQuery.graphql";
import HomeOwnersQuery from "./HomeOwnersQuery.graphql";
import EmployeesQuery from "./EmployeesQuery.graphql";

const PersonListViews = [
    {
        text: "Mieter",
        query: TenantsQuery
    },
    {
        text: "WEG-Eigentümer",
        query: HomeOwnersQuery
    },
    {
        text: "Arbeitnehmer",
        query: EmployeesQuery
    }
];
export default PersonListViews;

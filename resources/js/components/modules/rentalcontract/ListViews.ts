import RentalContractsQuery from "./RentalContractsQuery.graphql";
import MovingInQuery from "./MovingInQuery.graphql";
import MovedInQuery from "./MovedInQuery.graphql";
import MovingOutQuery from "./MovingOutQuery.graphql";
import MovedOutQuery from "./MovedOutQuery.graphql";

const RentalContractsListViews =
    [{
        text: "Alle",
        query: RentalContractsQuery
    }, {
        text: "Einziehende",
        query: MovingInQuery
    }, {
        text: "Eingezogene",
        query: MovedInQuery
    }, {
        text: "Ausziehende",
        query: MovingOutQuery
    }, {
        text: "Ausgezogene",
        query: MovedOutQuery
    }];
export default RentalContractsListViews;

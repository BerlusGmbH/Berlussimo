import {Assignment} from "./Assignment";
import {BankAccount} from "./BankAccount";
import {Detail} from "./Detail";
import {Unit} from "./Unit";
import {House} from "./House";
import {Job} from "./Job";
import {JobTitle} from "./JobTitle";
import {RentalContract} from "./RentalContract";
import {PurchaseContract} from "./PurchaseContract";
import {Person} from "./Person";
import {Partner} from "./Partner";
import {AccountingEntity} from "./AccountingEntity";
import {ConstructionSite} from "./ConstructionSite";
import {InvoiceItem} from "./InvoiceItem";
import {BankAccountStandardChart} from "./BankAccountStandardChart";
import {BookingAccount} from "./BookingAccount";
import {Invoice} from "./Invoice";
import {InvoiceLine} from "./InvoiceLine";
import {InvoiceLineAssignment} from "./InvoiceLineAssignment";
import {ModelBase} from "./ModelBase";
import {PostalAddress} from "./PostalAddress";
import {EMail} from "./EMail";
import {Fax} from "./Fax";
import {Phone} from "./Phone";
import {Property} from "./Property";
import {Role} from "./Role";

export abstract class Model extends ModelBase {
    static readonly MODELS = [
        AccountingEntity,
        PostalAddress,
        Assignment,
        BankAccount,
        BankAccountStandardChart,
        BookingAccount,
        ConstructionSite,
        Detail,
        EMail,
        Fax,
        House,
        Invoice,
        InvoiceItem,
        InvoiceLine,
        InvoiceLineAssignment,
        Job,
        JobTitle,
        Partner,
        Person,
        Phone,
        Property,
        RentalContract,
        Role,
        PurchaseContract,
        Unit,
    ];

    static applyPrototype(model) {
        if (Array.isArray(model)) {
            model.forEach(v => {
                Model.applyPrototype(v);
            });
        }
        if (typeof model === "object" && model !== null) {
            if (model.__typename) {
                Model.MODELS.forEach(v => {
                    if (model.__typename === v.__typename || model.__typename === v.__typename + "Stub") {
                        Object.setPrototypeOf(model, v.prototype);
                    }
                });
            }
            Object.keys(model).forEach(v => {
                Model.applyPrototype(model[v]);
            })
        }
        return model;
    }
}
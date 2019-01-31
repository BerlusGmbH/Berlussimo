import {ModelBase} from "./ModelBase";
import {InvoiceLineAssignment} from "./InvoiceLineAssignment";
import {InvoiceItem} from "./InvoiceItem";


export class InvoiceLine extends ModelBase {
    static __typename = 'InvoiceLine';
    id: number = -1;
    position: number;
    invoiceId: number;
    originatingInvoiceId: number;
    supplierId: number;
    itemNumber: string;
    quantity: number = 0.00;
    price: string = "0.0000";
    VAT: number = 19;
    rebate: number = 0.00;
    discount: number = 0.00;
    netAmount: number;
    quantityUnit: string = "Stk";
    description: string;
    assignments: InvoiceLineAssignment[];

    getID() {
        return this.id;
    }

    toString(): string {
        return this.netAmount + ' €';
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    fill(item: InvoiceItem) {
        this.supplierId = Number(item.supplierId);
        this.itemNumber = item.itemNumber;
        this.price = item.price;
        this.VAT = item.VAT;
        this.discount = item.discount;
        this.rebate = item.rebate;
        this.quantityUnit = item.quantityUnit;
        this.description = item.description;
    }

    getDetailUrl() {
        return InvoiceLine.getBaseURL();
    }
}

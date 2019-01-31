import {ModelBase} from "./ModelBase";
import {Partner} from "./Partner";

export class InvoiceItem extends ModelBase {
    static __typename = 'InvoiceItem';
    id: number = -1;
    supplierId: number;
    itemNumber: string;
    description: string;
    price: string;
    VAT: number;
    rebate: number;
    discount: number;
    quantityUnit: string;
    supplier: Partner;

    getID() {
        return this.id;
    }

    toString(): string {
        return this.description;
    }

    getEntityIcon(): string {
        return 'mdi-cart-outline';

    }

    getDetailUrl() {
        return InvoiceItem.getBaseURL() + '/katalog?option=preisentwicklung&lieferant=' + this.supplierId + '&artikel_nr=' + this.itemNumber;
    }
}
